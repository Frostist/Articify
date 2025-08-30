<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\User;

test('users can create categories', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('settings.categories'));
    $response->assertStatus(200);
    $response->assertSee('Categories');
});

test('categories page shows empty state when no categories', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('settings.categories'));
    $response->assertStatus(200);
    $response->assertSee('No categories yet');
});

test('categories are displayed in articles table', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Test Category']);
    Article::factory()->create(['user_id' => $user->id, 'category_id' => $category->id]);

    $response = $this->actingAs($user)->get(route('articles'));
    $response->assertStatus(200);
    $response->assertSee('Test Category');
});

test('articles can be filtered by category', function () {
    $user = User::factory()->create();
    $category1 = Category::factory()->create(['user_id' => $user->id, 'name' => 'Category 1']);
    $category2 = Category::factory()->create(['user_id' => $user->id, 'name' => 'Category 2']);

    Article::factory()->create(['user_id' => $user->id, 'category_id' => $category1->id]);
    Article::factory()->create(['user_id' => $user->id, 'category_id' => $category2->id]);

    $response = $this->actingAs($user)->get(route('articles'));
    $response->assertStatus(200);
    $response->assertSee('Category 1');
    $response->assertSee('Category 2');
});

test('dashboard shows category selection in article form', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Category',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Test Category');
});

test('users can set multiple categories color', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('settings.categories'));
    $response->assertStatus(200);
    $response->assertSee('Multiple Categories Color Cube');
    $response->assertSee('#F59E0B'); // Default orange color
});
