<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
});

test('onboarding modal appears for new users', function () {
    $user = User::factory()->create(['has_completed_onboarding' => false]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Welcome to Articify!');
    $response->assertSee('Step 1 of 3');
});

test('onboarding modal does not appear for users who completed onboarding', function () {
    $user = User::factory()->create(['has_completed_onboarding' => true]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertDontSee('Welcome to Articify!');
    $response->assertDontSee('Step 1 of 3');
});