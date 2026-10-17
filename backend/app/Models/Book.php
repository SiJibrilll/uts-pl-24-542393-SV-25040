<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_year',
        'price',
        'stock',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'published_year' => 'integer',
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }
}
