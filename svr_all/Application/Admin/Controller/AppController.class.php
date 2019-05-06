<?php
namespace Admin\Controller;
use Think\Controller;

class AppController extends Controller {

	public function _suc_ret($data = null) {
		$res = array(
			'code' => 0,
			'msg' => 'success',
		);
		if ($data != null) {
			$res['data'] = $data;
		}
		$this->ajaxReturn($res);

	}
	public function _tb_suc_ret($data = null, $count = 0) {
		$res = array(
			'code' => 0,
			'msg' => 'success',
		);
		if ($data != null) {
			$res['data'] = $data;
			$res['count'] = $count;
		}
		$this->ajaxReturn($res);

	}
	public function _err_ret($msg = 'sys err!') {
		$res = array(
			'code' => -6000,
			'msg' => $msg,
		);
		$this->ajaxReturn($res);
	}
	/**
	 * [config_page 设置分页样式]
	 * @return [type] [description]
	 */
	public function config_page($page) {
		$page->setConfig('header', '<a class="btn btn-default" href="javascript:;">共 %TOTAL_ROW% 条记录</a>');
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$page->setConfig('last', '最后一页');
		$page->setConfig('first', '第一页');
		$page->setConfig('theme', '%HEADER%%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%');
		$page->lastSuffix = false;
	}
	/**
	 * [getRecommendsPayByDate 查询佣金]
	 * @param  [type] $status [description]
	 * @param  [type] $days   [description]
	 * @return [type]         [description]
	 */
	public function getRecommendsPayByDate($status, $days, $merchant_id) {
		$data = array();
		foreach ($days as $key => $value) {
			$recommendModel = D('recommend');
			$map = array(
				'add_time' => array('between', $value . " 00:00:00," . $value . " 23:59:59"),
				'status' => $status,
				'merchant_id' => $merchant_id,
			);
			$pay = 0;
			$recommends = $recommendModel->relation(true)->where($map)->select();
			foreach ($recommends as $key_r => $value_r) {
				$pay += ($value_r['good']['pay_price'] / 100) * $value_r['chengjiao_price'];
				$pay += ($value_r['good']['pay_price_sub'] / 100) * $value_r['chengjiao_price'];
			}
			$tmp_data = array(
				$value,
				$pay,
			);
			array_push($data, $tmp_data);
		}
		return $data;

	}
	/**
	 * [getRecommendsCountByDate 查询推荐数量]
	 * @param  [type] $status [description]
	 * @param  [type] $days   [description]
	 * @return [type]         [description]
	 */
	public function getRecommendsCountByDate($status, $days) {
		$admin = session('admin');
		if (!$admin) {
			exit;
		}
		$data = array();
		foreach ($days as $key => $value) {
			$recommendModel = D('recommend');
			$map = array(
				'add_time' => array('between', $value . " 00:00:00," . $value . " 23:59:59"),
				'status' => $status,
				'merchant_id' => $admin['merchant_id'],
			);
			$count = $recommendModel->where($map)->count();
			$tmp_data = array(
				$value,
				$count,
			);
			array_push($data, $tmp_data);
		}
		return $data;
	}
	/**
	 * [insertAdminLog 插入操作记录]
	 * @param  [type] $content  [description]
	 * @return [type]           [description]
	 */
	public function insertAdminLog($content) {
		$admin = session('admin');
		$data = array(
			'admin_id' => $admin['id'],
			'add_time' => date('Y-m-d H:i:s', time()),
			'content' => $admin['name'] . "(" . $admin['tel'] . ")" . $content,
		);
		$adminLogModel = D("admin_log");
		$res = $adminLogModel->add($data);
		return $res;
	}
	/**
	 * [upload_image 上传图片]
	 * @return [type] [description]
	 */
	public function upload_image() {
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->autoSub = true;
		$upload->subType = 'date';
		$upload->dateFormat = 'Ym';
		$path = 'images/';
		$upload->savePath = $path;
		$info = $upload->uploadOne($_FILES['file']);
		if (!$info) {
			$this->_err_ret($upload->getError());
		} else {
			//$image = new \Think\Image();
			// 在图片右下角添加水印文字 ThinkPHP 并保存为new.jpg
			//$image->open('Uploads/' . $info['savepath'] . $info['savename'])->text('悦美经纪人', './font/pingfang.ttf', 10, '#FFFFFF', \Think\Image::IMAGE_WATER_SOUTHEAST, -20)->save('Uploads/' . $info['savepath'] . $info['savename']);
			$data = array(
				'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/Uploads/' . $info['savepath'] . $info['savename'],
			);
			$this->_suc_ret($data);
		}
	}
	/**
	 * [get_file_from_dir 从目录查询文件]
	 * @param  [type] $path [description]
	 * @return [type]       [description]
	 */
	public function get_file_from_dir($path = './', $order = 0) {
		$file_path = opendir($path);
		while ($file_name = readdir($file_path)) {
			if ($file_name != ".."
				&& $file_name != ".") {
				$file_arr[] = $file_name;
			}
		}
		$order = 0 ? sort($file_arr) : rsort($file_arr);
		return $file_arr;
	}

	public function httpGet($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);
		$res = curl_exec($curl);
		curl_close($curl);

