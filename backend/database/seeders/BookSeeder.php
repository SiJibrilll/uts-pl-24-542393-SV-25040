<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Robert C. Martin',
                'isbn' => '978-0132350884',
                'published_year' => 2008,
                'price' => 175000.00,
                'stock' => 25,
                'description' => 'Panduan klasik mengenai prinsip rekayasa perangkat lunak, refactoring, dan penulisan kode berkualitas tinggi.',
            ],
            [
                'title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'author' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'isbn' => '978-0201633610',
                'published_year' => 1994,
                'price' => 210000.00,
                'stock' => 15,
                'description' => 'Buku fundamental Gang of Four (GoF) yang membahas 23 pola desain perangkat lunak berorientasi objek.',
            ],
            [
                'title' => 'Building Microservices: Designing Fine-Grained Systems',
                'author' => 'Sam Newman',
                'isbn' => '978-1492034025',
                'published_year' => 2021,
                'price' => 195000.00,
                'stock' => 30,
                'description' => 'Arsitektur sistem terdistribusi, komunikasi antarlayanan, dan pola kontainerisasi berbasis Docker.',
            ],
            [
                'title' => 'The Pragmatic Programmer: Your Journey to Mastery',
                'author' => 'David Thomas, Andrew Hunt',
                'isbn' => '978-0135957059',
                'published_year' => 2019,
                'price' => 185000.00,
                'stock' => 20,
                'description' => 'Tips praktis dan filosofi pengembangan software modern untuk meningkatkan efektivitas pengembang.',
            ],
            [
                'title' => 'Laravel: Up & Running: A Framework for Building Modern PHP Apps',
                'author' => 'Matt Stauffer',
                'isbn' => '978-1492041214',
                'published_year' => 2023,
                'price' => 165000.00,
                'stock' => 40,
                'description' => 'Panduan komprehensif mendalam mengenai ekosistem framework Laravel, Eloquent ORM, dan RESTful API.',
            ],
        ];

        foreach ($books as $book) {
            Book::firstOrCreate(['isbn' => $book['isbn']], $book);
        }
    }
}
