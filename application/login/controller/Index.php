<?php
/**
 * 登录
 * User: liyaohui
 * Date: 2018/8/22
 * Time: 16:58
 */

namespace app\login\controller;

use \think\Db;
class Index extends \think\Container
{
    // 代理商 登录验证 需要调用主站接口
    public function agentLogin()
    {
        try {
            $tel = input('tel');
            $pwd = input('pwd');
            $tel = '18566007892';
            $pwd = 'stbwedid';
            if (!$tel || !$pwd) {
                return json(['code' => 500, 'error' => '请输入账号密码']);
            }
            // 检测手机号
            if (preg_match("/^1[34578]{1}\d{9}$/", $tel) == 0) {
                return json(['code' => 500, 'error' => '手机号格式不正确']);
            }
            $postUrl = "http://test.72e.net/mobile/instance/user.aspx?action=AppUserLogin";

            $returnData = curlRequest($postUrl, ['Mobile' => $tel, 'PassWord' => $pwd]);
            // 请求失败
            if($returnData['success'] === false ){
                return json($returnData);
            }
            $returnData = json_decode($returnData['data'], true);
            // 登录成功
            if ($returnData['code'] == 200) {
                $tokenInfo = [
                    'tel' => $tel,
                    'loginTime' => time(), // 登录的时间戳
                    'type' => USER_TYPE_AGENT
                ];
                // 返回加密后的token 以后的请求数据 都需要token做验证
                $token = passport_encrypt(json_encode($tokenInfo), TOKEN_CODE);
                return json([
                    'code' => 200,
                    'type' => USER_TYPE_AGENT,
                    'token' => $token
                ]);
            } else {
                return json([
                    'code' => 401,
                    'error' => $returnData['data'] ? $returnData['data'] : '登录失败'
                ]);
            }
        } catch (\Exception $e) {
            return json(['code' => 500, 'error' => $e->getMessage()]);
        }
    }

    // 直客登录验证 使用云指的账号登录
    public function customerLogin()
    {
        try{
            $host = input('host');
            $userName = input('user_name');
            $pwd = input('pwd');
            if (!$host || !$userName || !$pwd) {
                return json(['code' => 500, 'error' => '请输入登录信息']);
            }
            // 先查找域名是否存在
            $hostInfo = Db::table('tbl_user')
                        ->where('DomainName', 'like', "%$host%")
                        ->field('UserID')
                        ->find();
            if (!$hostInfo) {
                return json(['code' => 500, 'error' => '域名不存在']);
            }
            // 查找该域名下的管理员
            $userInfo = Db::table('tbl_siteadmin')
                        ->where([
                            'WUserID' => $hostInfo['UserID'],
                            'UserName' => $userName,
                            'IsDeleted' => 0
                        ])
                        ->field('Password,Perms,SiteID')
                        ->find();
            if (!$userInfo) {
                return json(['code' => 404, 'error' => '账号不存在']);
            }
            if (md5($pwd) != $userInfo['Password']) {
                return json(['code' => 401, 'error' => '密码错误']);
            }
            $tokenInfo = [
                'siteID' => $userInfo['SiteID'],
                'loginTime' => time(), // 登录的时间戳
                'type' => USER_TYPE_CUSTOMER,
                'userName' => $userName
            ];
            // 生成token
            $token = passport_encrypt(json_encode($tokenInfo), TOKEN_CODE);
            return json([
                'code' => 200,
                'type' => USER_TYPE_CUSTOMER,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'error' => $e->getMessage()]);
        }
    }

    // 验证token合法性
    public function checkToken()
    {
        try{
            $token = input('token');
            $tokenInfo = \Check::checkToken($token);
            return json($tokenInfo);
        } catch (\Exception $e) {
            return json(['code' => 500, 'error' => $e->getMessage()]);
        }
    }

    // 代理获取获取所有的域名
    public function agentGetDomains()
    {
        $token = input('token');
        $checkTokenInfo = \Check::checkToken($token);

        // token 验证错误
        if (!$checkTokenInfo['success']) {
            return json($checkTokenInfo);
        }

        // token 保存的信息不是代理
        $tokenInfo = $checkTokenInfo['data'];
        if ($tokenInfo['type'] !== USER_TYPE_AGENT) {
            return json(['code' => 401, 'error' => '身份验证错误']);
        }

        // 去主站获取数据
        $tel = $tokenInfo['tel'];
        $url = 'http://test.72e.net/mobile/instance/product.aspx?action=AppDomainList';
        $curlData = curlRequest($url, ['Mobile' => $tel]);

        if (!$curlData['success']) {
            return json($curlData);
        }
        $data = json_decode($curlData['data'], true);
        if ($data['code'] != 200) {
            return ['code' => 500, 'error' => $data['msg']];
        }
        return json(['code' => 200, 'data' => $data['data']]);
    }

}