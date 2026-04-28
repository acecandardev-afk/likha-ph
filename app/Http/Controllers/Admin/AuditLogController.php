<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;

class AuditLogController extends AdminController
{
    /**
     * Recent sensitive actions (who did what, when).
     */
    public function index()
    {
        $logs = AuditLog::query()
            ->with('user')
            ->latest('created_at')
            ->paginate(40);

        return view('admin.audit_logs.index', compact('logs'));
    }
}
