<?php

namespace App\Console\Commands;

use App\Contracts\NewsProviderInterface;
use App\Services\News\NewsApiOrgService;
use App\Services\News\TheGuardianService;
use App\Services\News\TheNYTimesService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\ConnectionException;

/** @codeCoverageIgnore */
class FetchArticles extends Command
{
    protected $signature = 'articles:fetch {--keyword=technology}';

    protected $description = 'Fetch articles from external news APIs';

    /**
     * @throws ConnectionException
     * @throws BindingResolutionException
     */
    public function handle()
    {
        $query = $this->option('keyword');

        $this->info('Fetching articles...');

        $providers = app()->make(NewsProviderInterface::class);

        /** @var NewsApiOrgService|TheGuardianService|TheNYTimesService $provider */
        foreach ($providers as $provider) {
            $this->info('Fetching from '.$provider::class);
            $articles = $provider->fetchArticles($query);
            $this->info('Total '.get_class($provider).' articles fetched: '.count($articles));

            $formattedArticles = $provider->format($articles);
            $provider->save($formattedArticles);
        }

        $this->info('Articles fetched and stored successfully.');
    }
}
