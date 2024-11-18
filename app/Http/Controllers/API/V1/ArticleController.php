<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArticleResource;
use App\Models\Article;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function index(): ResourceCollection
    {
        $articles = Cache::tags('articles')
            ->remember($this->getKeyFromFilters(), now()->addMinutes(15), function () {
                return Article::query()
                    ->when(request('date'), fn($query, $date) => $query->whereDate('published_at', $date))
                    ->when(request('category'), fn($query, $category) => $query->where('category', $category))
                    ->when(request('author'), fn($query, $author) => $query->where('author', $author))
                    ->when(request('source'), fn($query, $source) => $query->where('source', $source))
                    ->when(request('keyword'), fn($query, $keyword) => $query->where('title', 'like', "%$keyword%"))
                    ->simplePaginate();
            });

        return ArticleResource::collection($articles)->additional([
            'message' => 'Articles fetched successfully',
        ]);
    }

    public function show(int $id): JsonResponse
    {
        /** @var Article $article */
        $article = Article::find($id);

        if (!$article) {
            return $this->respondError(message: 'Article not found', status: 404);
        }

        return $this->respondWithSuccess(
            message: 'Article fetched successfully', data: new ArticleResource($article)
        );
    }

    protected function getKeyFromFilters(): string
    {
        return "articles:" . implode('_', request()->query());
    }
}
