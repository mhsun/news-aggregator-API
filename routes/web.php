<?php

use App\Http\Resources\V1\ArticleResource;
use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   dd("articles:".implode('_', request()->query()));
});
