<?php

namespace FmTod\SmsCommunications\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    use \FmTod\SmsCommunications\Traits\SmsServiceTrait;

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
        $all_ext = implode(',', $this->allExtensions());

        return [
            'conversation_id' => 'required|integer',
            'body' => 'nullable|string',
            'channel' => 'required|string',
            'file' => 'nullable|file|mimes:'.$all_ext.'|max:'.(int) config('sms-communications.mms.size') * 1000,
        ];
    }
}
