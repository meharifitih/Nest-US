<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountReviewController extends Controller
{
    public function showReviewPage()
    {
        $user = Auth::user();
        
        // Skip review page for super admin users
        if ($user->type === 'super admin') {
            return redirect()->route('dashboard');
        }
        
        if ($user->approval_status === 'approved') {
            return redirect()->route('dashboard');
        }
        
        // Get tutorial videos from settings
        $tutorialVideos = getSettingsValByName('tutorial_videos');
        if (empty($tutorialVideos)) {
            $tutorialVideos = [
                [
                    'title' => 'Getting Started Guide',
                    'url' => 'https://www.youtube.com/embed/example1',
                    'description' => 'Learn the basics of using our property management system'
                ],
                [
                    'title' => 'Managing Properties',
                    'url' => 'https://www.youtube.com/embed/example2',
                    'description' => 'How to add and manage your properties'
                ],
                [
                    'title' => 'Tenant Management',
                    'url' => 'https://www.youtube.com/embed/example3',
                    'description' => 'Learn how to manage your tenants effectively'
                ]
            ];
        } else {
            $tutorialVideos = json_decode($tutorialVideos, true);
        }
        
        return view('account.review', compact('tutorialVideos'));
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
            'subject' => 'Account Approved',
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
