<?php
namespace app\index\controller;

use think\Db;
class Login extends \BaseController
{
    // 登录验证
    public function index()
    {
        $info = ['SiteID' => 1070, 'Type' => '1', 'UserID' => '12356697646'];
        $pwd = passport_encrypt(json_encode($info), TOKEN_CODE);
        $res = [$pwd, passport_decrypt($pwd, TOKEN_CODE)];
        return json($res);
    }


}