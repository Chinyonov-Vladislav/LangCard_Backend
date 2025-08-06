<?php

namespace App\Annotations\V1\Headers;


/**
 * @OA\Parameter(
 *     parameter="AcceptLanguageHeader",
 *     name="Accept-Language",
 *     in="header",
 *     description="Язык локали",
 *     required=true,
 *     @OA\Schema(
 *         type="string",
 *         enum={"ru", "en"},
 *         default="ru"
 *     )
 * )
 */
class AcceptLanguageHeader
{

}
