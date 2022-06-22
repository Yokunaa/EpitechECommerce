<?php

namespace App\Resources;

use App\Resources\Resource;

class UserResource extends Resource
{
    protected $hidden = ['id', 'password'];
}