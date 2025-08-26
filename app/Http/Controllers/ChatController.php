<?php

namespace App\Http\Controllers;

use App\Enums\GroupChatInviteTypes;
use App\Enums\TypesMessage;
use App\Enums\TypesRoom;
use App\Enums\TypesUserInRoom;
use App\Http\Requests\Api\V1\ChatRequests\BlockUserInCharRequest;
use App\Http\Requests\Api\V1\ChatRequests\CreatingDirectCharRequest;
use App\Http\Requests\Api\V1\ChatRequests\CreatingGroupChatRequest;
use App\Http\Requests\Api\V1\ChatRequests\InvitesInChatsRequests\RequestOrInviteFilterRequest;
use App\Http\Requests\Api\V1\ChatRequests\InvitesInChatsRequests\ResponseUserToRequestOrInvitationToGroupChatRequest;
use App\Http\Requests\Api\V1\ChatRequests\InvitesInChatsRequests\SendInviteToChatRequest;
use App\Http\Requests\Api\V1\ChatRequests\MessagesRequests\MessageChatFilterRequest;
use App\Http\Requests\Api\V1\ChatRequests\MessagesRequests\SendingMessageRequest;
use App\Http\Requests\Api\V1\ChatRequests\MessagesRequests\UpdatingMessageRequest;
use App\Http\Resources\V1\ChatResources\ChatResource;
use App\Http\Resources\V1\ChatResources\RequestOrInvitationResource;
use App\Http\Resources\V1\MessageResources\MessageResource;
use App\Http\Resources\V1\PaginationResources\PaginationResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\EmotionRepositories\EmotionRepositoryInterface;
use App\Repositories\InviteToChatRepositories\InviteToChatRepositoryInterface;
use App\Repositories\MessageEmotionRepositories\MessageEmotionRepositoryInterface;
use App\Repositories\MessageEmotionUserRepositories\MessageEmotionUserRepository;
use App\Repositories\MessageRepositories\MessageRepositoryInterface;
use App\Repositories\RoomRepositories\RoomRepositoryInterface;
use App\Repositories\RoomUserRepositories\RoomUserRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\PaginatorService;

class ChatController extends Controller
{
    protected RoomRepositoryInterface $roomRepository;
    protected RoomUserRepositoryInterface $roomUserRepository;

    protected MessageRepositoryInterface $messageRepository;

    protected UserRepositoryInterface $userRepository;

    protected InviteToChatRepositoryInterface $inviteToChatRepository;

    protected EmotionRepositoryInterface $emotionRepository;

    protected MessageEmotionRepositoryInterface $messageEmotionRepository;

    protected MessageEmotionUserRepository $messageEmotionUserRepository;

    public function __construct(RoomRepositoryInterface $roomRepository,
                                RoomUserRepositoryInterface $roomUserRepository,
                                MessageRepositoryInterface $messageRepository,
                                UserRepositoryInterface $userRepository,
                                InviteToChatRepositoryInterface $inviteToChatRepository,
                                EmotionRepositoryInterface $emotionRepository,
                                MessageEmotionRepositoryInterface $messageEmotionRepository,
                                MessageEmotionUserRepository $messageEmotionUserRepository)
    {
        $this->roomRepository = $roomRepository;
        $this->roomUserRepository = $roomUserRepository;
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
        $this->inviteToChatRepository = $inviteToChatRepository;
        $this->emotionRepository = $emotionRepository;
        $this->messageEmotionRepository = $messageEmotionRepository;
        $this->messageEmotionUserRepository = $messageEmotionUserRepository;
    }

    public function getChats()
    {
        $chats = $this->roomRepository->getRoomsOfUser(auth()->id());
        return ApiResponse::success("Комнаты авторизованного пользователя", (object)['rooms' => ChatResource::collection($chats)]);
    }

