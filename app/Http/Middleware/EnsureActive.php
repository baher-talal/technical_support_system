<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureActive
{
    /** يُخرج المستخدم فورا إذا عُطّل حسابه أثناء جلسته */
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if ($u && !$u->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => 'تم تعطيل حسابك، تواصل مع مدير النظام']);
        }
        return $next($request);
    }
}
