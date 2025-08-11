<?php

namespace App\Annotations\V1\Responses;

/**
 *  @OA\Response(
 *      response="NotVerifiedEmail",
 *      description="Электронный адрес пользователя не подтвержден",
 *      @OA\JsonContent(
 *          type="object",
 *          required = {"status","message","errors"},
 *          @OA\Property(property="status", type="string", example="error"),
 *          @OA\Property(property="message", type="string", example="Электронная почта авторизованного пользователя не подтверждена"),
 *          @OA\Property(property="errors", type="object", nullable=true, example=null)
 *      )
 * )
 */
class NotVerifiedEmail
{

}
