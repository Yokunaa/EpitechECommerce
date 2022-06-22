<?php

namespace App\Controllers;

use App\Helpers\Request;
use App\Repository\OrderRepository;
use App\Services\TokenService;
use App\Repository\UserRepository;
use App\Resources\OrderProductResource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @Route("/api")
 */
class OrderController extends RestController implements TokenAuthenticatedController
{
    /**
     * @Route("/orders", name="orders.all", methods={"GET", "HEAD"})
     */
    public function all(
        SymfonyRequest $sr,
        UserRepository $ur,
        TokenService $ts,
        EntityManagerInterface $em,
    ) {
        $request = new Request($sr);
        $user = $request->getUser($ur, $ts);

        $orders = [];

        foreach ($user->getOrders() as $key => $order) {
            $orderProductResource = new OrderProductResource($em);

            $orders[$key] = [
                'id' => $order->getId(),
                'totalPrice' => $order->getTotalPrice(),
                'creationDate' => $order->getCreationDate(),
                'products' => $orderProductResource->resourceCollection($order->getProducts())
            ];
        }

        return $this->handleResponse("Order completed", [
            'orders' => $orders
        ], 201);
    }
    /**
     * @Route("/orders/{orderId}", name="orders.show", methods={"GET", "HEAD"})
     */
    public function show(
        UserRepository $ur,
        SymfonyRequest $sr,
        TokenService $ts,
        OrderRepository $or,
        EntityManagerInterface $em,
        $orderId
    ) {
        $request = new Request($sr);
        $user = $request->getUser($ur, $ts);

        $order = $or->findOneById($orderId);

        if (!isset($order)) {
            return $this->handleError("Order not found", [], 404);
        }

        if ($order->getUser() !== $user) {
            return $this->handleError("You're not the owner of this order", [], 403);
        }

        $orderProductResource = new OrderProductResource($em);

        return $this->handleResponse("", [
            'order' => [
                'id' => $order->getId(),
                'totalPrice' => $order->getTotalPrice(),
                'creationDate' => $order->getCreationDate(),
                'products' => $orderProductResource->resourceCollection($order->getProducts())
            ]
        ], 201);
    }
}