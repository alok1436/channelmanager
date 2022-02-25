<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Mail\PasswordMail;
use Session;
use Mail;

class LoginController extends Controller
{
    //
    public function index() {
        $setting    = DB::table('tbl_setting')
                        ->first();

        Session::put("main_back",   $setting->main_background);
        Session::put("logo1",       $setting->logo1);
        Session::put("logo2",       $setting->logo2);
        return View::make('login');
    }

    public function dologin(Request $request) {
        $userName = $request['username'];
        $password = $request['password'];

        $user = DB::table('user')
            ->where('role', '=', 'admin')
            ->where('password', '=', $password)
            ->where('username', '=', $userName)
            ->orwhere('email', '=', $userName)
            ->get();

        if(count($user) > 0) {
            Session::put('userID', $user[0]->userid);
            return redirect()->route('dashboard');
        } else {
            $params['error'] = "Invalid email or password.";
            return View::make('login', $params);
        }
    }

    public function logout() {
        session()->flush();
        return redirect()->route('dashboard');
    }

    public function passwordresetemail(Request $request) {
        $username = $request['username'];
        $user = DB::table('user')
            ->where('username', '=', $username)
            ->orwhere('email', '=',  $username)
            ->get();
        if(count($user) > 0) {
            $userid         = $user[0]->userid;
            $email          = $user[0]->email;
            $suject         = "Password reset";
            $description    = "This is the link to change your password!";
            $link           = "http://provenzana.com/channelmanager/public/passwordresetview?id=".$userid;
            $details = [
                'email' => $request['email'],
                'subject' => $suject,
                'description' => $description,
                'link' => $link
            ];

            Mail::to($email)->send(new PasswordMail($details));

            $params["msg"] = "Email sent. Please check your inbox or junk";
            return View::make("login", $params);
        } else {
            $params["error"] = "This username or email is not existed!";
            $params["username"] = $username;
            return View::make("login", $params);
        }
    }

    public function passwordresetview() {
        $userid             = $_GET['id'];
        $params['userid']   = $userid;
        return View::make('forget', $params);
    }

    public function passwordreset(Request $request) {
        $userid     = $request['userid'];
        $password   = $request['password'];

        DB::table('user')
            ->where('userid', '=', $userid)
            ->update([
                'password'    => $password
            ]);

        $params["msg"] = "Password changed!";
        return View::make("login", $params);
    }
}
