<?php

namespace App\Services;

use App\Models\MyLog;

class HttpService
{

    /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @return bool|mixed
     */
    public static function curl($url, $params , $ispost = 0, $https = 0)
    {
        set_time_limit(0);

        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true); //  PHP 5.6.0 后必须开启
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, 1);
            // curl_setopt($ch, CURLOPT_INFILESIZE, 1048576);
            // curl_setopt($ch, CURLOPT_UPLOAD, 0);

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
            
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    public static function curlWithHeader($url, $params , $type = 'get', $https = 0,$headers = array())
    {
        set_time_limit(0);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1); //返回response头部信息
        curl_setopt($ch, CURLINFO_HEADER_OUT, true); //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
//        curl_setopt($ch,CURLOPT_SSLVERSION,3);
//         curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true); //  PHP 5.6.0 后必须开启

//        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
//        curl_setopt($ch, CURLOPT_PROXY, "ss.alphaflycross.top"); //代理服务器地址
//        curl_setopt($ch, CURLOPT_PROXYPORT, 1238); //代理服务器端口
//        curl_setopt($ch, CURLOPT_PROXYUSERPWD, "alpha:iu654321"); //http代理认证帐号，username:password的格式
//        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式


        if(!empty($headers)){
            foreach ($headers as $k => $v){
                $headerData[] = $k.":".$v;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        }
        if($https){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if(strtolower($type) == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            // curl_setopt($ch, CURLOPT_INFILESIZE, 1048576);
            // curl_setopt($ch, CURLOPT_UPLOAD, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_URL, $url);
        }elseif(strtolower($type) == 'delete') {

            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }elseif(strtolower($type == 'put')){
            curl_setopt($ch, CURLOPT_PUT, true);
        }else{
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
//                dd('aa');
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);
//        dd($response);
//        dd(json_decode($response,1));

        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
//        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        $header = curl_getinfo($ch,CURLINFO_HEADER_OUT);
//        $hh = curl_getinfo($ch,CURLOPT_HEADER);


//        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        $arr = explode("\r\n",$response);
        $temp = array_pop($arr);
        $data['data'] = json_decode($temp);
        $data['header'] = $arr;

        return $data;

        $arr = [
            'data' => $response,
            'header' => $header,
            'http_info' => $httpInfo,
            'hh' => $hh,
        ];
        dd($arr);
        return [
            'data' => $response,
            'header' => $header,
            'http_info' => $httpInfo,
        ];
    }
    
    public static function curl_($url, $params , $type = 'get', $https = 0,$headers = array())
    {
        set_time_limit(0);
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch,CURLOPT_SSLVERSION,3);
//         curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true); //  PHP 5.6.0 后必须开启

//        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
//        curl_setopt($ch, CURLOPT_PROXY, "ss.alphaflycross.top"); //代理服务器地址
//        curl_setopt($ch, CURLOPT_PROXYPORT, 1238); //代理服务器端口
//        curl_setopt($ch, CURLOPT_PROXYUSERPWD, "alpha:iu654321"); //http代理认证帐号，username:password的格式
//        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式


        if(!empty($headers)){
            foreach ($headers as $k => $v){
                $headerData[] = $k.":".$v;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        }
        if($https){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if(strtolower($type) == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            // curl_setopt($ch, CURLOPT_INFILESIZE, 1048576);
            // curl_setopt($ch, CURLOPT_UPLOAD, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_URL, $url);
        }elseif(strtolower($type) == 'delete') {
            
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }elseif(strtolower($type == 'put')){
            curl_setopt($ch, CURLOPT_PUT, true);
        }else{
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
//                dd('aa');
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

	/**
     * curl工具方法
     * @param $url 请求地址
     * @param string $requestType 请求方式 post 或 get
     * @param array $data post 请求数据
     * @param int $timeout 请求超时
     * @return mixed
     */
    public static function curlRequest($url, $requestType = "get", $data = array(), $headers= 0, $timeout = 6)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, $headers);

        if (strtolower($requestType) == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }elseif(strtolower($requestType) == 'delete'){
            if (is_array($data)) {
                $data = http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $data);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    public static function post($url, $data, $header){//file_get_content
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 1);

        curl_setopt($ch, CURLOPT_TIMEOUT, 6); //设置curl执行超时时间最大是多少
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}