<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth',[
            'only' => ['edit','update']
        ]);

        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

    public function index()
    {
        $users = User::paginate(30);
        return view('users.index',compact('users'));
    }

    //
    public function create()
    {
        return view('users.create');
    }

    /**
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * 展示页面
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $statuses = $user->statuses()
                        ->orderBy('created_at','desc')
                        ->paginate(30);
        return view('users.show',compact('user','statuses'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * 添加用户信息
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $this->sendEmailConfirmationTo($user);
//        Auth::login($user);
        session()->flash('success','欢迎，您将在这里开启一段新的旅程');
        return redirect()->route('users.show',[$user]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * 编辑用户
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update',$user);
        return view('users.edit',compact('user'));
    }

    public function update($id,Request $request)
    {
        $this->validate($request,[
            'name' => 'required | max:50',
            'password' => 'confirmed | min:6'
        ]);

        $user = User::findOrFail($id);
        $this->authorize('update',$user);

        $data = [];
        $data['name'] =  $request->name;
        if($request->password){
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success','更新成功');
        return redirect()->route('users.show',$id);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('destroy',$user);
        $user->delete();
        session()->flash('success','成功删除用户');
        return back();
    }

    /**
     * @param $token
     * @return \Illuminate\Http\RedirectResponse
     * 激活用户
     */
    public function confirmEmail($token)
    {
        $user = User::where('activation_token',$token)->firstOrFail();
        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success','恭喜你,激活成功');
        return redirect()->route('user.show',[$user]);
    }

    /**
     * @param $user
     */
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'aufree@yousails.com';
        $name = 'Aufree';
        $to = $user->email;
        $subject = "感谢注册Sample应用！请确认你的邮箱";

        Mail::send($view,$data,function ($message) use ($from,$name,$to,$subject){
            $message->from($from,$name)->to($to)->subject($subject);
        });
    }


}
