<?php

namespace Nobelatunje\Jwt;

use Exception;
use Lcobucci\JWT\Signer;
use Illuminate\Support\Str;
use Lcobucci\JWT\Configuration;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;

class TokenFactory
{
    /**
     * @var Configuration
     */
    private Configuration $config;

    /**
     * JWT issued by
     *
     * @var string
     */
    protected string $issuer;

    /**
     * JWT Expiry in seconds
     *
     * @var int
     */
    protected int $expires_in;

    /**
     * JWT private key
     *
     * @var string
     */
    protected string $private_key;

    /**
     * JWT public key
     *
     * @var string
     */
    protected string $public_key;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->private_key = config(Config::CONFIG_FILE . '.private_key');

        $this->public_key = config(Config::CONFIG_FILE . '.public_key');

        $this->issuer = config(Config::CONFIG_FILE . '.jwtconfig.issuer');

        $this->expires_in = config(Config::CONFIG_FILE . '.jwtconfig.token_life');

        $this->configure();
    }

    /**
     * Issue the jwt and return the string
     *
     * @param string $user_identifier
     * @return string
     */
    public function issueToken(string $user_identifier): string
    {
        $now = new \DateTimeImmutable();
        $unique_id = Str::random(20);
        $expires_at = $now->modify('+ '. $this->expires_in . ' seconds');

        $token = $this->config->builder()
            ->issuedBy($this->issuer) // Configures the issuer (iss claim)
            ->identifiedBy($unique_id) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($now) // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($now) // Configures the time that the token can be used (nbf claim)
            ->expiresAt($expires_at) // Configures the expiration time of the token (exp claim)
            ->withClaim('uid', $user_identifier) // Configures a new claim, called "uid"
            ->getToken($this->config->signer(), $this->config->signingKey()); // Builds a new token

        return new $token->toString();
    }

    /**
     * Get the stored jwtToken if token is valid
     *
     * @param string $token
     * @return mixed
     */
    public function validate(string $token): mixed
    {
        try {
            //parse the token
            /** @var UnencryptedToken $unencrypted_token */
            $unencrypted_token = $this->config->parser()->parse($token);

            //validate token against the constraints set
            $constraints = $this->config->validationConstraints();
            if ($this->config->validator()->validate($unencrypted_token, ...$constraints)) {
                //get the user identifier
                return $unencrypted_token->claims()->get('uid');
            }

            return null;
        } catch (Exception) {
            return null;
        }
    }

    protected function configure(): void
    {
        $signer = new Signer\Hmac\Sha256();
        $private_key = InMemory::plainText($this->private_key);
        $public_key = InMemory::plainText($this->public_key);

        //create the config
        $this->config = Configuration::forAsymmetricSigner($signer, $private_key, $public_key);

        $clock = SystemClock::fromSystemTimezone();

        //set the validation constraints
        $this->config->setValidationConstraints(
            new IssuedBy($this->issuer),
            new SignedWith($signer, $private_key),
            new StrictValidAt($clock),
            new LooseValidAt($clock)
        );
    }
}
