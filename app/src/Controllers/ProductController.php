<?php

namespace App\Controllers;

use App\Entity\Product;
use App\Helpers\Request;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Resources\ProductResource;
use App\Services\TokenService;
use App\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class ProductController extends RestController implements TokenAuthenticatedController
{
    /**
     * @Route("/products", name="product.create", methods={"POST"})
     */
    public function create(
        EntityManagerInterface $em,
        SymfonyRequest $sr,
        ProductRepository $pr,
        UserRepository $ur,
        TokenService $ts,
        ValidatorInterface $validator
    ) {
        $request = new Request($sr);
        $user = $request->getUser($ur, $ts);

        if (!isset($user)) {
            return $this->handleError('', []);
        }

        $product = new Product();
        $product->setName($request->get('name') ?? '');
        $product->setDescription($request->get('description') ?? '');
        $product->setPrice($request->get('price') ?? '');
        $product->setPhoto($request->get('photo') ?? '');

        $errors = $validator->validate($product);

        if (count($errors) > 0) {
            return $this->handleError('The data contains some errors', ValidationService::getErrors($errors));
        }

        $product->setUser($user);
        $pr->add($product);

        $productResource = new ProductResource($em);

        return $this->handleResponse('Product created', [
            'product' => $productResource->resource($product)
        ], 201);
    }

    /**
     * @Route("/products/{productId}", name="product.update", methods={"POST"})
     */
    public function update(
        EntityManagerInterface $em,
        SymfonyRequest $sr,
        ProductRepository $pr,
        UserRepository $ur,
        TokenService $ts,
        ValidatorInterface $validator,
        $productId
    ) {
        $request = new Request($sr);
        $user = $request->getUser($ur, $ts);

        $product = $pr->findOneBy(['id' => $productId]);

        if (isset($product)) {
            if ($product->getUser() === $user) {

                $product->setName($request->get('name') ?? $product->getName());
                $product->setDescription($request->get('description') ?? $product->getDescription());
                $product->setPrice($request->get('price') ?? $product->getPrice());
                $product->setPhoto($request->get('photo') ?? $product->getPhoto());

                $errors = $validator->validate($product);

                if (count($errors) > 0) {
                    return $this->handleError('The data contains some errors', ValidationService::getErrors($errors));
                }

                $pr->persist($product);

                $productResource = new ProductResource($em);

                return $this->handleResponse('Product updated', [
                    'product' => $productResource->resource($product)
                ]);
            }

            return $this->handleError('You\'re not the creator of this product');
        }

        return $this->handleError('This product isn\'t in our shop');
    }

    /**
     * @Route("/products/{productId}", name="product.delete", methods={"DELETE"})
     */
    public function delete(
        SymfonyRequest $sr,
        ProductRepository $pr,
        UserRepository $ur,
        TokenService $ts,
        $productId
    ) {
        $request = new Request($sr);
        $user = $request->getUser($ur, $ts);

        $product = $pr->findOneBy(['id' => $productId]);
        if (isset($product)) {
            if ($product->getUser() === $user) {
                $pr->remove($product);

                return $this->handleResponse('Product deleted');
            }

            return $this->handleError('You\'re not the creator of this product');
        }

        return $this->handleError('This product isn\'t in our shop');
    }
}