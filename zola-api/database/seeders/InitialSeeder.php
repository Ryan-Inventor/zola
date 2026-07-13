<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed initial de développement — utilisateurs Zola (un par rôle).
 * Idempotent : ne recrée pas les comptes existants.
 */
class InitialSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Zola',
                'phone' => '670000001',
                'email' => 'admin@zola.test',
                'role' => UserRole::Admin,
                'status' => UserStatus::Active,
            ],
            [
                'name' => 'Owner Zola',
                'phone' => '690000001',
                'email' => 'owner@zola.test',
                'role' => UserRole::Owner,
                'status' => UserStatus::Active,
            ],
            [
                'name' => 'Superviseur Zola',
                'phone' => '650000001',
                'email' => 'superviseur@zola.test',
                'role' => UserRole::Superviseur,
                'status' => UserStatus::Active,
            ],
        ];

        foreach ($users as $user) {
            User::query()->firstOrCreate(
                ['phone' => $user['phone']],
                [...$user, 'password' => Hash::make('password')],
            );
        }
    }
}
