<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('articles'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the articles page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('articles'));
    $response->assertStatus(200);
    $response->assertSee('Articles');
});

test('articles page displays articles for authenticated user', function () {
    $user = User::factory()->create();
    $articles = Article::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('articles'));
    $response->assertStatus(200);
    $response->assertSee($articles->first()->title);
    $response->assertSee($articles->last()->title);
});

test('articles page shows correct stats', function () {
    $user = User::factory()->create();
    Article::factory()->count(5)->create(['user_id' => $user->id]);
    Article::factory()->create([
        'user_id' => $user->id,
        'title' => 'Missed Reading Day',
        'is_missed_day' => true,
    ]);

    $response = $this->actingAs($user)->get(route('articles'));
    $response->assertStatus(200);
    $response->assertSee('5'); // Articles read
    $response->assertSee('1'); // Missed days
});

test('articles page shows empty state when no articles', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('articles'));
    $response->assertStatus(200);
    $response->assertSee('No articles found');
    $response->assertSee('Get started by adding your first article from the dashboard');
});
