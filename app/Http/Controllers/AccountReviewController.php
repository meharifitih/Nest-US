<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PackageTransaction;

class AccountReviewController extends Controller
{
    public function showReviewPage()
    {
        $user = auth()->user();
        if ($user->type === 'tenant' || $user->type === 'maintainer') {
            return redirect()->route('dashboard');
        }
        $latestTransaction = PackageTransaction::with('subscription')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Skip review page for super admin users
        if ($user->type === 'super admin') {
            return redirect()->route('dashboard');
        }
        
        if ($user->approval_status === 'approved') {
            return redirect()->route('dashboard');
        }
        
        // Always fetch tutorial videos from the global (super admin) settings
        $settings = settingsById(1);
        $tutorialVideos = isset($settings['tutorial_videos']) ? $settings['tutorial_videos'] : '';
        if (empty($tutorialVideos)) {
            $tutorialVideos = [];
        } else {
            $tutorialVideos = json_decode($tutorialVideos, true);
        }
        
        // Normalize and convert YouTube links to embed format
        $tutorialVideos = array_map(function($item) {
            $url = is_array($item) && isset($item['url']) ? $item['url'] : (is_string($item) ? $item : null);
            if ($url) {
                // Convert YouTube watch links to embed links
                if (preg_match('/youtube\\.com\\/watch\\?v=([\\w-]+)/', $url, $matches)) {
                    $url = 'https://www.youtube.com/embed/' . $matches[1];
                }
                return [
                    'url' => $url,
                ];
            }
            return null;
        }, $tutorialVideos);
        
        // Remove any nulls (invalid entries)
        $tutorialVideos = array_filter($tutorialVideos);
        
        return view('account.review', compact('user', 'latestTransaction', 'tutorialVideos'));
    }

    public function approveUser($id)
    {
        if (Auth::user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Unauthorized action.'));
        }

        $user = User::findOrFail($id);
        
        // Only allow approving owner type users
        if ($user->type !== 'owner') {
            return redirect()->back()->with('error', __('Only owner accounts can be approved.'));
        }
        
        // Update user status without deleting
        $user->update([
            'approval_status' => 'approved',
            'is_active' => 1
        ]);

        // Send email notification to user
        $data = [
            'subject' => 'Your Account Has Been Approved!',
            'module' => 'account_approved',
            'name' => $user->name,
            'email' => $user->email,
            'url' => env('APP_URL'),
        ];
        $to = $user->email;
        commonEmailSend($to, $data);

        return redirect()->back()->with('success', __('User account has been approved.'));
    }

    public function rejectUser($id)
    {
        if (Auth::user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Unauthorized action.'));
        }

        $user = User::findOrFail($id);
        
        // Only allow rejecting owner type users
        if ($user->type !== 'owner') {
            return redirect()->back()->with('error', __('Only owner accounts can be rejected.'));
        }
        
        // Update user status without deleting
        $user->update([
            'approval_status' => 'rejected',
            'is_active' => 0
        ]);

        // Send email notification to user
        $data = [
            'subject' => 'Account Rejected',
            'module' => 'account_rejected',
            'name' => $user->name,
            'email' => $user->email,
            'url' => env('APP_URL'),
        ];
        $to = $user->email;
        commonEmailSend($to, $data);

        return redirect()->back()->with('success', __('User account has been rejected.'));
    }
}
