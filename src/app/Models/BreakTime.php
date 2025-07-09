<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'branch_number',
        'started_at',
        'ended_at',
    ];

    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }

    public function scopeAttencanceId($query, $attendance_id) {
        if (!empty($attendance_id)) {
            $query->where('attendance_id', $attendance_id);
        }
    }

    public function scopeGetBreakTime($query, $attendance_id, $branch_number) {
        $query->where('attendance_id', $attendance_id)->where('branch_number', $branch_number);
    }
}
