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
