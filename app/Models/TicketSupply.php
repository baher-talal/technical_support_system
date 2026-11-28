<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TicketSupply extends Model {
    protected $guarded = [];
    public function issuer() { return $this->belongsTo(User::class, 'issued_by'); }
}
