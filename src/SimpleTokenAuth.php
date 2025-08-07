<?php

namespace Hadefication\SimpleTokenAuth;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface as Log;

class SimpleTokenAuth
{
    protected $config;

    protected $log;

    public function __construct(Config $config, Log $log)
    {
        $this->config = $config;
        $this->log = $log;
    }

    public function validateToken(Request $request, ?string $service = null): bool
    {
        $token = $this->getTokenFromRequest($request);

        if (empty($token)) {
            return false;
        }

        $valid = $this->isValid($token, $service);

        if (! $valid && $this->config->get('simple-token-auth.log_failed_attempts')) {
            $this->log->warning('Failed token authentication attempt.', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
        }

        return $valid;
    }

    protected function getTokenFromRequest(Request $request): ?string
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            $token = $request->header('X-API-Token');
        }

        return $token;
    }

    protected function isValid(string $token, ?string $service): bool
    {
        $configuredTokens = $this->getConfiguredTokens($service);

        foreach ($configuredTokens as $configuredToken) {
            if (hash_equals($configuredToken, $token)) {
                return true;
            }
        }

        return false;
    }

    protected function getConfiguredTokens(?string $service): array
    {
        $tokens = [];

        if ($service) {
            $tokens[] = $this->config->get("simple-token-auth.tokens.{$service}");
        } else {
            $tokens = array_merge($tokens, array_values($this->config->get('simple-token-auth.tokens', [])));
            $tokens[] = $this->config->get('simple-token-auth.fallback_token');
        }

        return array_filter($tokens);
    }
}
