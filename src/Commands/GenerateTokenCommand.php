<?php

namespace Hadefication\SimpleTokenAuth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateTokenCommand extends Command
{
    protected $signature = 'simple-token:generate {service?} {--length=64} {--show-env} {--save}';

    protected $description = 'Generate a new token for a service.';

    public function handle()
    {
        $service = $this->argument('service');
        $length = (int) $this->option('length');
        $token = bin2hex(random_bytes($length / 2));

        $this->info("Generated token for service [{$service}]:");
        $this->line($token);

        $envVar = $this->getEnvVariableName($service);

        if ($this->option('save')) {
            $this->saveToEnvFile($envVar, $token);
        }

        if ($this->option('show-env') || $this->option('save')) {
            $this->line('');
            $this->line('Add the following to your .env file:');
            $this->line("{$envVar}={$token}");
        }

        if ($this->option('save')) {
            $this->showConfigInstructions($service, $envVar);
        }
    }

    protected function getEnvVariableName(?string $service): string
    {
        return $service ? 'API_TOKEN_'.Str::upper(str_replace('-', '_', $service)) : 'API_TOKEN';
    }

    protected function saveToEnvFile(string $envVar, string $token): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            // Create .env file if it doesn't exist
            file_put_contents($envPath, "# Laravel Environment File\n");
        }

        $envContent = file_get_contents($envPath);
        $newLine = "\n{$envVar}={$token}";

        // Always append to .env file
        file_put_contents($envPath, $envContent.$newLine);

        $this->line('');
        $this->info("Token saved to .env file as: {$envVar}");
    }

    protected function showConfigInstructions(?string $service, string $envVar): void
    {
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Add the following to your config/simple-token-auth.php file:');

        if ($service) {
            $this->line("   '{$service}' => env('{$envVar}'),");
        } else {
            $this->line("   'fallback_token' => env('{$envVar}'),");
        }

        $this->line('');
        $this->line('2. Clear config cache: php artisan config:clear');
        $this->line('');
        $this->line('3. Verify configuration: php artisan simple-token:info');
    }
}
