<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Queued mail telling a tenant admin their organization's status changed.
 */
final class OrganizationStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Organization $organization,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->organization->status;

        $mail = (new MailMessage)
            ->subject("Your organization is now {$status->label()}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The status of \"{$this->organization->name}\" has changed to {$status->label()}.");

        if ($status === OrganizationStatus::Approved) {
            $mail->line('You can now sign in and start using the platform.')
                ->action('Sign in', route('login'));
        } else {
            $mail->line('Please contact support if you have any questions.');
        }

        return $mail;
    }
}
