<?php

namespace Nobelatunje\Jwt\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Nobelatunje\Jwt\Config;

class InstallPackage extends Command
{
    protected $signature = 'jwt:install';

    protected $description = 'InstallPackage the Nobelatunje/Jwt Package';

    public function handle()
    {
        $this->info('Installing package...');

        $this->info('Publishing configuration...');

        if (! $this->configExists()) {
            $this->publishConfiguration();
            $this->info('Published configuration');
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->info('Overwriting configuration file...');
                $this->publishConfiguration($force = true);
            } else {
                $this->info('Existing configuration was not overwritten');
            }
        }

        $this->info('Installed Nobelatunje/Jwt');
    }

    private function configExists(): bool
    {
        return File::exists(config_path(Config::CONFIG_FILE . ".php"));
    }

    private function shouldOverwriteConfig(): bool
    {
        return $this->confirm(
            'Config file already exists. Do you want to overwrite it?',
            false
        );
    }

    private function publishConfiguration($forcePublish = false)
    {
        $params = [
            '--provider' => "Nobelatunje\Jwt\JWTServiceProvider",
            '--tag' => 'config'
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
