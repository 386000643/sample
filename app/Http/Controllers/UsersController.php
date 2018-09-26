<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    public function __construct(){
        //用户权限，除了show','create','store，其他都需要登录后才能访问
        $this->middleware('auth',[
            'except'=>['show','create','store','index','confirmEmail']
        ]);
        //只让未登录用户访问注册页面
        $this->middleware('guest',[
            'only'=>['create']
        ]);
    }

    public function index(){
        $users=User::paginate(10);
        return view('users.index',compact('users'));
    }


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
        // TODO 这里发送邮件有问题呢。居然又是 cache 的问题呢，，，
        $this->sendEmailConfirmationTo($user);
        session()->flash('success','验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
        //自动登录
        //Auth::login($user);
    	//session()->flash('success','欢迎，您将在这里开启一段新的旅程～');
    	//return redirect()->route('users.show',[$user]);
    }

    public function edit(User $user){
        $this->authorize('update',$user);
        return view('users.edit',compact('user'));
    }

    public function update(User $user,Request $request){
        $this->validate($request,[
            'name'=>'required|max:50',
            'password'=>'nullable|confirmed|min:6'
        ]);
        //dump($user->id);exit;
        $this->authorize('update',$user);
        //不更新密码的操作
        $data=[];
        $data['name']=$request->name;
        if ($request->password) {
            $data['password']=bcrypt($request->password);
        }
        $user->update($data);
        session()->flash('success','个人资料更新成功');
        return redirect()->route('users.show',$user->id);
    }

    public function destroy(User $user){
        $this->authorize('destroy',$user);
        $user->delete();
        session()->flash('success','成功删除用户');
        return back();
    }

    //发送确认邮件
    protected function sendEmailConfirmationTo($user){
        $view='emails.confirm';
        $data=compact('user');
        // $from='aufree@yousails.com';
        // $name='Aufree';
        $to=$user->email;
        $subject="感谢注册Sample应用，请确认你的邮箱。";

        // Mail::send($view,$data,function($message) use($from,$name,$to,$subject){
        //     $message->from($from,$name)->to($to)->subject($subject);
        // });
        Mail::send($view,$data,function($message) use($to,$subject){
            $message->to($to)->subject($subject);
        });
    }

    //确认邮件
    public function confirmEmail($token){
        $user=User::where('activation_token',$token)->firstOrFail();
        $user->activated=true;
        $user->activation_token=null;
        $user->save();

        Auth::login($user);
        session()->flash('success','恭喜你，激活成功！');
        return redirect()->route('users.show',[$user]);
    }
}
