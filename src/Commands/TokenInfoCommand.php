<?php

namespace Hadefication\SimpleTokenAuth\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;

class TokenInfoCommand extends Command
{
    protected $signature = 'simple-token:info';

    protected $description = 'Display information about the token configuration.';

    protected $config;

    public function __construct(Config $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    public function handle()
    {
        $this->info('Simple Token Auth Configuration:');

        $this->displayTokens();
        $this->displayRateLimiting();
        $this->displayLogging();
    }

    protected function displayTokens()
    {
        $this->line('');
        $this->line('Tokens:');
        $tokens = $this->config->get('simple-token-auth.tokens', []);
        $fallback = $this->config->get('simple-token-auth.fallback_token');

        if (empty($tokens) && empty($fallback)) {
            $this->line('  No tokens configured.');

            return;
        }

        foreach ($tokens as $service => $token) {
            $this->line("  - Service: {$service}, Token: ".($token ? $this->maskToken($token) : 'Not Set'));
        }

        if ($fallback) {
            $this->line('  - Fallback Token: '.$this->maskToken($fallback));
        }
    }

    protected function displayRateLimiting()
    {
        $this->line('');
        $this->line('Rate Limiting:');
        $config = $this->config->get('simple-token-auth.rate_limiting', []);
        $this->line('  - Enabled: '.($config['enabled'] ? 'Yes' : 'No'));
        $this->line("  - Max Attempts: {$config['max_attempts']}");
        $this->line("  - Lockout Duration: {$config['lockout_duration']} seconds");
    }

    protected function displayLogging()
    {
        $this->line('');
        $this->line('Logging:');
        $enabled = $this->config->get('simple-token-auth.log_failed_attempts', false);
        $this->line('  - Log Failed Attempts: '.($enabled ? 'Yes' : 'No'));
    }

    protected function maskToken(string $token): string
    {
        if (strlen($token) <= 8) {
            return '********';
        }

        return substr($token, 0, 4).'********'.substr($token, -4);
    }
}
