<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Triggered when an employee has 5 or more missed / overdue tasks.
 * Sent directly to the employee with their department manager CC'd.
 */
class MissedTasksAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param User $employee
     * @param Collection $overdueTasks
     * @param User|null $manager
     */
    public function __construct(
        public User $employee,
        public Collection $overdueTasks,
        public ?User $manager = null
    ) {
    }

    public function build(): self
    {
        $fromAddress = config('mail.from.address') ?: 'no-reply@iconicpages.com';
        $fromName = config('mail.from.name', 'ICPG Office');

        $this->subject($this->subjectLine())
            ->from($fromAddress, $fromName)
            ->to($this->employee->email, $this->employee->name);

        if ($this->manager && $this->manager->email && $this->manager->id !== $this->employee->id) {
            $this->cc($this->manager->email, $this->manager->name);
            $this->replyTo($this->manager->email, $this->manager->name);
        }

        return $this->view('emails.tasks.missed-threshold', [
            'employee' => $this->employee,
            'overdueTasks' => $this->overdueTasks,
            'manager' => $this->manager,
            'missedCount' => $this->overdueTasks->count(),
        ]);
    }

    public function subjectLine(): string
    {
        $count = $this->overdueTasks->count();

        return "Action Required: {$count} Missed Task Deadlines Alert - {$this->employee->name}";
    }
}

