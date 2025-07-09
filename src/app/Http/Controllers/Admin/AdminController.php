<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\StatefulGuard;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\CorrectRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminController extends Controller
{
    use AuthenticatesUsers;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::ADMINHOME;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard) {
        $this->guard = $guard;
    }

    /**
     * Show the login view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\LoginViewResponse
     */

    public function login() {
        return view('admin.login');
    }

    /**
     * Attempt to authenticate a new session.
     *
     * @param  \Laravel\Fortify\Http\Requests\LoginRequest  $request
     * @return mixed
     */
    public function store(LoginRequest $request) {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $request->email)->first();
        if ($user->is_admin) {
            if (Auth::attempt($credentials)) {
                if ($user && Hash::check($request->password, $user->password)) {
                    // 管理者としてログイン
                    $request->session()->regenerate();

                    return redirect()->route('admin.list');
                }
            } else {
                return redirect('/admin/login')->withErrors(['email' => 'ログイン情報が登録されていません']);
            }
        } else {
            // 管理者用ログインでは、admin権限がない限り、一般ユーザーのパスワード合致してもログイン不可。
            // こちらでメッセージを付与。
            return redirect('/admin/login')->withErrors(['email' => 'ログイン情報が登録されていません']);
        }
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param  \Laravel\Fortify\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Pipeline\Pipeline
     */
    protected function loginPipeline(LoginRequest $request) {
        dd($request);
        if (Fortify::$authenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                call_user_func(Fortify::$authenticateThroughCallback, $request)
            ));
        }
        if (is_array(config('fortify.pipelines.login'))) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('fortify.pipelines.login')
            ));
        }
        return (new Pipeline(app()))->send($request)->through(array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]));
    }
    
    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return App\Http\Response\LogoutResponse
     */
    public function destroy(Request $request): LogoutResponse {
        $this->guard->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        return app(LogoutResponse::class);
    }

    public function index(Request $request) {
        if(isset($request['today'])) {

            $now = Carbon::parse($request['today']);
            $yesterday = $now->copy()->subDay();
            $tomorrow = $now->copy()->addDay();

        } else {
            $now = Carbon::now();
            $yesterday = Carbon::yesterday();
            $tomorrow = Carbon::tomorrow();
        }
        $today = $now->isoFormat('YYYY-MM-DD');
        $attendances = Attendance::TodayAttendance('%', $today)->with('user')->get();
        $this_day_slash = $now->isoFormat('YYYY/MM/DD');
        $this_day_jp = $now->isoFormat('Y年M月D日');
        $user = Auth::user();

        return view('admin-attendance-list', compact('attendances', 'this_day_slash', 'this_day_jp', 'yesterday', 'tomorrow', 'user'));
    }

    public function update(CorrectRequest $request, $id) {
        // まずは休憩時間。実態の更新ない場合でも$requestに休憩開始、終了が全てあるため一律更新とする。
        $started_at_arrays = $request->started_at;
        $ended_at_arrays = $request->ended_at;
        $break_times = array_map(function ($started_at, $ended_at) {
            return ['started_at' => $started_at, 'ended_at' => $ended_at,];
        }, $started_at_arrays, $ended_at_arrays);

        $i = 0;
        $total_break_time = Carbon::parse("0:0:0");   // 初期化
        foreach($break_times as $break_time) {
            $i++;
            $update_started_at = Carbon::parse($break_time['started_at']);
            $update_ended_at = Carbon::parse($break_time['ended_at']);
            $update_break_time = BreakTime::updateOrCreate(
                ['attendance_id' => $id, 'branch_number' => $i ],
                ['started_at' => $update_started_at,
                 'ended_at' => $update_ended_at,
            ]);
            // 休憩時間の累計も併せて行う
            $diff = $update_started_at->diff($update_ended_at);
            $total_break_time = $total_break_time->copy()->add($diff);
        }
        // 次に勤務時間の合計を集計してデータベースを更新する
        $attendanced_at = Carbon::parse($request->attendanced_at);
        $leaved_at = Carbon::parse($request->leaved_at);
        $net_attendance_time = $attendanced_at->diff($leaved_at);

        // 休憩時間を控除。共にCarbonオブジェクトに変換した上で、勤務開始終了 ー 休憩時間を算出。
        $now = Carbon::now();
        $carbon_attendance_time = Carbon::instance($now->copy()->add($net_attendance_time));
        $break_time_dateInterval = Carbon::parse('0:0:0')->diff($total_break_time);
        $carbon_break_time = Carbon::instance($now->copy()->add($break_time_dateInterval));
        $total_attendance_time = Carbon::parse('0:0:0')->add($carbon_break_time->diff($carbon_attendance_time));
        $attendance = Attendance::find($id)->update([
            'attendanced_at' => $attendanced_at,
            'leaved_at' => $leaved_at,
            'total_break_time' => $total_break_time,
            'total_attendance_time' => $total_attendance_time,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('attendance.detail',['id' => $id]);
    }
}
