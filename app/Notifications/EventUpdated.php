<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventUpdated extends Notification
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
        switch (setting('notifications_event_updated', 'on-site', $notifiable->id)) {
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
            ->subject('Event ' . $this->event->name . ' Updated')
            ->markdown('mail.notifications.event_updated', ['event' => $this->event, 'user' => $notifiable->name]);
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
            'event_updated' => $this->event,
            'user' => $this->user
        ];
    }
}
