<?php

use App\Enums\UserStatus;
use App\Models\User;

const LOGIN_GENERIC_ERROR = 'Identifiants incorrects. Vérifiez votre email/téléphone et votre mot de passe.';

function loginPayload(string $identifier, string $password = 'password'): array
{
    return [
        'identifier' => $identifier,
        'password' => $password,
    ];
}

function postLogin(array $payload)
{
    return test()->postJson('/api/v1/auth/login', $payload);
}

it('returns a token when logging in with email', function () {
    $user = User::factory()->create([
        'email' => 'owner@zola.test',
        'phone' => '670000010',
        'status' => UserStatus::Active,
    ]);

    $response = postLogin(loginPayload('owner@zola.test'));

    $response->assertOk()
        ->assertJsonPath('data.token', fn ($token) => is_string($token) && $token !== '')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', 'owner@zola.test')
        ->assertJsonPath('data.user.phone', '670000010')
        ->assertJsonPath('data.user.status', 'active');

    expect($user->tokens()->count())->toBe(1);
});

it('returns a token when logging in with phone', function () {
    $user = User::factory()->create([
        'email' => 'phone-login@zola.test',
        'phone' => '671111111',
        'status' => UserStatus::Active,
    ]);

    $response = postLogin(loginPayload('671111111'));

    $response->assertOk()
        ->assertJsonPath('data.token', fn ($token) => is_string($token) && $token !== '')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.phone', '671111111')
        ->assertJsonPath('data.user.status', 'active');

    expect($user->tokens()->count())->toBe(1);
});

it('returns 401 with a generic message when the password is incorrect', function () {
    $user = User::factory()->create([
        'email' => 'wrong-pass@zola.test',
        'phone' => '672222222',
        'status' => UserStatus::Active,
    ]);

    $response = postLogin(loginPayload('wrong-pass@zola.test', 'bad-password'));

    $response->assertUnauthorized()
        ->assertJsonPath('error', 'UNAUTHORIZED')
        ->assertJsonPath('message', LOGIN_GENERIC_ERROR);

    expect($user->tokens()->count())->toBe(0);
});

it('returns 401 with the same generic message when the identifier does not exist', function () {
    $response = postLogin(loginPayload('ghost@zola.test'));

    $response->assertUnauthorized()
        ->assertJsonPath('error', 'UNAUTHORIZED')
        ->assertJsonPath('message', LOGIN_GENERIC_ERROR);
});

it('uses the same generic message for wrong password and unknown identifier', function () {
    User::factory()->create([
        'email' => 'same-msg@zola.test',
        'phone' => '673333333',
        'status' => UserStatus::Active,
    ]);

    $wrongPassword = postLogin(loginPayload('same-msg@zola.test', 'bad-password'));
    $unknownIdentifier = postLogin(loginPayload('nobody@zola.test', 'password'));

    expect($wrongPassword->json('message'))
        ->toBe(LOGIN_GENERIC_ERROR)
        ->toBe($unknownIdentifier->json('message'));
});

it('returns 403 for pending accounts without issuing a token', function () {
    $user = User::factory()->pending()->create([
        'email' => 'pending@zola.test',
        'phone' => '674444444',
    ]);

    $response = postLogin(loginPayload('pending@zola.test'));

    $response->assertForbidden()
        ->assertJsonPath('error', 'FORBIDDEN')
        ->assertJsonPath('message', 'Compte en attente d\'activation')
        ->assertJsonMissingPath('data.token');

    expect($user->tokens()->count())->toBe(0);
});

it('returns 403 for suspended accounts without issuing a token', function () {
    $user = User::factory()->suspended()->create([
        'email' => 'suspended@zola.test',
        'phone' => '675555555',
    ]);

    $response = postLogin(loginPayload('suspended@zola.test'));

    $response->assertForbidden()
        ->assertJsonPath('error', 'FORBIDDEN')
        ->assertJsonPath('message', 'Compte suspendu, contactez le support')
        ->assertJsonMissingPath('data.token');

    expect($user->tokens()->count())->toBe(0);
});

it('only issues a token when the account status is active', function () {
    User::factory()->pending()->create([
        'email' => 'pending2@zola.test',
        'phone' => '676666666',
    ]);
    User::factory()->suspended()->create([
        'email' => 'suspended2@zola.test',
        'phone' => '677777777',
    ]);
    $active = User::factory()->create([
        'email' => 'active@zola.test',
        'phone' => '678888888',
        'status' => UserStatus::Active,
    ]);

    postLogin(loginPayload('pending2@zola.test'))->assertForbidden();
    postLogin(loginPayload('suspended2@zola.test'))->assertForbidden();

    $activeResponse = postLogin(loginPayload('active@zola.test'));

    $activeResponse->assertOk()
        ->assertJsonPath('data.user.id', $active->id)
        ->assertJsonPath('data.user.status', 'active');

    expect($active->tokens()->count())->toBe(1)
        ->and(User::query()->where('email', 'pending2@zola.test')->first()->tokens()->count())->toBe(0)
        ->and(User::query()->where('email', 'suspended2@zola.test')->first()->tokens()->count())->toBe(0);
});
