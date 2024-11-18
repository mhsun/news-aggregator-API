<?php

namespace App\Services\News;

use App\Contracts\NewsProviderInterface;
use App\Models\Article;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class NewsApiOrgService implements NewsProviderInterface
{
    protected string $url;

    public function __construct()
    {
        $this->url = config('services.newsapiorg.v1.url');
    }

    /**
     * Fetch articles from the news provider.
     *
     * @throws ConnectionException
     */
    public function fetchArticles(string $query): array
    {
        $params = [
            'q' => $query,
            'pageSize' => config('services.newsapiorg.v1.page_size'),
            'apiKey' => config('services.newsapiorg.api_key'),
            'from' => today()->subDay()->toDateString(),
            'to' => today()->toDateString(),
        ];

        return $this->fetchDataFromAPI($params);
    }

    /**
     * Fetch data from the API.
     *
     * @throws ConnectionException
     */
    protected function fetchDataFromAPI($queryParams): array
    {
        $initialResponse = Http::get($this->url, $queryParams);

        if ($initialResponse->failed()) {
            throw new \RuntimeException('Failed to fetch the initial page: '.$initialResponse->body());
        }

        $data = $initialResponse->json();

        $allData = $data['articles'];

        $totalPages = ceil($data['totalResults'] / $queryParams['pageSize']);

        $nextPagesData = [];

        for ($i = 2; $i <= $totalPages; $i++) {
            $queryParams['page'] = $i;

            $response = Http::retry(3, 3000)->get($this->url, $queryParams);

            if ($response->ok()) {
                $nextPagesData = collect($response->json('articles'))->merge($nextPagesData);
            } else {
                break;
            }
        }

        return collect($allData)->merge($nextPagesData)->toArray();
    }

    /**
     * Format the response from the news provider.
     */
    public function format(array $response): array
    {
        return collect($response)->map(function ($article) {
            return [
                'title' => $article['title'],
                'content' => $article['content'],
                'author' => $article['author'] ?? 'Unknown',
                'external_url' => $article['url'],
                'source' => $article['source']['name'] ?? 'News API Org.',
                'category' => $article['category'] ?? 'General',
                'published_at' => Carbon::parse($article['publishedAt'])->toDateTimeString(),
            ];
        })
            ->toArray();
    }

    /**
     * Save the article to the database.
     */
    public function save(array $articles): void
    {
        Article::upsert(
            $articles,
            ['title', 'author', 'category'],
            ['title', 'content', 'author', 'external_url', 'source', 'category', 'published_at']
        );
    }
}
