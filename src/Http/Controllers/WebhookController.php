<?php

namespace FmTod\SmsCommunications\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{
    public function __invoke(Request $request, string $service): Response|JsonResponse
    {
        if ($response = $this->verify($request, $service)) {
            return $response;
        }

        /** @var array<string, class-string<\FmTod\SmsCommunications\Contracts\ProcessesWebhook>> $processors */
        $processors = config('sms-communications.webhook.processors');

        if (! isset($processors[$service])) {
            abort(404);
        }

        /** @var \FmTod\SmsCommunications\Contracts\ProcessesWebhook $processor */
        $processor = app($processors[$service]);

        $processor::dispatch($request);

        return response()->json(['status' => 'success', 'message' => 'Webhook received successfully']);
    }

    protected function verify(Request $request, string $service): ?Response
    {
        if ($service === 'whatsapp' && $request->input('hub_mode') === 'subscribe') {
            $token = $request->input('hub_verify_token');
            $challenge = $request->input('hub_challenge', '');
            $verifyToken = config('sms-communications.webhook.whatsapp_hub_verify_token');

            return $verifyToken !== $token
                ? response()->noContent(403)
                : response($challenge);
        }

        return null;
    }
}
