<?php
namespace App\Http\Controllers;
use App\Models\{Device, Ticket, User};

class EmployeeController extends Controller
{
    public function show(User $user)
    {
        $user->load(['department', 'role']);
        $devices = Device::with('department')->where('user_id', $user->id)->get();
        $tickets = Ticket::with(['user', 'department', 'technician'])
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhereIn('device_id', $devices->pluck('id')))
            ->latest()->paginate(15);
        return view('employees.show', compact('user', 'devices', 'tickets'));
    }
}
