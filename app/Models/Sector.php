<?php
namespace App\Models;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use Auditable;

    protected $guarded = [];
    public function head() { return $this->belongsTo(User::class, 'head_id'); }
    public function departments() { return $this->hasMany(Department::class); }
}
