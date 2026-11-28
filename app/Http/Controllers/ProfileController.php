<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $r)
    {
        return view('profile.show', ['user' => $r->user()->load(['role', 'department'])]);
    }

    public function update(Request $r)
    {
        $u = $r->user();
        $d = $r->validate([
            'phone' => 'nullable|string|max:30',
            'current_password' => 'required_with:password|nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        if (!empty($d['password'])) {
            if (!Hash::check($d['current_password'], $u->password)) return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة']);
            $u->password = $d['password']; // التشفير تلقائي من cast الموديل
        }
        $u->phone = $d['phone'] ?? null;
        $u->save();
        return back()->with('ok', 'تم حفظ بياناتك');
    }
}
