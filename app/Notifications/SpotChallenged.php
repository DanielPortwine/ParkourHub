<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpotChallenged extends Notification
{
    use Queueable;

    protected $challenge;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($challenge)
    {
        $this->challenge = $challenge;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        switch (setting('notifications_challenge', 'none', $notifiable->id)) {
            case 'on-site':
                $channels = ['database'];
                break;
            case 'email':
                $channels = ['mail'];
                break;
            case 'email-site':
                $channels = ['database', 'mail'];
                break;
            default:
                $channels = [];
                break;
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Challenge on ' . $this->challenge->spot->name)
            ->markdown('mail.notifications.challenge', ['challenge' => $this->challenge]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'challenge' => $this->challenge
        ];
    }
}
