<?php

namespace App\Services;

use App\Models\ContentActivityLog;
use App\Models\ContentPost;
use App\Models\Task;
use App\Models\TaskActivityLog;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContentTaskIntegrationService
{
    public const WORK_TYPES = [
        'write_caption'                => 'Write Caption',
        'create_graphic'               => 'Create Graphic',
        'edit_video'                   => 'Edit Video',
        'prepare_carousel'             => 'Prepare Carousel',
        'review_content'               => 'Review Content',
        'prepare_publishing_material'  => 'Prepare Publishing Material',
        'other'                        => 'General Content Task',
    ];

    /**
     * Create and link a real Task from the Task Management Module for this content post.
     */
    public function createLinkedTask(ContentPost $post, User $actor, array $data): Task
    {
        return DB::transaction(function () use ($post, $actor, $data) {
            $workType = $data['content_task_type'] ?? 'create_graphic';
            $workLabel = self::WORK_TYPES[$workType] ?? 'Content Task';

            $assignedTo = ! empty($data['assigned_to']) ? (int) $data['assigned_to'] : $actor->id;
            $assignee = User::find($assignedTo);

            $brandName = $post->brand?->name ?? 'Brand';
            $title = ! empty($data['title']) ? trim($data['title']) : "[{$brandName}] {$workLabel}: {$post->title}";

            $dueDate = ! empty($data['due_date'])
                ? Carbon::parse($data['due_date'])
                : ($post->scheduled_at ? $post->scheduled_at->copy()->subDay() : null);

            $startDate = ! empty($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::today();

            $description = $data['description'] ?? "Work item for social media post #{$post->id}: \"{$post->title}\".\nContent concept: " . substr((string) $post->caption, 0, 200);

            $task = Task::create([
                'content_post_id'   => $post->id,
                'content_task_type' => $workType,
                'title'             => $title,
                'description'       => $description,
                'expected_outcomes' => $data['expected_outcomes'] ?? "Deliver polished {$workLabel} ready for review and publishing.",
                'department'        => 'marketing',
                'priority'          => $data['priority'] ?? 'medium',
                'status'            => 'open',
                'assigned_by'       => $actor->id,
                'assigned_to'       => $assignedTo,
                'start_date'        => $startDate,
                'due_date'          => $dueDate,
                'review_required'   => isset($data['review_required']) ? (bool) $data['review_required'] : true,
            ]);

            // Assignment history in task module
            TaskAssignment::create([
                'task_id'     => $task->id,
                'assigned_by' => $actor->id,
                'assigned_to' => $assignedTo,
                'notes'       => "Linked task created for Social Media Post #{$post->id} ({$workLabel}).",
            ]);

            // Activity log in task module
            TaskActivityLog::create([
                'task_id'     => $task->id,
                'user_id'     => $actor->id,
                'action'      => 'created',
                'description' => "Linked task created from Social Media Content Calendar for post #{$post->id}.",
            ]);

            // If the post was 'idea' or 'planned', advance it to 'in_creation'
            if (in_array($post->status, ['idea', 'planned'], true)) {
                $post->update([
                    'status'      => 'in_creation',
                    'assigned_to' => $post->assigned_to ?: $assignedTo,
                ]);
            }

            // Log activity in content calendar
            ContentActivityLog::create([
                'content_post_id' => $post->id,
                'user_id'         => $actor->id,
                'action'          => 'assigned',
                'field_name'      => 'task_linked',
                'new_value'       => (string) $task->id,
                'description'     => "Created linked task #{$task->id} ({$workLabel}) assigned to {$assignee?->name}.",
                'created_at'      => now(),
            ]);

            // Dispatch task assigned notification if assigned to another user
            if ($assignee && $assignee->id !== $actor->id) {
                try {
                    $assignee->notify(new TaskAssignedNotification($task, $actor->name));
                } catch (\Throwable) {}
            }

            return $task;
        });
    }
}

