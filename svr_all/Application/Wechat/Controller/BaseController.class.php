<?php
namespace Wechat\Controller;
use Think\Controller;

class BaseController extends AppController {
	/**
	 * [_initialize 前置方法]
	 * @return [type] [description]
	 */
	public function _initialize() {
		if (session('?user')) {
			$user = session('user');
		} else {
			//微信授权登录
			$redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
			$redirect_url .= ($_SERVER['QUERY_STRING'] == "") ? "" : ('?' . $_SERVER['QUERY_STRING']);
			$appId = "";
			$appSecret = "3d917eabbf544b099d23bdd7190c108e";
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appId . "&redirect_uri=" . urlencode($redirect_url) . "&response_type=code&scope=snsapi_userinfo&merchant_id=" . $merchant_id . "#wechat_redirect";

			$code = I('code');
			if ($code == "") {
				redirect($url);
			} else {
				$res = $this->getWechatLoginAccessToken($appId, $appSecret, $code);
				$res = $this->getWechatLoginUserInfo($res[0], $res[1]);
				if ($res->errcode != 0) {
					redirect($url);exit;
				}
				$userModel = D('user');
				$map = array(
					'openid' => $res->openid,
				);
				$user_info = $userModel->where($map)->find();
				//新用户并且存在上级用户
// 				if($user_info && $user_info['nickname'] == '' && $user_info['headimgurl'] == '' && $user_info['login_source'] == 0 && $user_info['p_id'] != 0 && $user_info['ticket'] == '' && $user_info['is_subscribe'] == 1){
// 				    $to_user_map = array(
// 				        'id'=>$user_info['p_id'],
// 				        'deleted'=>0
// 				    );
// 				    $p_user = M('user')->where($to_user_map)->find();
// 				    if($p_user){//存在上级用户
// 				        //判断时间
// 				        $hour = date('H');
// 				        /*if($hour<=10 || ($hour<=14 && $hour>10) || ($hour<=22 && $hour>14) || $hour>21){
// 				            $this->_err_ret('活动还未开启！');
// 				        }*/
// 				        if($hour<=10 || $hour>22){
// 				            $start_time = date('Y-m-d H:i:s',strtotime(date('Y-m-d ').'22:00:00')-86400);
// 				            $end_time = date('Y-m-d ').'10:00:00';
// 				        }
// 				        if($hour<=13 && $hour>10){
// 				            $start_time = date('Y-m-d ').'10:00:00';
// 				            $end_time = date('Y-m-d ').'13:00:00';
// 				        }
// 				        if($hour<=22 && $hour>13){
// 				            $start_time = date('Y-m-d ').'13:00:00';
// 				            $end_time = date('Y-m-d ').'22:00:00';
// 				        }
// 				        $largess_times_map = array(
// 				            'to_user_id'=>$p_user['id'],
// 				            'deleted'=>0,
// 				            'add_time'=>array(
// 				                array('egt', $start_time),
// 				                array('elt', $end_time),
// 				                'and',
// 				            )
// 				        );
// 				        $largess_times = M('box_largess_log')->where($largess_times_map)->count();
// 				        if(!$largess_times){
// 				            $largess_times = 0;
// 				        }
// 				        if($largess_times<C('BOX_LARGESS_TIMES')){
// 				            $largess_data =array(
// 				                'add_time'=>date('Y-m-d H:i:s'),
// 				                'deleted'=>0,
// 				                'to_user_id'=>$p_user['id'],
// 				                'from_user_id'=>$user_info['id'],
// 				            );
// 				            $temp_res = M('box_largess_log')->add($largess_data);
// 				            if($temp_res){
// 				                $p_data = array(
// 				                    'id'=>$p_user['id'],
// 				                    'box_times'=>$p_user['box_times'] + 1,
// 				                );
// 				                $temp_res = M('user')->save($p_data);
// 				            }
// 				        }
// 				    }
// 				}
				$user = $userModel->addWechatUserNoExist($res);
				// 关注公众号进来的
				if ($user_info) {
					// 已经关注公众号 并且是扫别人二维码进来的没进入到网页
					if ($user_info['is_subscribe'] == 1 && $user_info['p_id'] != 0 && $user_info['ticket'] == '') {
						$p_map = array(
							'id' => $user_info['p_id'],
						);
						$p_info = M('user')->where($p_map)->find();
						if ($p_info) {
							$this->send_invitation_msg($user, $p_info);
							$map_admin = array(
								'openid' => 'oKFOO1hc7P90MGMU39zaIgYmJh0k',
							);
							$admin_user = $userModel->where($map_admin)->find();
							$this->send_admin_invitation_msg($user, $admin_user);
						}
					}
				}
				session('user', $user);
				$this->assign('user', $user);
				//$this->deduction_luck();
				
				//添加用户登录记录
// 				$user_login_log_data = array(
// 				    'deleted'=>0,
// 				    'id'=>$user['id'],
// 				    'add_time'=>date('Y-m-d H:i:s'),
// 				);
// 				$user_login_log = M('user_login_log')->add($user_login_log_data);
			}
		}
		//更新用户宝箱抽奖次数
		//$this->update_user_box_times();
// 		$start_time = date('Y-m-d').' 00:00:00';
// 		$end_time = date('Y-m-d').' 23:59:59';
		
// 		$user_login_log_map = array(
// 		    'user_id'=>$user['id'],
// 		    'add_time'=>array(
// 		        array('egt', $start_time),
// 		        array('elt', $end_time),
// 		        'and',
// 		    )
// 		);
// 		$user_login_log_res = M('user_login_log')->where($user_login_log_map)->find();
// 		if(!$user_login_log_res){
		    //30天清除娃娃 (领养)
		    $this->delete_user_adopt($user);
		    //每日登陆增加领养殖
		    $this->add_adopt_val($user);
		    //合成用户分享二维码
		    $this->build_qrcode();
// 		}
		//当天消费糖豆达300增加领养殖
		$this->add_adopt_consume($user);
		//6.成功抓到1只娃娃     成长值+50；7.成功抓到两只娃娃    成长值+100；
		$this->add_adopt_record($user);
		
	}
	/**
	 * delete_user_adopt  30天清除娃娃
	 */
	public function delete_user_adopt($user){
	    //有没有正在领养的宠物
	    $map = array(
	        'user_id'=>$user['id'],
	        'deleted'=>0,
	    );
	    $user_adopt = M('adopt_log')->where($map)->find();
	    if($user_adopt){
	        $time = strtotime($user_adopt['add_time']);
	        if((time() - $time) > (30 * 86400)){
	            $data = array(
	                'id'=>$user_adopt['id'],
	                'deleted'=>1,
	            );
	            M('adopt_log')->save($data);
	        }
	    }
	}
	/**
	 * add_adopt_record 6.成功抓到1只娃娃     成长值+50；7.成功抓到两只娃娃    成长值+100；
	 */
	public function add_adopt_record($user){
	    $start_time = date('Y-m-d').' 00:00:00';
	    $end_time = date('Y-m-d H:i:s');
	    $map = array(
	        'user_id'=>$user['id'],
	        'deleted'=>0,
	        'is_hit'=>1,
	        'add_time'=>array(
	            array('egt', $start_time),
	            array('elt', $end_time),
	            'and',
	        )
	    );
	    $res = M('luck_draw_log')->where($map)->select();
	    if(count($res) >= 1){
	        $this->add_adopt_lottery($user, 6);
	    }
	    if(count($res) >= 2){
	        $this->add_adopt_lottery($user, 7);
	    }
	}
	/**
	 * add_adopt_consume 当天消费糖豆达300增加领养殖
	 */
	public function add_adopt_consume($user){
	    $start_time = date('Y-m-d').' 00:00:00';
	    $end_time = date('Y-m-d H:i:s');
	    //今日消费是否满足300糖豆
	    $map = array(
	        'deleted'=>0,
	        'user_id'=>$user['id'],
	        'add_time'=>array(
	            array('egt', $start_time),
	            array('elt', $end_time),
	            'and',
	        )
	    );
	    $luck_draw_log = M('luck_draw_log')->where($map)->sum('consume_num');
	    $luck_draw_log_count = M('luck_draw_log')->where($map)->count();
	    if($luck_draw_log_count && $luck_draw_log_count >= 3){
	        $this->add_adopt_three($user);
	    }
	    if($luck_draw_log_count && $luck_draw_log_count >= 5){
	        $this->add_adopt_five($user);
	    }
	    if(!$luck_draw_log){
	        return;
	    }
	    if($luck_draw_log < 300){
	        return;
	    }
	    //今日有没有领取过
	    $map = array(
	        'deleted'=>0,
	        'user_id'=>$user['id'],
	        'adopt_val_config_id'=>8,
	        'add_time'=>array(
	            array('egt', $start_time),
	            array('elt', $end_time),
	            'and',
	        )
	    );
	    $user_adopt_val = M('adopt_val_log')->where($map)->find();
	    if($user_adopt_val){
	        return;
	    }
	    $adopt_val_config = M('adopt_val_config')->where(array('id'=>8))->find();
	    //有没有正在领养的宠物
	    $map = array(
	        'user_id'=>$user['id'],
	        'deleted'=>0,
	    );
	    $user_adopt = M('adopt_log')->where($map)->find();
	    if($user_adopt){
	        $adopt_config_map = array(
	            'deleted'=>0,
	            'adopt_config'=>$user_adopt['adopt_config_id'],
	        );
	        $adopt_config = M('adopt_config')->where($adopt_config_map)->find();
	        if($adopt_config){
	            if($user_adopt['adopt_val'] < $adopt_config['adopt_val']){
	                $temp = $adopt_config['adopt_val'] - $user_adopt['adopt_val'];
	                if($temp > $adopt_val_config['times_adopt_val']){//加上登陆赠送领养值  未满所需领养值
	                    $adopt_val_log_data = array(
	                        'add_time'=>date('Y-m-d H:i:s'),
	                        'deleted'=>0,
	                        'user_id'=>$user['id'],
	                        'adopt_val'=>$adopt_val_config['times_adopt_val'],
	                        'adopt_config_id'=>$user_adopt['adopt_config_id'],
	                        'adopt_val_config_id'=>8
	                    );
	                    $res = M('adopt_val_log')->add($adopt_val_log_data);//增加领养值增长记录
	                    if($res){
	                        $adopt_log_data = array(
	                            'id'=>$user_adopt['id'],
	                            'adopt_val'=>$user_adopt['adopt_val'] + $adopt_val_config['times_adopt_val']
	                        );
	                        $res = M('adopt_log')->save($adopt_log_data);
	                    }
	                }else{//加上登陆赠送领养值  领养值满足领养条件
	                    $adopt_val_log_data = array(
	                        'add_time'=>date('Y-m-d H:i:s'),
	                        'deleted'=>0,
	                        'user_id'=>$user['id'],
	                        'adopt_val'=>$adopt_val_config['times_adopt_val'],
	                        'adopt_config_id'=>$user_adopt['adopt_config_id'],
	                        'adopt_val_config_id'=>8
	                    );
	                    $res = M('adopt_val_log')->add($adopt_val_log_data);//增加领养值增长记录
	                    if($res){
	                        $lottery_config = M('lottery_config')->where(array('id'=>$adopt_config['lottery_config_id']))->find();
	                        //增加娃娃
	                        $data = array(
	                            'lottery_type_id' => $lottery_config['lottery_type_id'],
	                            'lottery_config_id' => $adopt_config['lottery_config_id'],
	                            'user_id' => $user['id'],
	                            'lottery_good_id' => $adopt_config['lottery_good_id'],
	                            'add_time' => date('Y-m-d H:i:s', time(0)),
	                            'deleted' => 0,
	                            'type' => -4, //领养活动
	                        );
	                        $res = M('lottery_record')->add($data);
	                        if($res){
	                            $adopt_log_data = array(
	                                'id'=>$user_adopt['id'],
	                                'deleted'=>1
	                            );
	                            $res = M('adopt_log')->save($adopt_log_data);
	                        }
	                    }
	                }
	            }
	        }
	    }
	}
	/**
	 * add_adopt_lottery 3.开始1局任意游戏     成长值+5；6.成功抓到1只娃娃     成长值+50；7.成功抓到两只娃娃    成长值+100；
	 */
	public function add_adopt_lottery($user,$num){
	    //今日有没有领取过
	    $start_time = date('Y-m-d').' 00:00:00';
	    $end_time = date('Y-m-d H:i:s');
	    $map = array(
	        'deleted'=>0,
	        'user_id'=>$user['id'],
	        'adopt_val_config_id'=>$num,
	        'add_time'=>array(
	            array('egt', $start_time),
	            array('elt', $end_time),
	            'and',
	        )
	    );
	    $user_adopt_val = M('adopt_val_log')->where($map)->find();
	    if($user_adopt_val){
	        return;
	    }
	    $adopt_val_config = M('adopt_val_config')->where(array('id'=>$num))->find();
	    //有没有正在领养的宠物
	    $map = array(
	        'user_id'=>$user['id'],
	        'deleted'=>0,
	    );
	    $user_adopt = M('adopt_log')->where($map)->find();
	    if($user_adopt){
	        $adopt_config_map = array(
	            'deleted'=>0,
	            'adopt_config'=>$user_adopt['adopt_config_id'],
	        );
	        $adopt_config = M('adopt_config')->where($adopt_config_map)->find();
	        if($adopt_config){
	            if($user_adopt['adopt_val'] < $adopt_config['adopt_val']){
	                $temp = $adopt_config['adopt_val'] - $user_adopt['adopt_val'];
	                if($temp > $adopt_val_config['times_adopt_val']){//加上登陆赠送领养值  未满所需领养值
	                    $adopt_val_log_data = array(
	                        'add_time'=>date('Y-m-d H:i:s'),
	                        'deleted'=>0,
	                        'user_id'=>$user['id'],
	                        'adopt_val'=>$adopt_val_config['times_adopt_val'],
	                        'adopt_config_id'=>$user_adopt['adopt_config_id'],
	                        'adopt_val_config_id'=>$num
	                    );
	                    $res = M('adopt_val_log')->add($adopt_val_log_data);//增加领养值增长记录
	                    if($res){
	                        $adopt_log_data = array(
	                            'id'=>$user_adopt['id'],
	                            'adopt_val'=>$user_adopt['adopt_val'] + $adopt_val_config['times_adopt_val']
	                        );
	                        $res = M('adopt_log')->save($adopt_log_data);
	                    }
	                }else{//加上登陆赠送领养值  领养值满足领养条件
	                    $adopt_val_log_data = array(
	                        'add_time'=>date('Y-m-d H:i:s'),
	                        'deleted'=>0,
	                        'user_id'=>$user['id'],
	                        'adopt_val'=>$adopt_val_config['times_adopt_val'],
	                        'adopt_config_id'=>$user_adopt['adopt_config_id'],
	                        'adopt_val_config_id'=>$num
	                    );
	                    $res = M('adopt_val_log')->add($adopt_val_log_data);//增加领养值增长记录
	                    if($res){
	                        $lottery_config = M('lottery_config')->where(array('id'=>$adopt_config['lottery_config_id']))->find();
	                        //增加娃娃
	                        $data = array(
	                            'lottery_type_id' => $lottery_config['lottery_type_id'],
	                            'lottery_config_id' => $adopt_config['lottery_config_id'],
	                            'user_id' => $user['id'],
	                            'lottery_good_id' => $adopt_config['lottery_good_id'],
	                            'add_time' => date('Y-m-d H:i:s', time(0)),
	                            'deleted' => 0,
	                            'type' => -4, //领养活动
	                        );
	                        $res = M('lottery_record')->add($data);
	                        if($res){
	                            $adopt_log_data = array(
	                                'id'=>$user_adopt['id'],
	                                'deleted'=>1
	                            );
	                            $res = M('adopt_log')->save($adopt_log_data);
	                        }
	                    }
	                }
	            }
	        }
	    }
	}
	/**
	 * add_adopt_three 消费糖豆抓娃娃3次
	 */
	public function add_adopt_three($user){
	    //今日有没有领取过
	    $start_time = date('Y-m-d').' 00:00:00';
	    $end_time = date('Y-m-d H:i:s');
	    $map = array(
	        'deleted'=>0,
	        'user_id'=>$user['id'],
	        'adopt_val_config_id'=>4,
	        'add_time'=>array(
	            array('egt', $start_time),
	            array('elt', $end_time),
	            'and',
	        )
	    );
	    $user_adopt_val = M('adopt_val_log')->where($map)->find();
	    if($user_adopt_val){
	        return;
	    }
	    $adopt_val_config = M('adopt_val_config')->where(array('id'=>4))->find();
	    //有没有正在领养的宠物
	    $map = array(
	        'user_id'=>$user['id'],
	        'deleted'=>0,
	    );
	    $user_adopt = M('adopt_log')->where($map)->find();
	    if($user_adopt){
	        $adopt_config_map = array(
	            'deleted'=>0,
	            'adopt_config'=>$user_adopt['adopt_config_id'],
	        );
	        $adopt_config = M('adopt_config')->where($adopt_config_map)->find();
	        if($adopt_config){
	            if($user_adopt['adopt_val'] < $adopt_config['adopt_val']){
	                $temp = $adopt_config['adopt_val'] - $user_adopt['adopt_val'];
	                if($temp > $adopt_val_config['times_adopt_val']){//加上登陆赠送领养值  未满所需领养值
	                    $adopt_val_log_data = array(
	                        'add_time'=>date('Y-m-d H:i:s'),
	                        'deleted'=>0,
	                        'user_id'=>$user['id'],
	                        'adopt_val'=>$adopt_val_config['times_adopt_val'],
	                        'adopt_config_id'=>$user_adopt['adopt_config_id'],
	                        'adopt_val_config_id'=>4
	                    );
	                    $res = M('adopt_val_log')->add($adopt_val_log_data);//增加领养值增长记录
	                    if($res){
	                        $adopt_log_data = array(
	                            'id'=>$user_adopt['id'],
	                            'adopt_val'=>$user_adopt['adopt_val'] + $adopt_val_config['times_adopt_val']
	                        );
	                        $res = M('adopt_log')->save($adopt_log_data);
	                    }
	                }else{//加上登陆赠送领养值  领养值满足领养条件
	                    $adopt_val_log_data = array(
	                        'add_time'=>date('Y-m-d H:i:s'),
	                        'deleted'=>0,
	                        'user_id'=>$user['id'],
	                        'adopt_val'=>$adopt_val_config['times_adopt_val'],
	                        'adopt_config_id'=>$user_adopt['adopt_config_id'],
	                        'adopt_val_config_id'=>4
	                    );
	                    $res = M('adopt_val_log')->add($adopt_val_log_data);//增加领养值增长记录
	                    if($res){
	                        $lottery_config = M('lottery_config')->where(array('id'=>$adopt_config['lottery_config_id']))->find();
	                        //增加娃娃
	                        $data = array(
	                            'lottery_type_id' => $lottery_config['lottery_type_id'],
	                            'lottery_config_id' => $adopt_config['lottery_config_id'],
	                            'user_id' => $user['id'],
	                            'lottery_good_id' => $adopt_config['lottery_good_id'],
	                            'add_time' => date('Y-m-d H:i:s', time(0)),
	                            'deleted' => 0,
	                            'type' => -4, //领养活动
	                        );
	                        $res = M('lottery_record')->add($data);
	                        if($res){
	                            $adopt_log_data = array(
	                                'id'=>$user_adopt['id'],
	                                'deleted'=>1
	                            );
	                            $res = M('adopt_log')->save($adopt_log_data);
	                        }
	                    }
	                }
	            }
	        }
	    }
	}
	/**
	 * add_adopt_five 消费糖豆抓娃娃5次
	 */
	public function add_adopt_five($user){
	    //今日有没有领取过
	    $start_time = date('Y-m-d').' 00:00:00';
	    $end_time = date('Y-m-d H:i:s');
	    $map = array(
	        'deleted'=>0,
	        'user_id'=>$user['id'],
	        'adopt_val_config_id'=>5,
	        'add_time'=>array(
	            array('egt', $start_time),
	            array('elt', $end_time),
	            'and',
	        )
	    );
	    $user_adopt_val = M('adopt_val_log')->where($map)->find();
	    if($user_adopt_val){
	        return;
	    }
	    $adopt_val_config = M('adopt_val_config')->where(array('id'=>5))->find();
	    //有没有正在领养的宠物
	    $map = array(
	        'user_id'=>$user['id'],
	        'deleted'=>0,
	    );
	    $user_adopt = M('adopt_log')->where($map)->find();
	    if($user_adopt){
	        $adopt_config_map = array(
	            'deleted'=>0,
	            'id'=>$user_adopt['adopt_config_id'],
	        );
	        $adopt_config = M('adopt_config')->where($adopt_config_map)->find();
	        if($adopt_config){
	            if($user_adopt['adopt_val'] < $adopt_config['adopt_val']){
	                $temp = $adopt_config['adopt_val'] - $user_adopt['adopt_val'];
	                if($temp > $adopt_val_config['times_adopt_val']){//加上登陆赠送领养值  未满所需领养值
	                    $adopt_val_log_data = array(
	                        'add_time'=>date('Y-m-d H:i:s'),
	                        'deleted'=>0,
	                        'user_id'=>$user['id'],
	                        'adopt_val'=>$adopt_val_config['times_adopt_val'],
	                        'adopt_config_id'=>$user_adopt['adopt_config_id'],
	                        'adopt_val_config_id'=>5
	                    );
	                    $res = M('adopt_val_log')->add($adopt_val_log_data);//增加领养值增长记录
	                    if($res){
	                        $adopt_log_data = array(
	                            'id'=>$user_adopt['id'],
	                            'adopt_val'=>$user_adopt['adopt_val'] + $adopt_val_config['times_adopt_val']
	                        );
	                        $res = M('adopt_log')->save($adopt_log_data);
	                    }
	                }else{//加上登陆赠送领养值  领养值满足领养条件
	                    $adopt_val_log_data = array(
	                        'add_time'=>date('Y-m-d H:i:s'),
	                        'deleted'=>0,
	                        'user_id'=>$user['id'],
	                        'adopt_val'=>$adopt_val_config['times_adopt_val'],
	                        'adopt_config_id'=>$user_adopt['adopt_config_id'],
	                        'adopt_val_config_id'=>5
	                    );
	                    $res = M('adopt_val_log')->add($adopt_val_log_data);//增加领养值增长记录
	                    if($res){
	                        $lottery_config = M('lottery_config')->where(array('id'=>$adopt_config['lottery_config_id']))->find();
	                        //增加娃娃
	                        $data = array(
	                            'lottery_type_id' => $lottery_config['lottery_type_id'],
	                            'lottery_config_id' => $adopt_config['lottery_config_id'],
	                            'user_id' => $user['id'],
	                            'lottery_good_id' => $adopt_config['lottery_good_id'],
	                            'add_time' => date('Y-m-d H:i:s', time(0)),
	                            'deleted' => 0,
	                            'type' => -4, //领养活动
	                        );
	                        $res = M('lottery_record')->add($data);
	                        if($res){
	                            $adopt_log_data = array(
	                                'id'=>$user_adopt['id'],
	                                'deleted'=>1
	                            );
	                            $res = M('adopt_log')->save($adopt_log_data);
	                        }
	                    }
	                }
	            }
	        }
	    }
	}
	/**
	 * add_adopt_val 每日登陆增加领养殖
	 */
	public function add_adopt_val($user){//有没有正在领养的宠物
	    $map = array(
	        'user_id'=>$user['id'],
	        'deleted'=>0,
	    );
	    $user_adopt = M('adopt_log')->where($map)->find();
	    //昨天有没有领取过
	    $start_time = date('Y-m-d H:i:s',strtotime(date('Y-m-d').' 00:00:00')-86400);
	    $end_time = date('Y-m-d H:i:s',strtotime(date('Y-m-d').' 23:59:59')-86400);
	    $map = array(
	        'deleted'=>0,
	        'user_id'=>$user['id'],
	        'add_time'=>array(
	            array('egt', $start_time),
	            array('elt', $end_time),
	            'and',
	        )
	    );
	    $user_adopt_val = M('adopt_val_log')->where($map)->find();
	    if(!$user_adopt_val){//昨天没有操作
	        $map = array(
	            'deleted'=>0,
	            'user_id'=>$user['id'],
	            'add_time'=>array(
	                array('egt', date('Y-m-d').' 00:00:00'),
	                array('elt', date('Y-m-d H:i:s')),
	                'and',
	            )
	        );
	        $adopt_val_reduce = M('adopt_val_reduce')->where($map)->find();
	        if(!$adopt_val_reduce){
	            $temp_val = $user_adopt['adopt_val'] - 5;
	            if($temp_val < 0){
	                $temp_val = 0;
	            }
	            $user_adopt_data = array(
	                'id'=>$user_adopt,
	                'adopt_val'=>$temp_val
	            );
	            $res = M('adopt_log')->save($adopt_log_data);
	            if($res){
	                $data = array(
	                    'add_time'=>date('Y-m-d H:i:s'),
	                    'deleted'=>0,
	                    'user_id'=>$user['id'],
	                    'adopt_val'=>5
	                );
	                M('adopt_val_reduce')->add($data);
	            }
	        }
	    }
	    //今日有没有领取过
	    $start_time = date('Y-m-d').' 00:00:00';
	    $end_time = date('Y-m-d H:i:s');
	    $map = array(
	        'deleted'=>0,
	        'user_id'=>$user['id'],
	        'adopt_val_config_id'=>1,
	        'add_time'=>array(
	            array('egt', $start_time),
	            array('elt', $end_time),
	            'and',
	        )
	    );
	    $user_adopt_val = M('adopt_val_log')->where($map)->find();
	    if($user_adopt_val){
	        return;
	    }
	    $adopt_val_config = M('adopt_val_config')->where(array('id'=>1))->find();
	    
	    if($user_adopt){
	        $adopt_config_map = array(
	            'deleted'=>0,
	            'adopt_config'=>$user_adopt['adopt_config_id'],
	        );
	        $adopt_config = M('adopt_config')->where($adopt_config_map)->find();
	        if($adopt_config){
	            if($user_adopt['adopt_val'] < $adopt_config['adopt_val']){
	                $temp = $adopt_config['adopt_val'] - $user_adopt['adopt_val'];
	                if($temp > $adopt_val_config['times_adopt_val']){//加上登陆赠送领养值  未满所需领养值
	                    $adopt_val_log_data = array(
	                        'add_time'=>date('Y-m-d H:i:s'),
	                        'deleted'=>0,
	                        'user_id'=>$user['id'],
	                        'adopt_val'=>$adopt_val_config['times_adopt_val'],
	                        'adopt_config_id'=>$user_adopt['adopt_config_id'],
	                        'adopt_val_config_id'=>1
	                    );
	                    $res = M('adopt_val_log')->add($adopt_val_log_data);//增加领养值增长记录
	                    if($res){
	                        $adopt_log_data = array(
	                            'id'=>$user_adopt['id'],
	                            'adopt_val'=>$user_adopt['adopt_val'] + $adopt_val_config['times_adopt_val']
	                        );
	                        $res = M('adopt_log')->save($adopt_log_data);
	                    }
	                }else{//加上登陆赠送领养值  领养值满足领养条件
	                    $adopt_val_log_data = array(
	                        'add_time'=>date('Y-m-d H:i:s'),
	                        'deleted'=>0,
	                        'user_id'=>$user['id'],
	                        'adopt_val'=>$adopt_val_config['times_adopt_val'],
	                        'adopt_config_id'=>$user_adopt['adopt_config_id'],
	                        'adopt_val_config_id'=>1
	                    );
	                    $res = M('adopt_val_log')->add($adopt_val_log_data);//增加领养值增长记录
	                    if($res){
	                        $lottery_config = M('lottery_config')->where(array('id'=>$adopt_config['lottery_config_id']))->find();
	                        //增加娃娃
	                        $data = array(
	                            'lottery_type_id' => $lottery_config['lottery_type_id'],
	                            'lottery_config_id' => $adopt_config['lottery_config_id'],
	                            'user_id' => $user['id'],
	                            'lottery_good_id' => $adopt_config['lottery_good_id'],
	                            'add_time' => date('Y-m-d H:i:s', time(0)),
	                            'deleted' => 0,
	                            'type' => -4, //领养活动
	                        );
	                        $res = M('lottery_record')->add($data);
	                        if($res){
	                            $adopt_log_data = array(
	                                'id'=>$user_adopt['id'],
	                                'deleted'=>1
	                            );
	                            $res = M('adopt_log')->save($adopt_log_data);
	                        }
	                    }
	                }
	            }
	        }
	    }
	}
	/**
	 * update_user_box_times 更新用户中奖次数
	 */
	public function update_user_box_times() {
	    $user = session('user');
	    $user = M('user')->where(array('id' => $user['id']))->find();
	    $hour = date('H');
	    if($hour<9 || ($hour<12 && $hour>9) || ($hour<21 && $hour>12) || $hour>21){
	        //if($user['box_times']<C('BOX_TIMES')){
	            //更新用户中奖次数
	            $data = array(
	                'id'=>$user['id'],
	                'box_times'=>C('BOX_TIMES')
	            );
	            $res = M('user')->save($data);
	        //}
	        /*if($user['box_times']>(C('BOX_TIMES')+C('BOX_LARGESS_TIMES'))){
	            //更新用户中奖次数
	            $data = array(
	                'id'=>$user['id'],
	                'box_times'=>C('BOX_TIMES')+C('BOX_LARGESS_TIMES')
	            );
	            $res = M('user')->save($data);
	        }*/
	    }else{
	        if($hour == 9){
	            $start_time = date('Y-m-d ').'9:00:00';
	            $end_time = date('Y-m-d ').'10:00:00';
	        }
	        if($hour == 12){
	            $start_time = date('Y-m-d ').'12:00:00';
	            $end_time = date('Y-m-d ').'13:00:00';
	        }
	        if($hour == 21){
	            $start_time = date('Y-m-d ').'21:00:00';
	            $end_time = date('Y-m-d ').'22:00:00';
	        }
	        $log_map = array(
	            'user_id'=>$user['id'],
	            'deleted'=>0,
	            'add_time'=>array(
	                array('egt', $start_time),
	                array('elt', $end_time),
	                'and',
	            )
	        );
	        $box_log = M('box_log')->where($log_map)->find();
	        if(!$box_log){
	            $data = array(
	                'id'=>$user['id'],
	                'box_times'=>C('BOX_TIMES')
	            );
	            $res = M('user')->save($data);
	        }
	    }
	}
	/**
	 * build_qrcode 生成微信二维码
	 */
	public function build_qrcode() {
		$user = session('user');
		$user = M('user')->where(array('id' => $user['id']))->find();
		if ($user['ticket'] != '') {
		  return ;
		}
		// if ($user['ticket'] != '') {
		// 	if ($user['qrcode_url'] == '') {
		// 		// 合成分享的二维码
		// 		$share_bg_image = 'Public/share_bg.jpg';
		// 		$image = $this->_request($user['ticket']);
		// 		$file = "Uploads/qr_code/erweima_" . $user['id'] . ".jpg"; //设置图片名字
		// 		file_put_contents($file, $image); //二维码保存到本地
		// 		$share_image = "Uploads/qr_code/share_" . $user['id'] . ".png";
		// 		$imageApi = new \Think\Image();
		// 		$imageApi->open($file)->thumb(300, 300)->save($file);
		// 		$imageApi->open($share_bg_image)->water($file, array(227, 860), 100)->save($share_image);
		// 		$data = array(
		// 			'id' => $user['id'],
		// 			'qrcode_url' => 'https://' . $_SERVER['SERVER_NAME'] . '/' . $share_image,
		// 		);
		// 		$res = M('user')->save($data);
		// 	}
		// 	return;
		// }
		//微信授权登录
		$appId = "";
		$appSecret = "";

		$res = $this->getAccessToken($appId, $appSecret);
		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $res;
		//$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKEN';
		$data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": ' . $user['id'] . '}}}';
		$res = $this->http_request($url, $data);
		$res = json_decode($res, true);
		// 合成分享的二维码
		// $share_bg_image = 'Public/share_bg.jpg';
		// $image = $this->_request($user['ticket']);
		// $file = "Uploads/qr_code/erweima.jpg"; //设置图片名字
		// file_put_contents($file, $image); //二维码保存到本地
		// $share_image = "Uploads/qr_code/share_" . $user['id'] . ".png";
		// $imageApi = new \Think\Image();
		// $imageApi->open($file)->thumb(300, 300)->save($file);
		// $imageApi->open($share_bg_image)->water($file, array(227, 860), 100)->save($share_image);
		$data = array(
			'id' => $user['id'],
			'ticket' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($res['ticket']),
			//'qrcode_url' => 'https://' . $_SERVER['SERVER_NAME'] . '/' . $share_image,
		);
		$res = M('user')->save($data);
	}
	private function _request($curl, $https = true, $method = 'get', $data = null) {
		$ch = curl_init(); //初始化
		curl_setopt($ch, CURLOPT_URL, $curl);
		curl_setopt($ch, CURLOPT_HEADER, false); //设置不需要头信息
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //获取页面内容，但不输出
		if ($https) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //不做服务器认证
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //不做客户端认证
		}

