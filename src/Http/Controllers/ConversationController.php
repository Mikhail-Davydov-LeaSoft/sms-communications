<?php

namespace FmTod\SmsCommunications\Http\Controllers;

use FmTod\SmsCommunications\Models\AccountPhoneNumber;
use FmTod\SmsCommunications\Models\Conversation;
use FmTod\SmsCommunications\Models\PhoneNumber;
use FmTod\SmsCommunications\Requests\StoreConversationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class ConversationController extends Controller
{
    use \FmTod\SmsCommunications\Traits\SmsServiceTrait;

    public function index(Request $request)
    {
        $data = [
            'conversations' => Conversation::with(['messages.user:id,name', 'phoneNumber.contact', 'accountPhoneNumber.account'])->get(),
            'accountPhoneNumbers' => AccountPhoneNumber::with('account')->get(),
        ];

        return Inertia::render('SmsCommunications', $data);
    }

    public function show(int $conversation_id)
    {
        // Set all messages of conversation in 'read' status
        if (! empty($activeConversation = Conversation::with(['messages'])->where('id', $conversation_id)->first())) {
            $activeConversation->messages
                ->each(function ($item) {
                    if ($item->is_unread) {
                        $item->update(['is_unread' => false]);
                    }
                });
        }

        $conversations = Conversation::with(['messages.user:id,name', 'phoneNumber.contact', 'accountPhoneNumber.account'])->get();

        return Inertia::render('SmsCommunications', [
            'conversations' => $conversations,
            'activeConversation' => $conversations->firstWhere('id', $conversation_id),
            'accountPhoneNumbers' => AccountPhoneNumber::with('account')->get(),
        ]);
    }

    public function store(StoreConversationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (! empty($validated['phone_number'])) {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->value = $validated['phone_number'];
            $phoneNumber->is_landline = false;
            $phoneNumber->can_receive_text = true;
            $phoneNumber->has_whatsapp = false; // TODO Should be check
            $phoneNumber->save();

            $phoneNumberId = $phoneNumber->id;
        } else {
            $phoneNumberId = $validated['phone_number_id'];
        }

        $conversation = Conversation::where([
            'phone_number_id' => $phoneNumberId,
            'account_phone_number_id' => $validated['account_phone_number_id'],
        ])
        ->first();
        if (empty($conversation)) {
            $conversation = new Conversation();
            $conversation->phone_number_id = $phoneNumberId;
            $conversation->account_phone_number_id = $request->input('account_phone_number_id');
            $conversation->save();
        }

        return response()->json($conversation);
    }
}
