<?php
namespace App\Policies;

use App\Models\{Ticket, User};

class TicketPolicy
{
    /** يرى التيكيت من يسمح له نطاق visibleTo */
    public function view(User $u, Ticket $t): bool
    {
        return Ticket::visibleTo($u)->whereKey($t->id)->exists();
    }

    /** العمل على التيكيت (بدء/صرف/حل): المسؤول المسند إليه أو مدير النظام */
    public function work(User $u, Ticket $t): bool
    {
        return $t->technician_id === $u->id || $u->role?->slug === 'admin';
    }

    public function reopen(User $u, Ticket $t): bool
    {
        return $t->status === 'resolved' && ($t->user_id === $u->id || $u->hasPermission('tickets.approve'));
    }

    public function rate(User $u, Ticket $t): bool
    {
        return $t->status === 'resolved' && $t->user_id === $u->id;
    }
}
