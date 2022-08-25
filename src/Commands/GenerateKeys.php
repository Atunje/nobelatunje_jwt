<?php

namespace Nobelatunje\Jwt\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class GenerateKeys extends Command
{
    private string $envFilePath;

    const SK_FIELD = "JWT_PRIVATE_KEY";
    const PK_FIELD = "JWT_PUBLIC_KEY";

    public function __construct()
    {
        parent::__construct();

        $this->envFilePath = App::environmentFilePath();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the private and public keys for signing jwt';

    /**
     * @return void
     */
    public function handle(): void
    {
        $secret_key = "sk_" . Str::random(32);
        $public_key = "pk_" . Str::random(32);

        if ($this->writeEnvFile(self::SK_FIELD, $secret_key) && $this->writeEnvFile(self::PK_FIELD, $public_key)) {
            $this->info("JWT private and public keys successfully generated.");
        }
    }

    /**
     * Execute the console command.
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function writeEnvFile(string $key, string $value): bool
    {
        $newEnvFileContent = $this->setEnvVariable($key, $value);
        return $this->writeFile($newEnvFileContent);
    }

    /**
     * Set or update env-variable.
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function setEnvVariable(string $key, string $value): string
    {
        $envFileContent = file_get_contents($this->envFilePath);

        $oldPair = $this->readKeyValuePair($envFileContent, $key);

        // Wrap values that have a space or equals in quotes to escape them
        if (preg_match('/\s/',$value) || str_contains($value, '=')) {
            $value = '"' . $value . '"';
        }

        $newPair = $key . '=' . $value;

        // For existed key.
        if ($oldPair !== null) {
            return preg_replace('/^' . preg_quote($oldPair, '/') . '$/uimU', $newPair, $envFileContent);
        }

        // For a new key.
        return $envFileContent . "\n" . $newPair . "\n";
    }

    /**
     * Read the "key=value" string of a given key from the env file.
     * This function returns original "key=value" string and doesn't modify it.
     *
     * @param string $envFileContent
     * @param string $key
     *
     * @return string|null Key=value string or null if the key is not exists.
     */
    public function readKeyValuePair(string $envFileContent, string $key): ?string
    {
        // Match the given key at the beginning of a line
        if (preg_match("#^ *{$key} *= *[^\r\n]*$#uimU", $envFileContent, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Overwrite the contents of env file
     *
     * @param string $contents
     *
     * @return boolean
     */
    protected function writeFile(string $contents): bool
    {
        return (bool)file_put_contents($this->envFilePath, $contents, LOCK_EX);
    }
}
