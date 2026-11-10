<?php

namespace App\Services;

use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Task;
use App\Models\User;
use App\Notifications\LeaveApprovedNotification;
use App\Notifications\LeaveRequestNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;

/**
 * Decides who gets notified when something happens, and sends it.
 *
 * Keeping the recipient resolution here (rather than inline in the
 * controllers) means the same rule applies no matter which screen the event
 * was triggered from: a leave can be applied for by the employee themselves
 * or recorded on their behalf by an admin, and both must reach the same
 * approvers.
 */
class NotificationDispatcher
{
    /**
     * A task was assigned to somebody.
     */
    public function taskAssigned(Task $task, ?User $assigner = null): void
    {
        $assignee = $task->assignee;

        if (! $assignee || ($assigner && $assignee->id === $assigner->id)) {
            return;
        }

        $assignee->notify(new TaskAssignedNotification($task, $assigner?->name));
    }

    /**
     * The person a task was assigned to completed it.
     *
     * Reaches:
     *  - the assignee's direct manager (users.manager_id),
     *  - the assignee's department manager (departments.manager_id),
     *  - the assigner/creator who created the task, if different.
     */
    public function taskCompleted(Task $task, ?User $completedBy = null): void
    {
        $task->loadMissing(['assignee', 'creator']);
        $assignee = $task->assignee;

        $recipients = [];

        // 1. The manager/user who created or assigned the task
        if ($task->creator && (! $completedBy || $task->creator->id !== $completedBy->id)) {
            $recipients[$task->creator->id] = $task->creator;
        }

        // 2. The direct manager of the assignee
        if ($assignee?->manager_id && (! $completedBy || $assignee->manager_id !== $completedBy->id)) {
            $directManager = User::find($assignee->manager_id);
            if ($directManager && $directManager->is_active) {
                $recipients[$directManager->id] = $directManager;
            }
        }

        // 3. The manager of the assignee's department
        if ($assignee?->department) {
            $dept = Department::where('name', $assignee->department)->first();
            if ($dept?->manager_id && (! $completedBy || $dept->manager_id !== $completedBy->id)) {
                $deptManager = User::find($dept->manager_id);
                if ($deptManager && $deptManager->is_active) {
                    $recipients[$deptManager->id] = $deptManager;
                }
            }
        }

        foreach ($recipients as $recipient) {
            $recipient->notify(new TaskCompletedNotification($task, $completedBy?->name));
        }
    }

    /**
     * A leave request was submitted and needs approval.
     *
     * Reaches:
     *  - the applicant's own manager (direct report, or the manager of the
     *    applicant's department),
     *  - every HR user, because HR approves for the whole company.
     */
    public function leaveRequested(LeaveRequest $leave): void
    {
        $applicant = $leave->user;

        if (! $applicant) {
            return;
        }

        $recipients = $this->approversFor($applicant);

        foreach ($recipients as $recipient) {
            $recipient->notify(new LeaveRequestNotification($leave, $applicant->name));
        }
    }

    /**
     * A leave request was approved.
     */
    public function leaveApproved(LeaveRequest $leave, ?User $approver = null): void
    {
        $applicant = $leave->user;

        if (! $applicant || ($approver && $applicant->id === $approver->id)) {
            return;
        }

        $applicant->notify(new LeaveApprovedNotification($leave, $approver?->name));
    }

    /**
     * Everyone who should be told about a leave request from this applicant.
     *
     * @return list<User>
     */
    protected function approversFor(User $applicant): array
    {
        // A manager's leave is approved by HR only, so HR is the only party
        // that is told about it. Notifying a peer manager would only set up
        // an approval they are not allowed to give.
        if ($applicant->isManager()) {
            return $this->hrUsers();
        }

        $recipients = [];

        // The applicant's own manager.
        if ($applicant->manager_id) {
            $direct = User::find($applicant->manager_id);

            if ($direct) {
                $recipients[$direct->id] = $direct;
            }
        }

        // The manager of the applicant's department. Not everyone has their
        // manager_id populated, so the department is the reliable link.
        if ($applicant->department) {
            $department = Department::where('name', $applicant->department)->first();

            if ($department?->manager_id && $department->manager_id !== $applicant->id) {
                $deptManager = User::find($department->manager_id);

                if ($deptManager) {
                    $recipients[$deptManager->id] = $deptManager;
                }
            }
        }

        // HR approves on behalf of the whole company.
        foreach ($this->hrUsers() as $hr) {
            if ($hr->id !== $applicant->id) {
                $recipients[$hr->id] = $hr;
            }
        }

        return array_values($recipients);
    }

    /**
     * Every user who counts as HR.
     *
     * @return list<User>
     */
    protected function hrUsers(): array
    {
        $users = User::role('hr')->get();

        // The hr role is not the only way to be HR: the HR department and an
        // HR designation count too, the same way User::isHr() treats them.
        $extra = User::where('department', 'hr')
            ->orWhere('designation', 'like', '%hr%')
            ->get();

        $byId = [];

        foreach ($users->concat($extra) as $user) {
            if ($user->is_active) {
                $byId[$user->id] = $user;
            }
        }

        return array_values($byId);
    }
}
