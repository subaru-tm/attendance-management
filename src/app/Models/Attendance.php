<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Application;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'status',
        'attendanced_at',
        'leaved_at',
        'total_break_time',
        'total_attendance_time',
        'remarks',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function applications() {
        return $this->hasMany(Application::class);
    }

    public function scopeTodayAttendance($query, $user_id, $today) {
        if (!empty($today)) {
            $query->where('user_id', 'like' , $user_id)->where('date', $today);
        }
    }

    public function scopeThisMonth($query, $user_id, $this_month) {
        if (!empty($this_month)) {
            // DB側のdate項目はYYYY-MM-DD形式のため、前方一致として$this_month(YYYY-MM形式)を取得。
            $query->where('user_id', $user_id)
                ->where('date', 'like', $this_month.'%')
                ->orderBy('date', 'asc');
        }
    }

    public function GetThisMonthWithUser($user_id, $this_month) {
        if (!empty($this_month)) {
            // DB側のdate項目はYYYY-MM-DD形式のため、前方一致として$this_month(YYYY-MM形式)を取得。
            $attendances = Attendance::with('user')->where('user_id', $user_id)
                ->where('date', 'like', $this_month.'%')
                ->orderBy('date', 'asc')->get();
        }
        return $attendances;
    }


    public function GetByUserAndApproval($user_id, $approval_status) {
        if (!empty($user_id)) {
            $usersAttendances = Attendance::where('user_id', $user_id)->get();
            $applications = Application::where('approval_status', $approval_status)->get();
            $appsAtendanceId = $applications->pluck('attendance_id')->toArray();
            $attendances = $usersAttendances->whereIn('id', $appsAtendanceId);
        }
        return $attendances;
    }
}
