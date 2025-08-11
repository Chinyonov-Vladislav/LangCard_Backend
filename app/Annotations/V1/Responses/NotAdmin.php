<?php

namespace App\Annotations\V1\Responses;

/**
 *  @OA\Response(
 *      response="NotAdmin",
 *      description="Пользователь не является администратором",
 *      @OA\JsonContent(
 *          type="object",
 *          required = {"status","message","errors"},
 *          @OA\Property(property="status", type="string", example="error"),
 *          @OA\Property(property="message", type="string", example="Пользователь не является администратором"),
 *          @OA\Property(property="errors", type="object", nullable=true, example=null)
 *      )
 * )
 */
class NotAdmin
{

}
