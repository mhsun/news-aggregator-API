<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can set preferences', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/v1/preferences', [
        'sources' => ['BBC News', 'The Guardian'],
        'categories' => ['Technology', 'Health'],
        'authors' => ['John Doe'],
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Preferences updated successfully']);
});

test('user can retrieve preferences', function () {
    $user = User::factory()->create([
        'preferences' => [
            'sources' => ['BBC News'],
            'categories' => ['Technology'],
            'authors' => ['John Doe'],
        ],
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/preferences');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Preferences fetched successfully',
            'data' => [
                'sources' => ['BBC News'],
                'categories' => ['Technology'],
                'authors' => ['John Doe'],
            ],
        ]);
});

test('user can get personalized feed based on preferences', function () {
    $user = User::factory()->create([
        'preferences' => [
            'sources' => ['BBC News'],
            'categories' => ['Technology'],
        ],
    ]);

    Article::factory()->create(['source' => 'BBC News', 'category' => 'Technology']);
    Article::factory()->create(['source' => 'The Guardian', 'category' => 'Health']);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/personalized-feed');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data.data');
});
