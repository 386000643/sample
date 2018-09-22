<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

use Auth;

class SessionsController extends Controller
{
    public function __construct(){
        $this->middleware('guest',[
            'only'=>['create']
        ]);
    }

    //
    public function create(){
    	return view('sessions.create');
    }

    public function store(Request $request){
    	$credentials=$this->validate($request,[
    		'email'=>'required|email|max:255',
    		'password'=>'required'
    	]);
    	//Auth 的 attempt 方法可以让我们很方便的完成用户的身份认证操作，attempt接收的第一个参数是需要进行身份认证数组，第二个参数是否开启记住我功能的布尔值
    	if (Auth::attempt($credentials,$request->has('remember'))) {
            if (Auth::user()->activated) {
               # 登录成功
                session()->flash('success','欢迎回来');
                //Auth::user() 方法来获取 当前登录用户 的信息，并将数据传送给路由。
                return redirect()->intended(route('users.show',[Auth::user()]));
            }else{
                Auth::logout();
                session()->flash('warning','你的账号未激活，请检查邮箱中的注册邮件进行激活。');
                return redirect('/');
            }
    		
    	}else{
    		//登录失败
    		session()->flash('danger','很抱歉，您的邮箱和密码不匹配');
    		return redirect()->back();
    	}
    	
    }

    public function destroy(){
    	Auth::logout();
    	session()->flash('success','您已成功退出');
    	return redirect('login');
    }
}
