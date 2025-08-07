<?php

use Hadefication\SimpleTokenAuth\Http\Middleware\SimpleTokenAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    Route::middleware(SimpleTokenAuthMiddleware::class)->get('/test-route', function (Request $request) {
        return response()->json(['service' => $request->attributes->get('authenticated_service')]);
    });

    Route::middleware('simple-token-auth:test-service')->get('/test-service-route', function (Request $request) {
        return response()->json(['service' => $request->attributes->get('authenticated_service')]);
    });
});

it('allows access with a valid token', function () {
    $this->withHeaders(['Authorization' => 'Bearer fallback-token'])
        ->get('/test-route')
        ->assertStatus(Response::HTTP_OK);
});

it('blocks access with an invalid token', function () {
    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
        ->get('/test-route')
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it('allows access with a valid service token', function () {
    $response = $this->withHeaders(['Authorization' => 'Bearer test-token'])
        ->get('/test-service-route')
        ->assertStatus(Response::HTTP_OK);

    expect($response->json('service'))->toBe('test-service');
});

it('blocks access with an invalid service token', function () {
    $this->withHeaders(['Authorization' => 'Bearer fallback-token'])
        ->get('/test-service-route')
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it('rate limits requests with invalid tokens', function () {
    config()->set('simple-token-auth.rate_limiting.enabled', true);
    config()->set('simple-token-auth.rate_limiting.max_attempts', 2);

    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])->get('/test-route')->assertStatus(Response::HTTP_UNAUTHORIZED);
    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])->get('/test-route')->assertStatus(Response::HTTP_UNAUTHORIZED);
    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])->get('/test-route')->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
});

it('clears rate limit on successful authentication', function () {
    config()->set('simple-token-auth.rate_limiting.enabled', true);
    config()->set('simple-token-auth.rate_limiting.max_attempts', 2);

    // Fail once
    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])->get('/test-route')->assertStatus(Response::HTTP_UNAUTHORIZED);

    // Succeed - should clear the rate limit
    $this->withHeaders(['Authorization' => 'Bearer fallback-token'])->get('/test-route')->assertStatus(Response::HTTP_OK);

    // Should be able to fail again without hitting rate limit
    $this->withHeaders(['Authorization' => 'Bearer wrong-token'])->get('/test-route')->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it('respects rate limiting configuration', function () {
    config()->set('simple-token-auth.rate_limiting.enabled', false);

    // Should not rate limit when disabled
    for ($i = 0; $i < 10; $i++) {
        $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
            ->get('/test-route')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
});

it('injects service context into request attributes', function () {
    $response = $this->withHeaders(['Authorization' => 'Bearer test-token'])
        ->get('/test-service-route')
        ->assertStatus(Response::HTTP_OK);

    expect($response->json('service'))->toBe('test-service');
});

it('handles missing authorization header', function () {
    $this->get('/test-route')
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it('handles malformed authorization header', function () {
    $this->withHeaders(['Authorization' => 'InvalidFormat token'])
        ->get('/test-route')
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it('supports x-api-token header', function () {
    $this->withHeaders(['X-API-Token' => 'fallback-token'])
        ->get('/test-route')
        ->assertStatus(Response::HTTP_OK);
});
