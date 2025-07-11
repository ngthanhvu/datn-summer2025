<?php

namespace App\Helpers;

class EnvHelper
{
public static function setEnvValue(array $values): void
{
    try {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            throw new \Exception('.env file not found');
        }

        $envContent = file_get_contents($envPath);
        
        foreach ($values as $key => $value) {
            $key = strtoupper(trim($key));
            $value = trim($value);

            $needsQuotes = str_contains($value, ' ') || str_contains($value, '=') || str_contains($value, '"');
            $formattedValue = $needsQuotes ? "\"{$value}\"" : $value;

            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$formattedValue}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envPath, $envContent);
        
        \Artisan::call('config:clear');
        
    } catch (\Exception $e) {
        \Log::error('Error updating .env: ' . $e->getMessage());
        throw $e;
    }
}

    protected static function reloadEnv(): void
    {
        \Artisan::call('config:clear');
        \Artisan::call('config:cache');
    }
}