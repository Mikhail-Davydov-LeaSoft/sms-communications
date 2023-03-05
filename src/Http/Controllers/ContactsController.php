<?php

namespace FmTod\SmsCommunications\Http\Controllers;

use FmTod\SmsCommunications\Models\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ContactsController extends Controller
{
    use \FmTod\SmsCommunications\Traits\SmsServiceTrait;

    public function index(): JsonResponse
    {
        $contacts = PhoneNumber::with(['contact'])->get();

        return response()->json($contacts);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $phoneNumber = PhoneNumber::find($id);
        $value = is_null($phoneNumber->blocked_at) ? date('Y-m-d H:i:s') : null;
        $phoneNumber->update(['blocked_at' => $value]);

        return response()->json($value ?? '');
    }
}
