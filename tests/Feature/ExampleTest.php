<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_homepage_displays_the_dashboard_summary(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Ringkasan Dashboard')
            ->assertSee('Pratinjau · data ilustrasi')
            ->assertSee('Tren realisasi anggaran');
    }
}
