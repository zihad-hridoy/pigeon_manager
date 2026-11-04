<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PigeonMetadata;
use App\Models\RingGeneratorConfig;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@loft.test'],
            [
                'name' => 'Admin Breeder',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'loft_name' => 'Main Breeding Loft',
                'phone' => '1234567890',
                'bird_limit' => 500,
                'is_active' => true,
            ]
        );

        // 2. Default Member User
        User::firstOrCreate(
            ['email' => 'member@loft.test'],
            [
                'name' => 'Test Member',
                'password' => Hash::make('password'),
                'role' => 'member',
                'loft_name' => 'Sky High Loft',
                'phone' => '0987654321',
                'bird_limit' => 50,
                'is_active' => true,
            ]
        );

        // 3. Strains & Breeds Metadata
        $breeds = [
            'Janssen',
            'Gaby Vandenabeele',
            'Van Loon',
            'Meulemans',
            'Sion',
            'Aarden',
            'Stichelbaut',
            'Koopman',
        ];

        foreach ($breeds as $breed) {
            PigeonMetadata::firstOrCreate([
                'type' => 'strain_breed',
                'item_value' => $breed,
            ]);
        }

        // 4. Color Metadata
        $colors = [
            'Blue Bar',
            'Blue Chequer',
            'Dark Chequer',
            'Red Chequer',
            'Mealy',
            'Pied',
            'Grizzle',
            'White',
            'Black',
        ];

        foreach ($colors as $color) {
            PigeonMetadata::firstOrCreate([
                'type' => 'color',
                'item_value' => $color,
            ]);
        }

        // 5. Status Tags Metadata
        $statuses = [
            'Active',
            'Breeding',
            'Racing',
            'Quarantined',
            'Sold',
            'Lost',
            'Deceased',
        ];

        foreach ($statuses as $status) {
            PigeonMetadata::firstOrCreate([
                'type' => 'status_tag',
                'item_value' => $status,
            ]);
        }

        // 6. Ring Generator Default Configuration
        RingGeneratorConfig::firstOrCreate(
            ['owner_id' => $admin->id],
            [
                'prefix' => 'PIG-',
                'year_format' => '2026-',
                'last_sequence_number' => 1000,
            ]
        );
    }
}
