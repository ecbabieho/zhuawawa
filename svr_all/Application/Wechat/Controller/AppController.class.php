<?php
namespace Wechat\Controller;
use Think\Controller;

class AppController extends Controller {
	private $appid = '';
	private $mch_id = '';
	private $key = '';
	private $ip = '';

	/* 微信支付完成，回调地址url方法  xiao_notify_url() */
	public function xiao_notify_url() {
		$post = file_get_contents('php://input');
		file_put_contents("1.txt", "post" . json_encode($post) . "\r\n", FILE_APPEND);
		$post_data = $this->xml_to_array($post); //微信支付成功，返回回调地址url的数据：XML转数组Array
		$postSign = $post_data['sign'];
		unset($post_data['sign']);

		/* 微信官方提醒：
			 *  商户系统对于支付结果通知的内容一定要做【签名验证】,
			 *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
			 *  防止数据泄漏导致出现“假通知”，造成资金损失。
		*/
		ksort($post_data); // 对数据进行排序
		$str = $this->ToUrlParams($post_data); //对数组数据拼接成key=value字符串
		$user_sign = strtoupper(md5($post_data)); //再次生成签名，与$postSign比较

		//$order_status = $this->PayOrder->query("select * from pay_orders where id = {$post_data['out_trade_no']}");
		$order_id = end(explode('-', $post_data['out_trade_no']));
		$order_info = M('user_coin_record')->where(array('deleted' => 0, 'id' => $order_id))->find();
		if ($post_data['return_code'] == 'SUCCESS' && $postSign) {
			/*
				 * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
				 * 其次，订单已经为ok的，直接返回SUCCESS
				 * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
			*/
			if ($order_info['status'] != '0') {
				$this->return_success();
			} else {
				//获取赠送随机数
				$coin_map = array(
					'id' => $order_info['coin_config_id'],
				);
				$coin_config_info = M('coin_config')->where($coin_map)->find();
				if (!$coin_config_info) {
					$luck_coin = 0;
				} else {
					$luck_coin = rand($coin_config_info['luck_low'], $coin_config_info['luck_high']);
				}
				$user_map = array(
					'id' => $order_info['user_id'],
				);
				$user_info = M('user')->where($user_map)->find();
				$map = array('id' => $order_id);
				$data = array(
					'status' => 1,
					'before_balance' => $user_info['coin_num'],
					'after_balance' => $user_info['coin_num'] + $order_info['num'],
					'luck_num' => $luck_coin,
				);
				$res = M('user_coin_record')->where($map)->save($data);
				if ($res) {
					$data = array(
						'coin_num' => $user_info['coin_num'] + $order_info['num'],
						'charge_num' => $user_info['charge_num'] + $order_info['money'],
						'luck_num' => $user_info['luck_num'] + $luck_coin,
					);
					M('user')->where($user_map)->save($data);
					$res = $this->send_user_coin_pay_msg($user_info, $order_info['num'], $order_info['money']);
					//分佣结算
					/*if ($user_info['p_id'] != 0 && $user_info['charge_num']>0) {
						//有上级用户
						$p_map = array(
							'id' => $user_info['p_id'],
						);
						$p_info = M('user')->where($p_map)->find();
						if ($p_info) {
						    $data = array(
						        'add_time'=>date('Y-m-d H:i:s'),
						        'deleted'=>0,
						        'user_id'=>$user['id'],
						        'p_id'=>$p_info['id'],
						        'lottery_record_id'=>0,
						        'vip_order_id'=>$order_id,
						        'type'=>2
						    );
						    if($p_info['is_agent'] == 1){
						        $data['money'] = C('AGENT_VIP')*$order_info['money'];
						    }else{
						        $data['money'] = C('ORDINARY_VIP')*$order_info['money'];
						    }
						    $maid_res = M('maid_log')->add($data);
						    if($maid_res){
						        $user_update_data = array(
						            'id'=>$p_info['id'],
						            'yu_e'=>$p_info['yu_e']+$data['money']
						        );
						        $user_update_res = M('user')->save($user_update_data);
						        if($user_update_res){
						            //通知父级用户佣金到账

						        }
						    }
						}
					}*/
					$this->return_success();
				}
			}
		} else {
			$this->_err_ret('微信支付失败');
		}
	}
	
