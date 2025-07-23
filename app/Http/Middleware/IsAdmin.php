<?php

namespace App\Http\Middleware;

use App\Enums\TypeUsers;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUser = $request->user();
        if ($currentUser->type_user !== TypeUsers::Admin->value) {
            return ApiResponse::error(__('api.user_not_admin'),null,403);
        }
        return $next($request);
    }
}
