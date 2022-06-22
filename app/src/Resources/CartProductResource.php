<?php

namespace App\Resources;

class CartProductResource extends Resource
{
    public function resource($cartProduct)
    {
        $product = [];

        $product['id'] = $cartProduct->getProduct()->getId();
        $product['name'] = $cartProduct->getProduct()->getName();
        $product['description'] = $cartProduct->getProduct()->getDescription();
        $product['price'] = $cartProduct->getProduct()->getPrice();
        $product['photo'] = $cartProduct->getProduct()->getPhoto();
        $product['quantity'] = $cartProduct->getQuantity();

        return $product;
    }

    public function resourceCollection($cartProductCollection)
    {
        $products = [];

        foreach ($cartProductCollection as $key => $cartProduct) {
            $products[] = $this->resource($cartProduct);
        }

        return $products;
    }
}