<?php
function isMobile() {
	$mobile = array();
	static $mobilebrowser_list = 'Mobile|iPhone|Android|WAP|NetFront|JAVA|OperasMini|UCWEB|WindowssCE|Symbian|Series|webOS|SonyEricsson|Sony|BlackBerry|Cellphone|dopod|Nokia|samsung|PalmSource|Xphone|Xda|Smartphone|PIEPlus|MEIZU|MIDP|CLDC';
	//note 获取手机浏览器
	if (preg_match("/$mobilebrowser_list/i", $_SERVER['HTTP_USER_AGENT'], $mobile)) {
		return true;
	} else {
		if (preg_match('/(mozilla|chrome|safari|opera|m3gate|winwap|openwave)/i', $_SERVER['HTTP_USER_AGENT'])) {
			return false;
		} else {
			if ($_GET['mobile'] === 'yes') {
				return true;
			} else {
				return false;
			}
		}
	}
}
/**
 * [pages description]
 * @param  [type] $arr      [要处理的数组数据]
 * @param  [type] $offset   [其实位置从0开始]
 * @param  [type] $page_num [单页的数据数量]
 * @return [type]           [返回分割后的数据]
 */
function pages($arr,$offset,$page_num){
    $num = count($arr);
    if ( ((int)$offset + (int)$page_num) > $num) {
        return array_slice($arr,$offset,$num - $offset);
    }
    return array_slice($arr,$offset,$page_num);
}
/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug  调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false) {
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if($ssl){
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
    //return array($http_code, $response,$requestinfo);
}
function update_add_time($add_time){
    $add_time = strtotime($add_time);
    if(time()-$add_time>0 && time()-$add_time<60){
        return '刚刚';
    }else if(time()-$add_time>=60 && time()-$add_time<120){
        return '1分钟前';
    }else if(time()-$add_time>=120 && time()-$add_time<180){
        return '2分钟前';
    }else if(time()-$add_time>=180 && time()-$add_time<240){
        return '3分钟前';
    }else if(time()-$add_time>=240 && time()-$add_time<300){
        return '4分钟前';
    }else if(time()-$add_time>=300 && time()-$add_time<360){
        return '5分钟前';
    }else if(time()-$add_time>=360 && time()-$add_time<420){
        return '6分钟前';
    }else if(time()-$add_time>=420 && time()-$add_time<480){
        return '7分钟前';
    }else if(time()-$add_time>=480 && time()-$add_time<540){
        return '8分钟前';
    }else if(time()-$add_time>=540 && time()-$add_time<600){
        return '9分钟前';
    }else if(time()-$add_time>=600 && time()-$add_time<900){
        return '10分钟前';
    }else if(time()-$add_time>=900 && time()-$add_time<1200){
        return '15分钟前';
    }else if(time()-$add_time>=1200 && time()-$add_time<1500){
        return '20分钟前';
    }else if(time()-$add_time>=1500 && time()-$add_time<1800){
        return '25分钟前';
    }else if(time()-$add_time>=1800 && time()-$add_time<3600){
        return '30分钟前';
    }else if(time()-$add_time>=3600 && time()-$add_time<7200){
        return '1小时前';
    }else if(time()-$add_time>=7200 && time()-$add_time<10800){
        return '2小时前';
    }else{
        return date('m-d H:i',$add_time);
    }
}
?>