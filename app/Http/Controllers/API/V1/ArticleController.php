<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArticleResource;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    /**
     * Fetch and filter articles
     *
     * @group Articles
     *
     * @authenticated
     *
     * @queryParam page integer The page number. Example: 1
     *
     * @queryParam date string Filter articles by date. Example: 2021-10-10
     * @queryParam category string Filter articles by category. Example: technology
     * @queryParam author string Filter articles by author. Example: John Doe
     * @queryParam source string Filter articles by source. Example: The Guardian
     * @queryParam keyword string Filter articles by keyword. Example: technology
     *
     * @response 200 {
     *  "message": "Articles fetched successfully",
     *  "data": [
     *      {
     *          "id": 1,
     *          "title": "Article title",
     *          "description": "Article description",
     *          "published_at": "2021-10-10T00:00:00.000000Z",
     *          "category": "technology",
     *          "author": "John Doe",
     *          "source": "The Guardian",
     *          "external_url": "A link to external source",
     *          "created_at": "2021-10-10T00:00:00.000000Z",
     *          "updated_at": "2021-10-10T00:00:00.000000Z",
     *          "links": {
     *              "self": "link-to-visit-this-article"
     *          }
     *      }
     *  ],
     * "links": {
     *  ......
     * }
     *
     * "meta": {
     * ......
     * }
     *
     * }
     *
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        $articles = Cache::tags(Article::$cacheTag)
            ->remember($this->getKeyFromFilters(), now()->addHour(), function () {
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

    /**
     * Show an article
     *
     * @group Articles
     *
     * @authenticated
     *
     * @urlParam id required The ID of the article. Example: 1
     *
     * @response 200 {
     *  "message": "Article fetched successfully",
     *  "data": {
     *      "id": 1,
     *      "title": "Article title",
     *      "description": "Article description",
     *      "published_at": "2021-10-10T00:00:00.000000Z",
     *      "category": "technology",
     *      "author": "John Doe",
     *      "source": "The Guardian",
     *      "external_url": "A link to external source",
     *      "created_at": "2021-10-10T00:00:00.000000Z",
     *      "updated_at": "2021-10-10T00:00:00.000000Z",
     *      "links": {
     *           "self": "link-to-visit-this-article"
     *      }
     *  }
     * }
     *
     * @response 404 {
     *  "message": "Article not found"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
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
        return 'articles:' . implode('_', request()->query());
    }
}