    public function createGroupChat(CreatingGroupChatRequest $request)
    {
        $newGroupChat = $this->roomRepository->createGroupRoom($request->name, $request->is_private);
        $this->roomUserRepository->addUserToRoom($newGroupChat->id, auth()->id(), TypesUserInRoom::Admin->value);
        $message = "Групповой чат \"$newGroupChat->name\" был создан";
        $this->messageRepository->saveNewMessage(auth()->id(), $newGroupChat->id, $message, TypesMessage::Info->value);
        return ApiResponse::success("Новый групповой чат успешно создан", null, 201);
    }

    public function createDirectChat(CreatingDirectCharRequest $request)
    {
        $isExistDirectRoomForTwoUser = $this->roomRepository->isExistDirectRoomForUsers(auth()->id(), $request->second_user_id);
        if($isExistDirectRoomForTwoUser)
        {
            return ApiResponse::error("Комната для общения двух людей уже существует", null, 409);
        }
        $newDirectRoom = $this->roomRepository->createDirectRoom();
        $this->roomUserRepository->addUserToRoom($newDirectRoom->id, auth()->id(), TypesUserInRoom::Member->value);
        $this->roomUserRepository->addUserToRoom($newDirectRoom->id, $request->second_user_id, TypesUserInRoom::Member->value);
        $firstUser = $this->userRepository->getInfoUserById(auth()->id());
        $secondUser = $this->userRepository->getInfoUserById($request->second_user_id);
        $message = "Пользователь $firstUser->name начал диалог с пользователем $secondUser->name";
        $this->messageRepository->saveNewMessage(auth()->id(), $newDirectRoom->id, $message, TypesMessage::Info->value);
        return ApiResponse::success("Новый личный чат успешно создан", null, 201);
    }

    public function blockUserInChat(int $chatId, BlockUserInCharRequest $request)
    {
        $room = $this->roomRepository->getRoomById($chatId);
        $firstUser = $this->roomUserRepository->getUserInRoom($chatId, auth()->id());
        $secondUser = $this->roomUserRepository->getUserInRoom($chatId, $request->user_id);
        if($firstUser === null)
        {
            return ApiResponse::error("Вы не являетесь участником этого чата", null, 404);
        }
        if($secondUser === null)
        {
            return ApiResponse::error("Пользователя, которого вы хотите заблокировать/разблокировать, не является участником чата", null, 404);
        }
        if($room->room_type === TypesRoom::Group->value) // если чат групповой
        {
            if($firstUser->type !== TypesUserInRoom::Admin->value)
            {
                return ApiResponse::error("Вы не являетесь администратором в чате, поэтому вы не можете заблокировать/разблокировать пользователя");
            }
        }
        $this->roomUserRepository->changeBlockedStatusForUser($secondUser);
        return ApiResponse::success("Статус блокировки пользователя успешно изменён", null, 201);
    }
    public function sendMessage(int $chatId, SendingMessageRequest $request)
    {
        $userInRoom = $this->roomUserRepository->getUserInRoom($chatId, auth()->id());
        if($userInRoom === null)
        {
            return ApiResponse::error("Пользователь не является участником чата", null, 404);
        }
        if($userInRoom->is_blocked)
        {
            return ApiResponse::error("В данном чате пользователь заблокирован и не может отправлять сообщения", null, 409);
        }
        $newMessage = $this->messageRepository->saveNewMessage(auth()->id(), $chatId, $request->message, TypesMessage::MessageFromUser->value);
        foreach ($request->emotions as $emotionId)
        {
            $this->messageEmotionRepository->addEmotionToMessage($newMessage->id, $emotionId);
        }
        //TODO сделать отправку сообщения в чата с помощью WebSockets
        return ApiResponse::success("Сообщение успешно отправлено", null, 201);
    }

