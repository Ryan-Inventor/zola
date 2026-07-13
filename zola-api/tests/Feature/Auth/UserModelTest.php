<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

it('has the exact columns from the Zola schema', function () {
    expect(Schema::hasColumns('users', [
        'id', 'name', 'phone', 'email', 'password', 'role', 'status', 'created_at', 'updated_at',
    ]))->toBeTrue();
});

it('casts role and status to enums', function () {
    User::factory()->create([
        'phone' => '671234567',
        'role' => UserRole::Superviseur,
        'status' => UserStatus::Pending,
    ]);

    $user = User::query()->where('phone', '671234567')->firstOrFail();

    expect($user->role)->toBeInstanceOf(UserRole::class)
        ->and($user->role)->toBe(UserRole::Superviseur)
        ->and($user->status)->toBeInstanceOf(UserStatus::class)
        ->and($user->status)->toBe(UserStatus::Pending);
});

it('defaults status to pending at the database level', function () {
    $user = User::create([
        'name' => 'Sans Statut',
        'phone' => '690000099',
        'password' => 'secret123',
        'role' => UserRole::Owner,
    ]);

    expect($user->fresh()->status)->toBe(UserStatus::Pending);
});

it('hashes the password automatically', function () {
    $user = User::factory()->create(['password' => 'secret123']);

    expect($user->password)->not->toBe('secret123')
        ->and(Hash::check('secret123', $user->password))->toBeTrue();
});

it('allows a nullable email but enforces unique phone', function () {
    User::factory()->create(['phone' => '677777777', 'email' => null]);

    expect(fn () => User::factory()->create(['phone' => '677777777']))
        ->toThrow(Illuminate\Database\QueryException::class);
});

it('exposes Sanctum token creation', function () {
    $user = User::factory()->create();

    $token = $user->createToken('test');

    expect($token->plainTextToken)->toBeString()->not->toBeEmpty();
});
