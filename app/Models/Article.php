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

    public static string $cacheTag = 'articles';

    public function scopeFilterByPreferences($query, $preferences)
    {
        if ($preferences->preferred_sources) {
            $query->whereIn('source', $preferences->preferred_sources);
        }

        if ($preferences->preferred_categories) {
            $query->orWhereIn('category', $preferences->preferred_categories);
        }

        if ($preferences->preferred_authors) {
            $query->orWhereIn('author', $preferences->preferred_authors);
        }

        return $query;
    }
}
