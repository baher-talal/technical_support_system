<?php
namespace App\Http\Controllers;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function show() { return view('auth.login'); }

    public function login(Request $r)
    {
        $c = $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt($c + ['is_active' => true], $r->boolean('remember'))) {
            $r->session()->regenerate();
            Audit::log('login');
            return redirect()->intended('/');
        }
        Audit::log('login_failed', null, ['email' => $c['email']]);
        return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة أو الحساب معطل'])->onlyInput('email');
    }

    public function logout(Request $r)
    {
        Audit::log('logout');
        Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken();
        return redirect()->route('login');
    }
}
