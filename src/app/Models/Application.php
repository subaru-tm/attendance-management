<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'approval_status',
        'application_date',
        'correct_attendanced_at',
        'correct_leaved_at',
        'remarks',
    ];

    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }

    public function GetByUserAndApproval($user_id, $approval_status) {
        if (!empty($user_id)) {
            $usersAttendances = Attendance::where('user_id', $user_id)->get();
            $atendance_ids = $usersAttendances->pluck('id')->toArray();
            $applications = Application::with('attendance')->where('approval_status', $approval_status)->whereIn('attendance_id', $atendance_ids)->get();
        }
        return $applications;
    }

    public function scopeAttendanceIdApprovalStatus($query, $attendance_id, $approval_status) {
        if (!empty($attendance_id)) {
            $query->where('attendance_id', $attendance_id)->where('approval_status', $approval_status);
        }
    }

}
