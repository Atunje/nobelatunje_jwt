<?php

namespace Nobelatunje\Jwt\Tests\Unit;

use Nobelatunje\Jwt\Config;
use Nobelatunje\Jwt\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class InstallPackageTest extends TestCase
{
    private string $config_file;

    public function setUp(): void
    {
        parent::setUp();

        $this->config_file = Config::CONFIG_FILE . ".php";
    }

    /** @test */
    function the_install_command_copies_the_configuration()
    {
        // make sure we're starting from a clean state
        if (File::exists(config_path($this->config_file))) {
            unlink(config_path($this->config_file));
        }

        $this->assertFalse(File::exists(config_path($this->config_file)));

        Artisan::call('jwt:install');

        $this->assertTrue(File::exists(config_path($this->config_file)));
    }

    /** @test */
    public function users_can_overwrite_config_file()
    {
        // Given we already have an existing config file
        File::put(config_path($this->config_file), 'test contents');
        $this->assertTrue(File::exists(config_path($this->config_file)));

        // When we run the install command
        $command = $this->artisan('jwt:install');

        // We expect a warning that our configuration file exists
        $command->expectsConfirmation(
            'Config file already exists. Do you want to overwrite it?',
            // When answered with "yes"
            'yes'
        );

        // execute the command to force override
        $command->execute();

        $command->expectsOutput('Overwriting configuration file...');

        // Assert that the original contents are overwritten
        $this->assertEquals(
            file_get_contents(__DIR__.'/../../src/config/' . $this->config_file),
            file_get_contents(config_path($this->config_file))
        );

        // Clean up
        unlink(config_path($this->config_file));
    }
}
