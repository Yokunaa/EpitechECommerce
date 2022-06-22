<?php

namespace App\Resources;

use App\Resources\Resource;

class ProductResource extends Resource
{
    protected $hidden = ['user'];
}