<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Repositories\RoomRepositories\RoomRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckExistRoom
{
    protected RoomRepositoryInterface $roomRepository;
    public function __construct(RoomRepositoryInterface $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $chatId = $request->route('chatId'); // получаем chatId из роутов
        $room = $this->roomRepository->getRoomById($chatId);
        if($room === null)
        {
            return ApiResponse::error("Чат с id = $chatId не существует", null, 404);
        }
        return $next($request);
    }
}
