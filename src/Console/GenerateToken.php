<?php

namespace Morisawa\Auth\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateToken extends Command
{
    protected $signature = 'mino:secret {--force : Override existing secret key}';

    protected $description = 'Set the MinoAuth secret key used to sign the tokens';

    public function handle()
    {
        $sm4Key = Str::random(16);
        $hashKey = Str::random(16);
        $path = $this->envPath();
        try {
            if (! file_exists($path)) {
                $this->createEnvFile($path, $sm4Key, $hashKey);
            } else {
                $this->updateEnvFile($path, $sm4Key, $hashKey);
            }
            $this->displayKey($sm4Key, $hashKey);
        } catch (\Exception $e) {
            $this->error('An error occurred while setting the secret keys: '.$e->getMessage());
        }
    }

    protected function createEnvFile($path, $sm4Key, $hashKey)
    {
        $content = "MINO_SM4_SECRET=$sm4Key".PHP_EOL;
        $content .= "MINO_HASH_SIGN=$hashKey".PHP_EOL;

        file_put_contents($path, $content);

        $this->info('Created .env file and set the SM4 secret key and HASH sign key.');
    }

    protected function updateEnvFile($path, $sm4Key, $hashKey)
    {
        $contents = file_exists($path) ? file_get_contents($path) : '';

        $contents = $this->updateKey($contents, 'MINO_SM4_SECRET', $sm4Key);

        $contents = $this->updateKey($contents, 'MINO_HASH_SIGN', $hashKey);

        file_put_contents($path, $contents);

        $this->info('Updated .env file with the new SM4 secret key and HASH sign key.');
    }

    protected function updateKey($contents, $key, $value)
    {
        if (preg_match("/^{$key}=.+$/m", $contents)) {
            if (! $this->option('force') && ! $this->isConfirmed($key)) {
                $this->comment("No changes were made to {$key}.");

                return $contents;
            }

            return preg_replace("/^{$key}=.*/m", "{$key}={$value}", $contents);
        }

        return $contents.PHP_EOL."{$key}={$value}";
    }

    protected function displayKey($sm4Key, $hashKey)
    {
        $this->info("Mino SM4 Secret Key: [{$sm4Key}] set successfully.");
        $this->info("Mino HASH Sign Key: [{$hashKey}] set successfully.");
    }

    protected function isConfirmed($key)
    {
        return $this->confirm(
            "The existing {$key} will be overridden. This may invalidate existing tokens. Do you want to proceed?"
        );
    }

    protected function envPath()
    {
        return $this->laravel->basePath('.env');
    }
}
