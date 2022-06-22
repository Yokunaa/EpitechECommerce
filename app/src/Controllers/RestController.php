<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RestController extends AbstractController
{
    protected function handleResponse($message, $data = [], $status = 200)
    {
        return $this->json([
            'message' => $message,
            'data' => $data,
            'status' => $status
        ], $status);
    }

    protected function handleError($message, $data = [], $status = 400)
    {
        return $this->json([
            'message' => $message,
            'errors' => $data,
            'status' => $status
        ], $status);
    }
}