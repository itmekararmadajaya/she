<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ExampleTest extends TestCase
{
    // Pastikan baris ini ada
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Buat user dummy yang akan digunakan untuk testing
        $user = User::factory()->create();

        // Lakukan permintaan sebagai user yang sudah login
        $response = $this->actingAs($user)->get('/');

        // Verifikasi bahwa permintaan berhasil (status 200)
        $response->assertStatus(200);
    }
}