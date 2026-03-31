<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    public static function sendAttendance($studentName, $time, $phone)
    {
        try {
            $phone = preg_replace('/[^0-9]/', '', $phone);

            if (strlen($phone) !== 9) {
                return;
            }

            $twilio = new Client(
                env('TWILIO_SID'),
                env('TWILIO_TOKEN')
            );

            $twilio->messages->create(
                "whatsapp:+51{$phone}",
                [
                    "from" => env('TWILIO_WHATSAPP_FROM'),
                    "contentSid" => env('TWILIO_CONTENT_SID'),
                    "contentVariables" => json_encode([
                        "1" => $studentName,
                        "2" => $time
                    ])
                ]
            );

        } catch (\Exception $e) {
            Log::error('Twilio error', [
                'message' => $e->getMessage(),
                'phone' => $phone,
                'student' => $studentName
            ]);
        }
    }
}