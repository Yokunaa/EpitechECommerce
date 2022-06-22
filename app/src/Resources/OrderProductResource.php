<?php

namespace App\Resources;

class OrderProductResource extends Resource
{
    public function resource($orderProduct)
    {
        $product = [];

        $product['id'] = $orderProduct->getProduct()->getId();
        $product['name'] = $orderProduct->getProduct()->getName();
        $product['description'] = $orderProduct->getProduct()->getDescription();
        $product['price'] = $orderProduct->getProduct()->getPrice();
        $product['photo'] = $orderProduct->getProduct()->getPhoto();
        $product['quantity'] = $orderProduct->getQuantity();

        return $product;
    }

    public function resourceCollection($orderProductCollection)
    {
        $products = [];

        foreach ($orderProductCollection as $key => $orderProduct) {
            $products[] = $this->resource($orderProduct);
        }

        return $products;
    }
}