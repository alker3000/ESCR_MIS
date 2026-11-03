<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public static function send(User $user, string $type, string $subject, string $message): SystemNotification
    {
        try {
            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email, $user->name)
                    ->subject('[ESCR Portal] ' . $subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Mail send fallback to portal notification: ' . $e->getMessage());
        }

        return SystemNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'subject' => $subject,
            'message' => $message,
            'channel' => 'email_and_portal',
            'is_read' => false,
            'sent_at' => now(),
        ]);
    }
}
