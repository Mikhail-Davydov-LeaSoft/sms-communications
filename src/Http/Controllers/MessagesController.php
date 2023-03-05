<?php

namespace FmTod\SmsCommunications\Http\Controllers;

use FmTod\SmsCommunications\Exceptions\CouldNotCheckUnreadMessage;
use FmTod\SmsCommunications\Exceptions\CouldNotDeleteMessage;
use FmTod\SmsCommunications\Exceptions\CouldNotPinnedMessage;
use FmTod\SmsCommunications\Exceptions\CouldNotSendMessage;
use FmTod\SmsCommunications\Models\Conversation;
use FmTod\SmsCommunications\Models\Message;
use FmTod\SmsCommunications\Requests\DeleteMessagesRequest;
use FmTod\SmsCommunications\Requests\PinMessagesRequest;
use FmTod\SmsCommunications\Requests\StoreMessageRequest;
use FmTod\SmsCommunications\Requests\UnreadMessagesRequest;
use FmTod\SmsCommunications\Traits\SmsServiceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MessagesController extends Controller
{
    use SmsServiceTrait;

    public function store(StoreMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $conversation = Conversation::with('accountPhoneNumber.account')->findOrFail($validated['conversation_id']);
        $account = $conversation->accountPhoneNumber->account;

        if ($validated['channel'] === 'mms') {
            $fileName = $this->uploadFile($validated['file']);
        }

        $service = $this->getService($account);

        try {
            $response = $service->sendMessage(
                [
                    'to' => $conversation->phoneNumber->value->formatE164(),
                    'from' => $conversation->accountPhoneNumber->value->formatE164(),
                    'body' => $validated['channel'] === 'sms' ? $validated['body'] : null,
                    'file' => $validated['channel'] === 'mms' ? $validated['file'] : null,
                    'fileName' => $fileName ?? null,
                ]
            );
        } catch (CouldNotSendMessage $e) {
            throw new CouldNotSendMessage('Message was not sent due to an error '.$e->getMessage(), $e);
        }

        $message = new Message();
        $message->conversation_id = $validated['conversation_id'];
        $message->message_type = $response->message_type;
        $message->is_incoming = false;
        $message->is_unread = false;
        $message->user_id = Auth::user()->id;
        if ($response->service_message_id) {
            $message->service_message_id = $response->service_message_id;
        }
        if ($validated['channel'] === 'sms') {
            $message->body = $validated['body'];
        } else {
            $message->file_name = $fileName;
        }
        $message->save();

        return response()->json($message);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $message = Message::find($id);
        $conversation = Conversation::find((int) $message->conversation_id);
        $account = $conversation->accountPhoneNumber->account()->first();

        try {
            $service = $this->getService($account);
            $response = $service->sendMessage(
                [
                    'to' => $conversation->phoneNumber->value->formatE164(),
                    'from' => $conversation->accountPhoneNumber->value->formatE164(),
                    'body' => $message->message_type == 'text' ? $message->body : null,
                    'fileName' => $message->message_type !== 'text' ? $message->file_name : null,
                    'type' => $message->message_type,
                ]
            );
        } catch (CouldNotSendMessage $e) {
            throw new CouldNotSendMessage('Message was not sent due to an error '.$e->getMessage(), $e);
        }

        $newMessage = new Message();
        $newMessage->conversation_id = $conversation->conversation_id;
        $newMessage->message_type = $message->message_type;
        $newMessage->is_incoming = false;
        $newMessage->is_unread = false;
        $message->user_id = Auth::user()->id;
        if ($response->service_message_id) {
            $message->service_message_id = $response->service_message_id;
        }
        if ($message->message_type == 'text') {
            $newMessage->body = $message->body;
        } else {
            $newMessage->file_name = $message->file_name;
        }
        $newMessage->save();

        return response()->json($newMessage);
    }

    public function deleteMessages(DeleteMessagesRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = Message::destroy($validated['ids']);

        throw_if(! $result, CouldNotDeleteMessage::class, 'Message(s) was(were) not deleted due to an error');

        return response()->json('success');
    }

    public function unreadMessages(UnreadMessagesRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = Message::whereIn('id', $validated['ids'])
               ->update(['is_unread' => 1]);

        throw_if(! $result, CouldNotCheckUnreadMessage::class, 'Message(s) was(were) not checked unreaded due to an error');

        return response()->json('success');
    }

    public function pinMessages(pinMessagesRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = Message::whereIn('id', $validated['ids'])
               ->update(['is_pinned' => 1]);

        throw_if(! $result, CouldNotPinnedMessage::class, 'Message(s) was(were) not pinned due to an error');

        return response()->json('success');
    }

    public function unpinMessages(pinMessagesRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = Message::whereIn('id', $validated['ids'])
               ->update(['is_pinned' => 0]);

        throw_if(! $result, CouldNotPinnedMessage::class, 'Message(s) was(were) not unpinned due to an error');

        return response()->json('success');
    }
}
