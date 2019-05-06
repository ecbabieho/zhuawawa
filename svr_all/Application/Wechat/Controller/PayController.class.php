<?php
namespace Wechat\Controller;
use Think\Controller;

class PayController extends BaseController {
	private $appid = '';
	private $mch_id = '';
	private $key = '';
	/**
	 * ajax_pay 支付
	 */
	public function ajax_pay() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$coin_config_id = I('coin_config_id');
		if ($coin_config_id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $coin_config_id,
			'deleted' => 0,
		);
		$coin_info = M('coin_config')->where($map)->find();
		if (!$coin_info) {
			$this->_err_ret('支付规则不存在');
		}
		$total_fee = $coin_info['pay_num'] * 100;
		$coin_num = $coin_info['coin_num'];
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user['id'],
			'coin_config_id' => $coin_config_id,
			'num' => $coin_num,
			'before_balance' => 0,
			'after_balance' => 0,
			'status' => 0,
			'money' => $coin_info['pay_num'],
		    'type'=>0
		);
		$res = M('user_coin_record')->add($data);
		if (!$res) {
			$this->_err_ret('订单生成失败');
		}
		$appid = $this->appid; //如果是公众号 就是公众号的appid;小程序就是小程序的appid
		$body = '支付订单';
		$mch_id = $this->mch_id;
		$KEY = $this->key;

		$nonce_str = $this->randomkeys(32); //随机字符串
		$notify_url = 'https://fssw.bichonfrise.cn/index.php/Wechat/App/xiao_notify_url'; //支付完成回调地址url,不能带参数
		$out_trade_no = time() . '-' . $res; //商户订单号
		$spbill_create_ip = $_SERVER['SERVER_ADDR'];
		$trade_type = 'JSAPI'; //交易类型 默认JSAPI
		//这里是按照顺序的 因为下面的签名是按照(字典序)顺序 排序错误 肯定出错
		$post['appid'] = $appid;
		$post['body'] = $body;
		$post['mch_id'] = $mch_id;
		$post['nonce_str'] = $nonce_str; //随机字符串
		$post['notify_url'] = $notify_url;
		$post['openid'] = $user['openid'];
		$post['out_trade_no'] = $out_trade_no;
		$post['spbill_create_ip'] = $spbill_create_ip; //服务器终端的ip
		$post['total_fee'] = $total_fee; //总金额 最低为一分钱 必须是整数
		$post['trade_type'] = $trade_type;
		$sign = $this->MakeSign($post, $KEY); //签名

		$post_xml = '<xml>
               <appid>' . $appid . '</appid>
               <body>' . $body . '</body>
               <mch_id>' . $mch_id . '</mch_id>
               <nonce_str>' . $nonce_str . '</nonce_str>
               <notify_url>' . $notify_url . '</notify_url>
               <openid>' . $user['openid'] . '</openid>
               <out_trade_no>' . $out_trade_no . '</out_trade_no>
               <spbill_create_ip>' . $spbill_create_ip . '</spbill_create_ip>
               <total_fee>' . $total_fee . '</total_fee>
               <trade_type>' . $trade_type . '</trade_type>
               <sign>' . $sign . '</sign>
            </xml> ';
		//统一下单接口prepay_id
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$xml = $this->http_request($url, $post_xml); //POST方式请求http
		$array = $this->xml2array($xml); //将【统一下单】api返回xml数据转换成数组，全要大写
		if ($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS') {
			$time = time();
			$tmp = array(); //临时数组用于签名
			$tmp['appId'] = $appid;
			$tmp['nonceStr'] = $nonce_str;
			$tmp['package'] = 'prepay_id=' . $array['PREPAY_ID'];
			$tmp['signType'] = 'MD5';
			$tmp['timeStamp'] = "$time";

			$data['state'] = 1;
			$data['timeStamp'] = "$time"; //时间戳
			$data['nonceStr'] = $nonce_str; //随机字符串
			$data['signType'] = 'MD5'; //签名算法，暂支持 MD5
			$data['package'] = 'prepay_id=' . $array['PREPAY_ID']; //统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
			$data['paySign'] = $this->MakeSign($tmp, $KEY); //签名,具体签名方案参见微信公众号支付帮助文档;
			$data['out_trade_no'] = $out_trade_no;
			$data['appId'] = $appid;
		} else {
			$data['state'] = 0;
			$data['text'] = "错误";
			$data['RETURN_CODE'] = $array['RETURN_CODE'];
			$data['RETURN_MSG'] = $array['RETURN_MSG'];
			$this->_err_ret($array['RETURN_MSG']);
		}
		$this->_suc_ret($data);
	}
	
	/**
	 * ajax_pay_coin_vip 支付购买金币赠送会员
	 */
	public function ajax_pay_coin_vip() {
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $coin_config_id = I('coin_config_id');
	    if ($coin_config_id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $map = array(
	        'id' => $coin_config_id,
	        'deleted' => 0,
	    );
	    $coin_info = M('coin_config')->where($map)->find();
	    if (!$coin_info) {
	        $this->_err_ret('支付规则不存在');
	    }
	    $total_fee = $coin_info['vip_pay_num'] * 100;
	    $coin_num = $coin_info['coin_num'];
	    $data = array(
	        'add_time' => date('Y-m-d H:i:s'),
	        'deleted' => 0,
	        'user_id' => $user['id'],
	        'coin_config_id' => $coin_config_id,
	        'num' => $coin_num,
	        'before_balance' => 0,
	        'after_balance' => 0,
	        'status' => 0,
	        'money' => $coin_info['vip_pay_num'],
	        'type'=>-8
	    );
	    $res = M('user_coin_record')->add($data);
	    if (!$res) {
	        $this->_err_ret('订单生成失败');
	    }
	    $appid = $this->appid; //如果是公众号 就是公众号的appid;小程序就是小程序的appid
	    $body = '支付订单';
	    $mch_id = $this->mch_id;
	    $KEY = $this->key;
	    
	    $nonce_str = $this->randomkeys(32); //随机字符串
	    $notify_url = 'https://fssw.bichonfrise.cn/index.php/Wechat/App/xiao_notify_url_coin_vip'; //支付完成回调地址url,不能带参数
	    $out_trade_no = time() . '-' . $res; //商户订单号
	    $spbill_create_ip = $_SERVER['SERVER_ADDR'];
	    $trade_type = 'JSAPI'; //交易类型 默认JSAPI
	    //这里是按照顺序的 因为下面的签名是按照(字典序)顺序 排序错误 肯定出错
	    $post['appid'] = $appid;
	    $post['body'] = $body;
	    $post['mch_id'] = $mch_id;
	    $post['nonce_str'] = $nonce_str; //随机字符串
	    $post['notify_url'] = $notify_url;
	    $post['openid'] = $user['openid'];
	    $post['out_trade_no'] = $out_trade_no;
	    $post['spbill_create_ip'] = $spbill_create_ip; //服务器终端的ip
	    $post['total_fee'] = $total_fee; //总金额 最低为一分钱 必须是整数
	    $post['trade_type'] = $trade_type;
	    $sign = $this->MakeSign($post, $KEY); //签名
	    
	    $post_xml = '<xml>
               <appid>' . $appid . '</appid>
               <body>' . $body . '</body>
               <mch_id>' . $mch_id . '</mch_id>
               <nonce_str>' . $nonce_str . '</nonce_str>
               <notify_url>' . $notify_url . '</notify_url>
               <openid>' . $user['openid'] . '</openid>
               <out_trade_no>' . $out_trade_no . '</out_trade_no>
               <spbill_create_ip>' . $spbill_create_ip . '</spbill_create_ip>
               <total_fee>' . $total_fee . '</total_fee>
               <trade_type>' . $trade_type . '</trade_type>
               <sign>' . $sign . '</sign>
            </xml> ';
	    //统一下单接口prepay_id
	    $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	    $xml = $this->http_request($url, $post_xml); //POST方式请求http
	    $array = $this->xml2array($xml); //将【统一下单】api返回xml数据转换成数组，全要大写
	    if ($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS') {
	        $time = time();
	        $tmp = array(); //临时数组用于签名
	        $tmp['appId'] = $appid;
	        $tmp['nonceStr'] = $nonce_str;
	        $tmp['package'] = 'prepay_id=' . $array['PREPAY_ID'];
	        $tmp['signType'] = 'MD5';
	        $tmp['timeStamp'] = "$time";
	        
	        $data['state'] = 1;
	        $data['timeStamp'] = "$time"; //时间戳
	        $data['nonceStr'] = $nonce_str; //随机字符串
	        $data['signType'] = 'MD5'; //签名算法，暂支持 MD5
	        $data['package'] = 'prepay_id=' . $array['PREPAY_ID']; //统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
	        $data['paySign'] = $this->MakeSign($tmp, $KEY); //签名,具体签名方案参见微信公众号支付帮助文档;
	        $data['out_trade_no'] = $out_trade_no;
	        $data['appId'] = $appid;
	    } else {
	        $data['state'] = 0;
	        $data['text'] = "错误";
	        $data['RETURN_CODE'] = $array['RETURN_CODE'];
	        $data['RETURN_MSG'] = $array['RETURN_MSG'];
	        $this->_err_ret($array['RETURN_MSG']);
	    }
	    $this->_suc_ret($data);
	}
	
	/**
	 * ajax_activity_pay 支付
	 */
	public function ajax_activity_pay() {
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $type = I('type');
	    if ($type == '') {
	        $this->_err_ret('参数不完整');
	    }
	    if ($type != 0 && $type != 1) {
	        $this->_err_ret('支付规则不存在');
	    }
	    
	    //购买次数
	    $map = array(
	        'user_id'=>$user['id'],
	        'coin_config_id' => array('IN',array(-10,-11)),//-101块钱19糖豆，更多优惠
	        'status'=>1,
	        'deleted'=>0
	    );
	    $pay_times = M('user_coin_record')->where($map)->find();	
	    if ($pay_times) {
	        $this->_err_ret('每个人只能购买一次哦~');
	    }
	    //购买金额
	    $money = 1;
	    $total_fee = $money * 100;
	    $map = array(
	        'id' => $user['id'],
	    );
	    $userModel = D('user');
	    $user = $userModel->where($map)->find();
	    if($type == 0){//1块钱19糖豆，更多优惠
	        $data = array(
	            'add_time' => date('Y-m-d H:i:s'),
	            'deleted' => 0,
	            'user_id' => $user['id'],
	            'coin_config_id' => -10,//-101块钱19糖豆，更多优惠
	            'num' => 19,
	            'before_balance' => $user['coin_num'],
	            'after_balance' => $user['coin_num'] + 19,
	            'status' => 0,
	            'money' => $money,
	        );
	    }
	    if($type == 1){//1块钱10糖豆
	        $data = array(
	            'add_time' => date('Y-m-d H:i:s'),
	            'deleted' => 0,
	            'user_id' => $user['id'],
	            'coin_config_id' => -11,//1块钱10糖豆
	            'num' => 10,
	            'before_balance' => $user['coin_num'],
	            'after_balance' => $user['coin_num'] + 10,
	            'status' => 0,
	            'money' => $money,
	        );
	    }
	    $res = M('user_coin_record')->add($data);
	    if (!$res) {
	        $this->_err_ret('订单生成失败');
	    }
	    $appid = $this->appid; //如果是公众号 就是公众号的appid;小程序就是小程序的appid
	    $body = '支付订单';
	    $mch_id = $this->mch_id;
	    $KEY = $this->key;
	    
	    $nonce_str = $this->randomkeys(32); //随机字符串
	    $notify_url = 'https://fssw.bichonfrise.cn/index.php/Wechat/App/activity_notify_url'; //支付完成回调地址url,不能带参数
	    $out_trade_no = time() . '-' . $res; //商户订单号
	    $spbill_create_ip = $_SERVER['SERVER_ADDR'];
	    $trade_type = 'JSAPI'; //交易类型 默认JSAPI
	    //这里是按照顺序的 因为下面的签名是按照(字典序)顺序 排序错误 肯定出错
	    $post['appid'] = $appid;
	    $post['body'] = $body;
	    $post['mch_id'] = $mch_id;
	    $post['nonce_str'] = $nonce_str; //随机字符串
	    $post['notify_url'] = $notify_url;
	    $post['openid'] = $user['openid'];
	    $post['out_trade_no'] = $out_trade_no;
	    $post['spbill_create_ip'] = $spbill_create_ip; //服务器终端的ip
	    $post['total_fee'] = $total_fee; //总金额 最低为一分钱 必须是整数
	    $post['trade_type'] = $trade_type;
	    $sign = $this->MakeSign($post, $KEY); //签名
	    
	    $post_xml = '<xml>
               <appid>' . $appid . '</appid>
               <body>' . $body . '</body>
               <mch_id>' . $mch_id . '</mch_id>
               <nonce_str>' . $nonce_str . '</nonce_str>
               <notify_url>' . $notify_url . '</notify_url>
               <openid>' . $user['openid'] . '</openid>
               <out_trade_no>' . $out_trade_no . '</out_trade_no>
               <spbill_create_ip>' . $spbill_create_ip . '</spbill_create_ip>
               <total_fee>' . $total_fee . '</total_fee>
               <trade_type>' . $trade_type . '</trade_type>
               <sign>' . $sign . '</sign>
            </xml> ';
	    //统一下单接口prepay_id
	    $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	    $xml = $this->http_request($url, $post_xml); //POST方式请求http
	    $array = $this->xml2array($xml); //将【统一下单】api返回xml数据转换成数组，全要大写
	    if ($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS') {
	        $time = time();
	        $tmp = array(); //临时数组用于签名
	        $tmp['appId'] = $appid;
	        $tmp['nonceStr'] = $nonce_str;
	        $tmp['package'] = 'prepay_id=' . $array['PREPAY_ID'];
	        $tmp['signType'] = 'MD5';
	        $tmp['timeStamp'] = "$time";
	        
	        $data['state'] = 1;
	        $data['timeStamp'] = "$time"; //时间戳
	        $data['nonceStr'] = $nonce_str; //随机字符串
	        $data['signType'] = 'MD5'; //签名算法，暂支持 MD5
	        $data['package'] = 'prepay_id=' . $array['PREPAY_ID']; //统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
	        $data['paySign'] = $this->MakeSign($tmp, $KEY); //签名,具体签名方案参见微信公众号支付帮助文档;
	        $data['out_trade_no'] = $out_trade_no;
	        $data['appId'] = $appid;
	    } else {
	        $data['state'] = 0;
	        $data['text'] = "错误";
	        $data['RETURN_CODE'] = $array['RETURN_CODE'];
	        $data['RETURN_MSG'] = $array['RETURN_MSG'];
	        $this->_err_ret($array['RETURN_MSG']);
	    }
	    $this->_suc_ret($data);
	}
	/**
	 * ajax_pay_vip 会员VIP购买支付
	 */
	public function ajax_pay_vip() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();
		if (!$user) {
			exit;
		}

		$config_id = I('config_id');
		if ($config_id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $config_id,
			'deleted' => 0,
		);
		$coin_info = M('vip_pay_config')->where($map)->find();
		if (!$coin_info) {
			$this->_err_ret('支付规则不存在');
		}
		if($coin_info['name'] == '周卡-体验版'){
		    $map = array(
		        'deleted'=>0,
		        'user_id'=>$user['id'],
		        'config_id'=>$config_id,
		        'status'=>1,
		        'type'=>0,
		    );
		    $vip_order_info = M('vip_order')->where($map)->find();
		    if($vip_order_info){
		        $this->_err_ret('体验周卡只能购买一次哦~');
		    }
		}
		$vip_grade = M('vip_grade')->where(array('id' => $coin_info['grade_id']))->find();
		//如果是用户购买为黄金会员等级，购买会员为三次以下，金额为1元
		$user_order_map = array(
		    'deleted'=>0,
		    'user_id'=>$user['id'],
		    'status'=>1,
		    'type'=>0
		);
		$user_order = M('vip_order')->where($user_order_map)->select();
// 		if($vip_grade['level'] == 2 && count($user_order)<3 && $coin_info['discount_price']>1){
// 		    $coin_info['discount_price'] = 1;
// 		}
// 		if(count($user_order)<1 && $coin_info['discount_price']>10){
// 		    $coin_info['discount_price'] = 9.9;
// 		}
		// 判断用户当前支付的金额
		//当前会员没到期，并且当前会员比所选会员等级高
		if ($vip_grade['level'] < $user['level'] && time()<strtotime($user['over_time'])) {
			$this->_err_ret("您当前已经是更高级会员，享受此类会员服务哦~");
		} else if ($vip_grade['level'] < $user['level'] && time()>strtotime($user['over_time'])) {
		    //之前的会员等级已经到期或者没有购买过会员,并且当前会员比所选会员等级高
		    $total_fee = $coin_info['discount_price'] * 100;
		    $over_time = date('Y-m-d H:i:s',time()+$coin_info['days']*86400);
		}else if ($vip_grade['level'] == $user['level']) {
		    // 续费用户
		    if($user['over_time'] == '0000-00-00 00:00:00' || time()>strtotime($user['over_time'])){
		        //之前的会员等级已经到期或者没有购买过会员
		        $total_fee = $coin_info['discount_price'] * 100;
		        $over_time = date('Y-m-d H:i:s',time()+$coin_info['days']*86400);
		    }
		    if(time()<strtotime($user['over_time'])){
		        //没到期，延长续费
		        $total_fee = $coin_info['discount_price'] * 100;
		        $over_time = date('Y-m-d H:i:s',strtotime($user['over_time'])+$coin_info['days']*86400);
		    }
		}else{
		    // 低级用户转高级 重新计算费用
		    if($user['over_time'] == '0000-00-00 00:00:00' || time()>strtotime($user['over_time'])){
		        //之前的会员等级已经到期或者没有购买过会员
		        $total_fee = $coin_info['discount_price'] * 100;
		        $over_time = date('Y-m-d H:i:s',time()+$coin_info['days']*86400);
		    }
		    if(time()<strtotime($user['over_time'])){
		        //没到期，低级用户转高级 重新计算费用
		        //当前会员剩余天数
		        $days_num = intval((strtotime($user['over_time'])-time())/86400);
		        //所购买会员每天价格
		        $day_money = $coin_info['discount_price']/$coin_info['days'];
		        
		        $temp_grade = M('vip_grade')->where(array('level'=>$user['level']))->find();
		        $temp_pay_config = M('vip_pay_config')->where(array('grade_id'=>$temp_grade['id'],'days'=>30))->find();
		        $temp_day_money = $temp_pay_config['discount_price']/$temp_pay_config['days'];
		        
		        $day_money = $day_money-$temp_day_money;
		        $total_fee = ($coin_info['discount_price']+$days_num*$day_money) * 100;
		        $over_time = date('Y-m-d H:i:s',strtotime($user['over_time'])+$coin_info['days']*86400);
		    }
		}
		//$total_fee = $coin_info['discount_price'] * 100;
		$total_fee = intval($total_fee);
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user['id'],
			'config_id' => $config_id,
			'level' => $vip_grade['level'],
		    'money' => $total_fee/100,
			'day_num' => $coin_info['days'],
			'status' => 0,
		    'over_time'=>$over_time
		);
		$res = M('vip_order')->add($data);
		if (!$res) {
			$this->_err_ret('订单生成失败');
		}
		$appid = $this->appid; //如果是公众号 就是公众号的appid;小程序就是小程序的appid
		$body = '支付vip订单';
		$mch_id = $this->mch_id;
		$KEY = $this->key;

		$nonce_str = $this->randomkeys(32); //随机字符串
		$notify_url = 'https://fssw.bichonfrise.cn/index.php/Wechat/App/vip_notify_url'; //支付完成回调地址url,不能带参数
		$out_trade_no = 'vip-' . time() . '-' . $res; //商户订单号
		$spbill_create_ip = $_SERVER['SERVER_ADDR'];
		$trade_type = 'JSAPI'; //交易类型 默认JSAPI
		//这里是按照顺序的 因为下面的签名是按照(字典序)顺序 排序错误 肯定出错
		$post['appid'] = $appid;
		$post['body'] = $body;
		$post['mch_id'] = $mch_id;
		$post['nonce_str'] = $nonce_str; //随机字符串
		$post['notify_url'] = $notify_url;
		$post['openid'] = $user['openid'];
		$post['out_trade_no'] = $out_trade_no;
		$post['spbill_create_ip'] = $spbill_create_ip; //服务器终端的ip
		$post['total_fee'] = $total_fee; //总金额 最低为一分钱 必须是整数
		$post['trade_type'] = $trade_type;
		$sign = $this->MakeSign($post, $KEY); //签名

		$post_xml = '<xml>
               <appid>' . $appid . '</appid>
               <body>' . $body . '</body>
               <mch_id>' . $mch_id . '</mch_id>
               <nonce_str>' . $nonce_str . '</nonce_str>
               <notify_url>' . $notify_url . '</notify_url>
               <openid>' . $user['openid'] . '</openid>
               <out_trade_no>' . $out_trade_no . '</out_trade_no>
               <spbill_create_ip>' . $spbill_create_ip . '</spbill_create_ip>
               <total_fee>' . $total_fee . '</total_fee>
               <trade_type>' . $trade_type . '</trade_type>
               <sign>' . $sign . '</sign>
            </xml> ';
		//统一下单接口prepay_id
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$xml = $this->http_request($url, $post_xml); //POST方式请求http
		$array = $this->xml2array($xml); //将【统一下单】api返回xml数据转换成数组，全要大写
		if ($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS') {
			$time = time();
			$tmp = array(); //临时数组用于签名
			$tmp['appId'] = $appid;
			$tmp['nonceStr'] = $nonce_str;
			$tmp['package'] = 'prepay_id=' . $array['PREPAY_ID'];
			$tmp['signType'] = 'MD5';
			$tmp['timeStamp'] = "$time";

			$data['state'] = 1;
			$data['timeStamp'] = "$time"; //时间戳
			$data['nonceStr'] = $nonce_str; //随机字符串
			$data['signType'] = 'MD5'; //签名算法，暂支持 MD5
			$data['package'] = 'prepay_id=' . $array['PREPAY_ID']; //统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
			$data['paySign'] = $this->MakeSign($tmp, $KEY); //签名,具体签名方案参见微信公众号支付帮助文档;
			$data['out_trade_no'] = $out_trade_no;
			$data['appId'] = $appid;
		} else {
			$data['state'] = 0;
			$data['text'] = "错误";
			$data['RETURN_CODE'] = $array['RETURN_CODE'];
			$data['RETURN_MSG'] = $array['RETURN_MSG'];
			$this->_err_ret($array['RETURN_MSG']);
		}
		$this->_suc_ret($data);
	}
	/**
	 * ajax_pay_postal_card 包邮卡购买支付
	 */
	public function ajax_pay_postal_card() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $id,
			'deleted' => 0,
		);
		$info = M('postal_card')->where($map)->find();
		if (!$info) {
			$this->_err_ret('包邮卡不存在');
		}
		$total_fee = $info['money'] * 100;
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user['id'],
			'postal_card_id' => $id,
			'money' => $info['money'],
			'days' => $info['days'],
			'status' => 0,
		);
		$res = M('postal_order')->add($data);
		if (!$res) {
			$this->_err_ret('订单生成失败');
		}
		$appid = $this->appid; //如果是公众号 就是公众号的appid;小程序就是小程序的appid
		$body = '支付vip订单';
		$mch_id = $this->mch_id;
		$KEY = $this->key;

		$nonce_str = $this->randomkeys(32); //随机字符串
		$notify_url = 'https://fssw.bichonfrise.cn/index.php/Wechat/App/postal_order_notify_url'; //支付完成回调地址url,不能带参数
		$out_trade_no = 'postal-' . time() . '-' . $res; //商户订单号
		$spbill_create_ip = $_SERVER['SERVER_ADDR'];
		$trade_type = 'JSAPI'; //交易类型 默认JSAPI
		//这里是按照顺序的 因为下面的签名是按照(字典序)顺序 排序错误 肯定出错
		$post['appid'] = $appid;
		$post['body'] = $body;
		$post['mch_id'] = $mch_id;
		$post['nonce_str'] = $nonce_str; //随机字符串
		$post['notify_url'] = $notify_url;
		$post['openid'] = $user['openid'];
		$post['out_trade_no'] = $out_trade_no;
		$post['spbill_create_ip'] = $spbill_create_ip; //服务器终端的ip
		$post['total_fee'] = $total_fee; //总金额 最低为一分钱 必须是整数
		$post['trade_type'] = $trade_type;
		$sign = $this->MakeSign($post, $KEY); //签名

		$post_xml = '<xml>
               <appid>' . $appid . '</appid>
               <body>' . $body . '</body>
               <mch_id>' . $mch_id . '</mch_id>
               <nonce_str>' . $nonce_str . '</nonce_str>
               <notify_url>' . $notify_url . '</notify_url>
               <openid>' . $user['openid'] . '</openid>
               <out_trade_no>' . $out_trade_no . '</out_trade_no>
               <spbill_create_ip>' . $spbill_create_ip . '</spbill_create_ip>
               <total_fee>' . $total_fee . '</total_fee>
               <trade_type>' . $trade_type . '</trade_type>
               <sign>' . $sign . '</sign>
            </xml> ';
		//统一下单接口prepay_id
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$xml = $this->http_request($url, $post_xml); //POST方式请求http
		$array = $this->xml2array($xml); //将【统一下单】api返回xml数据转换成数组，全要大写
		if ($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS') {
			$time = time();
			$tmp = array(); //临时数组用于签名
			$tmp['appId'] = $appid;
			$tmp['nonceStr'] = $nonce_str;
			$tmp['package'] = 'prepay_id=' . $array['PREPAY_ID'];
			$tmp['signType'] = 'MD5';
			$tmp['timeStamp'] = "$time";

			$data['state'] = 1;
			$data['timeStamp'] = "$time"; //时间戳
			$data['nonceStr'] = $nonce_str; //随机字符串
			$data['signType'] = 'MD5'; //签名算法，暂支持 MD5
			$data['package'] = 'prepay_id=' . $array['PREPAY_ID']; //统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
			$data['paySign'] = $this->MakeSign($tmp, $KEY); //签名,具体签名方案参见微信公众号支付帮助文档;
			$data['out_trade_no'] = $out_trade_no;
			$data['appId'] = $appid;
		} else {
			$data['state'] = 0;
			$data['text'] = "错误";
			$data['RETURN_CODE'] = $array['RETURN_CODE'];
			$data['RETURN_MSG'] = $array['RETURN_MSG'];
			$this->_err_ret($array['RETURN_MSG']);
		}
		$this->_suc_ret($data);
	}
	//获取xml里面数据，转换成array
	public function xml2array($xml) {
		$p = xml_parser_create();
		xml_parse_into_struct($p, $xml, $vals, $index);
		xml_parser_free($p);
		$data = "";
		foreach ($index as $key => $value) {
			if ($key == 'xml' || $key == 'XML') {
				continue;
			}

			$tag = $vals[$value[0]]['tag'];
			$value = $vals[$value[0]]['value'];
			$data[$tag] = $value;
		}
		return $data;
	}
	//作用：产生随机字符串，不长于32位
	public function randomkeys($length = 32) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	/**
	 * 将xml转为array
	 * @param string $xml
	 * return array
	 */
	public function xml_to_array($xml) {
		if (!$xml) {
			return false;
		}
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $data;
	}
	/**
	 * 生成签名, $KEY就是支付key
	 * @return 签名
	 */
	public function MakeSign($params, $KEY) {
		//签名步骤一：按字典序排序数组参数
		ksort($params);
		$string = $this->ToUrlParams($params); //参数进行拼接key=value&k=v
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=" . $KEY;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}
	/**
	 * 将参数拼接为url: key=value&key=value
	 * @param $params
	 * @return string
	 */
	public function ToUrlParams($params) {
		$string = '';
		if (!empty($params)) {
			$array = array();
			foreach ($params as $key => $value) {
				$array[] = $key . '=' . $value;
			}
			$string = implode("&", $array);
		}
		return $string;
	}
	/**
	 * 调用接口， $data是数组参数
	 * @return 签名
	 */
	public function http_request($url, $data = null, $headers = array()) {
		$curl = curl_init();
		if (count($headers) >= 1) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		}
		curl_setopt($curl, CURLOPT_URL, $url);

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

		if (!empty($data)) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}

	/*
		     * 给微信发送确认订单金额和签名正确，SUCCESS信息 -xzz0521
	*/
	public function return_success() {
		$return['return_code'] = 'SUCCESS';
		$return['return_msg'] = 'OK';
		$xml_post = '<xml>
                    <return_code>' . $return['return_code'] . '</return_code>
                    <return_msg>' . $return['return_msg'] . '</return_msg>
                    </xml>';
		echo $xml_post;exit;
	}
	/**
	 * ajax_pay_grade_val 购买领养娃娃成长值
	 */
	public function ajax_pay_grade_val() {
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $map = array(
	        'deleted'=>0,
	        'user_id'=>$user['id'],
	    );
	    $user_adopt_log = M('adopt_log')->where($map)->find();
	    if(!$user_adopt_log){
	        $this->_err_ret('请先领养一只属于你的娃娃哦~');
	    }
	    $total_fee = C('GRADE_ADOPT_PAY_MONEY') * 100;
	    $data = array(
	        'add_time' => date('Y-m-d H:i:s'),
	        'deleted' => 0,
	        'user_id' => $user['id'],
	        'money' => C('GRADE_ADOPT_PAY_MONEY'),
	        'adopt_val' => 500,
	        'adopt_config_id' => $user_adopt_log['adopt_config_id'],
	        'status' => 0,
	    );
	    $res = M('adopt_order')->add($data);
	    if (!$res) {
	        $this->_err_ret('订单生成失败');
	    }
	    $appid = $this->appid; //如果是公众号 就是公众号的appid;小程序就是小程序的appid
	    $body = '成长值购买订单';
	    $mch_id = $this->mch_id;
	    $KEY = $this->key;
	    
	    $nonce_str = $this->randomkeys(32); //随机字符串
	    $notify_url = 'https://fssw.bichonfrise.cn/index.php/Wechat/App/adopt_order_notify_url'; //支付完成回调地址url,不能带参数
	    $out_trade_no = 'adopt-' . time() . '-' . $res; //商户订单号
	    $spbill_create_ip = $_SERVER['SERVER_ADDR'];
	    $trade_type = 'JSAPI'; //交易类型 默认JSAPI
	    //这里是按照顺序的 因为下面的签名是按照(字典序)顺序 排序错误 肯定出错
	    $post['appid'] = $appid;
	    $post['body'] = $body;
	    $post['mch_id'] = $mch_id;
	    $post['nonce_str'] = $nonce_str; //随机字符串
	    $post['notify_url'] = $notify_url;
	    $post['openid'] = $user['openid'];
	    $post['out_trade_no'] = $out_trade_no;
	    $post['spbill_create_ip'] = $spbill_create_ip; //服务器终端的ip
	    $post['total_fee'] = $total_fee; //总金额 最低为一分钱 必须是整数
	    $post['trade_type'] = $trade_type;
	    $sign = $this->MakeSign($post, $KEY); //签名
	    
	    $post_xml = '<xml>
               <appid>' . $appid . '</appid>
               <body>' . $body . '</body>
               <mch_id>' . $mch_id . '</mch_id>
               <nonce_str>' . $nonce_str . '</nonce_str>
               <notify_url>' . $notify_url . '</notify_url>
               <openid>' . $user['openid'] . '</openid>
               <out_trade_no>' . $out_trade_no . '</out_trade_no>
               <spbill_create_ip>' . $spbill_create_ip . '</spbill_create_ip>
               <total_fee>' . $total_fee . '</total_fee>
               <trade_type>' . $trade_type . '</trade_type>
               <sign>' . $sign . '</sign>
            </xml> ';
	    //统一下单接口prepay_id
	    $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	    $xml = $this->http_request($url, $post_xml); //POST方式请求http
	    $array = $this->xml2array($xml); //将【统一下单】api返回xml数据转换成数组，全要大写
	    if ($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS') {
	        $time = time();
	        $tmp = array(); //临时数组用于签名
	        $tmp['appId'] = $appid;
	        $tmp['nonceStr'] = $nonce_str;
	        $tmp['package'] = 'prepay_id=' . $array['PREPAY_ID'];
	        $tmp['signType'] = 'MD5';
	        $tmp['timeStamp'] = "$time";
	        
	        $data['state'] = 1;
	        $data['timeStamp'] = "$time"; //时间戳
	        $data['nonceStr'] = $nonce_str; //随机字符串
	        $data['signType'] = 'MD5'; //签名算法，暂支持 MD5
	        $data['package'] = 'prepay_id=' . $array['PREPAY_ID']; //统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
	        $data['paySign'] = $this->MakeSign($tmp, $KEY); //签名,具体签名方案参见微信公众号支付帮助文档;
	        $data['out_trade_no'] = $out_trade_no;
	        $data['appId'] = $appid;
	    } else {
	        $data['state'] = 0;
	        $data['text'] = "错误";
	        $data['RETURN_CODE'] = $array['RETURN_CODE'];
	        $data['RETURN_MSG'] = $array['RETURN_MSG'];
	        $this->_err_ret($array['RETURN_MSG']);
	    }
	    $this->_suc_ret($data);
	}
	/**
	 * ajax_pay_fruit_val 购买水果能量值
	 */
	public function ajax_pay_fruit_val() {
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $map = array(
	        'user_id'=>$user['id'],
	        'deleted'=>0,
	    );
	    $user_fruit = M('fruit_log')->where($map)->find();
	    if(!$user_fruit){
	        $this->_err_ret('请先领取水果~');
	    }
	    $total_fee = C('GRADE_FRUIT_PAY_MONEY') * 100;
	    $data = array(
	        'add_time' => date('Y-m-d H:i:s'),
	        'deleted' => 0,
	        'user_id' => $user['id'],
	        'money' => C('GRADE_FRUIT_PAY_MONEY'),
	        'fruit_val' => 500,
	        'fruit_config_id' => $user_fruit['fruit_config_id'],
	        'status' => 0,
	    );
	    $res = M('fruit_order')->add($data);
	    if (!$res) {
	        $this->_err_ret('订单生成失败');
	    }
	    $appid = $this->appid; //如果是公众号 就是公众号的appid;小程序就是小程序的appid
	    $body = '成长值购买订单';
	    $mch_id = $this->mch_id;
	    $KEY = $this->key;
	    
	    $nonce_str = $this->randomkeys(32); //随机字符串
	    $notify_url = 'https://fssw.bichonfrise.cn/index.php/Wechat/App/fruit_order_notify_url'; //支付完成回调地址url,不能带参数
	    $out_trade_no = 'fruit-' . time() . '-' . $res; //商户订单号
	    $spbill_create_ip = $_SERVER['SERVER_ADDR'];
	    $trade_type = 'JSAPI'; //交易类型 默认JSAPI
	    //这里是按照顺序的 因为下面的签名是按照(字典序)顺序 排序错误 肯定出错
	    $post['appid'] = $appid;
	    $post['body'] = $body;
	    $post['mch_id'] = $mch_id;
	    $post['nonce_str'] = $nonce_str; //随机字符串
	    $post['notify_url'] = $notify_url;
	    $post['openid'] = $user['openid'];
	    $post['out_trade_no'] = $out_trade_no;
	    $post['spbill_create_ip'] = $spbill_create_ip; //服务器终端的ip
	    $post['total_fee'] = $total_fee; //总金额 最低为一分钱 必须是整数
	    $post['trade_type'] = $trade_type;
	    $sign = $this->MakeSign($post, $KEY); //签名
	    
	    $post_xml = '<xml>
               <appid>' . $appid . '</appid>
               <body>' . $body . '</body>
               <mch_id>' . $mch_id . '</mch_id>
               <nonce_str>' . $nonce_str . '</nonce_str>
               <notify_url>' . $notify_url . '</notify_url>
               <openid>' . $user['openid'] . '</openid>
               <out_trade_no>' . $out_trade_no . '</out_trade_no>
               <spbill_create_ip>' . $spbill_create_ip . '</spbill_create_ip>
               <total_fee>' . $total_fee . '</total_fee>
               <trade_type>' . $trade_type . '</trade_type>
               <sign>' . $sign . '</sign>
            </xml> ';
	    //统一下单接口prepay_id
	    $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	    $xml = $this->http_request($url, $post_xml); //POST方式请求http
	    $array = $this->xml2array($xml); //将【统一下单】api返回xml数据转换成数组，全要大写
	    if ($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS') {
	        $time = time();
	        $tmp = array(); //临时数组用于签名
	        $tmp['appId'] = $appid;
	        $tmp['nonceStr'] = $nonce_str;
	        $tmp['package'] = 'prepay_id=' . $array['PREPAY_ID'];
	        $tmp['signType'] = 'MD5';
	        $tmp['timeStamp'] = "$time";
	        
	        $data['state'] = 1;
	        $data['timeStamp'] = "$time"; //时间戳
	        $data['nonceStr'] = $nonce_str; //随机字符串
	        $data['signType'] = 'MD5'; //签名算法，暂支持 MD5
	        $data['package'] = 'prepay_id=' . $array['PREPAY_ID']; //统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
	        $data['paySign'] = $this->MakeSign($tmp, $KEY); //签名,具体签名方案参见微信公众号支付帮助文档;
	        $data['out_trade_no'] = $out_trade_no;
	        $data['appId'] = $appid;
	    } else {
	        $data['state'] = 0;
	        $data['text'] = "错误";
	        $data['RETURN_CODE'] = $array['RETURN_CODE'];
	        $data['RETURN_MSG'] = $array['RETURN_MSG'];
	        $this->_err_ret($array['RETURN_MSG']);
	    }
	    $this->_suc_ret($data);
	}
}