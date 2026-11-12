<?php

namespace App\Http\Controllers;

use App\Models\Pigeon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('members.show', $user->id);
        }

        $members = User::withCount([
            'pigeons',
            'pigeons as active_pigeons_count' => fn ($q) => $q->where('status', '!=', 'deceased'),
        ])
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $stats = [
            'total_members' => $members->count(),
            'active_members' => $members->where('is_active', true)->count(),
            'total_birds' => $members->sum('pigeons_count'),
        ];

        return view('members.index', compact('members', 'stats'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403, 'Admin access required.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:member,admin',
            'loft_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'profile_pic' => 'nullable|image|max:2048',
        ]);

        $profilePicPath = null;
        if ($request->hasFile('profile_pic')) {
            $profilePicPath = $request->file('profile_pic')->store('profiles', 'public');
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'loft_name' => $validated['loft_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'profile_pic_path' => $profilePicPath,
            'is_active' => true,
        ]);

        return redirect()->route('members.index')->with('success', 'Member account created.');
    }

    public function show(User $member)
    {
        $currentUser = Auth::user();
        $isAdmin = $currentUser->role === 'admin';

        abort_unless($isAdmin || $currentUser->id === $member->id, 403, 'Unauthorized profile access.');

        $pigeons = Pigeon::where('owner_id', $member->id)
            ->orderBy('ring_number')
            ->paginate(20);

        $allMemberPigeons = Pigeon::where('owner_id', $member->id)
            ->select('id', 'status', 'gender', 'strain_breed')
            ->get();

        $loftStats = [
            'total_birds' => $allMemberPigeons->count(),
            'active_birds' => $allMemberPigeons->where('status', '!=', 'deceased')->count(),
            'cocks' => $allMemberPigeons->where('gender', 'male')->count(),
            'hens' => $allMemberPigeons->where('gender', 'female')->count(),
            'strains_count' => $allMemberPigeons->pluck('strain_breed')->filter()->unique()->count(),
        ];

        return view('members.show', compact('member', 'pigeons', 'loftStats', 'isAdmin'));
    }

    public function update(Request $request, User $member)
    {
        $currentUser = Auth::user();
        $isAdmin = $currentUser->role === 'admin';

        abort_unless($isAdmin || $currentUser->id === $member->id, 403, 'Unauthorized update.');

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member->id)],
            'loft_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'profile_pic' => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:6',
        ];

        if ($isAdmin) {
            $rules['role'] = 'required|in:member,admin';
            $rules['is_active'] = 'nullable|boolean';
        }

        $validated = $request->validate($rules);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'loft_name' => $validated['loft_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ];

        if ($request->hasFile('profile_pic')) {
            if ($member->profile_pic_path) {
                Storage::disk('public')->delete($member->profile_pic_path);
            }
            $updateData['profile_pic_path'] = $request->file('profile_pic')->store('profiles', 'public');
        }

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if ($isAdmin) {
            $updateData['role'] = $validated['role'];
            $updateData['is_active'] = $request->boolean('is_active');
        }

        $member->update($updateData);

        return redirect()->route('members.show', $member)->with('success', 'Profile updated successfully.');
    }
}
