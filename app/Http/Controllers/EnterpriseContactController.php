<?php

namespace App\Http\Controllers;

use App\Models\EnterpriseContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EnterpriseContactController extends Controller
{
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'property_limit' => 'required|string|max:255',
            'unit_limit' => 'required|string|max:255',
            'interval' => 'required|string|max:255',
            'message' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact = EnterpriseContact::create($request->all());

        // Send email notification
        $fromEmail = env('MAIL_FROM_ADDRESS', 'noreply@example.com');
        $toEmail = $fromEmail;

        try {
            Mail::send('emails.enterprise_contact', ['contact' => $contact], function ($message) use ($toEmail, $contact) {
                $message->to($toEmail)
                       ->subject('New Enterprise Contact Form Submission')
                       ->replyTo($contact->email, $contact->name);
            });
        } catch (\Exception $e) {
            \Log::error('Enterprise Contact Email Error: ' . $e->getMessage());
            return response()->json(['message' => 'Email could not be sent. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Thank you for your interest. We will contact you soon.']);
    }
} 