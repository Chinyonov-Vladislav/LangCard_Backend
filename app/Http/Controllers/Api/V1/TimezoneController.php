<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Repositories\TimezoneRepositories\TimezoneRepositoryInterface;
use Illuminate\Http\Request;
class TimezoneController extends Controller
{
    protected TimezoneRepositoryInterface $timezoneRepository;
    public function __construct(TimezoneRepositoryInterface $timezoneRepository)
    {
        $this->timezoneRepository = $timezoneRepository;
    }
    public function getTimezones(Request $request)
    {
        $fields = explode(',', $request->get('fields'));
        $data = $this->timezoneRepository->getAllTimezones($fields);
        return ApiResponse::success(__('api.timezone_data'),(object)['timezones'=>$data]);
    }
}
