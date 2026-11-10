<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missed Tasks Deadline Alert</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            max-width: 640px;
            margin: 0 auto;
            padding: 20px 10px;
            background-color: #f3f4f6;
        }
        .container {
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            padding: 28px 24px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .badge {
            display: inline-block;
            background-color: #e11d48;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 4px 14px;
            border-radius: 9999px;
            margin-top: 10px;
        }
        .content {
            padding: 28px 24px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 10px;
        }
        .message {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 20px;
        }
        .cc-notice {
            background-color: #fff1f2;
            border-left: 4px solid #f43f5e;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 22px;
            font-size: 13px;
            color: #9f1239;
        }
        .details-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .details-card h3 {
            margin: 0 0 12px 0;
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .tasks-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .tasks-table th {
            text-align: left;
            padding: 8px 10px;
            background-color: #f3f4f6;
            color: #4b5563;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        .tasks-table td {
            padding: 10px 10px;
            border-bottom: 1px solid #e5e7eb;
            color: #1f2937;
        }
        .priority-urgent {
            color: #dc2626;
            font-weight: 700;
        }
        .priority-high {
            color: #ea580c;
            font-weight: 600;
        }
        .priority-medium {
            color: #ca8a04;
            font-weight: 500;
        }
        .priority-low {
            color: #16a34a;
        }
        .cta-container {
            text-align: center;
            margin: 28px 0 12px;
        }
        .cta-button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
        }
        .footer {
            padding: 20px 24px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Missed Tasks Deadline Alert</h1>
            <span class="badge">{{ $missedCount }} Overdue Tasks</span>
        </div>

        <div class="content">
            <p class="greeting">Hello {{ $employee->name }},</p>
            <p class="message">
                Our records show that you have <strong>{{ $missedCount }} tasks</strong> that have passed their scheduled completion deadlines and remain in pending or in-progress status.
            </p>

            <div class="cc-notice">
                <strong>Attention:</strong> Because you have reached 5 or more missed deadlines, your department manager
                @if($manager)
                    (<strong>{{ $manager->name }}</strong> &lt;{{ $manager->email }}&gt;)
                @else
                    (Management)
                @endif
                has been CC'd on this notification in accordance with company task completion policies.
            </div>

            <div class="details-card">
                <h3>Overdue Tasks Summary</h3>
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th>Task Title</th>
                            <th>Project</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Overdue By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueTasks as $task)
                        <tr>
                            <td>
                                <strong>{{ $task->title }}</strong>
                            </td>
                            <td>
                                {{ $task->project?->title ?? ($task->project_name ?: 'General') }}
                            </td>
                            <td>
                                @php $pri = strtolower((string)$task->priority); @endphp
                                <span class="priority-{{ $pri }}">{{ ucfirst($task->priority ?? 'Normal') }}</span>
                            </td>
                            <td>
                                {{ $task->due_date ? $task->due_date->format('M d, Y') : '—' }}
                            </td>
                            <td>
                                <span style="color: #e11d48; font-weight: 600;">
                                    {{ $task->overdueDays() }} {{ $task->overdueDays() === 1 ? 'day' : 'days' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="message" style="margin-bottom: 8px;">
                Please review these tasks and update their status or complete them as soon as possible. If there are blockers or reasons for delay, please connect with your manager immediately.
            </p>

            <div class="cta-container">
                <a href="{{ route('tasks.index', ['tab' => 'my']) }}" class="cta-button" target="_blank">
                    Open My Tasks Pool &rarr;
                </a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} ICPG Technologies Pvt Ltd. All rights reserved.</p>
            <p>This is an automated notification from the ICPG Employee Portal.</p>
        </div>
    </div>
</body>
</html>

