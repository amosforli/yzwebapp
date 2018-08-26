<?php
/**
 * 用户验证类
 * Created by PhpStorm.
 * User: liyaohui
 * Date: 2018/8/23
 * Time: 15:45
 */

class Check
{
    /**
     * @param $token string     token字符串
     * @return array            返回解析的结果
     */
    public static function checkToken($token) {
        // 解码token
        $decToken = passport_decrypt($token, TOKEN_CODE);
        // 能否还原成数组
        $tokenInfo = json_decode($decToken, true);
        // 还原失败
        if (!$token || (json_last_error() === JSON_ERROR_NONE && !is_array($tokenInfo))) {
            return ['code' => 400, 'success' => false, 'error' => '数据错误'];
        } else {
            // 是否过期
            if (config('self_config.is_token_expire')) {
                if(time() - $tokenInfo['loginTime'] > config('self_config.token_expire_time')) {
                    return ['code' => 401, 'success' => false, 'error' => '登录过期'];
                }
            }
            return ['code' => 200, 'success' => true, 'data' => $tokenInfo];
        }
    }
}