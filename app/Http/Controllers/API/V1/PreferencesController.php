<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PreferenceSetRequest;
use App\Http\Resources\V1\ArticleResource;
use App\Http\Resources\V1\UserPreferenceResource;
use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PreferencesController extends Controller
{
    /**
     * Set user preferences
     *
     * @group Preferences
     *
     * @authenticated
     *
     * @bodyParam preferred_sources array required The preferred sources of the user. Example: ["The Guardian", "BBC News"]
     * @bodyParam preferred_categories array required The preferred categories of the user. Example: ["technology", "business"]
     * @bodyParam preferred_authors array required The preferred authors of the user. Example: ["John Doe", "Jane Doe"]
     *
     * @response 200 {
     *  "message": "Preferences updated successfully",
     *  "data": {
     *      "preferred_sources": ["The Guardian", "BBC News"],
     *      "preferred_categories": ["technology", "business"],
     *      "preferred_authors": ["John Doe", "Jane Doe"]
     *  }
     * }
     *
     * @param PreferenceSetRequest $request
     * @return JsonResponse
     */
    public function setPreferences(PreferenceSetRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $preferences = $user->preferences()->updateOrCreate([], [
            'preferred_sources' => $request->preferred_sources,
            'preferred_categories' => $request->preferred_categories,
            'preferred_authors' => $request->preferred_authors,
        ]);

        Cache::forget("user_{$user->id}_personalized_feed");

        return $this->respondWithSuccess(
            'Preferences updated successfully', data: new UserPreferenceResource($preferences)
        );
    }

    /**
     * Fetch user preferences
     *
     * @group Preferences
     *
     * @authenticated
     *
     * @response 200 {
     *  "message": "Preferences fetched successfully",
     *  "data": {
     *      "preferred_sources": ["The Guardian", "BBC News"],
     *      "preferred_categories": ["technology", "business"],
     *      "preferred_authors": ["John Doe", "Jane Doe"]
     *  }
     * }
     *
     * @response 404 {
     *  "message": "Preferences not set"
     * }
     *
     * @return JsonResponse
     */
    public function getPreferences(): JsonResponse
    {
        $preferences = UserPreference::where('user_id', auth()->id())->first();

        if (! $preferences) {
            return $this->respondError('Preferences not set', 404);
        }

        return $this->respondWithSuccess(
            message: 'Preferences fetched successfully', data: new UserPreferenceResource($preferences)
        );
    }

    /**
     * Fetch personalized feed
     *
     * @group Preferences
     *
     * @authenticated
     *
     * @response 200 {
     *  "message": "Personalized feed fetched successfully",
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
     *  "links": {
     *      ......
     *  },
     * "meta": {
     *     ......
     * }
     * }
     *
     * @response 404 {
     *  "message": "Preferences not set"
     * }
     *
     * @return JsonResponse
     */
    public function personalizedFeed(Request $request): ResourceCollection|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $cacheKey = "user_{$user->id}_personalized_feed";

        $articles = Cache::remember($cacheKey, now()->addHour(), function () use ($user) {
            /** @var UserPreference $preferences */
            if (! $preferences = $user->preferences) {
                return $this->respondError('Preferences not set', 404);
            }

            return Article::query()
                ->filterByPreferences($preferences)
                ->orderBy('published_at', 'desc')
                ->simplePaginate();
        });

        return ArticleResource::collection($articles)->additional([
            'message' => 'Personalized feed fetched successfully',
        ]);
    }
}
