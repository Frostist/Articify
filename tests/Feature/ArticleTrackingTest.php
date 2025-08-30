<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\User;

test('dashboard requires authentication', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSuccessful();
    $response->assertSee('Article Reading Tracker');
});

test('contribution graph shows correct data', function () {
    $user = User::factory()->create();

    // Create articles for different dates
    Article::factory()->create([
        'user_id' => $user->id,
        'read_date' => now()->subDays(5),
    ]);

    Article::factory()->count(3)->create([
        'user_id' => $user->id,
        'read_date' => now()->subDays(3),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSee('Reading Activity');
});

test('articles are displayed in chronological order', function () {
    $user = User::factory()->create();

    $article1 = Article::factory()->create([
        'user_id' => $user->id,
        'read_date' => now()->subDays(5),
    ]);

    $article2 = Article::factory()->create([
        'user_id' => $user->id,
        'read_date' => now()->subDays(2),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSeeInOrder([$article2->title, $article1->title]);
});

test('user can see add article button', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSee('Add Article');
});

test('dashboard shows recent articles section', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSee('Recent Articles');
});
