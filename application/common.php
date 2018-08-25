<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * Passport 加密函数
 *
 * @param   string  等待加密的原字串
 * @param   string  私有密匙(用于解密和加密)
 *
 * @return  string  原字串经过私有密匙加密后的结果
 */
function passport_encrypt($txt, $key)
{
    // 使用随机数发生器产生 0~32000 的值并 MD5()
    srand((double)microtime() * 1000000);
    $encrypt_key = md5(rand(0, 32000));

    // 变量初始化
    $ctr = 0;
    $tmp = '';

    // for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
    for ($i = 0; $i < strlen($txt); $i++) {
        // 如果 $ctr = $encrypt_key 的长度，则 $ctr 清零
        $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
        // $tmp 字串在末尾增加两位，其第一位内容为 $encrypt_key 的第 $ctr 位，
        // 第二位内容为 $txt 的第 $i 位与 $encrypt_key 的 $ctr 位取异或。然后 $ctr = $ctr + 1
        $tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
    }

    // 返回结果，结果为 passport_key() 函数返回值的 base64 编码结果
    return base64_encode(passport_key($tmp, $key));
}

/**
 * Passport 解密函数
 *
 * @param   string  加密后的字串
 * @param   string  私有密匙(用于解密和加密)
 * @return  string  字串经过私有密匙解密后的结果
 */
function passport_decrypt($txt, $key)
{
    // $txt 的结果为加密后的字串经过 base64 解码，然后与私有密匙一起，
    // 经过 passport_key() 函数处理后的返回值
    $txt = passport_key(base64_decode($txt), $key);

    // 变量初始化
    $tmp = '';

    // for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
    for ($i = 0; $i < strlen($txt); $i++) {
        // $tmp 字串在末尾增加一位，其内容为 $txt 的第 $i 位，
        // 与 $txt 的第 $i + 1 位取异或。然后 $i = $i + 1
        $tmp .= $txt[$i] ^ $txt[++$i];
    }

    // 返回 $tmp 的值作为结果
    return $tmp;
}

/**
 * Passport 密匙处理函数
 *
 * @param   string  待加密或待解密的字串
 * @param   string  私有密匙(用于解密和加密)
 *
 * @return  string  处理后的密匙
 */
function passport_key($txt, $encrypt_key)
{
    // 将 $encrypt_key 赋为 $encrypt_key 经 md5() 后的值
    $encrypt_key = md5($encrypt_key);

    // 变量初始化
    $ctr = 0;
    $tmp = '';

    // for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
    for ($i = 0; $i < strlen($txt); $i++) {
        // 如果 $ctr = $encrypt_key 的长度，则 $ctr 清零
        $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
        // $tmp 字串在末尾增加一位，其内容为 $txt 的第 $i 位，
        // 与 $encrypt_key 的第 $ctr + 1 位取异或。然后 $ctr = $ctr + 1
        $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
    }

    // 返回 $tmp 的值作为结果
    return $tmp;
}

/**
 * curlRequest curl请求
 * @param string $url           要请求的url
 * @param array $params         请求附带的参数
 * @param string $requestType   请求方式 post  get
 * @return array                success为false则请求失败 error curl报错的信息 否则成功 data为返回的数据
 */
function curlRequest($url, $params = [], $requestType = 'post')
{
    // $params 拼接成字符串
    $paramsStr = '';
    foreach($params as $key => $val) {
        $paramsStr .= "&$key=$val";
    }
    $paramsStr = ltrim($paramsStr, '&');
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    $requestType = strtolower($requestType) === 'post' ? CURLOPT_POST : CURLOPT_HTTPGET;
    curl_setopt($ch, $requestType, 1);//提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsStr);
    $returnData = curl_exec($ch);//运行curl

    if($returnData === false ){
        $returnData = ['success' => false, 'code' => 500, 'error' => curl_error($ch)];
    } else {
        $returnData = ['success' => true, 'code' => 200, 'data' => $returnData];
    }
    curl_close($ch);
    return $returnData;
}
