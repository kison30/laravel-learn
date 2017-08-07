<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

    //
    public function create()
    {
        return view('sessions.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|void
     * 登录
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'email' => 'required | email | max:255',
            'password' => 'required'
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        $auth = Auth::attempt($credentials,$request->has('remember'));
        if($auth){
            session()->flash('success','欢迎回来！');
            return redirect()->intended(route('users.show',[Auth::user()]));
        }else{
            session()->flash('danger','很抱歉，你的邮箱和密码不匹配！');
            return redirect()->back();
        }
    }

    public function destroy()
    {
        Auth::logout();
        session()->flash('success','你已成功退出');
        return redirect('login');
    }
}
