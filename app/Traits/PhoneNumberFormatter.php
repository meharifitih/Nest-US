<?php

namespace App\Traits;

trait PhoneNumberFormatter
{
    /**
     * Format phone number to ensure it has +251 prefix and follows the required format
     *
     * @param string $phoneNumber
     * @return string
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric except +
        $phone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        if (str_starts_with($phone, '+251')) {
            return $phone;
        }
        if (str_starts_with($phone, '251')) {
            return '+'.$phone;
        }
        if ((str_starts_with($phone, '9') || str_starts_with($phone, '7')) && strlen($phone) === 9) {
            return '+251'.$phone;
        }
        return $phone;
    }

    /**
     * Validate phone number format
     *
     * @param string $phoneNumber
     * @return bool
     */
    protected function isValidPhoneNumber(string $phoneNumber): bool
    {
        $phone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        if (str_starts_with($phone, '+251')) {
            $number = substr($phone, 4);
        } elseif (str_starts_with($phone, '251')) {
            $number = substr($phone, 3);
        } elseif ((str_starts_with($phone, '9') || str_starts_with($phone, '7')) && strlen($phone) === 9) {
            $number = $phone;
        } else {
            return false;
        }
        return preg_match('/^[97][0-9]{8}$/', $number);
    }
} 