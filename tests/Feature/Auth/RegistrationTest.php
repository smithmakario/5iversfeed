<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('supplier onboarding screen can be rendered', function () {
    $response = $this->get('/supplier/apply');

    $response->assertStatus(200);
});

test('suppliers can submit onboarding application', function () {
    Storage::fake('public');

    $response = $this->post('/supplier/apply', [
        'company_name' => 'Test Feeds Co',
        'contact_name' => 'Test User',
        'email' => 'supplier-onboard@example.com',
        'company_logo' => UploadedFile::fake()->image('logo.png'),
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseHas('suppliers', [
        'company_name' => 'Test Feeds Co',
        'email' => 'supplier-onboard@example.com',
        'status' => 'pending',
    ]);

    $user = \App\Models\User::query()->where('email', 'supplier-onboard@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->avatar)->not->toBeNull();

    Storage::disk('public')->assertExists($user->avatar);
});
