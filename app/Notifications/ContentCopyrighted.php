<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentCopyrighted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $type;
    protected $content;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($type, $content)
    {
        $this->type = $type;
        $this->content = $content;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
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
            ->subject('Copyright Infringement on ' . ucfirst($this->type) . ($this->type === 'entry' ? ' to ' . ucfirst($this->content->challenge->name) : ' ' . ucfirst($this->content->name)))
            ->markdown('mail.notifications.copyrighted', ['type' => $this->type, 'content' => $this->content]);
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
            'type' => $this->type,
            'copyright_content' => $this->content,
        ];
    }
}
