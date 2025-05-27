<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppTestController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function index()
    {
        return view('whatsapp.test');
    }

    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'message' => 'required'
        ]);

        $response = $this->whatsappService->sendMessage($request->phone, $request->message);

        if ($response['status'] === 'success') {
            return redirect()->back()->with('success', 'Message sent successfully! SID: ' . $response['sid']);
        }

        return redirect()->back()->with('error', $response['message']);
    }
} 
 