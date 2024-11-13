<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreferencesController extends Controller
{
    public function setPreferences(Request $request): JsonResponse
    {
        $request->validate([
            'sources' => 'array',
            'categories' => 'array',
            'authors' => 'array',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->preferences = [
            'sources' => $request->input('sources', []),
            'categories' => $request->input('categories', []),
            'authors' => $request->input('authors', []),
        ];
        $user->save();

        return $this->respondWithSuccess('Preferences updated successfully');
    }

    public function getPreferences(): JsonResponse
    {
        return $this->respondWithSuccess(
            message: 'Preferences fetched successfully', data: Auth::user()->preferences
        );
    }

    // Get personalized news feed
    public function personalizedFeed(): JsonResponse
    {
        $preferences = Auth::user()->preferences ?? [];

        $query = Article::query();

        if (!empty($preferences['sources'])) {
            $query->whereIn('source', $preferences['sources']);
        }

        if (!empty($preferences['categories'])) {
            $query->whereIn('category', $preferences['categories']);
        }

        if (!empty($preferences['authors'])) {
            $query->whereIn('author', $preferences['authors']);
        }

        return $this->respondWithSuccess(
            message: 'Personalized feed fetched successfully', data: $query->simplePaginate(10)
        );
    }
}
