<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seed initial de développement.
 *
 * AUTH-01 remplacera ce contenu par les utilisateurs Zola (admin / owner / superviseur)
 * avec phone, role, status — voir docs/zola-schema-db.md et docs/PROMPTS.md AUTH-01.
 */
class InitialSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->where('email', 'dev@zola.test')->exists()) {
            return;
        }

        User::factory()->create([
            'name' => 'Zola Dev',
            'email' => 'dev@zola.test',
            'password' => 'password',
        ]);

        // AUTH-01 — à activer après migration User Zola :
        // User::create([
        //     'name' => 'Admin Zola',
        //     'phone' => '670000001',
        //     'email' => 'admin@zola.test',
        //     'password' => Hash::make('password'),
        //     'role' => UserRole::Admin,
        //     'status' => UserStatus::Active,
        // ]);
    }
}
