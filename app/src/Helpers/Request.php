<?php

namespace App\Helpers;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\TokenService;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request
{
    public function __construct(public SymfonyRequest $request)
    {
        //
    }

    public function get(string $parameter, mixed $default = null)
    {
        if ($this->request->getContentType() === "json") {
            return $this->asJson()->$parameter ?? $default;
        }
        return $this->request->request->get($parameter) ?? $default;
    }

    public function query(string $parameter)
    {
        return $this->request->query->get($parameter);
    }

    public function getUser(UserRepository $ur, TokenService $ts): User|null
    {
        $token = substr($this->request->headers->get('Authorization'), 7);
        $claims = $ts->parseToken($token);

        return $ur->findOneByEmail($claims->get('data')['user_email']);
    }

    private function asJson()
    {
        return json_decode($this->request->getContent());
    }
}