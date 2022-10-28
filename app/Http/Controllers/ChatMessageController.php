<?php

namespace App\Http\Controllers;

use App\Events\NewMessageSent;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'page' => 'required|numeric',
            'page_size' => 'nullable|numeric'
        ]);
        $chatId = $data['chat_id'];
        $currentPage = $data['page'];
        $pageSize = $data['page_size'] ?? 15;

        $messages = ChatMessage::where('chat_id', $chatId)
            ->with('user')
            ->latest('created_at')
            ->simplePaginate(
                $pageSize,
                ['*'],
                'page',
                $currentPage
            );

        return $this->success($messages->getCollection());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string'
        ]);
        $data['user_id'] = auth()->user()->id;

        $chatMessage = ChatMessage::create($data);
        $chatMessage->load('user');

        ///__TODO and broadcast event to pusher and send notification to onesignal service
        $this->sendNotificationToOther($chatMessage);

        return $this->success($chatMessage, 'Message has been sent successfully!');
    }

    /**
     * Send notification to another
     *
     */
    public function sendNotificationToOther(ChatMessage $chatMessage): void
    {
        // $chatId = $chatMessage->chatId;

        broadcast(new NewMessageSent($chatMessage))->toOthers();

        $user = auth()->user();
        $userId = $user->id;

        $chat = Chat::where('id', $chatMessage->chat_id)
            ->with(['participants' => function ($query) use ($userId) {
                $query->where('user_id', '!=', $userId);
            }])
            ->first();

        if (count($chat->participants) > 0) {
            $otherUserId = $chat->participants[0]->user_id;

            $otherUser = User::where('id', $otherUserId)->first();

            $otherUser->sendMessageNotification([
                'messageData' => [
                    'senderName' => $user->name,
                    'message' => $chatMessage->message,
                    'chatId' => $chatMessage->chatId,
                ]
            ]);
        }
    }
}
