<?php
namespace App\Models;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
class Device extends Model {
    use Auditable;
    protected $guarded = [];
    public function department() { return $this->belongsTo(Department::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
    public function employee() { return $this->belongsTo(User::class, 'user_id'); }
}
