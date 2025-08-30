<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Repositories\RoomRepositories\RoomRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckChatIsDeleteMiddleware
{
    protected RoomRepositoryInterface $roomRepository;
    public function __construct(RoomRepositoryInterface $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $chatId = $request->route('chatId');
        $room = $this->roomRepository->getRoomById($chatId);
        if($room->deleted_at !== null)
        {
            return ApiResponse::error("Чат был удалён администратором", null, 409);
        }
        return $next($request);
    }
}
