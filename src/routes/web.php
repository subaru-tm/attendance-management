<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ExportController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['verify' => true]);

Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'store'])->name('admin.login.store');
Route::get('/admin/logout', [AdminController::class, 'destroy'])->name('admin.logout');

Route::post('/register', [RegisterController::class, 'store']);

//メール認証用
Route::get('/email/verify', function () {
    return view('auth.verify');
})->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    session()->get('unauthenticated_user')->sendEmailVerificationNotification();
    session()->put('resent', true);
    return back()->with('message', 'Verification link sent!');
})->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/attendance');
})->name('verification.verify');

Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::get('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware('admin')->group(function () {
  Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [ApplicationController::class, 'detail'])->name('application.detail');
  Route::patch('/stamp_correction_request/approve/{attendance_correct_request}', [ApplicationController::class, 'update'])->name('application.update');
  Route::get('/admin/attendance/staff/{id}/export', [ExportController::class, 'csvExport'])->name('attendance.list.byStaff.export');
  
  Route::get('/admin/attendance/staff/{id}', [AttendanceListController::class, 'byStaff'])->name('attendance.list.byStaff');
  Route::get('/admin/staff/list', [UserController::class, 'list'])->name('staff.list');
  Route::patch('/attendance/{id}/update', [AdminController::class, 'update'])->name('admin.update');
  Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('admin.list');
});

Route::middleware('auth')->group(function () {
    Route::get('/stamp_correction_request/list:{tab}', [ApplicationController::class, 'list'])->name('correction.list');
    Route::post('/attendance/{id}/correct_request', [ApplicationController::class, 'application'])->name('application');
    Route::get('/attendance/list', [AttendanceListController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::get('/attendance', [AttendanceController::class, 'today'])->name('attendance')->middleware( 'verified');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::patch('/attendance', [AttendanceController::class, 'update'])->name('attendance.update');
      // ↓↓ログアウト後リダイレクト用。結果としてログアウト後はログイン画面に遷移。（４０４エラー回避のため）
    Route::get('/', [AttendanceController::class, 'today'])->name('home');
});

