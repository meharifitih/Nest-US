<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to Property Management System')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Your account has been created successfully.')
            ->line('Your temporary password is: ' . $this->password)
            ->line('Please change your password after logging in for security reasons.')
            ->action('Login Now', url('/login'))
            ->line('Thank you for using our application!');
    }
} 