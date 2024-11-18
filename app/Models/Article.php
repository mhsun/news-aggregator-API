<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'author',
        'source',
        'category',
        'external_url',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
