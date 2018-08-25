<?php
/**
 * 获取统计数据
 * Created by PhpStorm.
 * User: liyaohui
 * Date: 2018/8/24
 * Time: 17:46
 */

namespace app\count_data\controller;

use think\Db;
class CountData extends \Container
{
    private $tokenInfo = [];
    public function __construct()
    {
        // 验证token
        $token = input('token');
        $checkTokenInfo = \Check::checkToken($token);

        // token 验证错误
        if (!$checkTokenInfo['success']) {
            return json($checkTokenInfo);
            die();
        } else {
            $this->tokenInfo = $checkTokenInfo;
        }
    }

    public function getCountIP()
    {
        try {
            $countDays = input('days');
        } catch (\Exception $e) {
            return json(['code' => 500, 'error' => $e->getMessage()]);
        }
    }
}