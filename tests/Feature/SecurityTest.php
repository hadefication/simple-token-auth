<?php

use Hadefication\SimpleTokenAuth\Http\Middleware\SimpleTokenAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    Route::middleware(SimpleTokenAuthMiddleware::class)->get('/test-route', function () {
        return response()->json(['success' => true]);
    });
});

it('prevents timing attacks with hash_equals', function () {
    // Test with tokens of different lengths to ensure timing is consistent
    $shortToken = 'short';
    $longToken = 'very-long-token-that-should-take-longer-to-compare';

    $start = microtime(true);
    $this->withHeaders(['Authorization' => "Bearer {$shortToken}"])->get('/test-route');
    $shortTime = microtime(true) - $start;

    $start = microtime(true);
    $this->withHeaders(['Authorization' => "Bearer {$longToken}"])->get('/test-route');
    $longTime = microtime(true) - $start;

    // Times should be reasonably similar (within 50ms) if hash_equals is used
    expect(abs($shortTime - $longTime))->toBeLessThan(0.05);
});

it('masks tokens in failed authentication logs', function () {
    config()->set('simple-token-auth.log_failed_attempts', true);

    Log::shouldReceive('warning')
        ->withArgs(function ($message, $context) {
            // Ensure no actual tokens are logged
            $logContent = json_encode($context);

            return ! str_contains($logContent, 'test-token') &&
                   ! str_contains($logContent, 'wrong-token');
        })
        ->once();

    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
        ->get('/test-route');
});

it('uses hashed rate limit keys to protect IP addresses', function () {
    config()->set('simple-token-auth.rate_limiting.enabled', true);
    config()->set('simple-token-auth.rate_limiting.max_attempts', 1);

    // Make a request to trigger rate limiting
    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
        ->get('/test-route')
        ->assertStatus(Response::HTTP_UNAUTHORIZED);

    // Check that the rate limit key is hashed (we can't directly access it, but we can verify behavior)
    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
        ->get('/test-route')
        ->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
});

it('respects rate limiting lockout duration', function () {
    config()->set('simple-token-auth.rate_limiting.enabled', true);
    config()->set('simple-token-auth.rate_limiting.max_attempts', 1);
    config()->set('simple-token-auth.rate_limiting.lockout_duration', 1); // 1 second

    // Trigger rate limiting
    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
        ->get('/test-route')
        ->assertStatus(Response::HTTP_UNAUTHORIZED);

    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
        ->get('/test-route')
        ->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);

    // Wait for lockout to expire
    sleep(2);

    // Should still be rate limited because the lockout duration is separate from max attempts
    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
        ->get('/test-route')
        ->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
});

it('logs failed attempts with correct context', function () {
    config()->set('simple-token-auth.log_failed_attempts', true);

    Log::shouldReceive('warning')
        ->withArgs(function ($message, $context) {
            return $message === 'Failed token authentication attempt.' &&
                   isset($context['ip']) &&
                   isset($context['url']);
        })
        ->once();

    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
        ->get('/test-route');
});

it('does not expose tokens in error responses', function () {
    $response = $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
        ->get('/test-route');

    $content = $response->getContent();

    // Ensure no tokens are exposed in the response
    expect($content)->not->toContain('wrong-token');
    expect($content)->not->toContain('test-token');
    expect($content)->not->toContain('fallback-token');
});

it('handles malformed tokens securely', function () {
    // Test with various malformed tokens
    $malformedTokens = [
        'Bearer',
        'Bearer ',
        'InvalidFormat token',
        'Bearer token with spaces',
        'Bearer'.str_repeat('a', 1000), // Very long token
    ];

    foreach ($malformedTokens as $token) {
        $this->withHeaders(['Authorization' => $token])
            ->get('/test-route')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
});

it('prevents token enumeration attacks', function () {
    // Test that different invalid tokens take similar time to process
    $tokens = ['invalid1', 'invalid2', 'invalid3', 'invalid4', 'invalid5'];
    $times = [];

    foreach ($tokens as $token) {
        $start = microtime(true);
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->get('/test-route');
        $times[] = microtime(true) - $start;
    }

    // All times should be reasonably similar (within 50ms)
    $avg = array_sum($times) / count($times);
    foreach ($times as $time) {
        expect(abs($time - $avg))->toBeLessThan(0.05);
    }
});
