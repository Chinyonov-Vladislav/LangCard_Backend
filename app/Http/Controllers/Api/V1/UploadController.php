<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ErrorDefiningFile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UploadRequest;
use App\Http\Responses\ApiResponse;
use App\Services\FileServices\SaveFileService;

class UploadController extends Controller
{
    protected SaveFileService $saveFileService;

    public function __construct()
    {
        $this->saveFileService = new SaveFileService();
    }
    /**
     * @OA\Post(
     *     path="/upload",
     *     operationId="uploadFile",
     *     tags={"Upload"},
     *     summary="Загрузить файл на сервер",
     *     description="Загружает файл (максимум 10 МБ) и возвращает путь к нему. Доступ только авторизованным пользователям.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Файл для загрузки",
     *         @OA\JsonContent(ref="#/components/schemas/UploadRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Файл успешно загружен",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Файл был сохранён на сервере"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="path", type="string", example="/uploads/files/document.pdf")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка при определении файла",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status","message","errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Ошибка определения типа файла"),
     *             @OA\Property(property="errors", type="object",nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin"),
     * )
     */
    public function uploadFile(UploadRequest $request)
    {
        try {
            $path = $this->saveFileService->saveFile($request->file);
            return ApiResponse::success('Файл был сохранён на сервере',(object)['path' => $path]);
        }
        catch (ErrorDefiningFile $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }
}
