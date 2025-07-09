<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'branch_number',
        'started_at',
        'ended_at',
    ];

    public function application() {
        return $this->belongsTo(Application::class);
    }

    public function scopeApplicationId($query, $application_id) {
        if (!empty($attendance_id)) {
            $query->where('application_id', $application_id);
        }
    }

}
