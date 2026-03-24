<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="Lang Cards API v1",
 *     version="1.0.0",
 *     description="Lang Cards API",
 *     @OA\Contact(email="vlad2000100600@gmail.com")
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST_V1,
 *     description="Server API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ApiController extends Controller
{
    //
}
