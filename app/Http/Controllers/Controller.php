<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;


/**
 * @OA\Info(
 *     title="My API",
 *     version="1.0.0",
 *     description="Description of my API"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

}
