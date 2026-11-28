<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$perms)
    {
        $u = $request->user();
        abort_unless($u && collect($perms)->contains(fn ($p) => $u->hasPermission($p)), 403, 'ليست لديك صلاحية لهذا الإجراء');
        return $next($request);
    }
}
