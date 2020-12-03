<?php

namespace app\month\controller;

use think\Controller;
use think\Db;
use think\facade\View;
use think\Request;

class User extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $token = $this->request->token('__token__', 'sha1');
        $this->assign('token', $token);
//        echo md5(123456);
        return View::fetch();
    }

    //登录
   public function login(){
        $data = input();
       if(!captcha_check($data['code'])) {
           return '验证码错了憨憨';
       }
       $where['username'] = $data['username'];
       $where['password'] = md5($data['password']);
       $user = Db::name('user')->where($where)->find();
       if ($user){
           session('user',$user);
           return redirect('listImg');
       }else{
           return '失败';
       }
   }
   //图片展示
   public function listImg(){
        $title = input('title');
        if ($title){
            $imgList = Db::name('images')->where('title',$title)->paginate(2);
        }else{
            $imgList = Db::name('images')->where('pid',0)->paginate(2);
        }
        $user = session('user');
        View::assign('user',$user);
        View::assign('imgList',$imgList);
        return View::fetch();
   }
   /*
    * 退出登录
    */
   public function loginout(){
        session('user',null);
        return redirect('index');
   }

   //验证
    public function add(){
        return View::fetch();
    }

    public function vali(){
        $data = input();
        if(!captcha_check($data['code'])) {
            return '验证码错了憨憨';
        }
        $where['username'] = $data['username'];
        $where['password'] = md5($data['password']);
        $user = Db::name('user')->where($where)->find();
        if ($user){
            return redirect('addimg');
        }else{
            return '失败';
        }
    }

    public function addimg(){
       $data = Db::name('images')->where('pid',0)->select();
       View::assign('data',$data);
       return View::fetch();
    }

    public function addfun(){
        $data = input();
        $pid = $data['pid'];
        $userid = session('user.id');
        $img = Db::name('images')->where('id',$pid)->find();
        $type = $img['type'];
        $title = $data['title'];
        $files = request()->file('imges');
        foreach($files as $file){
            // 移动到框架应用根目录/uploads/ 目录下
            $info = $file->move( './uploads');
            if($info){
                $arr['pid'] = $pid;
                $arr['uid'] = $userid;
                $arr['type'] = $type;
                $arr['title'] = $title;
                $arr['href'] = '/uploads/'.$info->getSaveName();
                Db::name('images')->insert($arr);
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
        return redirect('listImg');
    }

    public function typelist(){
       $data = input();
       $id = $data['id'];
       $img = Db::name('images')->where('id',$id)->find();
       $type = $img['type'];
       $imgList = Db::name('images')->where('pid',$id)->select();
       View::assign('imgList',$imgList);
       View::assign('type',$type);
       return View::fetch();
    }

}
