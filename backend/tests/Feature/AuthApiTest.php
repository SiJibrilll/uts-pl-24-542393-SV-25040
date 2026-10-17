<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('user dapat login dengan kredensial valid dan menerima token sanctum', function () {
    $user = User::factory()->create([
        'email' => 'admin@bookstore.test',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@bookstore.test',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'access_token',
            'token_type',
            'user' => ['id', 'name', 'email'],
        ])
        ->assertJson([
            'success' => true,
            'token_type' => 'Bearer',
        ]);
});

test('login dengan kata sandi salah mengembalikan status 422', function () {
    $user = User::factory()->create([
        'email' => 'admin@bookstore.test',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@bookstore.test',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ])
        ->assertJsonStructure(['errors' => ['email']]);
});

test('pengguna terautentikasi dapat melihat profilnya di /api/user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
});

test('pengguna terautentikasi dapat logout dan mencabut token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test_token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Berhasil keluar (logout) dan token telah dicabut.',
        ]);

    expect($user->tokens()->count())->toBe(0);
});

test('mengakses endpoint tanpa token mengembalikan status 401 unauthenticated', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});
