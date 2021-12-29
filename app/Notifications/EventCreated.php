<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
        $this->user = $event->user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        switch (setting('notifications_new_event', 'on-site', $notifiable->id)) {
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
            ->subject('New Event From ' . $this->event->user->name)
            ->markdown('mail.notifications.new_event', ['event' => $this->event, 'user' => $notifiable->name]);
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
            'new_event' => $this->event,
            'user' => $this->user
        ];
    }
}
