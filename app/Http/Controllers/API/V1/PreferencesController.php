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

    public function getPreferences(): JsonResponse
    {
        $preferences = UserPreference::where('user_id', auth()->id())->first();

        if (!$preferences) {
            return $this->respondError('Preferences not set', 404);
        }

        return $this->respondWithSuccess(
            message: 'Preferences fetched successfully', data: new UserPreferenceResource($preferences)
        );
    }

    public function personalizedFeed(Request $request): ResourceCollection|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $cacheKey = "user_{$user->id}_personalized_feed";

        $articles = Cache::remember($cacheKey, now()->addHour(), function () use ($user) {
            /** @var UserPreference $preferences */
            if (!$preferences = $user->preferences) {
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
