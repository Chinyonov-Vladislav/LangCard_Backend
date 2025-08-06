<?php

namespace App\Annotations\V1\Responses;

/**
 * @OA\Response(
 *     response="Unauthorized",
 *     description="Пользователь не авторизован",
 *     @OA\JsonContent(
 *         @OA\Property(property="status", type="string", example="error"),
 *         @OA\Property(property="message", type="string", example="Пользователь не авторизован и не имеет доступа к данным"),
 *         @OA\Property(property="errors", type="object", nullable=true, example=null)
 *     )
 * )
 */
class Unauthorized
{

}