    public function updateMessage(int $chatId,int $messageId, UpdatingMessageRequest $request)
    {
        $message = $this->messageRepository->getMessage($messageId);
        if($message === null)
        {
            return ApiResponse::error("Сообщение с id = $messageId не найдено", null, 404);
        }
        $userInRoom = $this->roomUserRepository->getUserInRoom($chatId, auth()->id());
        if($userInRoom === null)
        {
            return ApiResponse::error("Пользователь не является участником чата", null, 404);
        }
        if($userInRoom->is_blocked)
        {
            return ApiResponse::error("В данном чате пользователь заблокирован и не может редактировать сообщения", null, 409);
        }
        if($message->user_id !== auth()->id())
        {
            return ApiResponse::error("Пользователь не является автором редактируемого сообщения", null, 409);
        }
        if($message->type === TypesMessage::Info->value)
        {
            return ApiResponse::error("Редактируемое сообщение является информационным, из-за чего его нельзя редактировать", null, 409);
        }
        $this->messageRepository->updateMessage($messageId, $request->message_text);
        return ApiResponse::success("Сообщение было успешно отредактировано", null, 201);
    }

    public function deleteMessage(int $chatId, int $messageId)
    {
        $message = $this->messageRepository->getMessage($messageId);
        if($message === null)
        {
            return ApiResponse::error("Сообщение с id = $messageId не найдено", null, 404);
        }
        $userInRoom = $this->roomUserRepository->getUserInRoom($chatId, auth()->id());
        if($userInRoom === null)
        {
            return ApiResponse::error("Пользователь не является участником чата", null, 404);
        }
        if($userInRoom->is_blocked)
        {
            return ApiResponse::error("В данном чате пользователь заблокирован и не может удалять сообщения", null, 409);
        }
        if($message->user_id !== auth()->id())
        {
            return ApiResponse::error("Пользователь не является автором удаляемого сообщения", null, 409);
        }
        if($message->type === TypesMessage::Info->value)
        {
            return ApiResponse::error("Удаляемое сообщение является информационным, из-за чего его нельзя удалять", null, 409);
        }
        $this->messageRepository->deleteMessage($messageId);
        return ApiResponse::success("Сообщение с id = $messageId было успешно удалено", null, 201);
    }
    public function getInvites(PaginatorService $paginator, RequestOrInviteFilterRequest $request)
    {
        $acceptedSortDirection = ['asc', 'desc'];
        $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
        $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        $sortDirection = $request->input('sortDirection', 'asc');
        if(!in_array($sortDirection, $acceptedSortDirection))
        {
            $sortDirection = 'asc';
        }
        $data = $this->inviteToChatRepository->getRequestOrInvitationForUserWithPaginationAndSortDirection($paginator, auth()->id(), $countOnPage, $numberCurrentPage, $sortDirection);
        return ApiResponse::success("Получены заявки/приглашения на вступление в закрытые чаты", (object)["items"=>RequestOrInvitationResource::collection($data['items']),
            'pagination' => new PaginationResource($data['pagination'])]);
    }
    public function sendRequest(int $chatId)
    {
        $room = $this->roomRepository->getRoomByIdWithAdmin($chatId);
        if ($room->room_type !== TypesRoom::Group->value) {
            return ApiResponse::error("Невозможно создать приглашение в комнату с id = $chatId, так как она не является типа group", null, 409);
        }
        if ($room->is_private === false) {
            return ApiResponse::error("Невозможно создание приглашение в комнату с id = $chatId, так как она не является закрытой", null, 409);
        }
        $senderUserInRoom = $this->roomUserRepository->getUserInRoom($chatId, auth()->id());
        if ($senderUserInRoom !== null) {
            return ApiResponse::error("Пользователь уже является участником комнаты", null, 409);
        }
        if ($room->admin === null)
        {
            return ApiResponse::error("Для комнаты с id = $chatId не найден администратор", null, 404);
        }
        if($this->inviteToChatRepository->isExistRequestOrInvitationToChat($chatId, auth()->id(), $room->admin->id, GroupChatInviteTypes::Request->value))
        {
            return ApiResponse::error("Авторизованный пользователь уже делал запрос на вступление в групповой чат", null, 409);
        }
        $newInviteToGroupChat = $this->inviteToChatRepository->saveInviteToGroupChat($chatId, auth()->id(), $room->admin->id, GroupChatInviteTypes::Request->value);
        return ApiResponse::success("Вы отправили запрос на вступление в закрытый чат с id = $chatId", null, 201);
    }
    public function sendInvite(int $chatId, SendInviteToChatRequest $request)
    {
        $room = $this->roomRepository->getRoomById($chatId);
        if($room->room_type !== TypesRoom::Group->value)
        {
            return ApiResponse::error("Невозможно создать приглашение в комнату с id = $chatId, так как она не является типа group",null, 409);
        }
        if($room->is_private === false)
        {
            return ApiResponse::error("Невозможно создание приглашение в комнату с id = $chatId, так как она не является закрытой", null, 409);
        }
        $senderUserInRoom = $this->roomUserRepository->getUserInRoom($chatId, auth()->id());
        if($senderUserInRoom === null)
        {
            return ApiResponse::error("Пользователь не является участником чата", null, 404);
        }
        if($senderUserInRoom->type !== TypesUserInRoom::Admin->value)
        {
            return ApiResponse::error("Пользователь не является администратором группового чата, из-за чего не может выдавать приглашение", null, 409);
        }
        $recipientUserInRoom = $this->roomUserRepository->getUserInRoom($chatId, $request->user_id);
        if($recipientUserInRoom !== null)
        {
            return ApiResponse::error("Невозможно выдать пользователю приглашение в групповой чат, так как он уже состоит в нём", null, 409);
        }
        if($this->inviteToChatRepository->isExistRequestOrInvitationToChat($chatId, auth()->id(), $request->user_id, GroupChatInviteTypes::Invitation->value))
        {
            return ApiResponse::error("Авторизованный пользователь уже высылал приглашение пользователю в групповой чат", null, 409);
        }
        $newInviteToGroupChat = $this->inviteToChatRepository->saveInviteToGroupChat($chatId, auth()->id(), $request->user_id, GroupChatInviteTypes::Invitation->value);
        return ApiResponse::success("Приглашение в чат с id = $chatId было отправлено пользователю с id = $request->user_id");
    }
    public function deleteInvite(int $id)
    {
       $request_invite_info = $this->inviteToChatRepository->getRequestOrInvitationToChatById($id);
       if($request_invite_info === null)
       {
           return ApiResponse::error("Запрос на вступление в группу или приглашение в группу не найдено", null, 404);
       }
       if($request_invite_info->accepted !== null)
       {
           return ApiResponse::error("Пользователь - получатель уже ответил на запрос вступления/приглашения в группу", null, 409);
       }
       if($request_invite_info->sender_user_id !== auth()->id())
       {
           return ApiResponse::error("Авторизованный пользователь не является отправителем запроса на вступление/приглашение в группу, поэтому не может выполнить его удаление", null, 409);
       }
       $this->inviteToChatRepository->deleteRequestInviteToGroupChat($id);
       return ApiResponse::success("Запрос на вступление/приглашение в группу был успешно удалён");
    }
    public function responseToChatInvitationFromUser(int $id, ResponseUserToRequestOrInvitationToGroupChatRequest $request)
    {
        $request_invite_info = $this->inviteToChatRepository->getRequestOrInvitationToChatById($id);
        if($request_invite_info === null)
        {
            return ApiResponse::error("Запрос на вступление в группу или приглашение в группу не найдено", null, 404);
        }
        if($request_invite_info->recipient_user_id !== auth()->id())
        {
            return ApiResponse::error("Авторизованный пользователь не является пользователем, для которого предназначена заявка на вступление или приглашение в группу", null, 409);
        }
        if($request_invite_info->accepted !== null)
        {
            return ApiResponse::error("Пользователь, для которого была предназначена заявка на вступление или приглашение в группу, уже предоставил на неё ответ", null, 409);
        }
        $this->inviteToChatRepository->setResponseUserToRequestOrInvitationToChat($id, $request->response_user);
        if($request->response_user === false)
        {
            return ApiResponse::success("Пользователь отклонил заявку на вступление/приглашение в группу");
        }
        if($request_invite_info->type === GroupChatInviteTypes::Request->value)
        {
            $adminGroupId = $request_invite_info->recipient_user_id;
        }
        else
        {
            $adminGroupId = $request_invite_info->sender_user_id;
        }
        $this->roomUserRepository->addUserToRoom($request_invite_info->room_id, auth()->id(), TypesUserInRoom::Member->value);
        $nicknameUser = auth()->user()->name;
        $message = "В групповой чат был создан добавлен пользователь: $nicknameUser";
        $this->messageRepository->saveNewMessage($adminGroupId, $request_invite_info->room_id, $message, TypesMessage::Info->value);
        return ApiResponse::success("Пользователь принял заявку на вступление/приглашение в группу");
    }

