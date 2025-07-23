<?php

namespace App\Http\Requests\Api\V1\StatsRequests;

use App\Http\Responses\ApiResponse;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DatesForCountUsersByMonthsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'start_month' => [
                'nullable',
                'required_with:end_month',
                'date_format:Y-m',
                'before_or_equal:end_month',
            ],
            'end_month' => [
                'nullable',
                'required_with:start_month',
                'date_format:Y-m',
                'after_or_equal:start_month',
            ]
        ];
    }
    public function messages(): array
    {
        return [
            'start_month.date_format' => 'Start month must be in YYYY-MM format',
            'end_month.date_format' => 'End month must be in YYYY-MM format',
            'start_month.before_or_equal' => 'Start month must be before or equal to end month',
            'end_month.after_or_equal' => 'End month must be after or equal to start month',
            'start_month.required_with' => 'Start month is required when end month is provided',
            'end_month.required_with' => 'End month is required when start month is provided',
        ];
    }
    public function getStartMonth(): Carbon
    {
        if($this->has('start_month'))
        {
            return Carbon::parse($this->input('start_month'))->startOfMonth();
        }
        return Carbon::now()->startOfYear();
    }

    public function getEndMonth(): Carbon
    {
        if($this->has('end_month'))
        {
            return Carbon::parse($this->input('end_month'))->endOfMonth();
        }
        return Carbon::now()->endOfMonth();
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('The given data was invalid', $validator->errors(), 422));
    }
}
