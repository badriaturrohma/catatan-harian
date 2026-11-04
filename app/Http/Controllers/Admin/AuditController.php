<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $action = $request->query('action');
        $query = AuditLog::with('actor');

        if ($action) {
            $query->where('action', 'like', "%{$action}%");
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.audit.index', compact('logs', 'action'));
    }
}
