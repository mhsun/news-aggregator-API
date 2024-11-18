<?php

namespace App\Services\News;

use App\Contracts\NewsProviderInterface;
use App\Models\Article;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TheGuardianService implements NewsProviderInterface
{
    protected string $url;

    public function __construct()
    {
        $this->url = config('services.the_guardian.v1.url');
    }

    /**
     * @throws ConnectionException
     */
    public function fetchArticles(string $query): array
    {
        $params = [
            'q' => $query,
            'page-size' => config('services.the_guardian.v1.page_size'),
            'api-key' => config('services.the_guardian.api_key'),
            'from-date' => today()->subDay()->toDateString(),
        ];

        return $this->fetchDataFromAPI($params);
    }

    /**
     * @throws ConnectionException
     */
    protected function fetchDataFromAPI($queryParams): array
    {
        $initialResponse = Http::get($this->url, $queryParams);

        if ($initialResponse->failed()) {
            throw new \RuntimeException('Failed to fetch the initial page: '.$initialResponse->body());
        }

        $data = $initialResponse->json('response');

        $allData = $data['results'];

        $nextPagesData = [];

        for ($i = 2; $i <= $data['pages']; $i++) {
            $queryParams['page'] = $i;

            $response = Http::retry(3, 3000)->get($this->url, $queryParams);

            if ($response->ok()) {
                $nextPagesData = collect($response->json('response')['results'])->merge($nextPagesData);
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
                'title' => $article['webTitle'],
                'content' => $article['webUrl'],
                'author' => 'The Guardian',
                'external_url' => $article['apiUrl'],
                'source' => 'The Guardian',
                'category' => $article['sectionName'] ?? 'General',
                'published_at' => Carbon::parse($article['webPublicationDate'])->toDateTimeString(),
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
            ['title', 'category'],
            ['title', 'content', 'author', 'external_url', 'source', 'category', 'published_at']
        );

        Cache::tags(Article::$cacheTag)->flush();
    }
}
