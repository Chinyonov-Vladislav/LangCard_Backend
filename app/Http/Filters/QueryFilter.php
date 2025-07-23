<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QueryFilter
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @param Request $request
     */

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param Builder $builder
     */
    public function apply(Builder $builder): void
    {
        //для модели apiLimit
        $hasDay = isset($filters['day']);
        $hasRequestCount = isset($filters['requestCount']);
        //
        $this->builder = $builder;
        foreach ($this->fields() as $field => $value) {
            if ($hasDay && in_array($field, ['fromDay', 'toDay'])) {
                continue; // пропустить fromDay и toDay, если есть day
            }
            if($hasRequestCount && in_array($field, ['minRequestCount', 'maxRequestCount' ])) {
                continue;
            }

            $method = Str::camel($field);
            if (method_exists($this, $method)) {
                call_user_func_array([$this, $method], (array)$value);
            }
        }
    }

    /**
     * @return array
     */
    protected function fields(): array
    {
        return array_filter(
            array_map('trim', $this->request->all())
        );
    }
}
