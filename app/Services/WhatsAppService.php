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
        try {
            $accountSid = config('services.twilio.account_sid');
            $authToken = config('services.twilio.auth_token');
            $this->fromNumber = 'whatsapp:' . config('services.twilio.whatsapp_number');
            
            if (empty($accountSid) || empty($authToken) || empty($this->fromNumber)) {
                Log::error('Twilio configuration missing', [
                    'account_sid_exists' => !empty($accountSid),
                    'auth_token_exists' => !empty($authToken),
                    'whatsapp_number_exists' => !empty($this->fromNumber)
                ]);
                throw new \Exception('Twilio configuration is incomplete');
            }
            
            $this->client = new Client($accountSid, $authToken);
        } catch (\Exception $e) {
            Log::error('Twilio initialization error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendMessage($to, $message)
    {
        try {
            if (empty($to) || empty($message)) {
                Log::error('Invalid message parameters', [
                    'to' => $to,
                    'message_length' => strlen($message)
                ]);
                return [
                    'status' => 'error',
                    'message' => 'Invalid message parameters'
                ];
            }

            // Format the phone number to include whatsapp: prefix
            $to = 'whatsapp:' . $this->formatPhoneNumber($to);
            
            Log::info('Sending WhatsApp message', [
                'to' => $to,
                'from' => $this->fromNumber,
                'message_length' => strlen($message)
            ]);

            $response = $this->client->messages->create(
                $to,
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );

            Log::info('WhatsApp message sent successfully', [
                'sid' => $response->sid,
                'status' => $response->status
            ]);

            return [
                'status' => 'success',
                'message' => 'WhatsApp message sent successfully',
                'sid' => $response->sid
            ];
        } catch (\Exception $e) {
            Log::error('Twilio WhatsApp Error', [
                'error' => $e->getMessage(),
                'to' => $to,
                'message_length' => strlen($message)
            ]);
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