<?php
namespace App\Http\Controllers;

use App\Models\{AuditLog, User};
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $r)
    {
        $q = AuditLog::with('user')->latest('created_at')->latest('id');
        foreach (['user_id', 'action'] as $f) if ($r->filled($f)) $q->where($f, $r->input($f));
        if ($r->filled('type')) $q->where('subject_type', $r->type);
        if ($r->filled('from')) $q->where('created_at', '>=', $r->from . ' 00:00:00');
        if ($r->filled('to')) $q->where('created_at', '<=', $r->to . ' 23:59:59');
        if ($s = trim((string) $r->q)) $q->where('subject_label', 'like', '%' . addcslashes($s, '%_\\') . '%');
        return view('admin.audit', ['logs' => $q->simplePaginate(50)->withQueryString(), 'users' => User::orderBy('name')->get(['id', 'name'])]);
    }
}
