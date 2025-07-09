<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                if ( $request->path() == 'admin/logout' ) {
                    $redirect_url = '/admin/login' ;            
                } else {
                    $redirect_url = '/login' ;
                }

                return redirect($redirect_url);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::authenticateUsing(function (LoginRequest $request){
            $user = User::where('email', $request->email)->first();
            if ($user && Hash::check($request->password, $user->password)) {
                Auth::login($user);
                return $user;
            }
            return null;
        });
        Fortify::registerView(function() {
            return view('auth.register');
        });
        Fortify::loginView(function() {
            return view('auth.login');
        });
        Fortify::VerifyEmailview(function() {
            return view('auth.verify');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });


          // 一般ユーザーのログアウト後のリダイレクト先を設定するために追記
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);

    }
}
