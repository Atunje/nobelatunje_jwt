<?php

namespace Nobelatunje\Jwt\Tests\Unit;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Nobelatunje\Jwt\Commands\GenerateKeys;
use Nobelatunje\Jwt\Tests\TestCase;

class GenerateKeysTest extends TestCase
{
    private string $envFilePath;

    public function setUp(): void
    {
        parent::setUp();

        $this->envFilePath = App::environmentFilePath();
    }

    /** @test */
    function the_generate_command_generates_the_private_and_public_keys()
    {
        //generate the keys
        Artisan::call('jwt:generate');

        $envFileContent = file_get_contents($this->envFilePath);

        $sk_field = GenerateKeys::SK_FIELD;
        $pk_field = GenerateKeys::PK_FIELD;

        //assert that the secret key was created
        preg_match("#^ *{$sk_field} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $this->assertNotNull($matches[0]);

        //assert that the public key was created
        preg_match("#^ *{$pk_field} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $this->assertNotNull($matches[0]);
    }

    /** @test */
    function the_generate_command_overrides_current_private_and_public_keys()
    {
        //generate the keys
        Artisan::call('jwt:generate');

        $envFileContent = file_get_contents($this->envFilePath);

        $sk_field = GenerateKeys::SK_FIELD;
        $pk_field = GenerateKeys::PK_FIELD;

        //get the old secret key value pair
        preg_match("#^ *{$sk_field} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $sk_old_pair = $matches[0];

        //get the old public key value pair
        preg_match("#^ *{$pk_field} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $pk_old_pair = $matches[0];

        //now generate the keys again
        Artisan::call('jwt:generate');

        $envFileContent = file_get_contents($this->envFilePath);

        //assert that old is not equal to new
        preg_match("#^ *{$sk_field} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $sk_new_pair = $matches[0];
        $this->assertNotEquals($sk_old_pair, $sk_new_pair);

        //get the old public key value pair
        preg_match("#^ *{$pk_field} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $pk_new_pair = $matches[0];
        $this->assertNotEquals($pk_old_pair, $pk_new_pair);
    }

}
