<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(fn () => Sanctum::actingAs(User::factory()->create()));

test('can fetch paginated list of articles', function () {
    Article::factory()->count(15)->create();

    $response = $this->getJson('/api/v1/articles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'author', 'source', 'category', 'published_at'],
            ],
            'message',
        ]);
});

test('can fetch single article by ID', function () {
    $article = Article::factory()->create();

    $response = $this->getJson("/api/v1/articles/{$article->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'content' => $article->content,
            ],
        ]);
});

test('returns 404 if article is not found', function () {
    $response = $this->getJson('/api/v1/articles/99999');

    $response->assertStatus(404)
        ->assertJson(['message' => 'Article not found']);
});

test('can filter articles by keyword', function () {
    Article::factory()->create(['title' => 'Breaking News: Laravel']);
    Article::factory()->create(['title' => 'New PHP release announced']);

    $response = $this->getJson('/api/v1/articles?keyword=Laravel');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can filter articles by date', function () {
    Article::factory()->create(['published_at' => '2024-11-01']);
    Article::factory()->create(['published_at' => '2024-11-10']);

    $response = $this->getJson('/api/v1/articles?date=2024-11-01');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can filter articles by category', function () {
    Article::factory()->create(['category' => 'Technology']);
    Article::factory()->create(['category' => 'Health']);

    $response = $this->getJson('/api/v1/articles?category=Technology');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['category' => 'Technology']);
});

test('can filter articles by source', function () {
    Article::factory()->create(['source' => 'BBC News']);
    Article::factory()->create(['source' => 'The Guardian']);

    $response = $this->getJson('/api/v1/articles?source=BBC News');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['source' => 'BBC News']);
});