    public function addOrDeleteEmotion(int $chatId, int $messageId, int $emotionId)
    {
        $userInRoom = $this->roomUserRepository->getUserInRoom($chatId, auth()->id());
        if($userInRoom === null)
        {
            return ApiResponse::error("Пользователь не является участником чата", null, 404);
        }
        if($userInRoom->is_blocked)
        {
            return ApiResponse::error("В данном чате пользователь заблокирован и не может оставлять реакции на сообщения", null, 409);
        }
        $message = $this->messageRepository->getMessage($messageId);
        if($message === null)
        {
            return ApiResponse::error("Сообщение с id = $messageId не найдено", null, 404);
        }
        $emotion = $this->emotionRepository->getEmotion($emotionId);
        if($emotion === null)
        {
            return ApiResponse::error("Эмоция с id = $emotionId не найдена", null, 404);
        }
        $newMessageEmotion = $this->messageEmotionRepository->getMessageEmotion($messageId, $emotionId); //разрешенная эмоция для сообщения (объект класса MessageEmotion)
        if($newMessageEmotion === null)
        {
            return ApiResponse::error("Для сообщения с id = $messageId не разрешена эмоция с id = $emotionId", null, 409);
        }
        $emotionFromUserForMessage = $this->emotionRepository->getEmotionForMessageFromUser(auth()->id(), $messageId); // объект класса Emotion
        if($emotionFromUserForMessage === null)
        {
            $this->messageEmotionUserRepository->addNewEmotionToMessageFromUser($newMessageEmotion->id, auth()->id());
            return ApiResponse::success("Эмоция успешно оставлена к сообщению");
        }
        // пользователь оставлял эмоцию к сообщению
        if($emotionFromUserForMessage->id === $emotionId) //удалить эмоцию, если новая эмоция совпадает с той, которую прислал пользователь в запросе
        {
            $this->messageEmotionUserRepository->removeEmotionForMessageFromUser($newMessageEmotion->id, auth()->id());
            return ApiResponse::success("Оставленная эмоция успешно удалена из сообщения");
        }
        $currentMessageEmotion = $this->messageEmotionRepository->getMessageEmotion($messageId, $emotionFromUserForMessage->id);
        $this->messageEmotionUserRepository->removeEmotionForMessageFromUser($currentMessageEmotion->id, auth()->id());
        $this->messageEmotionUserRepository->addNewEmotionToMessageFromUser($newMessageEmotion->id, auth()->id());
        return ApiResponse::success("Эмоция успешно изменена в сообщении");
    }
    public function getMessages(int $chatId, MessageChatFilterRequest $request)
    {
        $limit = $request->limit === null ? config("app.limit_count_messages_per_request"): $request->limit;
        $data = $this->messageRepository->getMessagesOfChatWithPagination(auth()->id(),$chatId, $limit, $request->last_message_id);
        return ApiResponse::success("Сообщения для чата с id = $chatId", (object)["messages"=>MessageResource::collection($data["messages"]), "hasMoreMessages"=>$data["hasMoreMessages"]]);
    }
}
