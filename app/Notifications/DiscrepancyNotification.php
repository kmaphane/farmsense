<?php

declare(strict_types=1);

namespace App\Notifications;

use Domains\Broiler\Enums\DiscrepancyReason;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\SlaughterBatchSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscrepancyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $type,
        public int $expectedCount,
        public int $actualCount,
        public ?DiscrepancyReason $reason,
        public ?string $reference,
        public ?string $recordedBy,
        public ?string $notes = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $discrepancy = $this->expectedCount - $this->actualCount;

        return (new MailMessage)
            ->subject("⚠️ Discrepancy Alert: {$this->type}")
            ->greeting('Discrepancy Detected!')
            ->line('A discrepancy has been recorded that requires your attention.')
            ->line("**Type:** {$this->type}")
            ->line("**Reference:** {$this->reference}")
            ->line("**Expected:** {$this->expectedCount}")
            ->line("**Actual:** {$this->actualCount}")
            ->line("**Discrepancy:** {$discrepancy} birds")
            ->line('**Reason:** '.($this->reason?->label() ?? 'Not specified'))
            ->line("**Recorded By:** {$this->recordedBy}")
            ->when($this->notes, fn ($mail) => $mail->line("**Notes:** {$this->notes}"))
            ->action('View Details', url('/admin'))
            ->line('Please investigate this discrepancy as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'expected_count' => $this->expectedCount,
            'actual_count' => $this->actualCount,
            'discrepancy' => $this->expectedCount - $this->actualCount,
            'reason' => $this->reason?->value,
            'reason_label' => $this->reason?->label(),
            'reference' => $this->reference,
            'recorded_by' => $this->recordedBy,
            'notes' => $this->notes,
        ];
    }

    /**
     * Create notification from a slaughter batch source.
     */
    public static function fromSlaughterSource(SlaughterBatchSource $source): self
    {
        return new self(
            type: 'Slaughter Discrepancy',
            expectedCount: $source->expected_quantity,
            actualCount: $source->actual_quantity,
            reason: $source->discrepancy_reason,
            reference: "Slaughter #{$source->slaughter_record_id} - Batch {$source->batch->batch_number}",
            recordedBy: $source->slaughterRecord->recorder->name ?? 'Unknown',
            notes: $source->discrepancy_notes,
        );
    }

    /**
     * Create notification from a batch closure.
     */
    public static function fromBatchClosure(Batch $batch): self
    {
        return new self(
            type: 'Batch Closure Discrepancy',
            expectedCount: 0,
            actualCount: $batch->current_quantity,
            reason: $batch->closure_reason,
            reference: "Batch {$batch->batch_number}",
            recordedBy: 'System',
            notes: $batch->closure_notes,
        );
    }
}
