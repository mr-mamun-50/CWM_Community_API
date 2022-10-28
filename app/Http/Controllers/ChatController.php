<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'is_private' => 'nullable|boolean',
        ]);

        $isPrivate = 1;

        if ($request->has('is_private')) {
            $isPrivate = (int)$data['is_private'];
        }

        $chats = Chat::where('is_private', $isPrivate)
            ->hasParticipant(auth()->user()->id)
            ->whereHas('messages')
            ->with('lastMessage.user', 'participants.user')
            ->latest('updated_at')
            ->get();

        return $this->success($chats);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->prepareStoreData($request);

        if ($data['user_id'] === $data['otherUserId']) {
            return $this->error('You cannot create a chat with your own!');
        }

        $previousChat = $this->getPreviousChat($data['otherUserId']);

        if ($previousChat === null) {

            $chat = Chat::create($data['data']);
            $chat->participants()->createMany([
                [
                    'user_id' => $data['user_id']
                ],
                [
                    'user_id' => $data['otherUserId']
                ],
            ]);

            $chat->refresh()->load('lastMessage.user', 'participants.user');
            return $this->success($chat);
        }

        return $this->success($previousChat->load('lastMessage.user', 'participants.user'));
    }

    private function getPreviousChat(int $otherUserId): mixed
    {
        $userId = auth()->user()->id;

        return Chat::where('is_private', 1)
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereHas('participants', function ($query) use ($otherUserId) {
                $query->where('user_id', $otherUserId);
            })
            ->first();
    }

    public function  prepareStoreData(Request $request): array
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'nullable',
            'is_private' => 'nullable|boolean',
        ]);

        $otherUserId = (int)$data['user_id'];
        unset($data['user_id']);
        $data['created_by'] = auth()->user()->id;

        return [
            'otherUserId' => $otherUserId,
            'user_id' => auth()->user()->id,
            'data' => $data
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function show(Chat $chat): JsonResponse
    {
        $chat->load('messages.user', 'participants.user');

        return $this->success($chat);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function edit(Chat $chat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Chat $chat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function destroy(Chat $chat)
    {
        //
    }
}
