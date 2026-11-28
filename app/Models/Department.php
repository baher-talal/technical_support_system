<?php
namespace App\Models;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
class Department extends Model {
    use Auditable;
    protected $guarded = [];
    public function sector() { return $this->belongsTo(Sector::class); }
    public function users() { return $this->hasMany(User::class); }
    public function devices() { return $this->hasMany(Device::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
    public function getLocationAttribute(): string
    {
        return $this->building ? "مبنى {$this->building} - الدور {$this->floor} - غرفة {$this->room_number}" : '—';
    }
}
