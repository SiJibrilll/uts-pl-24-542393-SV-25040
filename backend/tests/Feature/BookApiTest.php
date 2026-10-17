<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('pengguna terautentikasi dapat melihat daftar seluruh buku', function () {
    Book::factory()->count(3)->create();

    $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/books');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'total' => 3,
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'author', 'isbn', 'published_year', 'price', 'stock'],
            ],
        ]);
});

test('pengguna terautentikasi dapat menambah buku baru dan mendapat status 201', function () {
    $payload = [
        'title' => 'Refactoring: Improving the Design of Existing Code',
        'author' => 'Martin Fowler',
        'isbn' => '978-0134757599',
        'published_year' => 2018,
        'price' => 190000.00,
        'stock' => 12,
        'description' => 'Buku fundamental mengenai teknik refactoring perangkat lunak.',
    ];

    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/books', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Buku baru berhasil ditambahkan.',
            'data' => [
                'title' => 'Refactoring: Improving the Design of Existing Code',
                'isbn' => '978-0134757599',
            ],
        ]);

    $this->assertDatabaseHas('books', [
        'isbn' => '978-0134757599',
    ]);
});

test('menambah buku dengan field tidak lengkap mengembalikan status 422', function () {
    $payload = [
        'title' => 'Judul Tanpa Pengarang',
        // 'author' missing
        // 'isbn' missing
    ];

    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/books', $payload);

    $response->assertStatus(422)
        ->assertJsonStructure(['errors' => ['author', 'isbn', 'published_year', 'price', 'stock']]);
});

test('menambah buku dengan ISBN duplikat mengembalikan status 422', function () {
    Book::factory()->create(['isbn' => '978-1111111111']);

    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/books', [
        'title' => 'Buku Duplikat',
        'author' => 'Penulis',
        'isbn' => '978-1111111111',
        'published_year' => 2024,
        'price' => 100000,
        'stock' => 5,
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['errors' => ['isbn']]);
});

test('pengguna terautentikasi dapat melihat detail satu buku spesifik', function () {
    $book = Book::factory()->create();

    $response = $this->actingAs($this->user, 'sanctum')->getJson("/api/books/{$book->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $book->id,
                'title' => $book->title,
            ],
        ]);
});

test('pengguna terautentikasi dapat memperbarui data buku', function () {
    $book = Book::factory()->create([
        'title' => 'Judul Lama',
        'price' => 100000,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/books/{$book->id}", [
        'title' => 'Judul Baru yang Diedit',
        'author' => $book->author,
        'isbn' => $book->isbn,
        'published_year' => $book->published_year,
        'price' => 125000,
        'stock' => 10,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Data buku berhasil diperbarui.',
            'data' => [
                'title' => 'Judul Baru yang Diedit',
            ],
        ]);

    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'title' => 'Judul Baru yang Diedit',
    ]);
});

test('pengguna terautentikasi dapat menghapus buku', function () {
    $book = Book::factory()->create();

    $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/books/{$book->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Buku berhasil dihapus dari sistem.',
        ]);

    $this->assertDatabaseMissing('books', [
        'id' => $book->id,
    ]);
});

test('mengakses endpoint books tanpa token autentikasi mengembalikan status 401', function () {
    $response = $this->getJson('/api/books');

    $response->assertStatus(401);
});
