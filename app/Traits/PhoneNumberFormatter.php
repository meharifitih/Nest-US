<?php

namespace App\Traits;

trait PhoneNumberFormatter
{
    /**
     * Format phone number to ensure it has +1 prefix and follows US format
     *
     * @param string $phoneNumber
     * @return string
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric except +
        $phone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        if (str_starts_with($phone, '+1')) {
            return $phone;
        }
        if (str_starts_with($phone, '1') && strlen($phone) === 11) {
            return '+'.$phone;
        }
        if (strlen($phone) === 10) {
            return '+1'.$phone;
        }
        return $phone;
    }

    /**
     * Validate US phone number format
     *
     * @param string $phoneNumber
     * @return bool
     */
    protected function isValidPhoneNumber(string $phoneNumber): bool
    {
        $phone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Check if it starts with +1
        if (str_starts_with($phone, '+1')) {
            $number = substr($phone, 2);
        } elseif (str_starts_with($phone, '1') && strlen($phone) === 11) {
            $number = substr($phone, 1);
        } elseif (strlen($phone) === 10) {
            $number = $phone;
        } else {
            return false;
        }
        
        // US phone number validation: area code must start with 2-9, and the next digit must be 2-9
        return preg_match('/^[2-9]\d{2}[2-9]\d{2}\d{4}$/', $number);
    }
} 