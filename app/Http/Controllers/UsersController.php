<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
    //注册
    public function create(){
    	return view('users.create');
    }

    public function show(User $user){
    	return view('users.show',compact('user'));#将用户对象 $user 通过 compact 方法转化为一个关联数组
    }

    public function store(Request $request){
    	//对提交的信息进行验证
    	//validate 方法接收两个参数，第一个参数为用户的输入数据，第二个参数为该输入数据的验证规则。
    	$this->validate($request,[
    		'name'=>'required|max:50',
    		'email'=>'required|email|unique:users|max:255',
    		'password'=>'required|confirmed|min:6'
    	]);

    	$user=User::create([
    		'name'=>$request->name,
    		'email'=>$request->email,
    		'password'=>bcrypt($request->password),
    	]);
        //自动登录
        Auth::login($user);
    	session()->flash('success','欢迎，您将在这里开启一段新的旅程～');
    	return redirect()->route('users.show',[$user]);
    }
}
