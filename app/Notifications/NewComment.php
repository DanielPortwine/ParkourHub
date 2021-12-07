<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        switch (setting('notifications_comment', 'on-site', $notifiable->id)) {
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
        $commentable = $this->comment->commentable()->first();

        return (new MailMessage)
            ->subject('New Comment on ' . $commentable->name)
            ->markdown('mail.notifications.comment', [
                'comment' => $this->comment,
                'commenter' => $this->comment->user->name,
                'commentableOwnerName' => $commentable->user->name,
                'commentableType' => str_replace('App\Models\\', '', $this->comment->commentable_type),
                'commentableName' => $commentable->name,
                'route' => route(strtolower(str_replace('App\Models\\', '', $this->comment->commentable_type)) . '_view', $this->comment->commentable_id),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $commentable = $this->comment->commentable()->first();

        return [
            'comment' => $this->comment,
            'commentableType' => str_replace('App\Models\\', '', $this->comment->commentable_type),
            'commentableName' => $commentable->name,
            'route' => strtolower(str_replace('App\Models\\', '', $this->comment->commentable_type)) . '_view',
        ];
    }
}
