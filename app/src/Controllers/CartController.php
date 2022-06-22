<?php

namespace App\Controllers;

use App\Entity\Order;
use DateTimeImmutable;
use App\Helpers\Request;
use App\Entity\CartProduct;
use App\Entity\OrderProduct;
use App\Services\TokenService;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\CartProductRepository;
use App\Repository\OrderProductRepository;
use App\Resources\CartProductResource;
use App\Resources\OrderProductResource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @Route("api")
 */
class CartController extends RestController implements TokenAuthenticatedController
{
    /**
     * @Route("/carts/{productId}", methods={"POST"})
     */
    public function addProduct(
        SymfonyRequest $sr,
        UserRepository $ur,
        TokenService $ts,
        ProductRepository $pr,
        CartRepository $cr,
        CartProductRepository $cpr,
        EntityManagerInterface $em,
        $productId
    ) {
        $request = new Request($sr);
        $user = $request->getUser($ur, $ts);

        $product = $pr->findOneById($productId);

        if (!isset($product)) {
            return $this->handleError("Product not found", [], '404');
        }

        $cart = $user->getCart();

        $cartProduct = $cpr->findOneByCartAndProduct($cart->getId(), $product->getId());

        if (!isset($cartProduct)) {
            $cartProduct = new CartProduct();
            $cartProduct->setCart($cart);
            $cartProduct->setProduct($product);
            $cartProduct->setQuantity(0);
            $cpr->add($cartProduct);

            $cart->addCartProduct($cartProduct);
            $cr->persist();
        }

        $cartProduct->setQuantity($cartProduct->getQuantity() + 1);
        $cpr->persist();

        $cartProductResource = new CartProductResource($em);

        return $this->handleResponse("Product added to cart", [
            'products' => $cartProductResource->resourceCollection($cart->getCartProducts())
        ]);
    }

    /**
     * @Route("/carts/{productId}", methods={"DELETE"})
     */
    public function removeProduct(
        SymfonyRequest $sr,
        UserRepository $ur,
        TokenService $ts,
        ProductRepository $pr,
        CartRepository $cr,
        CartProductRepository $cpr,
        EntityManagerInterface $em,
        $productId
    ) {
        $request = new Request($sr);
        $user = $request->getUser($ur, $ts);

        $product = $pr->findOneById($productId);

        if (!isset($product)) {
            return $this->handleError("Product not found", [], '404');
        }

        $cart = $user->getCart();

        $cartProduct = $cpr->findOneByCartAndProduct($cart->getId(), $product->getId());

        if (!isset($cartProduct)) {
            return $this->handleError('This product isn\'t in your cart', [], 404);
        }

        $quantity = max(0, $cartProduct->getQuantity() - 1);
        $cartProduct->setQuantity($quantity);
        if ($quantity === 0) {
            $cpr->remove($cartProduct);
            $cart->removeCartProduct($cartProduct);
            $cr->persist();
        }

        $cpr->persist();

        $cartProductResource = new CartProductResource($em);

        return $this->handleResponse("Product removed from cart", [
            'products' => $cartProductResource->resourceCollection($cart->getCartProducts())
        ]);
    }

    /**
     * @Route("/carts", methods={"GET", "HEAD"})
     */
    public function cart(
        SymfonyRequest $sr,
        UserRepository $ur,
        TokenService $ts,
        EntityManagerInterface $em
    ) {
        $request = new Request($sr);
        $user = $request->getUser($ur, $ts);

        $cart = $user->getCart();

        $cartProductResource = new CartProductResource($em);

        return $this->handleResponse("", [
            'products' => $cartProductResource->resourceCollection($cart->getCartProducts())
        ]);
    }

    /**
     * @Route("/carts/validate", methods={"GET", "HEAD"})
     */
    public function validate(
        SymfonyRequest $sr,
        UserRepository $ur,
        TokenService $ts,
        OrderRepository $or,
        OrderProductRepository $opr,
        EntityManagerInterface $em
    ) {
        $request = new Request($sr);
        $user = $request->getUser($ur, $ts);

        $cart = $user->getCart();

        if ($cart->getCartProducts()->count() < 1) {
            return $this->handleError("Your cart is empty");
        }

        $order = new Order();
        $order->setCreationDate(new DateTimeImmutable());
        $order->setTotalPrice(0);
        $order->setUser($user);
        $or->add($order);

        $totalPrice = 0;
        foreach ($cart->getCartProducts() as $cartProduct) {
            $product = $cartProduct->getProduct();
            $totalPrice += $product->getPrice() * $cartProduct->getQuantity();

            $orderProduct = new OrderProduct();
            $orderProduct->setTheOrder($order);
            $orderProduct->setProduct($product);
            $orderProduct->setQuantity($cartProduct->getQuantity());
            $opr->add($orderProduct);

            $order->addProduct($orderProduct);
        }

        $order->setTotalPrice($totalPrice);
        $or->persist();

        $orderProductResource = new OrderProductResource($em);

        return $this->handleResponse("Order completed", [
            'order' => [
                'id' => $order->getId(),
                'totalPrice' => $order->getTotalPrice(),
                'creationDate' => $order->getCreationDate(),
                'products' => $orderProductResource->resourceCollection($order->getProducts())
            ]
        ], 201);
    }
}