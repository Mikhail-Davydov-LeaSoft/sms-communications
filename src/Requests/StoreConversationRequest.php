<?php

namespace FmTod\SmsCommunications\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConversationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'phone_number_id' => 'integer',
            'phone_number' => 'string|phone:AUTO,US',
            'account_phone_number_id' => 'required|integer',
        ];
    }
}
