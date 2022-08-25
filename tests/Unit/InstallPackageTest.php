<?php

namespace Nobelatunje\Jwt\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\TestCase;

class InstallPackageTest extends TestCase
{
    /** @test */
    function the_install_command_copies_the_configuration()
    {
        // make sure we're starting from a clean state
        if (File::exists(config_path('nobelatunje_jwt.php'))) {
            unlink(config_path('nobelatunje_jwt.php'));
        }

        $this->assertFalse(File::exists(config_path('nobelatunje_jwt.php')));

        Artisan::call('jwt:install');

        $this->assertTrue(File::exists(config_path('nobelatunje_jwt.php')));
    }

    /** @test */
    public function users_can_choose_not_to_override_config_file()
    {
        // Given we already have an existing config file
        File::put(config_path('nobelatunje_jwt.php'), 'test contents');
        $this->assertTrue(File::exists(config_path('nobelatunje_jwt.php')));

        // When we run the install command
        $command = $this->artisan('jwt:install');

        // We expect a warning that our configuration file exists
        $command->expectsConfirmation(
            'Config file already exists. Do you want to overwrite it?',
            // When answered with "no"
            'no'
        );

        // We should see a message that our file was not overwritten
        $command->expectsOutput('Existing configuration was not overwritten');

        // Assert that the original contents of the config file remain
        $this->assertEquals('test contents', file_get_contents(config_path('nobelatunje_jwt.php')));

        // Clean up
        unlink(config_path('nobelatunje_jwt.php'));
    }

    /** @test */
    public function users_can_override_config_file()
    {
        // Given we already have an existing config file
        File::put(config_path('nobelatunje_jwt.php'), 'test contents');
        $this->assertTrue(File::exists(config_path('nobelatunje_jwt.php')));

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
            file_get_contents(__DIR__.'/../config/config.php'),
            file_get_contents(config_path('nobelatunje_jwt.php'))
        );

        // Clean up
        unlink(config_path('nobelatunje_jwt.php'));
    }
}
