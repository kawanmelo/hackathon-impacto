<?php

namespace App\Enums;

enum ApiStatus:string
{
    case Success = 'success';
    case Error = 'error';
}
