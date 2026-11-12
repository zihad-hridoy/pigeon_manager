<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pigeon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $members = User::withCount('pigeons')->orderBy('name')->get();
        $stats = [
            'total_members' => $members->count(),
            'active_members' => $members->where('is_active', true)->count(),
            'total_birds' => Pigeon::count(),
        ];

        return view('members.index', compact('members', 'stats'));
    }

    public function show(User $user)
    {
        $user->load(['pigeons' => function ($query) {
            $query->latest();
        }]);

        $totalBirds = $user->pigeons->count();
        $activeLines = $user->pigeons
            ->where('status', 'active')
            ->pluck('strain_breed')
            ->filter()
            ->unique()
            ->values();

        return view('members.show', compact('user', 'totalBirds', 'activeLines'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('members.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'loft_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'profile_pic' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_pic')) {
            if ($user->profile_pic_path) {
                Storage::disk('public')->delete($user->profile_pic_path);
            }
            $validated['profile_pic_path'] = $request->file('profile_pic')->store('profiles', 'public');
        }

        $user->update([
            'name' => $validated['name'],
            'loft_name' => $validated['loft_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'profile_pic_path' => $validated['profile_pic_path'] ?? $user->profile_pic_path,
        ]);

        return redirect()->route('members.show', $user)->with('success', 'Profile updated successfully.');
    }
}
