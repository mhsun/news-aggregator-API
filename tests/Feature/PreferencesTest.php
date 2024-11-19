<?php

use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can set preferences', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/v1/preferences', [
        'preferred_sources' => ['BBC News', 'The Guardian'],
        'preferred_categories' => ['Technology', 'Health'],
        'preferred_authors' => ['John Doe'],
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Preferences updated successfully',
            'data' => [
                'preferred_sources' => ['BBC News', 'The Guardian'],
                'preferred_categories' => ['Technology', 'Health'],
                'preferred_authors' => ['John Doe'],
            ],
        ]);
});

test('user can retrieve preferences', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $preferences = UserPreference::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->getJson('/api/v1/preferences');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Preferences fetched successfully',
            'data' => [
                'preferred_sources' => $preferences->preferred_sources,
                'preferred_categories' => $preferences->preferred_categories,
                'preferred_authors' => $preferences->preferred_authors,
            ],
        ]);
});

test('user can get personalized feed based on preferences', function () {
    $user = User::factory()->create();

    $preferences = UserPreference::factory()->create([
        'user_id' => $user->id,
        'preferred_sources' => ['BBC News'],
    ]);

    Article::factory()->create(['source' => 'BBC News', 'category' => 'Technology']);
    Article::factory()->create(['source' => 'The Guardian', 'category' => 'Health']);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/personalized-feed');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'author', 'source', 'category', 'published_at'],
            ],
            'message',
        ]);
});
