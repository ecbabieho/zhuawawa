<?php
namespace Wechat\Controller;
use Think\Controller;

class AgentController extends BaseController {
	private $appid = '';
	private $mch_id = '';
	private $key = '';
	/**
	 * [index 首页]
	 * @return [type] [description]
	 */
	public function index() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();
		$this->assign('user', $user);
		$this->show();
	}
	/**
	 * [users 邀请的好友]
	 * @return [type] [description]
	 */
	public function users() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'p_id' => $user['id'],
			'deleted' => 0,
		);
		$list = M('user')->where($map)->select();
		$this->assign('users', $list);
		$this->show();
	}
	/**
	 * [prizes 好友娃娃]
	 * @return [type] [description]
	 */
	public function prizes() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$lottery_type_id = I('lottery_type_id');
		$map = array(
			'maid_log.p_id' => $user['id'],
			'maid_log.type' => 1,
			'lottery_record.deleted' => 0,
		);
		if ($lottery_type_id != '' && $lottery_type_id != 0) {
			$map['lottery_record.lottery_type_id'] = $lottery_type_id;
		}
		$list = M('maid_log')->field('`lottery_record`.*,`maid_log`.money')->join('lottery_record ON `maid_log`.lottery_record_id = `lottery_record`.id')->where($map)->order('`maid_log`.add_time desc')->select();
		foreach ($list as $key => $val) {
			$map = array(
				'id' => $val['user_id'],
			);
			$list[$key]['user_info'] = M('user')->where($map)->find();
			$map = array(
				'id' => $val['lottery_good_id'],
			);
			$list[$key]['lottery_good'] = M('lottery_good')->where($map)->find();
			$map = array(
				'id' => $val['lottery_type_id'],
			);
			$list[$key]['lottery_type'] = M('lottery_type')->where($map)->find();
		}
		$map = array(
			'deleted' => 0,
		);
		$lottery_types = M('lottery_type')->where($map)->select();
		$this->assign('lottery_types', $lottery_types);
		$this->assign('lottery_records', $list);
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$this->assign('user', $user);
		$this->show();
	}/**
	 * [charge 好友充值]
	 * @return [type] [description]
	 */
	public function charge() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'p_id' => $user['id'],
			'deleted' => 0,
			'vip_order_id' => array('neq', 0),
		);
		$list = M('maid_log')->where($map)->select();
		foreach ($list as $key => $val) {
			$map = array(
				'id' => $val['user_id'],
			);
			$list[$key]['user_info'] = M('user')->where($map)->find();
		}
		$this->assign('maid_logs', $list);
		$types = array(
			array(
				'type' => 2,
				'name' => "购买会员",
			),
		);
		$this->assign('types', $types);
		$this->show();
	}
	/**
	 * [tixian 用户提现页面]
	 * @return [type] [description]
	 */
	public function tixian() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();
		$this->assign('user', $user);

		$this->show();
	}
	/**
	 * [ajax_apply_tixian 申请提现接口]
	 * @return [type] [description]
	 */
	public function ajax_apply_tixian() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$money = I('money');
		$tel = I('tel');
		if ($money == '' || $tel == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $user['id'],
			'deleted' => 0,
		);
		$user_info = M('user')->where($map)->find();
		if (!$user_info) {
			exit;
		}
		if ($money > $user_info['yu_e']) {
			$this->_err_ret('提现金额大于当前余额，请重新填写~');
		}
		if ($money < C('WITHDRAW_NUM')) {
			$this->_err_ret('最低提现金额是' . C('WITHDRAW_NUM') . '元哦~');
		}
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user_info['id'],
			'tel' => $tel,
			'money' => $money,
			'status' => 0,
			'ago_money' => $user_info['yu_e'],
			'after_money' => $user_info['yu_e'] - $money,
		);
		$res = M('withdraw')->add($data);
		if (!$res) {
			$this->_err_ret('提现失败，请稍后再试~');
		}
		$data = array(
			'id' => $user_info['id'],
			'yu_e' => $user_info['yu_e'] - $money,
		);
		$res = M('user')->save($data);
		$this->_suc_ret();
	}

	/**
	 * withdraw_money 提现支付
	 */
	public function withdraw_money($user, $money) {
		//支付信息
		$wxchat['appid'] = $this->appid;
		$wxchat['mchid'] = $this->mch_id;
		$webdata = array(
			'mch_appid' => $wxchat['appid'], //商户账号appid
			'mchid' => $wxchat['mchid'], //商户号
			'nonce_str' => md5(time()), //随机字符串
			'partner_trade_no' => 'w-p-' . date('YmdHis'), //商户订单号，需要唯一
			'openid' => $user['openid'], //转账用户的openid
			'check_name' => 'NO_CHECK', //OPTION_CHECK不强制校验真实姓名, FORCE_CHECK：强制 NO_CHECK：
			'amount' => $money * 100, //付款金额单位为分
			'desc' => '用户提现余额', //企业付款描述信息
			'spbill_create_ip' => request()->ip(), //获取IP
		);
		foreach ($webdata as $k => $v) {
			$tarr[] = $k . '=' . $v;
		}
		sort($tarr);
		$sign = implode($tarr, '&');
		$sign .= '&key=' . $this->key;
		$webdata['sign'] = strtoupper(md5($sign));
		$wget = $this->ArrToXml($webdata); //数组转XML
		$pay_url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; //api地址
		$res = $this->postData($pay_url, $wget); //发送数据
		if (!$res) {
			return array('status' => 1, 'msg' => "Can't connect the server");
		}
		$content = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
		if (strval($content->return_code) == 'FAIL') {
			return array('status' => 1, 'msg' => strval($content->return_msg));
		}
		if (strval($content->result_code) == 'FAIL') {
			return array('status' => 1, 'msg' => strval($content->err_code), ':' . strval($content->err_code_des));
		}
		/*$rdata = array(
			        'mch_appid'        => strval($content->mch_appid),
			        'mchid'            => strval($content->mchid),
			        'device_info'      => strval($content->device_info),
			        'nonce_str'        => strval($content->nonce_str),
			        'result_code'      => strval($content->result_code),
			        'partner_trade_no' => strval($content->partner_trade_no),
			        'payment_no'       => strval($content->payment_no),
			        'payment_time'     => strval($content->payment_time),
		*/
		return $rdata;

	}
	//数组转XML
	function ArrToXml($arr) {
		if (!is_array($arr) || count($arr) == 0) {
			return '';
		}

		$xml = "<xml>";
		foreach ($arr as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			} else {
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}
		$xml .= "</xml>";
		return $xml;
	}
	//发送数据
	function postData($url, $postfields) {
		$ch = curl_init();
		$params[CURLOPT_URL] = $url; //请求url地址
		$params[CURLOPT_HEADER] = false; //是否返回响应头信息
		$params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
		$params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
		$params[CURLOPT_POST] = true;
		$params[CURLOPT_POSTFIELDS] = $postfields;
		$params[CURLOPT_SSL_VERIFYPEER] = false;
		$params[CURLOPT_SSL_VERIFYHOST] = false;
		//以下是证书相关代码
		$params[CURLOPT_SSLCERTTYPE] = 'PEM';
		$params[CURLOPT_SSLCERT] = getcwd() . '/plugins/payment/weixin/cert/apiclient_cert.pem'; //绝对路径
		$params[CURLOPT_SSLKEYTYPE] = 'PEM';
		$params[CURLOPT_SSLKEY] = getcwd() . '/plugins/payment/weixin/cert/apiclient_key.pem'; //绝对路径
		curl_setopt_array($ch, $params); //传入curl参数
		$content = curl_exec($ch); //执行
		curl_close($ch); //关闭连接
		return $content;
	}

	public function admin_send_vip() {
		$user = session('user');
		if ($user['openid'] != "oKFOO1hc7P90MGMU39zaIgYmJh0k") {
			exit;
		}
		// 获取会员等级
		$vipGradeModel = D('vip_grade');
		$vip_grades = $vipGradeModel->select();
		$this->assign("vip_grades", $vip_grades);
		$this->show();
	}
	public function ajax_find_user_by_name_or_id() {
		$user = session('user');
		if ($user['openid'] != "oKFOO1hc7P90MGMU39zaIgYmJh0k") {
			exit;
		}
		$key = I('key');
		if ($key == "") {
			exit;
		}
		$map = array(
			'deleted' => 0,
		);
		$userModel = D('user');
		$where['nickname'] = array('like', '%' . $key . '%');
		$where['id'] = $key;
		$where['_logic'] = 'OR';
		$map['_complex'] = $where;
		$user = $userModel->where($map)->find();
		if (!$user) {
			if ($user === null) {
				$this->_suc_ret();
			}
			$this->_err_ret();
		}
		$this->_suc_ret($user);

	}
	public function ajax_admin_send_vip() {
		$user = session('user');
		if ($user['openid'] != "oKFOO1hc7P90MGMU39zaIgYmJh0k") {
			exit;
		}
		$id = I('id');
		$level = I('level');
		$days = I('days');
		if ($id == ""
			|| $level == ""
			|| $days == "") {
			exit;
		}
		$map = array(
			'id' => $id,
			'deleted' => 0,
		);
		$userModel = D('user');
		$user = $userModel->where($map)->find();
		if (!$user) {
			$this->_err_ret("没有此用户");
		}
		if ($level < $user['level']) {
			$this->_err_ret('赠送等级不能小于当前用户等级');
		}
		$end_time = strtotime($user['over_time']) + $days * 24 * 3600;
		$data = array(
			'level' => $level,
			'over_time' => date("Y-m-d H:i:s", $end_time),
		);
		$res = $userModel->where($map)->save($data);
		if (!$res) {
			$this->_err_ret("赠送失败");
		}
		$map = array(
			'level' => $level,
		);
		$level_info = M('vip_grade')->where($map)->find();
		//给用户发送消息
		$this->send_give_user_vip_msg($user, $level_info, $days, date("Y-m-d H:i:s", $end_time));
		$this->_suc_ret();
	}
	//赠送会员通知用户会员到账
	public function send_give_user_vip_msg($user_info, $level_info, $day_num, $over_time) {
		$openId = $user_info['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("PAY_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，尊敬的" . $user_info['nickname'] . "，哐糖抓抓赠送您" . $level_info['name'] . $day_num . "天，开启愉快的抓娃娃之旅吧~",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => $user_info['id'],
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => $day_num . '天' . $level_info['name'],
				),
				"keyword3" => array(
					"value" => "0元",
				),
				"remark" => array(
					"value" => "您的" . $level_info['name'] . "到期时间为" . substr($over_time, 0, 10) . '，会员可享受每月不够' . C('FREE_POST_WAWA_COUNT') . '个娃娃免费包邮机会，记得及时使用哦~',
					"color" => "#e2a114",
				),
			),
		);
		$appId = "";
		$appSecret = "3d917eabbf544b099d23bdd7190c108e";

		$res = $this->getAccessToken($appId, $appSecret);
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $res;
		$data = json_encode($postData);
		$res = $this->http_request($url, $data);
		$return = json_decode($res, true);
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user_info['id'],
			'template_id' => C("PAY_TMPL_ID"),
			'content' => $postData['data']['first']['value'],
			'callback_msg' => $res,
		);
		if ($return['errcode'] == 0 && !empty($return['msgid'])) {
			//发送成功
			$data['status'] = 1;
		} else {
			$data['status'] = 0;
		}
		$temp = M('wechat_msg_log')->add($data);
		return $res;

	}
}