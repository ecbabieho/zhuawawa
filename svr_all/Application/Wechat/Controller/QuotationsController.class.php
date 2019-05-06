<?php
namespace Wechat\Controller;
use Think\Controller;

class QuotationsController extends AppController {
	private $appid = 'wx51e865a23af10608';
	private $secret = '30954be6057cc412c25f9d76d01e6b0f';
	private $grant_type = 'authorization_code';
	private $url = 'https://api.weixin.qq.com/sns/jscode2session';
	/**
	 * @api {post} Wechat/Quotations/wxLogin 1.1 微信登录
	 * @apiVersion 1.0.0
	 * @apiName wxLogin
	 * @apiGroup 3-Login
	 * @apiDescription 3.1 微信登录
	 * @apiParam {string} openid openid
	 * @apiParam {string} unionid unionid
	 * @apiParam {string} nickname 昵称
	 * @apiParam {string} sex 性别
	 * @apiParam {string} headimgurl 头像
	 * @apiParam {string} province 省份
	 * @apiParam {string} city 市
	 * @apiParam {string} country 区
	 * @apiParam {string} privilege
	 * @apiSuccess {Number}   code           		返回码
	 * @apiSuccess {string}   message  				返回信息
	 * @apiSuccess {json}     result   				结果
	 * @apiSuccessExample {json} 成功示例:
	 * {
	"code": 0,
	"msg": "请求成功",
	"data": {}
	}
	 */
	public function wxLogin() {
		$openid = I('openid');
		$unionid = I('unionid');
		$nickname = I('nickname');
		$sex = I('sex');
		$headimgurl = I('headimgurl');
		$province = I('province');
		$city = I('city');
		$country = I('country');
		$privilege = I('privilege');
		if ($openid == '') {
			$this->_err_ret('参数不完整');
		}
		if (!$unionid) {
			$unionid = '';
		}
		$map = array(
			'openid' => $openid,
		);
		$user = M('user')->where($map)->find();
		if ($user) {
			// 更新下用户信息
			$data = array(
				'id' => $user['id'],
				'openid' => $openid,
				'unionid' => $unionid,
				'nickname' => $nickname,
				'sex' => $sex,
				'headimgurl' => $headimgurl,
				'province' => $province,
				'city' => $city,
				'country' => $country,
				'privilege' => $privilege,
				'login_source' => 1,
			);
			$res = M('user')->save($data);
			if ($res === false) {
				$this->_suc_ret($data);
			}
		} else {
			$data = array(
				'add_time' => date('Y-m-d H:i:s', time()),
				'openid' => $openid,
				'unionid' => $unionid,
				'nickname' => $nickname,
				'sex' => $sex,
				'headimgurl' => $headimgurl,
				'province' => $province,
				'city' => $city,
				'country' => $country,
				'privilege' => $privilege,
				'login_source' => 1,
			);
			$res = M('user')->add($data);
		}
		$map = array(
			'openid' => $openid,
		);
		$user = M('user')->where($map)->find();
		$data = array(
			'user' => $user,
		);
		$this->_suc_ret($data);
	}
	/**
	 * @api {post} Wechat/Quotations/login_code 1.2 小程序code请求
	 * @apiVersion 1.0.0
	 * @apiName login_code
	 * @apiGroup 1-Quotations
	 * @apiDescription 1.2 小程序code请求
	 * @apiParam {string} code code
	 * @apiSuccess {Number}   code           		返回码
	 * @apiSuccess {string}   message  				返回信息
	 * @apiSuccess {json}     result   				结果
	 * @apiSuccessExample {json} 成功示例:
	 * {
	"code": 0,
	"msg": "请求成功",
	"data": {}
	}
	 */
	public function login_code() {
		$code = I("code");
		if ($code == '') {
			$this->_err_ret('参数不完整');
		}
		//获取sessionKey
		$params = array(
			'appid' => $this->appid,
			'secret' => $this->secret,
			'js_code' => $code,
			'grant_type' => $this->grant_type,
		);
		$res = $this->makeRequest($this->url, $params);
		$temp = json_decode($res['result'], true);
		$map = array(
			'openid' => $temp['openid'],
		);
		$user = M('user')->where($map)->find();
		if (!$user) {
			$data = array(
				'add_time' => date('Y-m-d H:i:s', time()),
				'deleted' => 0,
				'openid' => $temp['openid'],
				'login_source' => 1,
			);
			$res = M('user')->add($data);
			$temp['id'] = $res;
		} else {
			$temp['id'] = $user['id'];
		}
		$data = array(
			'data' => $temp,
		);
		$this->_suc_ret($data);
	}
	/**
	 * @api {post} Wechat/Quotations/quotations_list 2.1
	 * @apiVersion 1.0.0
	 * @apiName quotations_list
	 * @apiGroup 2-Quotations
	 * @apiDescription 2.1 美妙语录列表页
	 * @apiParam {string} page_no 页数 （默认为1）
	 * @apiParam {string} page_num 每页显示条数 （默认为10）
	 * @apiSuccess {Number}   code           		返回码
	 * @apiSuccess {string}   message  				返回信息
	 * @apiSuccess {json}     result   				结果
	 * @apiSuccessExample {json} 成功示例:
	 * {
	"code": 0,
	"msg": "请求成功",
	"data": {}
	}
	 */
	public function quotations_list() {
		$page_no = I('page_no');
		$page_num = I('page_num');
		if ($page_no == '') {
			$page_no = 1;
		}
		if ($page_num == '') {
			$page_num = 10;
		}
		$map = array(
			'deleted' => 0,
		);
		$record_start = ($page_no - 1) * $page_num;
		$quotations_list = M('quotations')->where($map)->order('add_time desc')->limit($record_start, $page_num)->select();
		$banner_list = M('quotations')->where($map)->order('add_time desc')->limit(3)->select();
		$banners = array();
		foreach ($banner_list as $key => $val) {
			$banners[] = $val['cover_image'];
		}
		$data = array(
			'quotations' => $quotations_list,
			'banners' => $banners,
		);
		$this->_suc_ret($data);
	}

