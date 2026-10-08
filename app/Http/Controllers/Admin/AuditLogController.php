<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * Read-only audit trail.
 *
 * There is deliberately no store/update/destroy: the trail is append-only and
 * written exclusively by the Auditable model observer.
 */
class AuditLogController extends Controller
{
    use AuthorizesByPermission;
    public function index(Request $request)
    {
        $this->authorizePermission('audit_logs.view');

        $query = AuditLog::query()->with('user:id,name,email');

        if ($userId = $request->integer('user_id') ?: null) {
            $query->where('user_id', $userId);
        }

        if ($module = $request->string('module')->trim()->value()) {
            $query->where('module', $module);
        }

        if ($action = $request->string('action')->trim()->value()) {
            $query->where('action', $action);
        }

        if ($from = $request->date('date_from')) {
            $query->where('created_at', '>=', $from->startOfDay());
        }

        if ($to = $request->date('date_to')) {
            $query->where('created_at', '<=', $to->endOfDay());
        }

        $logs = $query->latest('id')->paginate(25)->withQueryString();

        // Filter options come from the data, so they can never drift.
        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'modules' => AuditLog::query()->distinct()->orderBy('module')->pluck('module'),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
