<?php

namespace App\Services;

use Exception;
use App\Entity\User;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class TokenService
{
    private $key;
    private $configuration;

    public function __construct(public ContainerBagInterface $params)
    {
        $this->key = InMemory::base64Encoded($params->get('jwt.encoder'));
        $this->configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            $this->key
        );
    }

    public function generateToken(User $user)
    {
        $now = new DateTimeImmutable();
        return
            $this->configuration->builder()
            // Configures the issuer (iss claim)
            ->issuedBy('http://example.com')
            // Configures the audience (aud claim)
            ->permittedFor('http://example.org')
            // Configures the time that the token was issue (iat claim)
            ->issuedAt($now)
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($now->modify('+1 hour'))
            // Configures a new claim, called "uid"
            ->withClaim('data', [
                'user_email' => $user->getEmail(),
                'used_for' => 'authentication',
            ])
            // Builds a new token
            ->getToken($this->configuration->signer(), $this->configuration->signingKey())->toString();
    }

    public function parseToken(string $token)
    {
        $token = $this->configuration->parser()->parse($token);

        assert($token instanceof UnencryptedToken);

        return $token->claims();
    }

    public function validate(string $token)
    {
        try {
            $token = $this->configuration->parser()->parse($token);
        } catch (Exception $e) {
            return false;
        }

        return assert($token instanceof UnencryptedToken);
    }
}