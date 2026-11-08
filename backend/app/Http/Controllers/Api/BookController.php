<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $books = Book::orderBy('id', 'desc')->get();
        } catch (QueryException $e) {
            Log::error('Gagal mengambil daftar buku dari basis data', [
                'who' => request()->user()?->id ?? 'guest',
                'action' => 'list_books',
                'service' => 'database',
                'reason' => 'Koneksi atau kueri basis data mengalami gangguan saat mengambil katalog',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dari basis data.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar buku berhasil diambil.',
            'total' => $books->count(),
            'data' => $books,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'author' => ['required', 'string', 'max:255'],
                'isbn' => ['required', 'string', 'max:50', 'unique:books,isbn'],
                'published_year' => ['required', 'integer', 'min:1500', 'max:2100'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:0'],
                'description' => ['nullable', 'string'],
            ]);
        } catch (ValidationException $e) {
            Log::warning('Validasi data buku gagal saat penambahan buku', [
                'who' => $request->user()?->id ?? 'guest',
                'action' => 'store_book',
                'reason' => 'Payload permintaan tidak memenuhi aturan validasi skema data',
                'failed_fields' => array_keys($e->errors()),
            ]);

            throw $e;
        }

        try {
            $book = Book::create($validated);
        } catch (QueryException $e) {
            Log::error('Gagal menghubungi basis data saat menyimpan buku', [
                'who' => $request->user()?->id ?? 'guest',
                'action' => 'store_book',
                'service' => 'database',
                'reason' => 'Koneksi ke host basis data terputus atau terjadi galat kueri',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi basis data.',
            ], 500);
        }

        Log::info('Buku baru berhasil disimpan ke dalam katalog', [
            'who' => $request->user()?->id ?? 'system',
            'action' => 'store_book',
            'book_id' => $book->id,
            'title' => $book->title,
            'isbn' => $book->isbn,
            'reason' => 'Penambahan koleksi buku baru berhasil diverifikasi dan disimpan ke basis data',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Buku baru berhasil ditambahkan.',
            'data' => $book,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $book = Book::find($id);
        } catch (QueryException $e) {
            Log::error('Gagal mengambil detail buku dari basis data', [
                'who' => request()->user()?->id ?? 'guest',
                'action' => 'show_book',
                'book_id' => $id,
                'service' => 'database',
                'reason' => 'Koneksi basis data bermasalah saat pencarian buku',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi basis data.',
            ], 500);
        }

        if (! $book) {
            return response()->json([
                'success' => false,
                'message' => "Buku dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $book,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'success' => false,
                'message' => "Buku dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        try {
            $validated = $request->validate([
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'author' => ['sometimes', 'required', 'string', 'max:255'],
                'isbn' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('books')->ignore($book->id)],
                'published_year' => ['sometimes', 'required', 'integer', 'min:1500', 'max:2100'],
                'price' => ['sometimes', 'required', 'numeric', 'min:0'],
                'stock' => ['sometimes', 'required', 'integer', 'min:0'],
                'description' => ['nullable', 'string'],
            ]);
        } catch (ValidationException $e) {
            Log::warning('Validasi data buku gagal saat pembaruan buku', [
                'who' => $request->user()?->id ?? 'guest',
                'action' => 'update_book',
                'book_id' => $book->id,
                'reason' => 'Atribut pembaruan buku tidak memenuhi aturan validasi',
                'failed_fields' => array_keys($e->errors()),
            ]);

            throw $e;
        }

        try {
            $book->update($validated);
        } catch (QueryException $e) {
            Log::error('Gagal memperbarui data buku pada basis data', [
                'who' => $request->user()?->id ?? 'guest',
                'action' => 'update_book',
                'book_id' => $book->id,
                'service' => 'database',
                'reason' => 'Galat kueri basis data saat operasi update',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memperbarui basis data.',
            ], 500);
        }

        Log::info('Data buku berhasil diperbarui dalam katalog', [
            'who' => $request->user()?->id ?? 'system',
            'action' => 'update_book',
            'book_id' => $book->id,
            'title' => $book->title,
            'reason' => 'Pembaruan data buku berhasil disimpan ke basis data',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data buku berhasil diperbarui.',
            'data' => $book,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'success' => false,
                'message' => "Buku dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        try {
            $book->delete();
        } catch (QueryException $e) {
            Log::error('Gagal menghapus buku dari basis data', [
                'who' => request()->user()?->id ?? 'guest',
                'action' => 'delete_book',
                'book_id' => $id,
                'service' => 'database',
                'reason' => 'Galat kueri basis data saat operasi delete',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghapus data buku.',
            ], 500);
        }

        Log::info('Buku berhasil dihapus dari katalog', [
            'who' => request()->user()?->id ?? 'system',
            'action' => 'delete_book',
            'book_id' => $id,
            'title' => $book->title,
            'reason' => 'Penghapusan data buku atas permintaan pengguna terautentikasi',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dihapus dari sistem.',
        ], 200);
    }
}