		return $res;
	}
	public function getAccessToken($appId, $appSecret) {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
		$res = json_decode($this->httpGet($url));
		$access_token = $res->access_token;
		return $access_token;
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
	/**
	 * [getUserById 通过ID查询用户]
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function getUserById($id) {
		$userModel = D('user');
		$map = array(
			'id' => $id,
		);
		$user = $userModel->where($map)->find();
		return $user;
	}
	public function send_user_package_inner_msg($user, $realname, $tel, $delivery_company, $delivery_sn) {
		$openId = $user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("PACKAGE_SEND_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "尊敬的" . $user['nickname'] . "您好，您的娃娃已送出，请保持手机畅通，以便快递及时联系您！",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => $realname,
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => $tel,
					"color" => "#173177",
				),
				"keyword3" => array(
					"value" => $delivery_company,
					"color" => "#173177",
				),
				"keyword4" => array(
					"value" => $delivery_sn,
					"color" => "#173177",
				),
				"keyword5" => array(
					"value" => "无",
				),
				"remark" => array(
					"value" => "史上最好抓的娃娃，娃娃质量超好！四次抓不到就送！",
					"color" => "#e2a114",
				),
			),
		);
		$appId = "";
		$appSecret = "";

		$res = $this->getAccessToken($appId, $appSecret);
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $res;
		$data = json_encode($postData);
		$res = $this->http_request($url, $data);
		$return = json_decode($res, true);
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $admin_user['id'],
			'template_id' => C("PACKAGE_SEND_TMPL_ID"),
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
	// 连续三次抓不到娃娃赠送一个的通知
	public function send_user_pay_user_gift_msg($openid, $game_name, $good_name) {
		$map = array(
			'openid' => $openid,
		);
		$userModel = D('user');
		$user = $userModel->where($map)->find();

		$postData = array(
			"touser" => $openid,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/bag.html",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "尊敬的" . $user['nickname'] . "，您抓取" . $game_name . "场的娃娃，客服为您免费补送一个" . $good_name . "，请查收！",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => "免费" . $game_name . "场的" . $good_name . "1个!",
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => C("PAY_USER_SAVE_DAYS") . "天",
				),
				"remark" => array(
					"value" => $remark,
					"color" => "#e2a114",
				),
			),
		);
		$appId = "";
		$appSecret = "";

		$res = $this->getAccessToken($appId, $appSecret);
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $res;
		$data = json_encode($postData);
		$res = $this->http_request($url, $data);
		$res = json_decode($res, true);

		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user['id'],
			'template_id' => C("ACTIVITY_COIN_GET_TMPL_ID"),
			'content' => $postData['data']['first']['value'],
			'callback_msg' => json_encode($res),
		);
		if ($res['errcode'] == 0 && !empty($res['msgid'])) {
			//发送成功
			$data['status'] = 1;
		} else {
			$data['status'] = 0;
		}
		$temp = M('wechat_msg_log')->add($data);
		return json_encode($res);
	}
	public function send_user_free_coin_msg($openid, $notice, $amount, $url, $remark) {
		$postData = array(
			"touser" => $openid,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => $url,
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => $notice,
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => $amount,
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => "无限期",
				),
				"remark" => array(
					"value" => $remark,
					"color" => "#e2a114",
				),
			),
		);
		$appId = "";
		$appSecret = "";

		$res = $this->getAccessToken($appId, $appSecret);
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $res;
		$data = json_encode($postData);
		$res = $this->http_request($url, $data);
		$res = json_decode($res, true);
		$map = array(
			'openid' => $openid,
		);
		$userModel = D('user');
		$user = $userModel->where($map)->find();

		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user['id'],
			'template_id' => C("ACTIVITY_COIN_GET_TMPL_ID"),
			'content' => $postData['data']['first']['value'],
			'callback_msg' => json_encode($res),
		);
		if ($res['errcode'] == 0 && !empty($res['msgid'])) {
			//发送成功
			$data['status'] = 1;
		} else {
			$data['status'] = 0;
		}
		$temp = M('wechat_msg_log')->add($data);
		return json_encode($res);
	}
	public function send_new_user_gonglve_msg($openid) {
		$postData = array(
			"touser" => $openid,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://mp.weixin.qq.com/s?__biz=MzU5OTY1MTYyNA==&mid=2247483677&idx=1&sn=b84feb3efbb403034ce2e27c378e55cf&chksm=feb0e55ac9c76c4cdbf0394b153876ee6c46fe9d46c9e8a6aa533cc3b4a560f0995edfa60f28&token=914258547&lang=zh_CN#rd",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您收到了一份抓娃娃必中攻略，点击立即查看！",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => "抓娃娃必中攻略1份",
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => "无限期",
				),
				"remark" => array(
					"value" => "史上最好抓的娃娃，娃娃质量超好！四次抓不到就送！记得多多关注公众号获取最新活动哦~",
					"color" => "#e2a114",
				),
			),
		);
		$appId = "";
		$appSecret = "";

		$res = $this->getAccessToken($appId, $appSecret);
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $res;
		$data = json_encode($postData);
		$res = $this->http_request($url, $data);

		$return = json_decode($res, true);
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $admin_user['id'],
			'template_id' => C("ACTIVITY_COIN_GET_TMPL_ID"),
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
		$appSecret = "";

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

	//赠送糖豆通知用户会员到账
	public function send_give_user_coin_msg($user_info, $coin_num) {
		$openId = $user_info['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("PAY_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，尊敬的" . $user_info['nickname'] . "，哐糖抓抓赠送您" . $coin_num . "糖豆，开启愉快的抓娃娃之旅吧~",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => $user_info['id'],
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => $coin_num . '糖豆',
				),
				"keyword3" => array(
					"value" => "0元",
				),
				"remark" => array(
					"value" => '史上最好抓的娃娃，娃娃质量超好！记得多多关注公众号获取最新活动哦~',
					"color" => "#e2a114",
				),
			),
		);
		$appId = "";
		$appSecret = "";

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