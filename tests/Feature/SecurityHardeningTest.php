<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_http_responses_include_security_headers(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_header_is_only_sent_in_production_over_https(): void
    {
        $middleware = new SecurityHeaders;
        $httpsRequest = Request::create('https://pariban.example.go.id/login', 'GET');
        $httpRequest = Request::create('http://pariban.example.go.id/login', 'GET');

        $previousEnv = $this->app['env'];
        $this->app['env'] = 'production';

        try {
            $httpsResponse = $middleware->handle($httpsRequest, fn () => new Response('ok'));
            $httpResponse = $middleware->handle($httpRequest, fn () => new Response('ok'));
        } finally {
            $this->app['env'] = $previousEnv;
        }

        $this->assertSame(
            'max-age=31536000; includeSubDomains',
            $httpsResponse->headers->get('Strict-Transport-Security')
        );
        $this->assertNull($httpResponse->headers->get('Strict-Transport-Security'));
    }

    public function test_login_endpoint_is_rate_limited_after_five_failed_attempts(): void
    {
        RateLimiter::clear('throttle:login');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->from(route('login'))
                ->post('/login', [
                    'username' => 'pengguna-uji',
                    'password' => 'salah-password',
                ])
                ->assertRedirect(route('login'))
                ->assertSessionHasErrors('username');
        }

        $this->from(route('login'))
            ->post('/login', [
                'username' => 'pengguna-uji',
                'password' => 'salah-password',
            ])
            ->assertStatus(429);

        // Username berbeda dari IP yang sama tetap dapat mencoba login
        $this->from(route('login'))
            ->post('/login', [
                'username' => 'pengguna-lain',
                'password' => 'salah-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');
    }
}
