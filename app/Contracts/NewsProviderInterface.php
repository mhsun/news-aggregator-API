<?php

namespace App\Contracts;

interface NewsProviderInterface
{
    /**
     * Fetch articles from the news provider.
     */
    public function fetchArticles(string $query): array;

    /**
     * Format the response from the news provider.
     */
    public function format(array $response): array;

    /**
     * Save the article to the database.
     */
    public function save(array $articles): void;
}