	/* 微信支付完成，回调地址url方法  xiao_notify_url_coin_vip() */
	public function xiao_notify_url_coin_vip() {
	    $post = file_get_contents('php://input');
	    file_put_contents("1.txt", "post" . json_encode($post) . "\r\n", FILE_APPEND);
	    $post_data = $this->xml_to_array($post); //微信支付成功，返回回调地址url的数据：XML转数组Array
	    $postSign = $post_data['sign'];
	    unset($post_data['sign']);
	    
	    /* 微信官方提醒：
	     *  商户系统对于支付结果通知的内容一定要做【签名验证】,
	     *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
	     *  防止数据泄漏导致出现“假通知”，造成资金损失。
	     */
	    ksort($post_data); // 对数据进行排序
	    $str = $this->ToUrlParams($post_data); //对数组数据拼接成key=value字符串
	    $user_sign = strtoupper(md5($post_data)); //再次生成签名，与$postSign比较
	    
	    //$order_status = $this->PayOrder->query("select * from pay_orders where id = {$post_data['out_trade_no']}");
	    $order_id = end(explode('-', $post_data['out_trade_no']));
	    $order_info = M('user_coin_record')->where(array('deleted' => 0, 'id' => $order_id))->find();
	    if ($post_data['return_code'] == 'SUCCESS' && $postSign) {
	        /*
	         * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
	         * 其次，订单已经为ok的，直接返回SUCCESS
	         * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
	         */
	        if ($order_info['status'] != '0') {
	            $this->return_success();
	        } else {
	            //获取赠送随机数
	            $coin_map = array(
	                'id' => $order_info['coin_config_id'],
	            );
	            $coin_config_info = M('coin_config')->where($coin_map)->find();
	            $user_map = array(
	                'id' => $order_info['user_id'],
	            );
	            $user_info = M('user')->where($user_map)->find();
	            $user = $user_info;
	            $map = array('id' => $order_id);
	            $data = array(
	                'status' => 1,
	                'before_balance' => $user_info['coin_num'],
	                'after_balance' => $user_info['coin_num'] + $order_info['num'],
	            );
	            $res = M('user_coin_record')->where($map)->save($data);
	            if ($res) {
	                $data = array(
	                    'coin_num' => $user_info['coin_num'] + $order_info['num'],
	                    'charge_num' => $user_info['charge_num'] + $order_info['money'],
	                );
	                M('user')->where($user_map)->save($data);
	                if($order_info['type'] == -8){//赠送会员
	                    if($coin_config_info['level'] != 1){
	                        
	                        //当前会员没到期，并且当前会员比所选会员等级高
	                        if ($coin_config_info['level'] < $user['level'] && time()<strtotime($user['over_time'])) {
	                            $over_time = $user['over_time'];
	                        } else if ($coin_config_info['level'] < $user['level'] && time()>strtotime($user['over_time'])) {
	                            //之前的会员等级已经到期或者没有购买过会员,并且当前会员比所选会员等级高
	                            $over_time = date('Y-m-d H:i:s',time()+$coin_config_info['vip_day_num']*86400);
	                        }else if ($vip_grade['level'] == $user['level']) {
	                            // 续费用户
	                            if($user['over_time'] == '0000-00-00 00:00:00' || time()>strtotime($user['over_time'])){
	                                $over_time = date('Y-m-d H:i:s',time()+$coin_config_info['vip_day_num']*86400);
	                            }
	                            if(time()<strtotime($user['over_time'])){
	                                //没到期，延长续费
	                                $over_time = date('Y-m-d H:i:s',strtotime($user['over_time'])+$coin_config_info['vip_day_num']*86400);
	                            }
	                        }else{
	                            // 低级用户转高级 重新计算费用
	                            if($user['over_time'] == '0000-00-00 00:00:00' || time()>strtotime($user['over_time'])){
	                                $over_time = date('Y-m-d H:i:s',time()+$coin_config_info['vip_day_num']*86400);
	                            }
	                            if(time()<strtotime($user['over_time'])){
	                                //没到期，低级用户转高级 重新计算费用
	                                $over_time = date('Y-m-d H:i:s',strtotime($user['over_time'])+$coin_config_info['vip_day_num']*86400);
	                            }
	                        }
	                        $user_data = array(
	                            'id' => $order_info['user_id'],
	                            'level' => $coin_config_info['level'],
	                            'over_time' => $over_time,
	                        );
	                        $res = M('user')->save($user_data);
	                        $grade_info = M('vip_grade')->where(array('level' => $coin_config_info['level']))->find();
	                        $res = $this->send_user_coin_vip_pay_msg($user_info, $order_info['num'], $order_info['money'],$grade_info['name'],$coin_config_info['vip_day_num'],$over_time);
	                    }
	                }
	                $this->return_success();
	            }
	        }
	    } else {
	        $this->_err_ret('微信支付失败');
	    }
	}
	public function send_user_coin_vip_pay_msg($user, $coin_num, $pay_money,$level_name,$day_num,$over_time) {
	    $openId = $user['openid'];
	    /*$openId = 'oKFOO1iGQTFOkFXobxHL0pGk3ai0';
	     $coin_num = 100;
	     */
	    $postData = array(
	        "touser" => $openId,
	        "template_id" => C("PAY_TMPL_ID"),
	        "url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Vip/index",
	        "topcolor" => "#FF0000",
	        "data" => array(
	            "first" => array(
	                "value" => "恭喜您，尊敬的" . $user['nickname'] . "您充值的" . $coin_num . "个糖豆已到账,并且赠送您'".$level_name."'".$day_num."天，到期时间为'".$over_time."'！开启愉快的抓娃娃之旅吧",
	                "color" => "#173177",
	            ),
	            "keyword1" => array(
	                "value" => $user['id'],
	                "color" => "#173177",
	            ),
	            "keyword2" => array(
	                "value" => $coin_num . "个糖豆,'".$level_name."'".$day_num."天",
	            ),
	            "keyword3" => array(
	                "value" => $pay_money . "元",
	            ),
	            "remark" => array(
	                "value" => "充值会员每月还可享受不够" . C("FREE_POST_WAWA_COUNT") . "个娃娃免费包邮哦~机会有限！点击立即充会员！",
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
	        'user_id' => $user['id'],
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
	

	/* 微信支付完成，回调地址url方法  activity_notify_url() */
	public function activity_notify_url() {
		$post = file_get_contents('php://input');
		file_put_contents("1.txt", "post" . json_encode($post) . "\r\n", FILE_APPEND);
		$post_data = $this->xml_to_array($post); //微信支付成功，返回回调地址url的数据：XML转数组Array
		$postSign = $post_data['sign'];
		unset($post_data['sign']);

		/* 微信官方提醒：
			     *  商户系统对于支付结果通知的内容一定要做【签名验证】,
			     *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
			     *  防止数据泄漏导致出现“假通知”，造成资金损失。
		*/
		ksort($post_data); // 对数据进行排序
		$str = $this->ToUrlParams($post_data); //对数组数据拼接成key=value字符串
		$user_sign = strtoupper(md5($post_data)); //再次生成签名，与$postSign比较

		//$order_status = $this->PayOrder->query("select * from pay_orders where id = {$post_data['out_trade_no']}");
		$order_id = end(explode('-', $post_data['out_trade_no']));
		$order_info = M('user_coin_record')->where(array('deleted' => 0, 'id' => $order_id))->find();
		if ($post_data['return_code'] == 'SUCCESS' && $postSign) {
			/*
				         * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
				         * 其次，订单已经为ok的，直接返回SUCCESS
				         * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
			*/
			if ($order_info['status'] != '0') {
				$this->return_success();
			} else {

				$user_map = array(
					'id' => $order_info['user_id'],
				);
				$user_info = M('user')->where($user_map)->find();
				$map = array('id' => $order_id);
				$data = array(
					'status' => 1,
					'before_balance' => $user_info['coin_num'],
					'after_balance' => $user_info['coin_num'] + $order_info['num'],
				);
				$res = M('user_coin_record')->where($map)->save($data);
				if ($res) {
					$data = array(
						'coin_num' => $user_info['coin_num'] + $order_info['num'],
						'charge_num' => $user_info['charge_num'] + $order_info['money'],
					);
					M('user')->where($user_map)->save($data);
					$res = $this->send_user_coin_pay_msg($user_info, $order_info['num'], $order_info['money']);
					$this->return_success();
				}
			}
		} else {
			$this->_err_ret('微信支付失败');
		}
	}

	/* 微信支付完成，回调地址url方法  vip_notify_url() */
	public function vip_notify_url() {
		$post = file_get_contents('php://input');
		file_put_contents("3.txt", "post" . json_encode($post) . "\r\n", FILE_APPEND);
		$post_data = $this->xml_to_array($post); //微信支付成功，返回回调地址url的数据：XML转数组Array
		$postSign = $post_data['sign'];
		unset($post_data['sign']);

		/* 微信官方提醒：
			 *  商户系统对于支付结果通知的内容一定要做【签名验证】,
			 *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
			 *  防止数据泄漏导致出现“假通知”，造成资金损失。
		*/
		ksort($post_data); // 对数据进行排序
		$str = $this->ToUrlParams($post_data); //对数组数据拼接成key=value字符串
		$user_sign = strtoupper(md5($post_data)); //再次生成签名，与$postSign比较

		//$order_status = $this->PayOrder->query("select * from pay_orders where id = {$post_data['out_trade_no']}");
		$order_id = end(explode('-', $post_data['out_trade_no']));
		$order_info = M('vip_order')->where(array('deleted' => 0, 'id' => $order_id))->find();
		if ($post_data['return_code'] == 'SUCCESS' && $postSign) {
			/*
				 * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
				 * 其次，订单已经为ok的，直接返回SUCCESS
				 * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
			*/
			if ($order_info['status'] != '0') {
				$this->return_success();
			} else {
				$userModel = D('user');
				$user_map = array(
					'id' => $order_info['user_id'],
				);
				$user = $userModel->where($user_map)->find();

				//获取到期时间
				/*$day = $order_info['day_num'];
				 $over_time = date('Y-m-d H:i:s', strtotime($user['over_time']) + intval($day) * 86400);*/
				$data = array(
					'charge_num' => $user['charge_num'] + $order_info['money'],
				);
				M('user')->where($user_map)->save($data);
				$user_data = array(
					'id' => $order_info['user_id'],
					'level' => $order_info['level'],
					'over_time' => $order_info['over_time'],
				);
				$res = M('user')->save($user_data);
				if ($res) {
					$order_data = array(
						'id' => $order_id,
						'status' => 1,
					);
					$res = M('vip_order')->save($order_data);
					$grade_info = M('vip_grade')->where(array('level' => $order_info['level']))->find();

					//分佣
					// 					$user_info = M('user')->where(array('id' => $order_info['user_id'], 'deleted' => 0))->find();
					// 					$this->send_user_vip_pay_msg($user, $grade_info, $order_info);
					// 					//分佣结算(上级用户)
					// 					if ($user_info['p_id'] != 0) {
					// 						//有上级用户
					// 						$p_map = array(
					// 							'id' => $user_info['p_id'],
					// 						);
					// 						$p_info = M('user')->where($p_map)->find();
					// 						if ($p_info && $p_info['charge_num'] > 0) {
					// 							$data = array(
					// 								'add_time' => date('Y-m-d H:i:s'),
					// 								'deleted' => 0,
					// 								'user_id' => $user['id'],
					// 								'p_id' => $p_info['id'],
					// 								'lottery_record_id' => 0,
					// 								'vip_order_id' => $order_id,
					// 								'type' => 2,
					// 								'is_cash' => 1,
					// 							);
					// 							if ($p_info['is_agent'] == 1) {
					// 								$data['money'] = C('AGENT_VIP') * $order_info['money'];
					// 							} else {
					// 								$data['money'] = C('ORDINARY_VIP') * $order_info['money'];
					// 							}
					// 							$maid_res = M('maid_log')->add($data);
					// 							if ($maid_res) {
					// 								if (C('IS_PAY_PARENT_USER_DIRECT') == 1) {
					// 									//$pay_res = $this->direct_send_parent_user_money($p_info, $user, $data['money']);
					// 									$pay_res = $this->direct_send_parent_user_redpackage_vip($p_info, $user, $data['money']);
					// 									//付款记录添加
					// 									$data = array(
					// 										'id' => $maid_res,
					// 										'is_cash' => C('IS_PAY_PARENT_USER_DIRECT'),
					// 										'wx_callback' => $pay_res,
					// 									);
					// 									$temp_maid_res = M('maid_log')->save($data);
					// 								} else {
					// 									$user_update_data = array(
					// 										'id' => $p_info['id'],
					// 										'yu_e' => $p_info['yu_e'] + $data['money'],
					// 									);
					// 									$user_update_res = M('user')->save($user_update_data);
					// 									$p_info = M('user')->where(array('id' => $p_info['id']))->find();
					// 									if ($user_update_res) {
					// 										//通知父级用户佣金到账
					// 										$vip_pay_config = M('vip_pay_config')->where(array('id' => $order_info['config_id']))->find();
					// 										$this->send_vip_msg($user_info, $p_info, $vip_pay_config, $grade_info['name'], $data['money']);

// 									}
					// 								}
					// 							}
					// 						}
					// 					}
					// 					//分佣结算(商户)
					// 					if ($user_info['merchant_id'] != 0) {
					// 						//有绑定商户
					// 						$merchant_map = array(
					// 							'id' => $user_info['merchant_id'],
					// 						);
					// 						$merchant_info = M('merchant')->where($merchant_map)->find();
					// 						if ($merchant_info && $merchant_info['bind_user_id'] != 0) {
					// 							$merchant_user_map = array(
					// 								'id' => $merchant_info['bind_user_id'],
					// 							);
					// 							$merchant_user = M('user')->where($merchant_user_map)->find();
					// 							if ($merchant_user) {
					// 								$data = array(
					// 									'add_time' => date('Y-m-d H:i:s'),
					// 									'deleted' => 0,
					// 									'user_id' => $user['id'],
					// 									'p_id' => $merchant_user['id'],
					// 									'lottery_record_id' => 0,
					// 									'vip_order_id' => $order_id,
					// 									'type' => 3,
					// 									'is_cash' => 1,
					// 									'merchant_id' => $merchant_info['id'],
					// 								);
					// 								//佣金
					// 								$data['money'] = C('MERCHANT_VIP_MAID') * $order_info['money'];
					// 								$maid_res = M('maid_log')->add($data);
					// 								if ($maid_res) {
					// 									if (C('IS_PAY_PARENT_USER_DIRECT') == 1) {
					// 										//$pay_res = $this->direct_send_parent_user_money($p_info, $user, $data['money']);
					// 										$pay_res = $this->direct_send_parent_user_redpackage_vip($merchant_user, $user, $data['money']);
					// 										//付款记录添加
					// 										$data = array(
					// 											'id' => $maid_res,
					// 											'is_cash' => C('IS_PAY_PARENT_USER_DIRECT'),
					// 											'wx_callback' => $pay_res,
					// 										);
					// 										$temp_maid_res = M('maid_log')->save($data);
					// 									} else {
					// 										$user_update_data = array(
					// 											'id' => $p_info['id'],
					// 											'yu_e' => $p_info['yu_e'] + $data['money'],
					// 										);
					// 										$user_update_res = M('user')->save($user_update_data);
					// 										$p_info = M('user')->where(array('id' => $p_info['id']))->find();
					// 										if ($user_update_res) {
					// 											//通知父级用户佣金到账
					// 											$vip_pay_config = M('vip_pay_config')->where(array('id' => $order_info['config_id']))->find();
					// 											$this->send_vip_msg($user_info, $p_info, $vip_pay_config, $grade_info['name'], $data['money']);

// 										}
					// 									}
					// 								}
					// 							}
					// 						}
					// 					}

					//赠送糖豆
					if ($order_data['level'] == 2) {
						$give_num = C('PAY_VIP_GIVE_COIN_HUANGJIN');
						if ($give_num > 0) {
							//购买黄金会员赠送糖豆
							$user_coin_data = array(
								'id' => $order_data['user_id'],
								'coin_num' => $user_info['coin_num'] + $give_num,
							);
							$user_coin_res = M('user')->save($user_coin_data);
							if ($user_coin_res) {
								$user_coin_record_data = array(
									'add_time' => date('Y-m-d H:i:s'),
									'deleted' => 0,
									'user_id' => $order_data['user_id'],
									'coin_config_id' => 0,
									'num' => $give_num,
									'before_balance' => $user_info['coin_num'],
									'after_balance' => $user_info['coin_num'] + $give_num,
									'status' => 1,
									'money' => 0,
									'type' => -5,
									'luck_num' => 0,
								);
								$user_coin_record_res = M('user_coin_record')->add($user_coin_record_data);
							}
						}
					}
					if ($order_data['level'] == 3) {
						$give_num = C('PAY_VIP_GIVE_COIN_ZUANSHI');
						if ($give_num > 0) {
							//购买黄金会员赠送糖豆
							$user_coin_data = array(
								'id' => $order_data['user_id'],
								'coin_num' => $user_info['coin_num'] + $give_num,
							);
							$user_coin_res = M('user')->save($user_coin_data);
							if ($user_coin_res) {
								$user_coin_record_data = array(
									'add_time' => date('Y-m-d H:i:s'),
									'deleted' => 0,
									'user_id' => $order_data['user_id'],
									'coin_config_id' => 0,
									'num' => $give_num,
									'before_balance' => $user_info['coin_num'],
									'after_balance' => $user_info['coin_num'] + $give_num,
									'status' => 1,
									'money' => 0,
									'type' => -5,
									'luck_num' => 0,
								);
								$user_coin_record_res = M('user_coin_record')->add($user_coin_record_data);
							}
						}
					}
				}
				$this->return_success();
			}
		} else {
			$this->_err_ret('微信支付失败');
		}
	}

	public function send_wxcallpay_msg_admin_vip($admin_user, $res_code, $res) {
		$openId = $admin_user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "商户支付失败(VIP)，错误码：" . $res_code . ",错误信息：" . $res,
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => "",
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => $qixian . "天",
				),
				"remark" => array(
					"value" => $this->get_free_post_notice($admin_user) . "点我继续抓娃娃！",
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
	/**
	 * 微信直接转账
	 */
	public function direct_send_parent_user_redpackage_vip($p_info, $user, $money) {
		$total_amount = 100 * $money;
		$data = array(
			'wxappid' => $this->appid, //商户账号appid
			'mch_id' => $this->mch_id, //商户号
			'nonce_str' => $this->createNoncestr(), //随机字符串
			'mch_billno' => date('YmdHis') . rand(1000, 9999), //商户订单号
			'send_name' => "哐糖",
			're_openid' => $p_info['openid'], //用户openid
			'total_amount' => $total_amount, //金额
			'total_num' => 1,
			'wishing' => "您邀请的好友" . $user['nickname'] . "购买会员补贴现金啦~",
			'client_ip' => $this->ip, //Ip地址
			'scene_id' => "PRODUCT_5",
			'remark' => $p_info['nickname'] . "邀请的好友" . $user['nickname'] . "购买会员补贴现金啦~",
			'act_name' => "邀请好友购买会员补贴",
		);
		$secrect_key = $this->key; ///这个就是个API密码。MD5 32位。
		$data = array_filter($data);
		ksort($data);
		$str = '';
		foreach ($data as $k => $v) {
			$str .= $k . '=' . $v . '&';
		}
		$str .= 'key=' . $secrect_key;
		$data['sign'] = md5($str);
		$xml = $this->arraytoxml_vip($data);

		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack'; //调用接口
		$res = $this->curl_vip($xml, $url);
		$return = $this->xmltoarray_vip($res);

		//print_r($return);
		//返回来的结果
		// [return_code] => SUCCESS [return_msg] => Array ( ) [mch_appid] => wxd44b890e61f72c63 [mchid] => 1493475512 [nonce_str] => 616615516 [result_code] => SUCCESS [partner_trade_no] => 20186505080216815
		// [payment_no] => 1000018361251805057502564679 [payment_time] => 2018-05-15 15:29:50

		$responseObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
		$res = $responseObj->return_code; //SUCCESS  如果返回来SUCCESS,则发生成功，处理自己的逻辑
		if ($responseObj->err_code != 0) {
			$map_admin = array(
				'openid' => 'oKFOO1hc7P90MGMU39zaIgYmJh0k',
			);
			$admin_user = $userModel->where($map_admin)->find();
			$this->send_wxcallpay_msg_admin_vip($admin_user, $responseObj->err_code, $responseObj->err_code_des);
		}
		return json_encode($return);
	}
	function arraytoxml_vip($data) {
		$str = '<xml>';
		foreach ($data as $k => $v) {
			$str .= '<' . $k . '>' . $v . '</' . $k . '>';
		}
		$str .= '</xml>';
		return $str;
	}
	function xmltoarray_vip($xml) {
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$val = json_decode(json_encode($xmlstring), true);
		return $val;
	}
	function curl_vip($param = "", $url) {

		$postUrl = $url;
		$curlPost = $param;
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost); // 增加 HTTP Header（头）里的字段
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 终止从服务端进行验证
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSLCERT, getcwd() . '/wxpay/cacert/apiclient_cert.pem'); //这个是证书的位置绝对路径
		curl_setopt($ch, CURLOPT_SSLKEY, getcwd() . '/wxpay/cacert/apiclient_key.pem'); //这个也是证书的位置绝对路径
		$data = curl_exec($ch); //运行curl
		curl_close($ch);
		return $data;
	}

	/* 微信支付完成，回调地址url方法  postal_order_notify_url() */
	public function postal_order_notify_url() {
		$post = file_get_contents('php://input');
		file_put_contents("1.txt", "post" . json_encode($post) . "\r\n", FILE_APPEND);
		$post_data = $this->xml_to_array($post); //微信支付成功，返回回调地址url的数据：XML转数组Array
		$postSign = $post_data['sign'];
		unset($post_data['sign']);

		/* 微信官方提醒：
			 *  商户系统对于支付结果通知的内容一定要做【签名验证】,
			 *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
			 *  防止数据泄漏导致出现“假通知”，造成资金损失。
		*/
		ksort($post_data); // 对数据进行排序
		$str = $this->ToUrlParams($post_data); //对数组数据拼接成key=value字符串
		$user_sign = strtoupper(md5($post_data)); //再次生成签名，与$postSign比较

		//$order_status = $this->PayOrder->query("select * from pay_orders where id = {$post_data['out_trade_no']}");
		$order_id = end(explode('-', $post_data['out_trade_no']));
		$order_info = M('postal_order')->where(array('deleted' => 0, 'id' => $order_id))->find();
		if ($post_data['return_code'] == 'SUCCESS' && $postSign) {
			/*
				 * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
				 * 其次，订单已经为ok的，直接返回SUCCESS
				 * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
			*/
			if ($order_info['status'] != '0') {
				$this->return_success();
			} else {
				//获取到期时间
				$day = $order_info['days'];
				$over_time = date('Y-m-d H:i:s', time() + intval($day) * 86400);
				$user_data = array(
					'id' => $order_info['user_id'],
					'postal_over_time' => $over_time,
				);
				$res = M('user')->save($user_data);
				if ($res) {
					$order_data = array(
						'id' => $order_id,
						'status' => 1,
						'over_time' => $over_time,
					);
					$res = M('postal_order')->save($order_data);
					//分佣
					$user_info = M('user')->where(array('id' => $order_info['user_id'], 'deleted' => 0))->find();
					$this->send_order_notify_msg($user_info, '包邮卡');
					if ($user_info['is_agent'] == 1) {
						if ($user_info['p_id'] != 0) {
							//有上级用户
							$p_map = array(
								'id' => $user_info['p_id'],
							);
							$p_info = M('user')->where($p_map)->find();
							if ($p_info) {
								//上级用户存在
								$data = array(
									'add_time' => date('Y-m-d H:i:s'),
									'deleted' => 0,
									'user_id' => $user_info['id'],
									'p_id' => $user_info['p_id'],
									'order_id' => $order_id,
									'money' => C('MAID_PRE') * $order_info['money'],
									'type' => 3,
								);
								$temp = M('maid_log')->add($data);
								if ($temp) {
									$p_data = array(
										'id' => $user_info['p_id'],
										'yu_e' => $p_info['yu_e'] + C('MAID_PRE') * $order_info['money'],
									);
									$res = M('user')->save($p_data);
								}
							}
						}
					}
				}

				$this->return_success();
			}
		} else {
			$this->_err_ret('微信支付失败');
		}
	}
	/* 微信支付完成，回调地址url方法  adopt_order_notify_url() */
	public function adopt_order_notify_url() {
		$post = file_get_contents('php://input');
		file_put_contents("5.txt", "post" . json_encode($post) . "\r\n", FILE_APPEND);
		$post_data = $this->xml_to_array($post); //微信支付成功，返回回调地址url的数据：XML转数组Array
		$postSign = $post_data['sign'];
		unset($post_data['sign']);

		/* 微信官方提醒：
			     *  商户系统对于支付结果通知的内容一定要做【签名验证】,
			     *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
			     *  防止数据泄漏导致出现“假通知”，造成资金损失。
		*/
		ksort($post_data); // 对数据进行排序
		$str = $this->ToUrlParams($post_data); //对数组数据拼接成key=value字符串
		$user_sign = strtoupper(md5($post_data)); //再次生成签名，与$postSign比较

		//$order_status = $this->PayOrder->query("select * from pay_orders where id = {$post_data['out_trade_no']}");
		$order_id = end(explode('-', $post_data['out_trade_no']));
		$order_info = M('adopt_order')->where(array('deleted' => 0, 'id' => $order_id))->find();
		if ($post_data['return_code'] == 'SUCCESS' && $postSign) {
			/*
				         * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
				         * 其次，订单已经为ok的，直接返回SUCCESS
				         * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
			*/
			if ($order_info['status'] != 0) {
				$this->return_success();
			} else {
				//有没有正在领养的宠物
				$map = array(
					'user_id' => $order_info['user_id'],
					'deleted' => 0,
				);
				$user_adopt = M('adopt_log')->where($map)->find();
				if ($user_adopt) {
					$adopt_config_map = array(
						'deleted' => 0,
						'adopt_config' => $user_adopt['adopt_config_id'],
					);
					$adopt_config = M('adopt_config')->where($adopt_config_map)->find();
					if ($adopt_config) {
						if ($user_adopt['adopt_val'] < $adopt_config['adopt_val']) {
							$temp = $adopt_config['adopt_val'] - $user_adopt['adopt_val'];
							if ($temp > $order_info['adopt_val']) {
//加上登陆赠送领养值  未满所需领养值
								$adopt_val_log_data = array(
									'add_time' => date('Y-m-d H:i:s'),
									'deleted' => 0,
									'user_id' => $order_info['user_id'],
									'adopt_val' => $order_info['adopt_val'],
									'adopt_config_id' => $user_adopt['adopt_config_id'],
									'adopt_val_config_id' => 9,
								);
								$res = M('adopt_val_log')->add($adopt_val_log_data); //增加领养值增长记录
								if ($res) {
									$adopt_log_data = array(
										'id' => $user_adopt['id'],
										'adopt_val' => $user_adopt['adopt_val'] + $order_info['adopt_val'],
									);
									$res = M('adopt_log')->save($adopt_log_data);
								}
							} else {
//加上登陆赠送领养值  领养值满足领养条件
								$adopt_val_log_data = array(
									'add_time' => date('Y-m-d H:i:s'),
									'deleted' => 0,
									'user_id' => $order_info['user_id'],
									'adopt_val' => $order_info['adopt_val'],
									'adopt_config_id' => $user_adopt['adopt_config_id'],
									'adopt_val_config_id' => 9,
								);
								$res = M('adopt_val_log')->add($adopt_val_log_data); //增加领养值增长记录
								if ($res) {
									$lottery_config = M('lottery_config')->where(array('id' => $adopt_config['lottery_config_id']))->find();
									//增加娃娃
									$data = array(
										'lottery_type_id' => $lottery_config['lottery_type_id'],
										'lottery_config_id' => $adopt_config['lottery_config_id'],
										'user_id' => $order_info['user_id'],
										'lottery_good_id' => $adopt_config['lottery_good_id'],
										'add_time' => date('Y-m-d H:i:s', time(0)),
										'deleted' => 0,
										'type' => -4, //领养活动
									);
									$res = M('lottery_record')->add($data);
									if ($res) {
										$adopt_log_data = array(
											'id' => $user_adopt['id'],
											'deleted' => 1,
										);
										$res = M('adopt_log')->save($adopt_log_data);
									}
								}
							}
						}
					}
				}
				$data = array(
					'id' => $order_id,
					'status' => 1,
				);
				$res = M('adopt_order')->save($data);
				$user_info = M('user')->where(array('id' => $order_info['user_id']))->find();
				$data = array(
					'id' => $user_info['id'],
					'charge_num' => $user_info['charge_num'] + $order_info['money'],
				);
				$res = M('user')->save($data);
				$this->return_success();
			}
		} else {
			$this->_err_ret('微信支付失败');
		}
	}
	/* 微信支付完成，回调地址url方法  fruit_order_notify_url() */
	public function fruit_order_notify_url() {
		$post = file_get_contents('php://input');
		file_put_contents("6.txt", "post" . json_encode($post) . "\r\n", FILE_APPEND);
		$post_data = $this->xml_to_array($post); //微信支付成功，返回回调地址url的数据：XML转数组Array
		$postSign = $post_data['sign'];
		unset($post_data['sign']);

		/* 微信官方提醒：
			     *  商户系统对于支付结果通知的内容一定要做【签名验证】,
			     *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
			     *  防止数据泄漏导致出现“假通知”，造成资金损失。
		*/
		ksort($post_data); // 对数据进行排序
		$str = $this->ToUrlParams($post_data); //对数组数据拼接成key=value字符串
		$user_sign = strtoupper(md5($post_data)); //再次生成签名，与$postSign比较

		//$order_status = $this->PayOrder->query("select * from pay_orders where id = {$post_data['out_trade_no']}");
		$order_id = end(explode('-', $post_data['out_trade_no']));
		$order_info = M('fruit_order')->where(array('deleted' => 0, 'id' => $order_id))->find();
		if ($post_data['return_code'] == 'SUCCESS' && $postSign) {
			/*
				         * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
				         * 其次，订单已经为ok的，直接返回SUCCESS
				         * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
			*/
			if ($order_info['status'] != 0) {
				$this->return_success();
			} else {
				$user = M('user')->where(array('id' => $order_info['user_id']))->find();
				$this->add_fruit_lottery($user, 6);
				$this->return_success();
			}
		} else {
			$this->_err_ret('微信支付失败');
		}
	}
	public function send_user_coin_pay_msg($user, $coin_num, $pay_money) {
		$openId = $user['openid'];
		/*$openId = 'oKFOO1iGQTFOkFXobxHL0pGk3ai0';
			$coin_num = 100;
		*/
		$postData = array(
			"touser" => $openId,
			"template_id" => C("PAY_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/vip_charge",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，尊敬的" . $user['nickname'] . "您充值的" . $coin_num . "个糖豆已到账！开启愉快的抓娃娃之旅吧",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => $user['id'],
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => $coin_num . "个糖豆",
				),
				"keyword3" => array(
					"value" => $pay_money . "元",
				),
				"remark" => array(
					"value" => "充值会员每月还可享受不够" . C("FREE_POST_WAWA_COUNT") . "个娃娃免费包邮哦~机会有限！点击立即充会员！",
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
			'user_id' => $user['id'],
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

	public function send_user_vip_pay_msg($user, $grade, $order_info) {
		$openId = $user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("PAY_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，尊敬的" . $user['nickname'] . ",您已成为尊贵的" . $grade['name'] . "，开启愉快的抓娃娃之旅吧~",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => $user['id'],
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => $order_info['day_num'] . '天' . $grade['name'],
				),
				"keyword3" => array(
					"value" => $order_info['money'] . "元",
				),
				"remark" => array(
					"value" => "您的" . $grade['name'] . "到期时间为" . substr($order_info['over_time'], 0, 10) . '，会员可享受每月不够' . C('FREE_POST_WAWA_COUNT') . '个娃娃免费包邮机会，记得及时使用哦~',
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
			'user_id' => $user['id'],
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
	public function send_new_user_gonglve_msg($openid) {
		$postData = array(
			"touser" => $openid,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/article/id/6.html",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "亲，恭喜您发现了新的大陆，为了让您更好的抓到娃娃，简单了解下新手教程吧！点击立即查看！",
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
	public function send_new_user_gift_msg($openid) {
		$id = 5;
		$map = array(
			'id' => $id,
			'deleted' => 0,
		);
		$amountConfigModel = D('amount_config');
		$amount_config = $amountConfigModel->where($map)->find();
		if (!$amount_config) {
			return;
		}
		$res = $this->send_user_free_coin_msg($openid, "您的免费" . $amount_config['config_num'] . "个糖豆已到账！速速点击领取吧~", $amount_config['config_num'], "https://fssw.bichonfrise.cn/index.php/Wechat/Index/activity/id/" . $id . ".html");
		return $res;
	}
	/**
	 * qrcode_url 微信二维码扫描  回调连接
	 * @param unknown $data
	 */
	public function qrcode_url() {
		$post = file_get_contents('php://input');
		$post_data = $this->xml_to_array($post);
		file_put_contents("2.txt", "post" . json_encode($post_data) . "\r\n", FILE_APPEND);
		$fromUserName = $post_data['FromUserName'];
		$toUserName = $post_data['ToUserName'];
		$event = $post_data['Event'];
		$msgtype = $post_data['MsgType'];
		if ($msgtype == 'event') {
			if ($event == 'subscribe') {
				// 给用户发送糖豆领取的消息
				//$this->send_new_user_gift_msg($fromUserName);
				//$this->send_new_user_gonglve_msg($fromUserName);
				//推送引导事件
				$wechat_msg_config_map = array(
					'id' => 1,
				);
				$wechat_msg_config = M('wechat_msg_index')->where($wechat_msg_config_map)->find();
				if ($wechat_msg_config['is_open'] == 1) {
					$appId = "";
					$appSecret = "";
					$res = $this->getAccessToken($appId, $appSecret);
					$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $res;

					$data = '{
                        "touser":"' . $fromUserName . '",
                        "msgtype":"text",
                        "text":
                        {
                             "content":"' . $wechat_msg_config['content'] . '"
                        }
                    }';
					$res = $this->http_request($url, $data);
				}
				//关注事件
				$map = array(
					'openid' => $fromUserName,
				);
				$user = M('user')->where($map)->find();
				$pid = $post_data['EventKey'];
				if ($user) {
					if (empty($pid)) {
						$map = array(
							'openid' => $fromUserName,
						);
						$data = array(
							'is_subscribe' => 1,
						);
						M('user')->where($map)->save($data);
					} else {
						$pid_str = ltrim($pid, 'qrscene_');
						$pid_arr = explode('_', $pid_str);
						$pid = $pid_arr[0];
						if (count($pid_arr) > 1 && $pid_arr[1] == 'merchant') {
							if ($user['merchant_id'] == 0 && $user['p_id'] == 0) {
								$map = array(
									'openid' => $fromUserName,
								);
								$data = array(
									'is_subscribe' => 1,
									'merchant_id' => $pid,
								);
								M('user')->where($map)->save($data);
							} else {
								$map = array(
									'openid' => $fromUserName,
								);
								$data = array(
									'is_subscribe' => 1,
								);
								M('user')->where($map)->save($data);
							}
						} else {
							if ($user['p_id'] == 0 && $user['merchant_id'] == 0) {
								$map = array(
									'openid' => $fromUserName,
								);
								$data = array(
									'is_subscribe' => 1,
									'p_id' => $pid,
								);
								M('user')->where($map)->save($data);
								if ($pid) {
									$map = array(
										'id' => $pid,
									);
									$p_user = M('user')->where($map)->find($map);
									$map_admin = array(
										'openid' => 'oKFOO1hc7P90MGMU39zaIgYmJh0k',
									);
									$admin_user = M('user')->where($map_admin)->find();
									$this->send_admin_invitation_msg_ceshi($user, $p_user, $admin_user);
								}
							} else {
								$map = array(
									'openid' => $fromUserName,
								);
								$data = array(
									'is_subscribe' => 1,
								);
								M('user')->where($map)->save($data);
							}
						}

					}

				} else {
					if (empty($pid)) {
						$data = array(
							'is_subscribe' => 1,
							'openid' => $fromUserName,
							'add_time' => date('Y-m-d H:i:s'),
						);
						M('user')->add($data);
					} else {
						$pid_str = ltrim($pid, 'qrscene_');
						$pid_arr = explode('_', $pid_str);
						$pid = $pid_arr[0];
						$data = array(
							'is_subscribe' => 1,
							'openid' => $fromUserName,
							'add_time' => date('Y-m-d H:i:s'),
						);
						if (count($pid_arr) > 1 && $pid_arr[1] == 'merchant') {
							$data['merchant_id'] = $pid;
						} else {
							$data['p_id'] = $pid;
							if ($pid) {
								$map = array(
									'id' => $pid,
								);
								$p_user = M('user')->where($map)->find($map);
								$map_admin = array(
									'openid' => 'oKFOO1hc7P90MGMU39zaIgYmJh0k',
								);
								$admin_user = M('user')->where($map_admin)->find();
								$this->send_admin_invitation_msg_ceshi($data, $p_user, $admin_user);
							}
						}
						M('user')->add($data);
					}
				}
			}
			if ($event == 'unsubscribe') {
				//取消关注
				$map = array(
					'openid' => $fromUserName,
				);
				$data = array(
					'is_subscribe' => 0,
				);
				M('user')->where($map)->save($data);
			}
		}
		if ($msgtype == 'text') {
			$content = $post_data['Content'];
			$map = array(
				'deleted' => 0,
				//'msg' => array('LIKE', "%" . $content . "%"),
			);
			$wechat_callback_msgs = M('wechat_callback_msg')->where($map)->select();
			$target_index = -1;
			foreach ($wechat_callback_msgs as $key => $value) {
				if (strpos($content, $value['msg']) !== false) {
					$target_index = $key;
					break;
				}
			}
			if ($target_index != -1) {
				$res = $wechat_callback_msgs[$target_index];
				$this->return_success_msg($fromUserName, $toUserName, $res['callback_msg']);
			} else {
				$content = C('WECHAT_DEFAULT_RESP');
				$this->return_success_msg($fromUserName, $toUserName, $content);
			}
		}
		/*if (!isset($_GET['echostr'])) {
			 //$wechatObj->某个function();    //后续的有实质功能的function(此篇不用管）
			 echo '验证失败';exit();
			 }else{
			 $this->valid();    //调用valid函数进行基本配置
		*/
	}

	/*
		 * 给微信发送确认订单金额和签名正确，SUCCESS信息 -xzz0521
	*/
	public function return_success_msg($open_id, $f_UserName, $content) {
		//<xml> <ToUserName>< ![CDATA[toUser] ]></ToUserName> <FromUserName>< ![CDATA[fromUser] ]></FromUserName>
		//<CreateTime>12345678</CreateTime> <MsgType>< ![CDATA[text] ]></MsgType> <Content>< ![CDATA[你好] ]></Content>
		//</xml>
		$textTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		</xml>";
		$resultStr = sprintf($textTpl, $open_id, $f_UserName, time(), 'text', $content);
		//file_put_contents("4.txt", "post" . $resultStr . "\r\n", FILE_APPEND);
		echo $resultStr;exit;
	}
	public function valid() {
		//用于基本配置的函数
		$echoStr = $_GET["echostr"];
		if ($this->checkSignature()) {
			echo $echoStr;
			exit;
		}
	}
	private function checkSignature() {
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$token = 'cMMt2pnz';
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);

		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}
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

	public function _err_ret($msg = 'sys err!') {
		$res = array(
			'code' => -6000,
			'msg' => $msg,
		);
		$this->ajaxReturn($res);
	}

	public function send_sms($tel, $content) {
		return true;
	}
	/**
	 * [ajax_get_guanzhu_data 获取关注信息]
	 * @return [type] [description]
	 */
	public function ajax_get_guanzhu_data() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$merchant_id = session('merchant_id');
		if (!$merchant_id) {
			exit;
		}
		$sysConfigModel = D('sys_config');
		$logo_img = $sysConfigModel->findByTypeAndMerchantId(6, $merchant_id);
		$title = $sysConfigModel->findByTypeAndMerchantId(9, $merchant_id);
		$info = $sysConfigModel->findByTypeAndMerchantId(10, $merchant_id);
		$qrcode = $sysConfigModel->findByTypeAndMerchantId(11, $merchant_id);
		$data = array(
			'merchant_id' => $merchant_id,
			'qrcode' => $qrcode['textval'],
			'title' => $title['strval'],
			'info' => $info['strval'],
			'logo_img' => $logo_img['textval'],
		);
		$this->_suc_ret($data);

	}
	////////////微信分享接口获取参数////////////
	public function ajax_get_wx_share_package() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$url = I('url');
		if ($url == "") {
			exit;
		}
		//$this->_err_ret($url);
		$jsapiTicket = $this->getJsApiTicket();
		$timestamp = time();
		$nonceStr = $this->createNonceStr();
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=" . html_entity_decode($url);
		$signature = sha1($string);

		$imgUrl = "https://fssw.bichonfrise.cn/Public/weixin/image/logo.png";

		if($url == 'https://fssw.bichonfrise.cn/index.php/Wechat/Vip/search.html'){
			$signPackage = array(
				"appId" => "",
				"nonceStr" => $nonceStr,
				"timestamp" => $timestamp,
				"url" => 'https://fssw.bichonfrise.cn/index.php/Wechat/Vip/search'. '/user_id/' . $user['id'],///*rtrim($url, '.html')*/$url . '/user_id/' . $user['id'], //rtrim($url, '.html').'/user_id/'.$user['id'],
				"signature" => $signature,
				"rawString" => $string,
				'imgUrl' => $imgUrl,
				'desc' => "史上最好抓的娃娃，2个起包邮，而且都是质量很好的娃娃哦～3次连续抓一个娃娃，抓不到就送！",
			);
		}else{
			$signPackage = array(
				"appId" => "",
				"nonceStr" => $nonceStr,
				"timestamp" => $timestamp,
				"url" => rtrim($url, '.html'). '/user_id/' . $user['id'],///*rtrim($url, '.html')*/$url . '/user_id/' . $user['id'], //rtrim($url, '.html').'/user_id/'.$user['id'],
				"signature" => $signature,
				"rawString" => $string,
				'imgUrl' => $imgUrl,
				'desc' => "史上最好抓的娃娃，2个起包邮，而且都是质量很好的娃娃哦～3次连续抓一个娃娃，抓不到就送！",
			);
		}
		$data = array(
			'signPackage' => $signPackage,
		);
		$this->_suc_ret($data);
	}
	public function ajax_get_wx_share_fruit() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$url = I('url');
		if ($url == "") {
			exit;
		}
		//$this->_err_ret($url);
		$jsapiTicket = $this->getJsApiTicket();
		$timestamp = time();
		$nonceStr = $this->createNonceStr();
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=" . html_entity_decode($url);
		$signature = sha1($string);

		$imgUrl = "https://fssw.bichonfrise.cn/Public/weixin/image/guoyuan/share_tree.png";

		$signPackage = array(
			"appId" => "",
			"nonceStr" => $nonceStr,
			"timestamp" => $timestamp,
			"url" => rtrim($url, '.html') . '/user_id/' . $user['id'], //rtrim($url, '.html').'/user_id/'.$user['id'],
			"signature" => $signature,
			"rawString" => $string,
			'imgUrl' => $imgUrl,
			'desc' => "【送你一箱水果】亲手种水果，包邮送到家",
		);
		$data = array(
			'signPackage' => $signPackage,
		);
		$this->_suc_ret($data);
	}

	public function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	public function getJsApiTicket() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$merchant_id = $user['merchant_id'];
		$appId = "";
		$appSecret = "";
		$accessToken = $this->getAccessToken($appId, $appSecret);
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
		$res = json_decode($this->httpGet($url));
		$ticket = $res->ticket;
		return $ticket;
	}
	public function getAccessToken($appId, $appSecret) {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
		$res = json_decode($this->httpGet($url));
		$access_token = $res->access_token;
		return $access_token;
	}
	/**
	 * [getWechatLoginAccessToken 微信登录获取access_token]
	 * @param  [type] $appId     [description]
	 * @param  [type] $appSecret [description]
	 * @param  [type] $code      [description]
	 * @return [type]            [description]
	 */
	public function getWechatLoginAccessToken($appId, $appSecret, $code) {
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appId&secret=$appSecret&code=$code&grant_type=authorization_code";
		$res = $this->httpGet($url);
		$res = json_decode($res);
		$access_token = $res->access_token;
		$openid = $res->openid;
		return array($access_token, $openid);

	}
	/**
	 * [getWechatLoginUserInfo 微信登录获取用户信息]
	 * @param  [type] $access_token [description]
	 * @param  [type] $openid       [description]
	 * @return [type]               [description]
	 */
	public function getWechatLoginUserInfo($access_token, $openid) {
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=openid&lang=zh_CN";
		$res = $this->httpGet($url);
		$res = json_decode($res);
		return $res;
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
	////////////微信分享接口获取参数////////////
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
	public function generateShareImage($user_id, $name) {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$merchant_id = session('merchant_id');
		if (!$merchant_id) {
			return;
		}
		// 生成二维码
		$qr_data = "https://" . $_SERVER['HTTP_HOST'] . "/index.php/Wechat/Index/index.html?rid=" . base64_encode($user_id) . "&merchant_id=" . $merchant_id;

		vendor('PHPQRcode.PHPQRcode');
		\QRcode::png($qr_data, C('QRCODE_DIR') . "/" . sha1($user_id) . "_" . sha1($merchant_id) . ".png", 'H', 6, 2);
		// 合成分享的二维码
		$sysConfig = D('sys_config');
		$sys_config = $sysConfig->findByTypeAndMerchantId(3, $merchant_id);
		$share_bg_image = $sys_config['textval'];
		$share_bg_image = explode($_SERVER['HTTP_HOST'], $share_bg_image);
		$share_bg_image = '.' . $share_bg_image[1];
		$qr_code = C('QRCODE_DIR') . "/" . sha1($user_id) . "_" . sha1($merchant_id) . ".png";
		$share_image = C('QRCODE_DIR') . "/share_" . sha1($user_id) . "_" . sha1($merchant_id) . ".png";
		$imageApi = new \Think\Image();
		$x = 100;
		for ($i = 0; $i < (4 - mb_strlen($name)); $i++) {
			$x += 83;
		}
		$imageApi->open($share_bg_image)->text($name, './font/pingfang.ttf', 73, '#9D2DFD', array($x, 730));
		$imageApi->save($share_image, 'jpg', 100);
		$imageApi->open($share_image)->text($name, './font/pingfang.ttf', 73, '#9D2DFD', array($x, 730));
		$imageApi->open($share_image)->water($qr_code, array(402.5, 277), 100)->save($share_image);
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
			$data = array(
				'path' => 'https://' . $_SERVER['SERVER_NAME'] . '/Uploads/' . $info['savepath'] . $info['savename'],
			);
			$this->_suc_ret($data);
		}
	}
	/**
	 * 生成带LOGO的二维码
	 */
	function makecode($qrcode_path, $content, $matrixPointSize, $matrixMarginSize, $errorCorrectionLevel, $url) {
		/**     参数详情：
		 *      $qrcode_path:logo地址
		 *      $content:需要生成二维码的内容
		 *      $matrixPointSize:二维码尺寸大小
		 *      $matrixMarginSize:生成二维码的边距
		 *      $errorCorrectionLevel:容错级别
		 *      $url:生成的带logo的二维码地址
		 * */
		ob_clean();
		Vendor('PHPQRcode.PHPQRcode');
		$object = new \QRcode();
		$qrcode_path_new = C('QRCODE_DIR') . "/recommend_" . sha1($recommend_id) . ".png"; //定义生成二维码的路径及名称
		$object::png($content, $qrcode_path_new, $errorCorrectionLevel, $matrixPointSize, $matrixMarginSize);
		$QR = imagecreatefromstring(file_get_contents($qrcode_path_new)); //imagecreatefromstring:创建一个图像资源从字符串中的图像流
		$logo = imagecreatefromstring(file_get_contents($qrcode_path));
		$QR_width = imagesx($QR); // 获取图像宽度函数
		$QR_height = imagesy($QR); //获取图像高度函数
		$logo_width = imagesx($logo); // 获取图像宽度函数
		$logo_height = imagesy($logo); //获取图像高度函数
		$logo_qr_width = $QR_width / 4; //logo的宽度
		$scale = $logo_width / $logo_qr_width; //计算比例
		$logo_qr_height = $logo_height / $scale; //计算logo高度
		$from_width = ($QR_width - $logo_qr_width) / 2; //规定logo的坐标位置
		imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
		/**     imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
		 *      参数详情：
		 *      $dst_image:目标图象连接资源。
		 *      $src_image:源图象连接资源。
		 *      $dst_x:目标 X 坐标点。
		 *      $dst_y:目标 Y 坐标点。
		 *      $src_x:源的 X 坐标点。
		 *      $src_y:源的 Y 坐标点。
		 *      $dst_w:目标宽度。
		 *      $dst_h:目标高度。
		 *      $src_w:源图象的宽度。
		 *      $src_h:源图象的高度。
		 * */
		Header("Content-type: image/png");
		//$url:定义生成带logo的二维码的地址及名称
		imagepng($QR, $url);
	}
	public function get_free_post_notice($user) {
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
			'status' => 0,
		);
		$lotteryRecordModel = D('lottery_record');
		$user_not_post_goods = $lotteryRecordModel->where($map)->select();
		if (C('FREE_POST_WAWA_COUNT') - count($user_not_post_goods) - 1 > 0) {
			return "还差" . (C('FREE_POST_WAWA_COUNT') - count($user_not_post_goods)) . "个娃娃，我们会尽快为您免费快递到家了！";
		} else {
			return "亲，快去背包填写收货地址，我们会尽快为您免费快递到家了！";
		}
	}
	public function send_zhuawawa_inner_msg($user, $good_name, $game_name) {
		$qixian = C('FREE_USER_SAVE_DAYS');
		if ($user['charge_num'] > 0) {
			$qixian = C('PAY_USER_SAVE_DAYS');
		}
		$openId = $user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您运气爆棚！抓到了" . $game_name . "里的" . $good_name . "已为您存入背包！" . $freePostNotice,
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => "免费" . $game_name . $good_name . "1个",
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => $qixian . "天",
				),
				"remark" => array(
					"value" => $this->get_free_post_notice($user) . "点我继续抓娃娃！",
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
		return $res;

	}
	public function send_zhuawawa_inner_msg_admin($admin_user, $lottery_config, $is_hit, $user, $lottery_good_name) {

		$qixian = C('FREE_USER_SAVE_DAYS');
		if ($user['charge_num'] > 0) {
			$qixian = C('PAY_USER_SAVE_DAYS');
		}
		$openId = $admin_user['openid'];
		if ($is_hit == 0) {
			$hit_str = '没抓中~';
		}
		if ($is_hit == 1) {
			$hit_str = '抓中了' . $lottery_good_name . "!";
		}
		$postData = array(
			"touser" => $openId,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "用户" . $user['nickname'] . "在" . $lottery_config['name'] . "娃娃机上抓到了" . $hit_str . "!",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => "免费" . $lottery_config['name'] . $lottery_good_name . "1个~",
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => $qixian . "天",
				),
				"remark" => array(
					"value" => $this->get_free_post_notice($admin_user) . "点我继续抓娃娃！",
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
	//测试二维码消息
	public function send_zhuawawa_inner_msg_admin_ceshi($admin_user, $pid) {
		$openId = $admin_user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "二维码所带用户ID：" . $pid,
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => "",
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => "",
				),
				"remark" => array(
					"value" => $this->get_free_post_notice($admin_user) . "点我继续抓娃娃！",
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
	//信用卡管理员通知
	public function send_xinyongka_msg_admin($admin_user, $data, $user) {
		$openId = $admin_user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "用户" . $user['nickname'] . "提交了信用卡信息，公司名称：" . $data['company_name'] . ",公司电话：" . $data['company_tel'] . ",用户姓名：" . $data['real_name'] . ",用户电话：" . $data['tel'],
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => "信用卡信息",
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => "无限期",
				),
				"remark" => array(
					"value" => $this->get_free_post_notice($admin_user) . "点我继续抓娃娃！",
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
	//邀请好友赠送糖豆
	public function send_give_coin_msg($user) {
		$openId = $user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/charge.html",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，尊敬的" . $user['nickname'] . ",您通过邀请好友获得的" . C('ADD_USER_GET_COIN') . "个免费糖豆已到账！糖豆余额：" . $user['coin_num'] . '个！',
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => C('ADD_USER_GET_COIN') . "个免费糖豆",
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => "无限期",
				),
				"remark" => array(
					"value" => "邀请的好友越多，赚取的糖豆和现金越多哦，快去邀请好友吧~点我查看如何邀请好友！",
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
		return $res;

	}
	// 给管理员发送邀请消息
	public function send_admin_invitation_msg($user, $p_user) {
		$openId = $p_user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("NOTICE_SEND_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Agent/users.html",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，" . $p_user['nickname'] . "邀请的好友" . $user['nickname'] . "已加入抓娃娃！",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => $user['nickname'],
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => $p_user['nickname'],
				),
				"remark" => array(
					"value" => "邀请的好友越多，赚取的糖豆和现金越多哦，快起邀请好友吧~点我查看已邀请好友！",
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
			'user_id' => $p_user['id'],
			'template_id' => C("NOTICE_SEND_TMPL_ID"),
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
	// 给管理员发送邀请消息
	public function send_admin_invitation_msg_ceshi($user, $p_user, $admin) {
		$openId = $admin['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("NOTICE_SEND_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Agent/users.html",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，" . $p_user['nickname'] . "邀请的好友" . $user['nickname'] . "已加入抓娃娃！",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => $user['nickname'],
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => $p_user['nickname'],
				),
				"remark" => array(
					"value" => "邀请的好友越多，赚取的糖豆和现金越多哦，快起邀请好友吧~点我查看已邀请好友！",
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
			'user_id' => $p_user['id'],
			'template_id' => C("NOTICE_SEND_TMPL_ID"),
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
	//邀请好友，通知邀请人
	public function send_invitation_msg($user, $p_user) {
		$openId = $p_user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("NOTICE_SEND_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Agent/users.html",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，尊敬的" . $p_user['nickname'] . "，您邀请的好友" . $user['nickname'] . "已加入抓娃娃！",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => $user['nickname'],
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => $p_user['nickname'],
				),
				"remark" => array(
					"value" => "邀请的好友越多，赚取的糖豆和现金越多哦，快起邀请好友吧~点我查看已邀请好友！",
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
			'user_id' => $p_user['id'],
			'template_id' => C("NOTICE_SEND_TMPL_ID"),
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
	//抓到娃娃分佣
	public function send_lottery_msg($user, $p_user, $money) {
		$openId = $p_user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("LOTTERY_SEND_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/article/id/4.html",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，尊敬的" . $p_user['nickname'] . "，您邀请的好友" . $user['nickname'] . "抓到娃娃，您赚取的" . $money . "元现金已到账！",
					"color" => "#173177",
				),
				"date" => array(
					"value" => date('Y年m月d日'),
				),
				"adCharge" => array(
					"value" => "+" . $money . '元',
				),
				"cashBalance" => array(
					"value" => $p_user['yu_e'] . '元',
				),
				"remark" => array(
					"value" => "邀请的好友越多，赚取的糖豆和现金越多哦，快去邀请好友吧~点我查看如何邀请好友！",
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
			'user_id' => $p_user['id'],
			'template_id' => C("LOTTERY_SEND_TMPL_ID"),
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
	//充值VIP分佣
	public function send_vip_msg($user, $p_user, $vip_pay_config, $grade_name, $money) {
		$openId = $p_user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("LOTTERY_SEND_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/article/id/4.html",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，尊敬的" . $p_user['nickname'] . "，您邀请的好友" . $user['nickname'] . "充值" . $vip_pay_config['name'] . $grade_name . "，您赚取的" . $money . '元现金已到账！',
					"color" => "#173177",
				),
				"date" => array(
					"value" => date('Y年m月d日'),
				),
				"adCharge" => array(
					"value" => "+" . $money . '元',
				),
				"cashBalance" => array(
					"value" => $p_user['yu_e'] . '元',
				),
				"remark" => array(
					"value" => "邀请的好友越多，赚取的糖豆和现金越多哦，快起邀请好友吧~点我查看如何邀请好友！",
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
			'user_id' => $p_user['id'],
			'template_id' => C("LOTTERY_SEND_TMPL_ID"),
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
	public function send_user_free_coin_msg($openid, $notice, $amount, $url) {
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
					"value" => "免费糖豆" . $amount . "个",
					"color" => "#173177",
				),
				"keyword2" => array(
					"value" => date('Y年m月d日'),
				),
				"keyword3" => array(
					"value" => "无限期",
				),
				"remark" => array(
					"value" => "快来用糖豆抓娃娃，试试今天的手气吧~",
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

		return $res;
	}
	/**
	 * [send_user_vip_expire_msg 给用户发送会员过期消息]
	 * @param  [type] $user [description]
	 * @return [type]       [description]
	 */
	public function send_user_vip_expire_msg($user) {
		$name = "黄金会员";
		if ($user['level'] == 3) {
			$name = "钻石会员";
		}
		$postData = array(
			"touser" => $user['openid'],
			"template_id" => C("VIP_EXPIRE_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/vip_charge.html",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "尊敬的会员" . $user['nickname'] . "，您的会员权益明天到期。",
					"color" => "#173177",
				),
				"name" => array(
					"value" => $name,
				),
				"expDate" => array(
					"value" => date('Y年m月d日', strtotime($user['over_time'])),
				),
				"remark" => array(
					"value" => "点击续费，即可继续享受会员权益~",
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
				'template_id' => C("VIP_EXPIRE_TMPL_ID"),
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

		return $res;
	}
	/**
	 * [send_activity_inner_coin_msg 获取免费糖豆通知]
	 * @param  [type] $amount [description]
	 * @param  [type] $user   [description]
	 * @return [type]         [description]
	 */
	public function send_activity_inner_coin_msg($amount, $user) {
		$openId = $user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "恭喜您，您的免费" . $amount . "个糖豆已到账！祝您抓娃娃愉快~",
					"color" => "#173177",
				),
				"keyword1" => array(
					"value" => "免费糖豆" . $amount . "个",
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
		return $res;
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
	 * 发起http请求
	 * @param string $url 访问路径
	 * @param array $params 参数，该数组多于1个，表示为POST
	 * @param int $expire 请求超时时间
	 * @param array $extend 请求伪造包头参数
	 * @param string $hostIp HOST的地址
	 * @return array    返回的为一个请求状态，一个内容
	 */
	function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '') {
		if (empty($url)) {
			return array('code' => '100');
		}
		$_curl = curl_init();
		$_header = array(
			'Accept-Language: zh-CN',
			'Connection: Keep-Alive',
			'Cache-Control: no-cache',
		);
		// 方便直接访问要设置host的地址
		if (!empty($hostIp)) {
			$urlInfo = parse_url($url);
			if (empty($urlInfo['host'])) {
				$urlInfo['host'] = substr(DOMAIN, 7, -1);
				$url = "http://{$hostIp}{$url}";
			} else {
				$url = str_replace($urlInfo['host'], $hostIp, $url);
			}
			$_header[] = "Host: {$urlInfo['host']}";
		}
		// 只要第二个参数传了值之后，就是POST的
		if (!empty($params)) {
			curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
			curl_setopt($_curl, CURLOPT_POST, true);
		}
		if (substr($url, 0, 8) == 'https://') {
			curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		curl_setopt($_curl, CURLOPT_URL, $url);
		curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($_curl, CURLOPT_USERAGENT, 'API PHP CURL');
		curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);
		if ($expire > 0) {
			curl_setopt($_curl, CURLOPT_TIMEOUT, $expire); // 处理超时时间
			curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire); // 建立连接超时时间
		}
		// 额外的配置
		if (!empty($extend)) {
			curl_setopt_array($_curl, $extend);
		}
		$result['result'] = curl_exec($_curl);
		$result['code'] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
		$result['info'] = curl_getinfo($_curl);
		if ($result['result'] === false) {
			$result['result'] = curl_error($_curl);
			$result['code'] = -curl_errno($_curl);
		}
		curl_close($_curl);
		return $result;
	}

	/*****
		 * 水果种植
		 * $num 类型：1：绑定手机号（+20，每个用户只有一次），
		 *          2：签到（1天~6天 +10，7天~15天 +20，一天一次），
		 *          3：邀请好友浇水（+20，一天三次），
		 *          4：分享朋友圈（+20，一天一次），
		 *          5：定时奖励（12：00~14：00 +20，18：00~20：00 +20），
		 *          6：购买肥料（）
	*/
	public function add_fruit_lottery($user, $num, $from_user_id = 0) {
		//有没有正在领养的宠物
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$user_fruit = M('fruit_log')->where($map)->find();
		if (!$user_fruit) {
//没有正在种植的水果
			return $data = array(
				'msg' => '没有正在种植的水果',
				'status' => 1,
			);
		}
		if ($num == 1) {
			if ($user['phone'] != '') {
				$map = array(
					'deleted' => 0,
					'user_id' => $user['id'],
					'type' => 1,
				);
				$user_fruit_val = M('fruit_val_log')->where($map)->find();
				if ($user_fruit_val) {
					return $data = array(
						'msg' => '已经领取过了',
						'status' => 1,
					);
				} else {
					$add_fruit_val = 20;
				}
			}
		}
		if ($num == 2) {
//签到
			//今日有没有领取过
			$start_time = date('Y-m-d') . ' 00:00:00';
			$end_time = date('Y-m-d H:i:s');
			$map = array(
				'deleted' => 0,
				'user_id' => $user['id'],
				'type' => 2,
				'add_time' => array(
					array('egt', $start_time),
					array('elt', $end_time),
					'and',
				),
			);
			$user_fruit_val = M('fruit_val_log')->where($map)->find();
			if ($user_fruit_val) {
				return $data = array(
					'msg' => '今日已经领取过了',
					'status' => 1,
				);
			}
			$map = array(
				'deleted' => 0,
				'user_id' => $user['id'],
				'type' => 2,
			);
			$user_fruit_val = M('fruit_val_log')->where($map)->count();
			if (!$user_fruit_val) {
				$user_fruit_val = 0;
			}
			$times = $user_fruit_val % 15;
			if ($times < 7) {
				$add_fruit_val = 10;
			} else {
				$add_fruit_val = 20;
			}
		}
		if ($num == 3) {
//邀请好友浇水
			//今日浇过几次
			$start_time = date('Y-m-d') . ' 00:00:00';
			$end_time = date('Y-m-d H:i:s');
			$map = array(
				'deleted' => 0,
				'user_id' => $user['id'],
				'type' => 3,
				'add_time' => array(
					array('egt', $start_time),
					array('elt', $end_time),
					'and',
				),
			);
			$user_fruit_count = M('fruit_val_log')->where($map)->count();
			if (!$user_fruit_count) {
				$user_fruit_count = 0;
			}
			if ($user_fruit_count < 3) {
				$map = array(
					'deleted' => 0,
					'user_id' => $user['id'],
					'from_user_id' => $from_user_id,
					'add_time' => array(
						array('egt', $start_time),
						array('elt', $end_time),
						'and',
					),
				);
				$fruit_stroke = M('fruit_stroke')->where($map)->find();
				if ($fruit_stroke) {
					return $data = array(
						'msg' => '今日已经浇过水了',
						'status' => 1,
					);
				}
				$data = array(
					'deleted' => 0,
					'user_id' => $user['id'],
					'from_user_id' => $from_user_id,
					'add_time' => date('Y-m-d H:i:s'),
				);
				$temp = M('fruit_stroke')->add($data);
				$add_fruit_val = 20;
			} else {
				return $data = array(
					'msg' => '今日浇水次数已用尽',
					'status' => 1,
				);
			}
		}
		if ($num == 4) {
//分享朋友圈
			//今日有没有领取过
			$start_time = date('Y-m-d') . ' 00:00:00';
			$end_time = date('Y-m-d H:i:s');
			$map = array(
				'deleted' => 0,
				'user_id' => $user['id'],
				'type' => 4,
				'add_time' => array(
					array('egt', $start_time),
					array('elt', $end_time),
					'and',
				),
			);
			$user_fruit_val = M('fruit_val_log')->where($map)->find();
			if ($user_fruit_val) {
				return $data = array(
					'msg' => '今日已经领取过了',
					'status' => 1,
				);
			}
			$add_fruit_val = 20;
		}
		if ($num == 5) {
//定时奖励（12：00~14：00 +20，18：00~20：00 +20）
			$now_time = time();
			$start_time_noon = strtotime(date('Y-m-d') . ' 12:00:00');
			$end_time_noon = strtotime(date('Y-m-d') . ' 14:00:00');
			$start_time_night = strtotime(date('Y-m-d') . ' 18:00:00');
			$end_time_night = strtotime(date('Y-m-d') . ' 20:00:00');
			if (($now_time < $start_time_noon && $now_time > $end_time_noon)
				|| ($now_time < $start_time_night && $now_time > $end_time_night)) {
//不在活动时间内
				return $data = array(
					'msg' => '活动还未开始哦',
					'status' => 1,
				);
			}
			if ($now_time > $start_time_noon && $now_time < $end_time_noon) {
				$start_time = date('Y-m-d') . ' 12:00:00';
				$end_time = date('Y-m-d') . ' 14:00:00';
			}
			if ($now_time > $start_time_night && $now_time < $end_time_night) {
				$start_time = date('Y-m-d') . ' 18:00:00';
				$end_time = date('Y-m-d') . ' 20:00:00';
			}
			$map = array(
				'deleted' => 0,
				'user_id' => $user['id'],
				'type' => 5,
				'add_time' => array(
					array('egt', $start_time),
					array('elt', $end_time),
					'and',
				),
			);
			$user_fruit_val = M('fruit_val_log')->where($map)->find();
			if ($user_fruit_val) {
				return $data = array(
					'msg' => '今日已经领取过了',
					'status' => 1,
				);
			}
			$add_fruit_val = 20;
		}
		if ($num == 6) {
			$add_fruit_val = 500;
		}

		$fruit_config_map = array(
			'deleted' => 0,
			'id' => $user_fruit['fruit_config_id'],
		);
		$fruit_config = M('fruit_config')->where($fruit_config_map)->find();
		if (!$add_fruit_val) {
			return $data = array(
				'msg' => '服务器错误',
				'status' => 1,
			);
		}
		if ($fruit_config) {
			if ($user_fruit['fruit_val'] < $fruit_config['fruit_val']) {
				$temp = $fruit_config['fruit_val'] - $user_fruit['fruit_val'];
				if ($temp > $add_fruit_val) {
//加上登陆赠送领养值  未满所需领养值
					$fruit_val_log_data = array(
						'add_time' => date('Y-m-d H:i:s'),
						'deleted' => 0,
						'user_id' => $user['id'],
						'fruit_num' => $add_fruit_val,
						'fruit_config_id' => $user_fruit['fruit_config_id'],
						'type' => $num,
					);
					$res = M('fruit_val_log')->add($fruit_val_log_data); //增加领养值增长记录
					if ($res) {
						$fruit_log_data = array(
							'id' => $user_fruit['id'],
							'fruit_val' => $user_fruit['fruit_val'] + $add_fruit_val,
						);
						$res = M('fruit_log')->save($fruit_log_data);
					} else {
						return $data = array(
							'msg' => '领取失败，请稍后重试',
							'status' => 1,
						);
					}
				} else {
//加上登陆赠送领养值  领养值满足领养条件
					$fruit_val_log_data = array(
						'add_time' => date('Y-m-d H:i:s'),
						'deleted' => 0,
						'user_id' => $user['id'],
						'fruit_num' => $add_fruit_val,
						'fruit_config_id' => $user_fruit['fruit_config_id'],
						'type' => $num,
					);
					$res = M('fruit_val_log')->add($fruit_val_log_data); //增加领养值增长记录
					if ($res) {
						$lottery_config = M('lottery_config')->where(array('id' => $fruit_config['lottery_config_id']))->find();
						//增加娃娃
						$data = array(
							'lottery_type_id' => $lottery_config['lottery_type_id'],
							'lottery_config_id' => $fruit_config['lottery_config_id'],
							'user_id' => $user['id'],
							'lottery_good_id' => $fruit_config['lottery_good_id'],
							'add_time' => date('Y-m-d H:i:s', time(0)),
							'deleted' => 0,
							'type' => -6, //水果种植活动
						);
						$res = M('lottery_record')->add($data);
						if ($res) {
							$fruit_log_data = array(
								'id' => $user_fruit['id'],
								'deleted' => 1,
							);
							$res = M('fruit_log')->save($fruit_log_data);
						} else {
							return $data = array(
								'msg' => '领取失败，请稍后重试',
								'status' => 1,
							);
						}
					} else {
						return $data = array(
							'msg' => '领取失败，请稍后重试',
							'status' => 1,
						);
					}
				}
			} else {
				return $data = array(
					'msg' => '领取失败，请稍后重试',
					'status' => 1,
				);
			}
		} else {
			return $data = array(
				'msg' => '水果不存在',
				'status' => 1,
			);
		}
		return $data = array(
			'msg' => '领取成功',
			'status' => 0,
		);

	}
}