<?php

namespace App\Controllers;

use App\Entity\Cart;
use App\Entity\User;
use App\Helpers\Request;
use App\Services\TokenService;
use App\Resources\UserResource;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use App\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthController extends RestController
{
    /**
     * @Route("/login", name="auth.login", methods={"POST"})
     */
    public function login(
        SymfonyRequest $sr,
        UserPasswordHasherInterface $ph,
        UserRepository $ur,
        TokenService $ts
    ) {
        $request = new Request($sr);

        $user = $ur->findOneByLogin($request->get('login'));

        if (!isset($user)) {
            return $this->handleResponse('Wrong credentials', [], Response::HTTP_CONFLICT);
        }

        if (!$ph->isPasswordValid($user, $request->get('password'))) {
            return $this->handleResponse('Wrong credentials', [], Response::HTTP_UNAUTHORIZED);
        }

        $token = $ts->generateToken($user);

        return $this->handleResponse('Logged in', ['token' => $token], Response::HTTP_OK);
    }

    /**
     * @Route("/register", name="auth.register", methods={"POST"})
     */
    public function register(
        EntityManagerInterface $em,
        SymfonyRequest $sr,
        UserPasswordHasherInterface $ph,
        UserRepository $ur,
        CartRepository $cr,
        ValidatorInterface $validator
    ) {
        $request = new Request($sr);

        $user = new User();
        $user->setLogin($request->get('login', ''));
        $user->setEmail($request->get('email', ''));
        $user->setFirstname($request->get('firstname', ''));
        $user->setLastname($request->get('lastname', ''));
        $user->setPassword($request->get('password', ''));

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            return $this->handleError('The data contains some errors', ValidationService::getErrors($errors));
        }

        $hashedPassword = $ph->hashPassword(
            $user,
            $request->get('password')
        );
        $user->setPassword($hashedPassword);

        $cart = new Cart();
        $cr->add($cart);
        $user->setCart($cart);

        $ur->add($user);


        $userResource = new UserResource($em);

        return $this->handleResponse('Registered successfully', [
            'user' => $userResource->resource($user)
        ], 201);
    }
}