		if ($method == 'post') {
			curl_setopt($ch, CURLOPT_POST, true); //设置请求是post方式
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data); //设置post请求数据

		}

		$str = curl_exec($ch); //执行访问
		curl_close($ch); //关闭curl，释放资源
		return $str;
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
	 * deduction_luck 扣除幸运值
	 */
	public function deduction_luck() {
		$user = session('user');
		$userModel = D('user');
		$user = $userModel->where(array('id' => $user['id']))->find();
		if ($user['luck_num'] == 0) {
//没有幸运值
			return;
		}
		//查询用户最后一次充值时间
		$pay_map = array(
			'deleted' => 0,
			'status' => 1,
			'type' => 0,
			'user_id' => $user['id'],
		);
		$temp = M('user_coin_record')->where($pay_map)->order('add_time desc')->find();
		if (!$temp) {
//没有充值过,没有幸运值，不用扣除
			return;
		}
		$last_pay_time = strtotime($temp['add_time']);
		if (time() - $last_pay_time > 30 * 86400) {
//超过30天
			//最后一次扣除的时间
			$deduction_map = array(
				'deleted' => 0,
				'user_id' => $user['id'],
			);
			$deduction_info = M('deduction_luck_log')->where($deduction_map)->order('add_time desc')->find();
			if (!$deduction_info) {
//没扣除过
				$number = intval((time() - $last_pay_time) / (30 * 86400)); //满足的扣除次数
				$luck_num = $user['luck_num'] - $number * 5;
				$deduction_num = $number * 5;
				if ($luck_num < 0) {
					$luck_num = 0;
					$deduction_num = $user['luck_num'];
				}
				$data = array(
					'id' => $user['id'],
					'luck_num' => $luck_num, //每满足一次 扣除5点幸运值
				);
				$res = $userModel->save($data);
				if ($res) {
					$data = array(
						'add_time' => date('Y-m-d H:i:s'),
						'deleted' => 0,
						'user_id' => $user['id'],
						'deduction_num' => $deduction_num,
					);
					$res = M('deduction_luck_log')->add($data);
				}
				return;
			} else {
//扣除过
				$deduction_time = strtotime($deduction_info['add_time']);
				if ($last_pay_time > $deduction_time) {
//最后一次充值之前扣除的
					$number = intval((time() - $last_pay_time) / (30 * 86400)); //满足的扣除次数
					$luck_num = $user['luck_num'] - $number * 5;
					$deduction_num = $number * 5;
					if ($luck_num < 0) {
						$luck_num = 0;
						$deduction_num = $user['luck_num'];
					}
					$data = array(
						'id' => $user['id'],
						'luck_num' => $luck_num, //每满足一次 扣除5点幸运值
					);
					$res = $userModel->save($data);
					if ($res) {
						$data = array(
							'add_time' => date('Y-m-d H:i:s'),
							'deleted' => 0,
							'user_id' => $user['id'],
							'deduction_num' => $deduction_num,
						);
						$res = M('deduction_luck_log')->add($data);
					}
					return;
				} else {
//最后一次充值之后，已经扣除过
					//获取上一次扣除时，扣除了几次
					$last_number = intval(($deduction_time - $last_pay_time) / (30 * 86400));
					//当前满足的扣除次数
					$number = intval((time() - $last_pay_time) / (30 * 86400));
					//当前可扣除次数
					$num_times = $number - $last_number;
					if ($num_times < 1) {
//不满足扣除一次
						return;
					}
					$luck_num = $user['luck_num'] - $num_times * 5;
					$deduction_num = $num_times * 5;
					if ($luck_num < 0) {
						$luck_num = 0;
						$deduction_num = $user['luck_num'];
					}
					$data = array(
						'id' => $user['id'],
						'luck_num' => $luck_num, //每满足一次 扣除5点幸运值
					);
					$res = $userModel->save($data);
					if ($res) {
						$data = array(
							'add_time' => date('Y-m-d H:i:s'),
							'deleted' => 0,
							'user_id' => $user['id'],
							'deduction_num' => $deduction_num,
						);
						$res = M('deduction_luck_log')->add($data);
					}
					return;
				}
			}
		}
	}
}