	/**
	 * @api {post} Wechat/Quotations/quotations_info 2.2 美妙语录详情页
	 * @apiVersion 1.0.0
	 * @apiName quotations_info
	 * @apiGroup 2-Quotations
	 * @apiDescription 2.2 美妙语录详情页
	 * @apiParam {string} id 语录ID
	 * @apiParam {string} user_id 用户ID
	 * @apiSuccess {Number}   code           		返回码
	 * @apiSuccess {string}   message  				返回信息
	 * @apiSuccess {json}     result   				结果
	 * @apiSuccessExample {json} 成功示例:
	 * {
	"code": 0,
	"msg": "请求成功",
	"data": {}
	}
	 */
	public function quotations_info() {
		$id = I('id');
		$user_id = I('user_id');
		if ($id == '' || $user_id == '') {
			$this->_err_ret('参数不完整');
			exit();
		}
		$map = array(
			'id' => $id,
		);
		$quotations = M('quotations')->where($map)->find();
		$data = array(
			'user_id' => $user_id,
			'quotations_id' => $id,
		);
		$res = M('quotations_like')->where($data)->find();
		if (!$res) {
			$self_like = 0;
		} else {
			$self_like = 1;
		}
		$temp_map = array(
			'quotations_id' => $id,
		);
		$count = M('quotations_like')->where($temp_map)->count();
		if (!$count) {
			$count = 0;
		}
		$data = array(
			'quotations' => $quotations, //语录详情
			'count' => $count, //点赞数量
			'self_like' => $self_like, //当前用户是否点赞，1：点了   0：没点
		);
		$this->_suc_ret($data);
	}
	/**
	 * @api {post} Wechat/Quotations/quotations_like 2.3 美妙语录点赞
	 * @apiVersion 1.0.0
	 * @apiName quotations_info
	 * @apiGroup 2-Quotations
	 * @apiDescription 2.3 美妙语录点赞
	 * @apiParam {string} id 语录ID
	 * @apiParam {string} user_id 用户ID
	 * @apiSuccess {Number}   code           		返回码
	 * @apiSuccess {string}   message  				返回信息
	 * @apiSuccess {json}     result   				结果
	 * @apiSuccessExample {json} 成功示例:
	 * {
	"code": 0,
	"msg": "请求成功",
	"data": {}
	}
	 */
	public function quotations_like() {
		$id = I('id');
		$user_id = I('user_id');
		if ($id == '' || $user_id == '') {
			$this->_err_ret('参数不完整');
			exit();
		}
		$map = array(
			'id' => $id,
		);
		$quotations = M('quotations')->where($map)->find();
		if (!$quotations) {
			$this->_err_ret('语录不存在');
		}
		$map = array(
			'user_id' => $user_id,
			'quotations_id' => $id,
		);
		$quotations_like = M('quotations_like')->where($map)->find();
		if (!$quotations_like) {
			$data = array(
				'user_id' => $user_id,
				'quotations_id' => $id,
				'add_time' => date('Y-m-d H:i:s'),
				'deleted' => 0,
			);
			$res = M('quotations_like')->add($data);
			$temp_map = array(
				'quotations_id' => $id,
			);
			$count = M('quotations_like')->where($temp_map)->count();
			if (!$count) {
				$count = 0;
			}
			$return = array(
				'msg' => '点赞成功',
				'type' => 1,
				'count' => $count,
			);
		} else {
			$res = M('quotations_like')->where($map)->delete();
			$temp_map = array(
				'quotations_id' => $id,
			);
			$count = M('quotations_like')->where($temp_map)->count();
			if (!$count) {
				$count = 0;
			}
			$return = array(
				'msg' => '取消成功',
				'type' => 0,
				'count' => $count,
			);
		}
		$this->_suc_ret($return);
	}
	/**
	 * @api {post} Wechat/Quotations/send_quotations 2.4 每日日程提醒
	 * @apiVersion 1.0.0
	 * @apiName send_quotations
	 * @apiGroup 2-Quotations
	 * @apiDescription 2.4 每日日程提醒
	 * @apiSuccess {Number}   code           		返回码
	 * @apiSuccess {string}   message  				返回信息
	 * @apiSuccess {json}     result   				结果
	 * @apiSuccessExample {json} 成功示例:
	 * {
	"code": 0,
	"msg": "请求成功",
	"data": {}
	}
	 */
	public function send_quotations() {
		//删除过期的form_id
		$this->delete_user_form_id_over();
		//查找今日有没有新的语录
		$start_time = date('Y-m-d') . ' 00:00:00';
		$end_time = date('Y-m-d H:i:s');
		$map = array(
			'deleted' => 0,
			'is_send' => 0,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$quotations = M('quotations')->where($map)->order('add_time desc')->find();
		if ($quotations) {
			//查找所有用户
			$user_map = array(
				'deleted' => 0,
				'login_source' => 1,
			);
			$user_list = M('user')->where($user_map)->select();
			$res_arr = array();
			foreach ($user_list as $key => $val) {
				$form_map = array(
					'user_id' => $val['id'],
					'add_time' => array('GT', date('Y-m-d H:i:s', time() - 86400 * 7)),
					'status' => 0,
				);
				$temp = M('user_form_id')->where($form_map)->order('add_time asc')->find();
				if ($temp) {
				    $res = $this->send_quotations_msg($val, $quotations, $temp);
				    array_push($res_arr, $res);
				}

			}
			$data = array(
				'id' => $quotations['id'],
				'is_send' => 1,
			);
			M('quotations')->save($data);
			$this->_suc_ret($res_arr);
		}
	}
	public function send_quotations_msg($user, $quotations, $temp) {
		$openId = $user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => 'hbtIjPCql4dK5a--d7JJss0o274CXUnGOnPBMf-BH_8',
			"page" => "pages/detail/index?send=" . str_replace('+', '%20', urlencode(json_encode($quotations))),
			"form_id" => $temp['form_id'],
			"data" => array(
				"keyword1" => array(
					"value" => $quotations['title'],
				),
				"keyword2" => array(
					"value" => $quotations['add_time'],
				),
				"keyword3" => array(
					"value" => $quotations['content'],
				),
			),
			"emphasis_keyword" => "keyword1.DATA",
		);
		$appId = $this->appid;
		$appSecret = $this->secret;

		$res = $this->getAccessToken($appId, $appSecret);
		$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $res;
		$data = json_encode($postData);
		$res = $this->http_request($url, $data);
		$return = json_decode($res, true);
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user['id'],
			'template_id' => 'hbtIjPCql4dK5a--d7JJss0o274CXUnGOnPBMf-BH_8',
			'content' => $postData['data']['keyword1']['value'] . ',form_id:' . $temp['form_id'] . ',page:' . $postData['page'],
			'callback_msg' => $res,
		);
		if ($return['errcode'] == 0 && $return['errmsg'] == 'ok') {
			//发送成功
			$data['status'] = 1;
		} else {
			$data['status'] = 0;
		}
		$temp_res = M('wechat_msg_log')->add($data);
		//删除已经使用的form_id
		$form_map = array(
			'id' => $temp['id'],
			'status' => 1,
		);
		$temp_res = M('user_form_id')->save($form_map);
		return $res . $temp['form_id'];

	}
	public function delete_user_form_id_over() {
	    $time = time();
	    $end = $time - 86400 * 7;
	    $end_time = date('Y-m-d H:i:s', $end);
		$map = array(
			'add_time' => array('ELT', $end_time),
		);
		$res = M('user_form_id')->where($map)->delete();
	}
	/**
	 * @api {post} Wechat/Quotations/update_form_id 2.4 更新用户form_id
	 * @apiVersion 1.0.0
	 * @apiName update_form_id
	 * @apiGroup 2-Quotations
	 * @apiDescription 2.4 更新用户form_id
	 * @apiParam {string} form_id form_id
	 * @apiParam {string} user_id 用户ID
	 * @apiSuccess {Number}   code           		返回码
	 * @apiSuccess {string}   message  				返回信息
	 * @apiSuccess {json}     result   				结果
	 * @apiSuccessExample {json} 成功示例:
	 * {
	"code": 0,
	"msg": "请求成功",
	"data": {}
	}
	 */
	public function update_form_id() {
		$form_id = I('form_id');
		$user_id = I('user_id');
		$data = array(
			'user_id' => $user_id,
			'form_id' => $form_id,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'status' => 0,
		);
		$res = M('user_form_id')->add($data);
		if (!$res) {
			$this->_err_ret('添加失败');
		}
		$this->_suc_ret();
	}
	/**
	 * @api {post} Wechat/Quotations/send_box_game 3.1 宝箱抽奖通知
	 * @apiVersion 1.0.0
	 * @apiName send_box_game
	 * @apiGroup 3-Quotations
	 * @apiDescription 3.1 宝箱抽奖通知
	 * @apiSuccess {Number}   code           		返回码
	 * @apiSuccess {string}   message  				返回信息
	 * @apiSuccess {json}     result   				结果
	 * @apiSuccessExample {json} 成功示例:
	 * {
	"code": 0,
	"msg": "请求成功",
	"data": {}
	}
	 */
	public function send_box_game() {
		//查找所有用户
		$user_map = array(
			'deleted' => 0,
			'login_source' => 0,
		);
		$user_list = M('user')->where($user_map)->select();
		$res_arr = array();
		foreach ($user_list as $key => $val) {
			if ($val['openid'] != '') {
				//$res = $this->send_box_game_msg($val['openid']);
			}
			array_push($res_arr, $res);
		}
		//$this->send_box_game_msg('oKFOO1hc7P90MGMU39zaIgYmJh0k');
		$this->_suc_ret($res_arr);
	}

	public function send_box_game_msg($openid) {
		$postData = array(
			"touser" => $openid,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Activity/box_game",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "免费开宝箱活动开始啦~",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => "3把免费金钥匙！",
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => "1小时",
				),
				"remark" => array(
					"value" => "每天9点，12点，21点，3个时段开宝箱，每个时段每人免费赠送3把金钥匙！每时段邀请1位好友可获得1把金钥匙，邀请好友每时段最多获得2把金钥匙哦~",
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
		$user = M('user')->where(array('openid' => $openid))->find();
		if ($user) {
			$return = json_decode($res, true);
			$data = array(
				'add_time' => date('Y-m-d H:i:s'),
				'deleted' => 0,
				'user_id' => $user['id'],
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
		}

	}
	/**
	 * 中奖记录到期处理（计划任务）
	 */
	public function record_over_time(){
	    //获取3天之前的所有未发货中奖记录
	    $date = date('Y-m-d H:i:s');
	    $start_time = date('Y-m-d H:i:s',strtotime($date)-C('FREE_USER_SAVE_DAYS')*86400);
	    $map = array(
	        'deleted' => 0,
	        'status' => 0,
	        'add_time' => array('ELT', $start_time),
	    );
	    $record_list = M('lottery_record')->where($map)->select();
	    $user_list = array();
	    foreach($record_list as $key=>$val){
	        if(!in_array($val['user_id'],$user_list)){
	            $user_list[] = $val['user_id'];
	        }
	    }
	    //var_dump($user_list);die();
	    //$user_list = array(571,572);
	    foreach($user_list as $key=>$val){
	        $user_map = array(
	            'id'=>$val,
	            'deleted'=>0
	        );
	        $user = M('user')->where($user_map)->find();
	        if($user){
	            if($user['charge_num'] == -1){//普通用户
	                //清空该用户宝箱中奖记录、、不增加糖豆
	                $user_box_map = array(
	                    'user_id'=>$val,
	                    'deleted' => 0,
	                    'status' => 0,
	                    'type'=>-3,
	                    'add_time' => array('ELT', $start_time),
	                );
	                $box_data = array(
	                    'deleted'=>1
	                );
	                $user_box_res = M('lottery_record')->where($user_box_map)->save($box_data);
	                //兑换中奖记录，并清空
	                $user_record_map = array(
	                    'user_id'=>$val,
	                    'deleted' => 0,
	                    'status' => 0,
	                    'add_time' => array('ELT', $start_time),
	                );
	                $user_record_count = M('lottery_record')->where($user_record_map)->count();
	                if($user_record_count){
	                    //清空记录
	                    $record_data = array(
	                        'deleted'=>1
	                    );
	                    $user_record_res = M('lottery_record')->where($user_record_map)->save($record_data);
	                    //兑换糖豆
	                    $user_data = array(
	                        'coin_num'=>$user['coin_num'] + $user_record_count*20,
	                    );
	                    $user_coin_res = M('user')->where($user_map)->save($user_data);
	                    //增加兑换糖豆记录。。。。中奖娃娃兑换
	                    $user_coin_record_data = array(
	                        'add_time' => date('Y-m-d H:i:s'),
	                        'deleted' => 0,
	                        'user_id'=>$val,
	                        'coin_config_id'=>0,
	                        'num'=>$user_record_count*20,
	                        'before_balance' => $user['coin_num'],
	                        'after_balance' => $user_data['coin_num'],
	                        'status'=>1,
	                        'money'=>0,
	                        'type'=>-4,
	                        'luck_num'=>0
	                    );
	                    $user_coin_record_res = M('user_coin_record')->add($user_coin_record_data);
	                }
	            }
	            if($user['charge_num'] > 0){//付费用户
	                //保存30天
	                $start_time = date('Y-m-d H:i:s',strtotime($date)-C('PAY_USER_SAVE_DAYS')*86400);
	                //宝箱记录处理
	                $user_box_map = array(
	                    'user_id'=>$val,
	                    'deleted' => 0,
	                    'status' => 0,
	                    'type'=>-3,
	                    'add_time' => array('ELT', $start_time),
	                );
	                $user_box_count = M('lottery_record')->where($user_box_map)->count();
	                if($user_box_count){
	                    //清空记录
	                    $record_data = array(
	                        'deleted'=>1
	                    );
	                    $user_box_res = M('lottery_record')->where($user_box_map)->save($record_data);
	                    //兑换糖豆
	                    $user_data = array(
	                        'coin_num'=>$user['coin_num'] + $user_box_count*20,
	                    );
	                    $user_coin_res = M('user')->where($user_map)->save($user_data);
	                    
	                    //增加兑换糖豆记录。。。。宝箱娃娃兑换
	                    $user_coin_record_data = array(
	                        'add_time' => date('Y-m-d H:i:s'),
	                        'deleted' => 0,
	                        'user_id'=>$val,
	                        'coin_config_id'=>0,
	                        'num'=>$user_record_count*20,
	                        'before_balance' => $user['coin_num'],
	                        'after_balance' => $user_data['coin_num'],
	                        'status'=>1,
	                        'money'=>0,
	                        'type'=>-3,
	                        'luck_num'=>0
	                    );
	                    $user_coin_record_res = M('user_coin_record')->add($user_coin_record_data);
	                }
	                //中奖记录处理
	                $user_record_map = array(
	                    'user_id'=>$val,
	                    'deleted' => 0,
	                    'status' => 0,
	                    'add_time' => array('ELT', $start_time),
	                );
	                $user_record_list = M('lottery_record')->where($user_record_map)->select();
	                if($user_record_list){
	                    //清空记录
	                    $record_data = array(
	                        'deleted'=>1
	                    );
	                    $user_box_res = M('lottery_record')->where($user_record_map)->save($record_data);
	                    $add_coin = 0;
	                    foreach($user_record_list as $record_key=>$record_val){
	                        $lottery_config_map = array(
	                            'id'=>$record_val['lottery_config_id']
	                        );
	                        $lottery_config = M('lottery_config')->where($lottery_config_map)->find();
	                        if($lottery_config){
	                            $add_coin = $add_coin + $lottery_config['coin_num'] * 3;
	                        }
	                    }
	                    //兑换糖豆
	                    $user_data = array(
	                        'coin_num'=>$user['coin_num'] + $add_coin,
	                    );
	                    $user_coin_res = M('user')->where($user_map)->save($user_data);
	                    
	                    //增加兑换糖豆记录。。。。中奖娃娃兑换
	                    $user_coin_record_data = array(
	                        'add_time' => date('Y-m-d H:i:s'),
	                        'deleted' => 0,
	                        'user_id'=>$val,
	                        'coin_config_id'=>0,
	                        'num'=>$user_record_count*20,
	                        'before_balance' => $user['coin_num'],
	                        'after_balance' => $user_data['coin_num'],
	                        'status'=>1,
	                        'money'=>0,
	                        'type'=>-4,
	                        'luck_num'=>0
	                    );
	                    $user_coin_record_res = M('user_coin_record')->add($user_coin_record_data);
	                }
	            }
	        }
	    }
	}
	
	/**
	 * @api {post} Wechat/Quotations/send_adopt 4.1 领养提醒
	 * @apiVersion 1.0.0
	 * @apiName send_adopt
	 * @apiGroup 2-Quotations
	 * @apiDescription 4.1 领养提醒
	 * @apiSuccess {Number}   code           		返回码
	 * @apiSuccess {string}   message  				返回信息
	 * @apiSuccess {json}     result   				结果
	 * @apiSuccessExample {json} 成功示例:
	 * {
	 "code": 0,
	 "msg": "请求成功",
	 "data": {}
	 }
	 */
	public function send_adopt() {
	    //获取所有领养娃娃的用户ID
	    $map = array(
	        'deleted'=>0
	    );
	    $adopt_list = M('adopt_log')->where($map)->select();
	    $user_id_arr = array();
	    foreach($adopt_list as $key=>$val){
	        if(!in_array($val['user_id'],$user_id_arr)){
	            $user_id_arr[] = $val['user_id'];
	        }
	    }
	    foreach($user_id_arr as $key=>$val){
	        $user_map = array(
	            'deleted'=>0,
	            'id'=>$val
	        );
	        $user_info = M('user')->where($user_map)->find();
	        if($user_info){
	            //$this->send_adopt_user_msg($user_info['openid'], $val);
	        }
	    }
	}
	public function send_adopt_user_msg($openid,$user_id) {
	    $postData = array(
	        "touser" => $openid,
	        "template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
	        "url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/my_wawa/user_id/".$user_id,
	        "topcolor" => "#FF0000",
	        "data" => array(
	            "first" => array(
	                "value" => "亲，您领取的娃娃今日还没有喂养哦，快来喂养吧~",
	                "color" => "#173177",
	            ),
	            "keyword1" => array(
	                "value" => "新手教程1份",
	                "color" => "#173177",
	            ),
	            "keyword2" => array(
	                "value" => date('Y年m月d日'),
	            ),
	            "keyword3" => array(
	                "value" => "无限期",
	            ),
	            "remark" => array(
	                "value" => "史上最好抓的娃娃，娃娃质量超好！记得多多关注公众号获取最新活动哦~",
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
	    $user = M('user')->where(array('openid' => $openid))->find();
	    if ($user) {
	        $return = json_decode($res, true);
	        $data = array(
	            'add_time' => date('Y-m-d H:i:s'),
	            'deleted' => 0,
	            'user_id' => $user['id'],
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
	    }
	    
	}
	
	public function get_user_record(){
	    $p_id = I('p_id');
	    $map = array(
	        'p_id'=>$p_id,
	        'deleted'=>0,
	    );
	    $list = M('user')->where($map)->select();
	    $user_list = array();
	    foreach($list as $key=>$val){
	        if(!in_array($val['id'],$user_list)){
	            $user_list[] = $val['id'];
	        }
	    }
	    $map = array(
	        'user_id'=>array('IN',$user_list),
	        'deleted'=>0,
	        'add_time'=>array('EGT','2018-09-21 16:00:00'),
	    );
	    $list = M('lottery_record')->where($map)->select();
	    echo '<pre>';
	    var_dump($list);die();
	}
// 	public function activity_lottery() {
// 	    $map = array(
// 	        'id' => 1,
// 	    );
// 	    $lottery_config = M('lottery_activity')->where($map)->find();
// 	    if (!$lottery_config) {
// 	        exit;
// 	    }
// 	    if ($lottery_config['is_open'] == 0) {
// 	        exit;
// 	    }
	    
// 	    //$user = session('user');
// 	    $map = array(
// 	        'id' => 2577,
// 	    );
// 	    $userModel = D('user');
// 	    $user = $userModel->where($map)->find();
// 	    if (!$user) {
// 	        exit;
// 	    }
// 	    //已抓取次数
// 	    $lottery_activity_log_map = array(
// 	        'deleted'=>0,
// 	        'user_id'=>$user['id'],
// 	        'lottery_activity_id'=>1
// 	    );
// 	    $lottery_activity_log = M('lottery_activity_log')->where($lottery_activity_log_map)->select();
// 	    $fail_img_url = 'https://fssw.bichonfrise.cn/Public/weixin/image/fail.png';
// 	    $res_data = array(
// 	        'good_name' => '',
// 	        'good_url' => $fail_img_url,
// 	        'user_coin_num' => $user['coin_num'],
// 	        'msg' => "很遗憾，您这次没有抓到，请再接再厉！",
// 	    );
	    
// 	    $res_data['times'] = $lottery_config['times'] - count($lottery_activity_log)-1;
// 	    $is_hit = 0;
// 	    $hit_good_id = 0;
// 	    if ($user['forbidden'] == 1) {
// 	        $res_data['msg'] = "因恶意使用系统，您已被禁止抽奖！";
// 	        $this->_suc_ret($res_data);
// 	    }
// 	    if ($lottery_config['times'] - count($lottery_activity_log) < 1) {
// 	        $res_data['msg'] = "次数已用完~";
// 	        $res_data['times'] = 0;
// 	        $this->_suc_ret($res_data);
// 	    }
// 	    if ($user['coin_num']<1) {
// 	        $res_data['msg'] = "糖豆不足，快去收集或充值糖豆吧~";
// 	        $this->_suc_ret($res_data);
// 	    }
	    
// 	    // 扣除用户的糖豆
// 	    $data = array(
// 	        'id' => $user['id'],
// 	        'coin_num' => $user['coin_num'] - 1,
// 	    );
// 	    $res = $userModel->save($data);
// 	    if ($res === false) {
// 	        $res_data['msg'] = "扣除糖豆失败!";
// 	        $this->_suc_ret($res_data);
// 	    }
// 	    $res_data['user_coin_num'] = $user['coin_num'] - 1;
// 	    $MAX_PERCENT_NUM = mt_getrandmax(); //2147483647
// 	    // 开始抽奖
// 	    //获取中奖商品
// 	    $map = array(
// 	        'lottery_activity_id'=>1,
// 	        'deleted'=>0
// 	    );
// 	    $goods_list = M('lottery_activity_goods')->where($map)->select();
// 	    $is_hit = 0;
// 	    $hit_goods_id = 0;
// 	    $lottery_activity_goods_info = array();
// 	    //已抓取次数
// 	    $lottery_activity_log_map = array(
// 	        'deleted'=>0,
// 	        'user_id'=>$user['id'],
// 	        'lottery_activity_id'=>1,
// 	        'is_hit'=>1
// 	    );
// 	    $lottery_activity_log = M('lottery_activity_log')->where($lottery_activity_log_map)->select();
// 	    if(count($lottery_activity_log) == 0){
// 	        for ($i = 0; $i < count($goods_list); $i++) {
// 	            $luck_num = $goods_list[$i]['probability'] * 100;
// 	            $temp = rand(1, 100);
// 	            if ($temp > 0 && $temp < $luck_num && $goods_list[$i]['obtain_num'] > 0) {
// 	                $is_hit = 1;
// 	                $hit_goods_id = $goods_list[$i]['goods_id'];
// 	                $goods_list[$i]['goods_info'] = M('lottery_good')->where(array('id'=>$goods_list[$i]['goods_id']))->find();
// 	                $lottery_activity_goods_info = $goods_list[$i];
// 	                break;
// 	            }
// 	        }
// 	    }
// 	    $lottery_good_name = '';
// 	    if ($is_hit == 0) {
// 	        $res_data['good_name'] = "";
// 	        $res_data['good_url'] = $fail_img_url;
	        
// 	        $temp_data = array(
// 	            'add_time' => date('Y-m-d H:i:s'),
// 	            'deleted' => 0,
// 	            'user_id' => $user['id'],
// 	            'lottery_config_id' => 0,
// 	            'consume_num' => 1,
// 	            'is_hit' => $is_hit,
// 	            'hit_good_id' => 0,
// 	        );
// 	        $lottery_activity_log_data = array(
// 	            'add_time' => date('Y-m-d H:i:s'),
// 	            'deleted' => 0,
// 	            'user_id' => $user['id'],
// 	            'lottery_activity_id' => 1,
// 	            'is_hit' => $is_hit,
// 	            'hit_good_id' => $hit_goods_id,
// 	        );
// 	    } else {
// 	        $lotteryGoodModel = D('lottery_good');
// 	        $map = array(
// 	            'id' => $hit_goods_id,
// 	        );
// 	        $lottery_good = $lotteryGoodModel->where($map)->find();
// 	        $res_data['good_name'] = $lottery_good['name'];
// 	        $res_data['good_url'] = $lottery_good['img_url'];
// 	        // 添加中奖记录
// 	        $lotteryRecordModel = D('lottery_record');
// 	        $data = array(
// 	            'lottery_type_id' => $lottery_activity_goods_info['goods_info']['lottery_type_id'],
// 	            'lottery_config_id' => $lottery_activity_goods_info['lottery_config_id'],
// 	            'user_id' => $user['id'],
// 	            'lottery_good_id' => $hit_goods_id,
// 	            'add_time' => date('Y-m-d H:i:s', time(0)),
// 	            'type' => -5,
// 	        );
// 	        $res = $lotteryRecordModel->add($data);
// 	        $lottery_record_id = $res;
// 	        // 添加记录失败提示用户没抽中，以免引起投诉
// 	        if (!$res) {
// 	            $is_hit = 0;
// 	            $hit_goods_id = 0;
// 	            $res_data['good_name'] = "";
// 	            $res_data['good_url'] = $fail_img_url;
// 	            $temp_data = array(
// 	                'add_time' => date('Y-m-d H:i:s'),
// 	                'deleted' => 0,
// 	                'user_id' => $user['id'],
// 	                'lottery_config_id' => 0,
// 	                'consume_num' => 1,
// 	                'is_hit' => $is_hit,
// 	                'hit_good_id' => 0,
// 	            );
// 	            $lottery_activity_log_data = array(
// 	                'add_time' => date('Y-m-d H:i:s'),
// 	                'deleted' => 0,
// 	                'user_id' => $user['id'],
// 	                'lottery_activity_id' => 1,
// 	                'is_hit' => $is_hit,
// 	                'hit_good_id' => $hit_goods_id,
// 	            );
// 	        } else {
// 	            $lottery_good_name = $lottery_good['name'];
// 	            // 通知用户中奖了
// 	            $this->send_zhuawawa_inner_msg($user, $lottery_activity_goods_info['goods_info']['name'], $lottery_config['name']);
	            
// 	            $where = array(
// 	                'goods_id' => $hit_goods_id,
// 	            );
// 	            $data = array(
// 	                'obtain_num'=>$lottery_activity_goods_info['obtain_num']-1
// 	            );
// 	            M('lottery_activity_goods')->where($where)->save($data);
// 	            //更新用户中奖记录
// 	            $user_data = array(
// 	                'id' => $user['id'],
// 	                'record_total' => $user['record_total'] + 1,
// 	            );
// 	            $res = M('user')->save($user_data);
// 	            // 添加糖豆扣除记录
// 	            $temp_data = array(
// 	                'add_time' => date('Y-m-d H:i:s'),
// 	                'deleted' => 0,
// 	                'user_id' => $user['id'],
// 	                'lottery_config_id' => $lottery_activity_goods_info['lottery_config_id'],
// 	                'consume_num' => 1,
// 	                'is_hit' => $is_hit,
// 	                'hit_good_id' => $hit_goods_id,
// 	            );
// 	        }
// 	    }
	    
// 	    $map_admin = array(
// 	        'openid' => 'oKFOO1hc7P90MGMU39zaIgYmJh0k',
// 	    );
// 	    $admin_user = $userModel->where($map_admin)->find();
// 	    $this->send_zhuawawa_inner_msg_admin($admin_user, $lottery_config, $is_hit, $user, $lottery_good_name);
	    
// 	    $res = M('luck_draw_log')->add($temp_data);
// 	    $res = M('lottery_activity_log')->add($lottery_activity_log_data);
// 	    $this->_suc_ret($res_data);
// 	}

}