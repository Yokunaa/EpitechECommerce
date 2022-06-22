<?php

namespace App\Controllers;

use App\Helpers\Request;
use App\Resources\ProductResource;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

#[Route('/api')]
/**
 * @Route("/api")
 */
class CatalogController extends RestController
{
    /**
     * @Route("/products", name="product.all", methods={"GET", "HEAD"})
     */
    public function all(
        EntityManagerInterface $em,
        SymfonyRequest $sr,
        ProductRepository $pr
    ) {
        $request = new Request($sr);

        $query = $request->query('q') ?? '';
        $offset = $request->query('page') ? $request->query('page') - 1 : 0;
        $limit = $request->query('limit') ?? 50;

        $productsRaw = $pr->findAllByQuery($query, $limit, $offset);

        $productResource = new ProductResource($em);

        return $this->handleResponse('', [
            'products' => $productResource->resourceCollection($productsRaw),
            'page' => $request->query('page') ?? 1
        ]);
    }

    /**
     * @Route("/products/{productId}", name="product.show", methods={"GET", "HEAD"})
     */
    public function show(
        EntityManagerInterface $em,
        ProductRepository $pr,
        $productId
    ) {
        $product = $pr->findOneBy(['id' => $productId]);

        if (!isset($product)) {
            return $this->handleError('Product not found', [], 404);
        }

        $productResource = new ProductResource($em);

        return $this->handleResponse('Product found', [
            'product' => $productResource->resource($product)
        ]);
    }
}