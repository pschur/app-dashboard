<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirect(){
        return Socialite::driver('authentik')->redirect();
    }

    public function callback(){
        $remote = Socialite::driver('authentik')->user();

        User::updateOrCreate([
            'remote_id' => $remote->id,
        ], [
            'name' => $remote->name ?? '',
            'username' => $remote->nickname ?? '',
            'email' => $remote->email ?? '',
            'avatar' => $remote->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($remote->name ?? 'Anonymous').'&color=7F9CF5&background=EBF4FF',
            'token' => $remote->token,
        ]);

        $user = User::where('remote_id', $remote->id)->first();

        Auth::login($user, true);

        return redirect()->route('home');
    }

    public function logout(){
        $user_id = Auth::id();
        Auth::logout();
        Session::flush();

        User::destroy($user_id);

        return redirect()->away(config('authentik.logout_url'));
    }
}
