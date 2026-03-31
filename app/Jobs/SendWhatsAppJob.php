<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $phone;
    public $message;

    public function __construct($phone, $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle()
    {
        try {
            $response = Http::post(env('WHATSAPP_API_URL') . '/send', [
                'phone' => $this->phone,
                'message' => $this->message
            ]);

            Log::info('WhatsApp response', [
                'phone' => $this->phone,
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            sleep(2);
        } catch (\Exception $e) {
            Log::error('WhatsApp error', [
                'message' => $e->getMessage(),
                'phone' => $this->phone
            ]);
        }
    }
}
