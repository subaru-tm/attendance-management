<?php

namespace App\Http\Response;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // ログアウト後のリダイレクト先を設定
        $is_admin = Auth::user()->is_admin;
        if($is_admin) {
            $redirect_url = '/admin/login' ;            
        } else {
            $redirect_url = '/login' ;
        }

        if($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        return redirect($redirect_url);
    }
}
