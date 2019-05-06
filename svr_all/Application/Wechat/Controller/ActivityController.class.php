<?php
namespace Wechat\Controller;
use Think\Controller;

class ActivityController extends BaseController {
	public function guangfa_act() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'id' => $user['id'],
		);
		$userModel = D('user');
		$user = $userModel->where($map)->find();
		if (!$user) {
			exit;
		}
		$this->assign('user', $user);
		// 获取总用户数
		$map = array(
			'deleted' => 0,
			'status' => 1,
		);
		$bank_user_count = M('bank_user')->where($map)->count();
		if (!$bank_user_count) {
			$bank_user_count = 0;
		}
		$this->assign('user_count', $bank_user_count);
		// 获取当前赠送糖豆数量
		if ($bank_user_count == 0) {
			$this->assign('total_coin', C('BANK_USER_COIN'));
		} else {
			$total_coin = 0;
			if ($bank_user_count <= 50) {
				$total_coin = $bank_user_count * C('BANK_USER_COIN');
			} else {
				$total_coin = $bank_user_count * (C('BANK_USER_COIN') + C('BANK_USER_COIN_MORE'));
			}
			$total_coin = $total_coin / $bank_user_count;
			$this->assign('total_coin', $total_coin);
		}
		$this->show();
	}
	public function ajax_sub_xinyongka_act() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$real_name = I('real_name');
		$tel = I('tel');
		$company_name = I('company_name');
		$company_tel = I('company_tel');
		$type = I('type');
		$map = array(
			'real_name' => $real_name,
			'tel' => $tel,
			'type' => $type,
			'deleted' => 0,
		);
		$res = M('bank_user')->where($map)->find();
		if ($res) {
			$this->_err_ret('信息已经提交过了');
		}
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'real_name' => $real_name,
			'tel' => $tel,
			'company_name' => $company_name,
			'company_tel' => $company_tel,
			'company_address' => '',
			'type' => $type,
			'status' => 0,
			'user_id' => $user['id'],
		);
		$res = M('bank_user')->add($data);
		if (!$res) {
			$this->_err_ret('提交失败');
		}
		//通知马超
		$map_admin = array(
			'openid' => 'oKFOO1hc7P90MGMU39zaIgYmJh0k',
		);
		$admin_user = $userModel->where($map_admin)->find();
		$this->send_xinyongka_msg_admin($admin_user, $data, $user);

		$this->_suc_ret();
	}
	/**
	 * 宝箱抽奖页面
	 */
	public function box_game() {
		//用户信息  box_times可开宝箱次数  （钥匙个数）
		$user = session('user');
		if (!$user) {
			exit;
		}
		//获取用户抽奖次数
		$user_map = array(
			'deleted' => 0,
			'id' => $user['id'],
		);
		$user = M('user')->where($user_map)->find();
		$this->assign('user', $user);
		//可中奖商品列表
		$map = array(
			'deleted' => 0,
			'is_open' => 1,
		);
		$box_goods = M('box_good')->where($map)->order('add_time asc')->select();
		foreach ($box_goods as $key => $val) {
			$map = array(
				'id' => $val['lottery_good_id'],
			);
			$goods_info = M('lottery_good')->where($map)->find();
			$box_goods[$key]['lottery_good'] = $goods_info;
			$map = array(
				'id' => $val['lottery_config_id'],
			);
			$lottery_config = M('lottery_config')->where($map)->find();
			$box_goods[$key]['lottery_config'] = $lottery_config;
		}
		$this->assign('box_goods', $box_goods);
		//已中奖列表
		$map = array(
			'deleted' => 0,
			'is_hit' => 1,
		);
		$box_logs = M('box_log')->where($map)->order('add_time desc')->limit(5)->select();
		foreach ($box_logs as $key => $val) {
			$map = array(
				'id' => $val['box_good_id'],
			);
			$box_good = M('box_good')->where($map)->find();
			$box_logs[$key]['box_good'] = $box_good;
			$map = array(
				'id' => $box_good['lottery_good_id'],
			);
			$lottery_good = M('lottery_good')->where($map)->find();
			$box_logs[$key]['lottery_good'] = $lottery_good;
			$map = array(
				'id' => $val['user_id'],
			);
			$user_info = M('user')->where($map)->find();
			$box_logs[$key]['user'] = $user_info;
		}
		$this->assign('box_logs', $box_logs);
		//已送出
		$map = array(
			'deleted' => 0,
			'is_hit' => 1,
		);
		$box_count = M('box_log')->where($map)->order('add_time desc')->count();
		if (!$box_count) {
			$box_count = 0;
		}
		$this->assign('box_count', $box_count);
		$this->show();
	}
	/**
	 * 宝箱抽奖
	 */
	public function ajax_box_game() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$this->_err_ret("活动已经结束啦~感谢关注和参与！");
		//获取用户抽奖次数
		$user_map = array(
			'deleted' => 0,
			'id' => $user['id'],
		);
		$user = M('user')->where($user_map)->find();
		if (!$user) {
			$this->_err_ret('用户不存在！');
		}
		//判断时间
		$hour = date('H');
		if ($hour < 9 || ($hour < 12 && $hour > 9) || ($hour < 21 && $hour > 12) || $hour > 21) {
			$this->_err_ret('本次时段已过，请下个时段再来吧~');
		}
		if ($user['box_times'] == 0) {
			$this->_err_ret('您的金钥匙已经用完了，快去邀请好友获得金钥匙吧~');
		}
		//次数足够，先减掉抽奖次数
		$data = array(
			'id' => $user['id'],
			'box_times' => $user['box_times'] - 1,
		);
		$res = M('user')->save($data);
		$box_times = $user['box_times'] - 1;
		$fail_img_url = 'https://fssw.bichonfrise.cn/Public/weixin/image/fail.png';
		$res_data = array(
			'good_name' => '',
			'good_url' => $fail_img_url,
			'box_times' => $box_times,
			'msg' => "很遗憾，您没有中奖！",
		);
		if ($user['charge_num'] == 0) {
//没充值过
			$box_log_map = array(
				'user_id' => $user['id'],
				'is_hit' => 1,
			);
			$user_box_log = M('box_log')->where($box_log_map)->find();
			if ($user_box_log) {
				$this->add_box_log($user['id'], 0, 0);
				$this->_suc_ret($res_data); //免费用户已经中过奖，直接返回未中奖
			}
		}
		$map = array(
			'deleted' => 0,
			'is_open' => 1,
		);
		$box_good_list = M('box_good')->where($map)->select();
		if (!$box_good_list) {
			$this->add_box_log($user['id'], 0, 0);
			$this->_suc_ret($res_data); //后台没有配置中奖商品
		}
		//查询用户前一天有没有中过奖
		if (C('BOX_OPEN_ONE_DAY_HIT') == 1) {
			$after_map = array(
				'deleted' => 0,
				'is_hit' => 1,
				'add_time' => array(
					array('egt', date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . '00:00:00') - 86400)),
					array('elt', date('Y-m-d ') . '23:59:59'),
					'and',
				),
				'user_id' => $user['id'],
			);
			$box_log = M('box_log')->where($after_map)->select();
			if ($box_log) {
				$this->add_box_log($user['id'], 0, 0);
				$this->_suc_ret($res_data); //前一天已经中奖
			}
		}

		//判断中没中奖
		$hit_times = C('BOX_HIT_TIMES');
		$start_time = date('Y-m-d H') . ':00:00';
		$end_time = date('Y-m-d H') . ':59:59';
		$times_map = array(
			'deleted' => 0,
			'is_hit' => 1,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$box_log = M('box_log')->where($times_map)->select();
		if (count($box_log) >= $hit_times) {
			$this->add_box_log($user['id'], 0, 0);
			$this->_suc_ret($res_data); //可中奖次数已经用完
		}
		$hit_chance = C('BOX_HIT_CHANCE') * 100;
		$temp = rand(1, 100);
		if ($temp > 0 && $temp <= $hit_chance) {
			//中奖
			//随机商品
			$box_id = rand(0, count($box_good_list));
			$box_config = $box_good_list[$box_id];
			$good_map = array(
				'id' => $box_config['lottery_good_id'],
				'deleted' => 0,
			);
			$lottery_good = M('lottery_good')->where($good_map)->find();
			if (!$lottery_good) {
				//商品不存在，或者已经被删除
				$log_id = $this->add_box_log($user['id'], 0, 0);
				$this->_suc_ret($res_data); //商品不存在，直接返回未中奖
			}
			$log_id = $this->add_box_log($user['id'], 1, $box_config['id']);
			$lottery_config_map = array(
				'id' => $box_config['lottery_config_id'],
			);
			$lottery_config = M('lottery_config')->where($lottery_config_map)->find();
			//添加中奖商品lottery_record
			$data = array(
				'lottery_type_id' => $lottery_config['lottery_type_id'],
				'lottery_config_id' => $box_config['lottery_config_id'],
				'user_id' => $user['id'],
				'lottery_good_id' => $box_config['lottery_good_id'],
				'add_time' => date('Y-m-d H:i:s', time(0)),
				'deleted' => 0,
				'type' => -3, //宝箱活动
			);
			$res = M('lottery_record')->add($data);
			if (!$res) {
				$edit_data = array(
					'id' => $log_id,
					'is_hit' => 0,
				);
				M('box_log')->save($edit_data);
				$this->_suc_ret($res_data); //奖品添加失败，直接返回未中奖
			} else {
				$res_data['good_name'] = $lottery_good['name'];
				$res_data['good_url'] = $lottery_good['img_url'];
				$this->_suc_ret($res_data);
			}
		} else {
			$this->add_box_log($user['id'], 0, 0);
			$this->_suc_ret($res_data); //没有中奖
		}
	}
	//添加宝箱记录
	public function add_box_log($user_id, $is_hit, $box_good_id) {
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user_id,
			'is_hit' => $is_hit,
			'box_good_id' => $box_good_id,
		);
		$res = M('box_log')->add($data);
		return $res;
	}
	//领养宠物页面
	public function adopt_view() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		//获取用户抽奖次数
		$user_map = array(
			'deleted' => 0,
			'id' => $user['id'],
		);
		$user = M('user')->where($user_map)->find();
		if (!$user) {
			$this->_err_ret('用户不存在！');
		}
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$user_adopt = M('adopt_log')->where($map)->find();
		if ($user_adopt) {
			$is_adopt = 1;
		} else {
			$is_adopt = 0;
		}
		//是否已经领养
		$this->assign('is_adopt', $is_adopt);
		//领养详情
		$this->assign('user_adopt', $user_adopt);
		$adopt_config_map = array(
			'deleted' => 0,
			'id' => $user_adopt['adopt_config_id'],
		);
		$adopt_config = M('adopt_config')->where($adopt_config_map)->find();
		//领养配置详情
		$this->assign('adopt_config', $adopt_config);
		$lottery_good_map = array(
			'deleted' => 0,
			'id' => $adopt_config['lottery_good_id'],
		);
		$lottery_good = M('lottery_good')->where($lottery_good_map)->find();
		//领养娃娃详情
		$this->assign('lottery_good', $lottery_good);
		$this->show();
	}
	/**
	 * 领养活动  喂食
	 */
	public function ajax_adopt_other_stroke() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$to_user_id = I('to_user_id');
		if ($to_user_id == $user['id']) {
			$this->_err_ret('快快分享此页面邀请好友给娃娃喂小饼干吧~');
		}
		$start_time = date('Y-m-d') . ' 00:00:00';
		$end_time = date('Y-m-d H:i:s');
		//查询今日这两个用户有没有发生关系
		$map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
			'to_user_id' => $to_user_id,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$adopt_stroke = M('adopt_stroke')->where($map)->find();
		if ($adopt_stroke) {
			$this->_err_ret('谢谢叔叔/阿姨，您今天已经喂过饼干啦，明天再来吧~我还小，我一天吃不了太多哦~');
		}
		$map = array(
			'user_id' => $to_user_id,
			'deleted' => 0,
		);
		$user_adopt = M('adopt_log')->where($map)->find();
		if (!$user_adopt) {
			exit();
		}
		$map = array(
			'deleted' => 0,
			'user_id' => $to_user_id,
			'adopt_val_config_id' => 2,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$user_adopt_val = M('adopt_val_log')->where($map)->select();
		$adopt_val_config = M('adopt_val_config')->where(array('id' => 2))->find();
		if (count($user_adopt_val) < $adopt_val_config['total_count']) {
			$adopt_config_map = array(
				'deleted' => 0,
				'adopt_config' => $user_adopt['adopt_config_id'],
			);
			$adopt_config = M('adopt_config')->where($adopt_config_map)->find();
			if ($adopt_config) {
				if ($user_adopt['adopt_val'] < $adopt_config['adopt_val']) {
					$temp = $adopt_config['adopt_val'] - $user_adopt['adopt_val'];
					if ($temp > $adopt_val_config['times_adopt_val']) {
//加上赠送领养值  未满所需领养值
						$adopt_val_log_data = array(
							'add_time' => date('Y-m-d H:i:s'),
							'deleted' => 0,
							'user_id' => $to_user_id,
							'adopt_val' => $adopt_val_config['times_adopt_val'],
							'adopt_config_id' => $user_adopt['adopt_config_id'],
							'adopt_val_config_id' => 2,
						);
						$res = M('adopt_val_log')->add($adopt_val_log_data); //增加领养值增长记录
						if ($res) {
							$adopt_log_data = array(
								'id' => $user_adopt['id'],
								'adopt_val' => $user_adopt['adopt_val'] + $adopt_val_config['times_adopt_val'],
							);
							$res = M('adopt_log')->save($adopt_log_data);
						}
					} else {
//加上登陆赠送领养值  领养值满足领养条件
						$adopt_val_log_data = array(
							'add_time' => date('Y-m-d H:i:s'),
							'deleted' => 0,
							'user_id' => $to_user_id,
							'adopt_val' => $adopt_val_config['times_adopt_val'],
							'adopt_config_id' => $user_adopt['adopt_config_id'],
							'adopt_val_config_id' => 2,
						);
						$res = M('adopt_val_log')->add($adopt_val_log_data); //增加领养值增长记录
						if ($res) {
							//增加娃娃
							$lottery_config = M('lottery_config')->where(array('id' => $adopt_config['lottery_config_id']))->find();
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
							if ($res) {
								$adopt_log_data = array(
									'id' => $user_adopt['id'],
									'deleted' => 1,
								);
								$res = M('adopt_log')->save($adopt_log_data);
							}
						}
					}
					$data = array(
						'add_time' => date('Y-m-d H:i:s'),
						'user_id' => $user['id'],
						'to_user_id' => $to_user_id,
						'deleted' => 0,
					);
					$temp_res = M('adopt_stroke')->add($data);
				}
			}
		}
		$this->_suc_ret();
	}
	public function lvguonongchang() {
	    
		$this->show();
	}
	
	/*****
	 * 水果种植
	 * $num 类型：1：绑定手机号（+20，每个用户只有一次），
	 *          2：签到（1天~6天 +10，7天~15天 +20，一天一次），
	 *          3：邀请好友浇水（+20，一天三次），
	 *          4：分享朋友圈（+20，一天一次），
	 *          5：定时奖励（12：00~14：00 +20，18：00~20：00 +20），
	 *          6：购买肥料（）
	 *****/
	//水果分享回调接口
	public function ajax_add_fruit_share(){
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $user = M('user')->where(array('id'=>$user['id']))->find();
	    if(!$user){
	        exit();
	    }
	    $res = $this->add_fruit_lottery($user, 4);
	    if($res['status'] == 1){
	        //$this->_err_ret($res['msg']);
	        $this->_suc_ret();
	    }
	    if($res['status'] ==0){
	        $this->_suc_ret();
	    }
	}
	//定时奖励领取
	public function ajax_add_fruit_timing(){
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $user = M('user')->where(array('id'=>$user['id']))->find();
	    if(!$user){
	        exit();
	    }
	    $res = $this->add_fruit_lottery($user, 5);
	    if($res['status'] == 1){
	        $this->_err_ret($res['msg']);
	    }
	    if($res['status'] ==0){
	        $this->_suc_ret();
	    }
	}
	
	//签到领取
	public function ajax_add_fruit_sign(){
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $user = M('user')->where(array('id'=>$user['id']))->find();
	    if(!$user){
	        exit();
	    }
	    $res = $this->add_fruit_lottery($user, 2);
	    if($res['status'] == 1){
	        $this->_err_ret($res['msg']);
	    }
	    if($res['status'] ==0){
	        $this->_suc_ret();
	    }
	}
	//领取水果接口
	public function ajax_add_user_fruit(){
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $user = M('user')->where(array('id'=>$user['id']))->find();
	    if(!$user){
	        exit();
	    }
	    $fruit_config_id = I('fruit_config_id');
	    if($fruit_config_id == ''){
	        $this->_err_ret('参数不完整');
	    }
	    $fruit_config_map = array(
	        'deleted'=>0,
	        'id'=>$fruit_config_id,
	    );
	    $fruit_config = M('fruit_config')->where($fruit_config_map)->find();
	    if(!$fruit_config){
	        $this->_err_ret('水果不存在');
	    }
	    //有没有正在领养的宠物
	    $map = array(
	        'user_id'=>$user['id'],
	        'deleted'=>0,
	    );
	    $user_fruit = M('fruit_log')->where($map)->find();
	    if($user_fruit){
	        $this->_err_ret('每个人同一时间只能种植一种水果哦~');
	    }
	    $data = array(
	        'add_time'=>date('Y-m-d H:i:s'),
	        'deleted'=>0,
	        'user_id'=>$user['id'],
	        'fruit_config_id'=>$fruit_config_id,
	        'fruit_val'=>0
	    );
	    $res = M('fruit_log')->add($data);
	    if(!$res){
	        $this->_err_ret('领取失败');
	    }
	    $this->_suc_ret();
	}
	public function lvguonongchang_zyf() {
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $user = M('user')->where(array('id'=>$user['id']))->find();
	    if(!$user){
	        exit();
	    }
	    $user_id = I("user_id");
	    if($user_id != ''){//给好友浇水
	        if($user_id != $user['id']){
	            $to_user_info = M('user')->where(array('id'=>$user_id))->find();
	            $res = $this->add_fruit_lottery($to_user_info,3,$user['id']);
	            $this->assign('friend_status',1);
	            if($res['status'] == 1){
	                $this->assign('msg',$res['msg']);
	            }
	            if($res['status'] ==0){
	                $this->assign('msg','浇水成功，快去种植自己的水果吧~');
	            }
	        }else{
	            $this->assign('friend_status',1);
	            $this->assign('msg','自己不能给自己浇水哦~');
	        }
	    }else{
	        $this->assign('friend_status',0);
	    }
	    $this->assign('user',$user);//当前登陆用户信息
	    $this->add_fruit_lottery($user,1);//手机号能量值
	    //if($user['phone'] == ''){
	    if(!$user){
	        $this->assign('status',0);//绑定手机号
	    }else{
	        //有没有正在领养的宠物
	        $map = array(
	            'user_id'=>$user['id'],
	            'deleted'=>0,
	        );
	        $user_fruit = M('fruit_log')->where($map)->find();
	        if(!$user_fruit){
	            $this->assign('status',1);//领取页面
	            //获取所有水果
	            $fruit_list_map = array(
	                'deleted'=>0,
	                'is_open'=>1
	            );
	            $fruit_list = M('fruit_config')->where($fruit_list_map)->order('fruit_val asc')->select();
	            foreach($fruit_list as $key=>$val){
	                $goods_map = array(
	                    'id'=>$val['lottery_good_id'],
	                    'deleted'=>0
	                );
	                $goods_info = M('lottery_good')->where($goods_map)->find();
	                $fruit_list[$key]['lottery_good'] = $goods_info;
	            }
	            $this->assign('fruit_list',$fruit_list);//水果列表
// 	            echo '<pre>';
// 	            var_dump($fruit_list);die();
	        }else{
	            $this->assign('user_fruit',$user_fruit);//当前用户领养信息
	            $fruit_config_map = array(
	                'deleted'=>0,
	                'id'=>$user_fruit['fruit_config_id'],
	            );
	            $fruit_config = M('fruit_config')->where($fruit_config_map)->find();
	            $fruit_config['lottery_good'] = M('lottery_good')->where(array('id'=>$fruit_config['lottery_good_id']))->find();
	            $this->assign('fruit_config',$fruit_config);//领取信息
	            
	            
	            $lottery_config_map = array(
	                'deleted'=>0,
	                'id'=>$user_fruit['lottery_config_id'],
	            );
	            $lottery_config = M('lottery_config')->where($lottery_config_map)->find();
	            $this->assign('lottery_config',$lottery_config);//水果场次
	            
	            $lottery_good_map = array(
	                'deleted'=>0,
	                'id'=>$user_fruit['lottery_good_id'],
	            );
	            $lottery_good = M('lottery_good')->where($lottery_good_map)->find();
	            $this->assign('lottery_good',$lottery_good);//水果详情
	            
	            //次数返回
	            //分享
	            //今日有没有领取过
	            $start_time = date('Y-m-d').' 00:00:00';
	            $end_time = date('Y-m-d H:i:s');
	            $map = array(
	                'deleted'=>0,
	                'user_id'=>$user['id'],
	                'type'=>4,
	                'add_time'=>array(
	                    array('egt', $start_time),
	                    array('elt', $end_time),
	                    'and',
	                )
	            );
	            $user_fruit_val = M('fruit_val_log')->where($map)->find();
	            if($user_fruit_val){
	                $this->assign('is_share',1);//已经分享领取了
	            }else{
	                $this->assign('is_share',0);//没有分享领取
	            }
	            //邀请好友浇水
	            $map = array(
	                'deleted'=>0,
	                'user_id'=>$user['id'],
	                'type'=>3,
	                'add_time'=>array(
	                    array('egt', $start_time),
	                    array('elt', $end_time),
	                    'and',
	                )
	            );
	            $user_fruit_count = M('fruit_val_log')->where($map)->count();
	            if(!$user_fruit_count){
	                $user_fruit_count = 0;
	            }
	            $this->assign('count_friend',$user_fruit_count);//好友浇水次数
	            $friend_map = array(
	                'deleted'=>0,
	                'user_id'=>$user['id']
	            );
	            $friend_list = M('fruit_stroke')->where($map)->order('add_time desc')->select();
	            foreach($friend_list as $key=>$val){
	                $from_user_map = array(
	                    'id'=>$val['from_user_id'],
	                    'deleted'=>0
	                );
	                $from_user = M('user')->where($from_user_map)->find();
	                $friend_list[$key]['user'] = $from_user;
	            }
	            $this->assign('friend_list',$friend_list);//浇水好友
	            //定时奖励（12：00~14：00 +20，18：00~20：00 +20）
	            $now_time = time();
	            $start_time_noon = strtotime(date('Y-m-d').' 12:00:00');
	            $end_time_noon = strtotime(date('Y-m-d').' 14:00:00');
	            $start_time_night = strtotime(date('Y-m-d').' 18:00:00');
	            $end_time_night = strtotime(date('Y-m-d').' 20:00:00');
                $start_time = date('Y-m-d').' 12:00:00';
                $end_time = date('Y-m-d').' 14:00:00';
                $map = array(
                    'deleted'=>0,
                    'user_id'=>$user['id'],
                    'type'=>5,
                    'add_time'=>array(
                        array('egt', $start_time),
                        array('elt', $end_time),
                        'and',
                    )
                );
                $user_fruit_val = M('fruit_val_log')->where($map)->find();
                if($user_fruit_val){
                    $this->assign('is_noon',1);//中午已经领取
                }else{
                    $this->assign('is_noon',0);//中午没有领取
                }
                $start_time = date('Y-m-d').' 12:00:00';
                $end_time = date('Y-m-d').' 14:00:00';
                $map = array(
                    'deleted'=>0,
                    'user_id'=>$user['id'],
                    'type'=>5,
                    'add_time'=>array(
                        array('egt', $start_time),
                        array('elt', $end_time),
                        'and',
                    )
                );
                $user_fruit_val = M('fruit_val_log')->where($map)->find();
                if($user_fruit_val){
                    $this->assign('is_night',1);//晚上已经领取
                }else{
                    $this->assign('is_night',0);//晚上没有领取
                }
	            
	            $this->assign('status',2);//正常页面
	        }
	    }
		$this->show();
	}
	public function share_res(){
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $user_id = I("user_id");
	    if($user_id == ''){
	        $this->assign('msg','参数不完整哦~');
	    }
	    if($user_id == $user['id']){
	        $this->assign('msg','不能给自己浇水哦~');
	    }
        $to_user_info = M('user')->where(array('id'=>$user_id))->find();
        $res = $this->add_fruit_lottery($to_user_info,3,$user['id']);
        if($res['status'] == 1){
            $this->assign('msg',$res['msg']);
        }
        if($res['status'] ==0){
            $this->assign('msg','浇水成功，快去种植自己的水果吧~');
        }
        $this->show();
	}
}