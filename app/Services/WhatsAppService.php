<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $client;
    protected $fromNumber;

    public function __construct()
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $this->fromNumber = 'whatsapp:' . config('services.twilio.whatsapp_number');
        
        $this->client = new Client($accountSid, $authToken);
    }

    public function sendMessage($to, $message)
    {
        try {
            // Format the phone number to include whatsapp: prefix
            $to = 'whatsapp:' . $this->formatPhoneNumber($to);
            
            $response = $this->client->messages->create(
                $to,
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );

            return [
                'status' => 'success',
                'message' => 'WhatsApp message sent successfully',
                'sid' => $response->sid
            ];
        } catch (\Exception $e) {
            Log::error('Twilio WhatsApp Error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Error sending WhatsApp message: ' . $e->getMessage()
            ];
        }
    }

    protected function formatPhoneNumber($number)
    {
        // Remove any non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // If number doesn't start with +, add it
        if (substr($number, 0, 1) !== '+') {
            $number = '+' . $number;
        }
        
        return $number;
    }
} 