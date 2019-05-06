<?php
namespace Wechat\Controller;
use Qcloud\Sms\SmsSingleSender;
use Think\Controller;

class IndexController extends BaseController {
	private $appid = '';
	private $mch_id = '';
	private $key = '';
	private $ip = '';
	/**
	 * [index 首页]
	 * @return [type] [description]
	 */
	public function index() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$lotteryTypeModel = D('lottery_type');
		$map = array(
			'deleted' => 0,
		);
		$lottery_types = $lotteryTypeModel->where($map)->select();
		$lottery_types_user = array(
			array(
				'id' => -1,
				'name' => "&nbsp;新品",
			),
			array(
				'id' => -2,
				'name' => "收藏",
			),
		);
		foreach ($lottery_types_user as $key => $value) {
			array_push($lottery_types, $value);
		}
		$this->assign('lottery_types', $lottery_types);
		// 查询banner
		$bannerModel = D('banner');
		$map = array(
			'deleted' => 0,
			'type' => 0,
		);
		$banners = $bannerModel->where($map)->order("id desc")->select();
		$this->assign('banners', $banners);
		//查询抽奖
		$lotteryConfigModel = D('lottery_config');
		$map = array(
			'deleted' => 0,
			'is_public' => 1,
			'lottery_type_id' => 4,
		);
		$lottery_type_id = I('lottery_type_id');
		if ($lottery_type_id == -1) {
//新品
			$time_now = time(0);
			$time_start = $time_now - 10 * (24 * 3600);
			$time_start = date('Y-m-d H:i:s', $time_start);
			$time_map = array(
				array('egt', $time_start),
				array('elt', date('Y-m-d H:i:s')),
				'and',
			);
			unset($map['lottery_type_id']);
			$map['add_time'] = $time_map;
			$lottery_configs = $lotteryConfigModel->where($map)->order("sort asc")->select();

			foreach ($lottery_configs as $key => $val) {
				$temp_map = array(
					'lottery_config_id' => $val['id'],
					'user_id' => $user['id'],
				);
				$info = M('collection')->where($temp_map)->find();
				if ($info) {
					$lottery_configs[$key]['is_collection'] = 1;
				} else {
					$lottery_configs[$key]['is_collection'] = 0;
				}
			}
		} else if ($lottery_type_id == -2) {
//收藏
			$map = array(
				'deleted' => 0,
				'user_id' => $user['id'],
			);
			$lottery_configs = M('collection')->where($map)->order("add_time asc")->select();
			foreach ($lottery_configs as $key => $val) {
				$temp_map = array(
					'id' => $val['lottery_config_id'],
				);
				$info = $lotteryConfigModel->where($temp_map)->find();
				$info['is_collection'] = 1;
				$lottery_configs[$key] = $info;
			}
		} else {
			if ($lottery_type_id != ""
				&& $lottery_type_id != 0) {
				$map['lottery_type_id'] = $lottery_type_id;
			}
			$lottery_configs = $lotteryConfigModel->where($map)->order("sort asc")->select();
			foreach ($lottery_configs as $key => $val) {
				$temp_map = array(
					'lottery_config_id' => $val['id'],
					'user_id' => $user['id'],
				);
				$info = M('collection')->where($temp_map)->find();
				if ($info) {
					$lottery_configs[$key]['is_collection'] = 1;
				} else {
					$lottery_configs[$key]['is_collection'] = 0;
				}
			}
		}
		$this->assign('lottery_configs', $lottery_configs);
		$this->assign('user', $user);
		$this->show();
	}
	/**
	 * ajax_get_index_lottery_config 首页分页
	 */
	public function ajax_get_index_lottery_config() {
		$lottery_type_id = I('lottery_type_id');
		$page_no = I('page_no');
		$page_num = I('page_num');
		$lotteryConfigModel = D('lottery_config');
		$map = array(
			'deleted' => 0,
			'is_public' => 1,
			'lottery_type_id' => 4,
		);
		if ($page_no == '' || $page_num == '') {
			exit();
		}
		if ($lottery_type_id == -1) {
			//新品
			$time_now = time(0);
			$time_start = $time_now - 10 * (24 * 3600);
			$time_start = date('Y-m-d H:i:s', $time_start);
			$time_map = array(
				array('egt', $time_start),
				array('elt', date('Y-m-d H:i:s')),
				'and',
			);
			unset($map['lottery_type_id']);
			$map['add_time'] = $time_map;
			$lottery_configs = $lotteryConfigModel->where($map)->order("sort asc")->limit(($page_no - 1) * $page_num, $page_num)->select();

			foreach ($lottery_configs as $key => $val) {
				$temp_map = array(
					'lottery_config_id' => $val['id'],
					'user_id' => $user['id'],
				);
				$info = M('collection')->where($temp_map)->find();
				if ($info) {
					$lottery_configs[$key]['is_collection'] = 1;
				} else {
					$lottery_configs[$key]['is_collection'] = 0;
				}
			}
		} else if ($lottery_type_id == -2) {
			//收藏
			$map = array(
				'deleted' => 0,
				'user_id' => $user['id'],
			);
			$lottery_configs = M('collection')->where($map)->order("add_time asc")->limit(($page_no - 1) * $page_num, $page_num)->select();
			foreach ($lottery_configs as $key => $val) {
				$temp_map = array(
					'id' => $val['lottery_config_id'],
				);
				$info = $lotteryConfigModel->where($temp_map)->find();
				$info['is_collection'] = 1;
				$lottery_configs[$key] = $info;
			}
		} else {
			if ($lottery_type_id != ""
				&& $lottery_type_id != 0) {
				$map['lottery_type_id'] = $lottery_type_id;
			}
			$lottery_configs = $lotteryConfigModel->where($map)->order("sort asc")->limit(($page_no - 1) * $page_num, $page_num)->select();
			foreach ($lottery_configs as $key => $val) {
				$temp_map = array(
					'lottery_config_id' => $val['id'],
					'user_id' => $user['id'],
				);
				$info = M('collection')->where($temp_map)->find();
				if ($info) {
					$lottery_configs[$key]['is_collection'] = 1;
				} else {
					$lottery_configs[$key]['is_collection'] = 0;
				}
			}
		}
		$this->_suc_ret($lottery_configs);
	}
	/**
	 * [address 修改收货地址]
	 * @return [type] [description]
	 */
	public function address() {
		$this->show();
	}
	public function update_address() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
		$user = session('user');
		if (!$user) {
			exit;
		}

		$addressModel = D('address');
		$map = array(
			'id' => $id,
		);
		$address = $addressModel->where($map)->find();
		if ($address['user_id'] != $user['id']) {
			exit;
		}
		$this->assign('address', $address);
		$this->show();
	}

	public function ajax_update_address() {
		$realname = I('realname');
		$id = I('id');
		$address = I('address');
		$tel = I('tel');
		$remark = I('remark');
		if ($realname == ""
			|| $id == ""
			|| $address == ""
			|| $tel == "") {

			exit;
		}
		$addressModel = D('address');
		$map = array(
			'id' => $id,
		);
		$address = $addressModel->where($map)->find();
		if (!$address) {
			$this->_err_ret();
		}
		$user = session('user');
		if (!$user) {
			exit;
		}
		if ($user['id'] != $address['user_id']) {
			exit;
		}
		$data = array(
			'id' => $id,
			'real_name' => $realname,
			'address' => $address,
			'tel' => $tel,
			'remark' => $remark,
		);
		$res = $addressModel->save($data);
		if ($res === false) {
			$this->_err_ret();
		}
		$this->_suc_ret();
	}

	public function ajax_bind_address() {
		$realname = I('realname');
		$id = I('id');
		$address = I('address');
		$tel = I('tel');
		$remark = I('remark');
		if ($realname == ""
			|| $id == ""
			|| $address == ""
			|| $tel == "") {
			exit;
		}
		$lotteryRecordModel = D('lottery_record');
		$map = array(
			'id' => $id,
		);
		$lottery_record = $lotteryRecordModel->where($map)->find();
		if (!$lottery_record) {
			exit;
		}
		if ($lottery_record['status'] == 1) {
			$this->_err_ret("娃娃已经发货了，不能修改地址了哦~");
		}
		$user = session('user');
		if (!$user) {
			exit;
		}
		if ($user['id'] != $lottery_record['user_id']) {
			exit;
		}
		$data = array(
			'id' => $id,
			'realname' => $realname,
			'address' => $address,
			'tel' => $tel,
			'memo' => $remark,
		);
		$res = $lotteryRecordModel->save($data);
		if ($res === false) {
			$this->_err_ret();
		}
		$this->_suc_ret();
	}
	/**
	 * [charge 充值]
	 * @return [type] [description]
	 */
	public function charge() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();
		$this->assign('coin_num', $user['coin_num']);

		$coinConfigModel = D('coin_config');
		$map = array(
			'deleted' => 0,
		);
		$coin_configs = $coinConfigModel->where($map)->order('coin_num asc')->select();
		foreach ($coin_configs as $key => $value) {
			$vip_map = array(
				'pay_num' => $value['pay_num'],
				'level' => array('gt', 1),
			);
			$vip_coin_config = $coinConfigModel->where($vip_map)->find();
			$coin_configs[$key]['vip_config'] = $vip_coin_config;
		}
		$this->assign('coin_configs', $coin_configs);

		$this->show();
	}
	/**
	 * [vip_charge 会员充值]
	 * @return [type] [description]
	 */
	public function vip_charge() {
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
	 * [bag 我的背包]
	 * @return [type] [description]
	 */
	public function bag() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$lotteryTypeModel = D('lottery_type');
		$map = array(
			'deleted' => 0,
		);
		$lottery_types = $lotteryTypeModel->where($map)->select();
		$this->assign('lottery_types', $lottery_types);
		//查询背包
		$lotteryRecordModel = D('lottery_record');
		$map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
		);
		$lottery_type_id = I('lottery_type_id');
		if ($lottery_type_id != ""
			&& $lottery_type_id != 0) {
			$map['lottery_type_id'] = $lottery_type_id;
		}
		$lottery_records = $lotteryRecordModel->where($map)->relation(true)->select();
		$this->assign('lottery_records', $lottery_records);
		$this->show();
	}
	/**
	 * [user 个人中心]
	 * @return [type] [description]
	 */
	public function user() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();
		$this->assign('coin_num', $user['coin_num']);
		$this->assign('head_image', $user['headimgurl']);
		$this->assign('user_id', $user['id']);
		$this->assign('nickname', $user['nickname']);
		$this->assign('phone', $user['phone']);
		$this->assign('level', $user['level']);
		$this->assign('ticket', $user['ticket']);
		$this->assign('is_agent', $user['is_agent']);
		$map = array(
			'level' => $user['level'],
		);
		$temp = M('vip_grade')->where($map)->find();
		$this->assign('level_name', $temp['name']);
		if ($user['over_time'] != '0000-00-00 00:00:00') {
			$over_time = strtotime($user['over_time']);
			if (time() > $over_time) {
				$is_over = 1;
			} else {
				$is_over = 0;
			}
		} else {
			$is_over = 0;
		}
		$this->assign('is_over', $is_over);

		$this->show();
	}
	/**
	 * [ajax_get_vip_config 会员购买配置]
	 * @return [type] [description]
	 */
	public function ajax_get_vip_config() {
		$level = I('level');
		if ($level == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'level' => $level,
		);
		$temp = M('vip_grade')->where($map)->find();
		if (!$temp) {
			$this->_err_ret('会员等级不存在');
		}
		$map = array(
			'grade_id' => $temp['id'],
		);
		$res = M('vip_pay_config')->where($map)->find();
		$this->_suc_ret($res);
	}
	/**
	 * [ajax_get_vip 会员等级列表]
	 * @return [type] [description]
	 */
	public function ajax_get_vip() {
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

		$map = array(
			'deleted' => 0,
		);
		$temp = M('vip_grade')->where($map)->order('level asc')->select();
		foreach ($temp as $key => $val) {
			$temp[$key]['over_time'] = substr($user['over_time'], 0, 10);
			if ($user['over_time'] != '0000-00-00') {
				// 判断用户是否处于当前等级
				if ($user['level'] == $temp[$key]['level']) {
					$temp[$key]['is_this_level'] = 1;
					if (time(0) >= strtotime($user['over_time'])) {
						$temp[$key]['is_over'] = 1;
					} else {
						$temp[$key]['is_over'] = 0;
					}
				} else {
					$temp[$key]['is_this_level'] = 0;
					$temp[$key]['is_over'] = 1;
				}
			} else {
				$temp[$key]['is_this_level'] = 0;
				$temp[$key]['is_over'] = 1;
			}

			$map = array(
				'grade_id' => $val['id'],
			);
			$vip_pay_config = M('vip_pay_config')->where($map)->order('days asc')->select();
			if ($val['level'] == 2) {
				$user_order_map = array(
					'deleted' => 0,
					'user_id' => $user['id'],
					'status' => 1,
					'type' => 0,
				);
				$user_order = M('vip_order')->where($user_order_map)->select();
				if (count($user_order) < 3 && $vip_pay_config[0]['discount_price'] > 1) {
					$vip_pay_config[0]['discount_price'] = '1.00';
				}
			}
			$temp[$key]['vip_pay_config'] = $vip_pay_config;
		}

		$this->_suc_ret($temp);
	}
	/**
	 * [game 游戏]
	 * @return [type] [description]
	 */
	public function game() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'id' => $id,
		);
		$lotteryConfigModel = D('lottery_config');
		$lottery_config = $lotteryConfigModel->where($map)->find();
		if (!$lottery_config) {
			exit;
		}
		/*
			$user = session('user');
			$map = array(
			    'id' => $user['id'],
			);
			$userModel = D('user');
			$user = $userModel->where($map)->find();
			if (!$user) {
			    exit;
			}
			if($lottery_config['level']>$user['level']){
			    exit('等级不够');
		*/
		$goods_list = array();
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id0']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id1']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id2']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id3']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id4']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$zhua_num = mt_rand(20, 30);
		$lottery_config['zhua_num'] = $zhua_num;
		$this->assign('goods_list', $goods_list);
		$this->assign('lottery_config', $lottery_config);
		$temp_map = array(
			'lottery_config_id' => $lottery_config['id'],
			'user_id' => $user['id'],
		);
		$info = M('collection')->where($temp_map)->find();
		if ($info) {
			$is_collection = 1;
		} else {
			$is_collection = 0;
		}
		$this->assign('is_collection', $is_collection);
		$this->show();
	}
	public function game_gloden() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'id' => $id,
		);
		$lotteryConfigModel = D('lottery_config');
		$lottery_config = $lotteryConfigModel->where($map)->find();
		if (!$lottery_config) {
			exit;
		}
		/*
			$user = session('user');
			$map = array(
			    'id' => $user['id'],
			);
			$userModel = D('user');
			$user = $userModel->where($map)->find();
			if (!$user) {
			    exit;
			}
			if($lottery_config['level']>$user['level']){
			    exit('等级不够');
		*/
		$goods_list = array();
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id0']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id1']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id2']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id3']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id4']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$zhua_num = mt_rand(20, 30);
		$lottery_config['zhua_num'] = $zhua_num;
		$this->assign('goods_list', $goods_list);
		$this->assign('lottery_config', $lottery_config);

		$temp_map = array(
			'lottery_config_id' => $lottery_config['id'],
			'user_id' => $user['id'],
		);
		$info = M('collection')->where($temp_map)->find();
		if ($info) {
			$is_collection = 1;
		} else {
			$is_collection = 0;
		}
		$this->assign('is_collection', $is_collection);
		$this->show();
	}
	public function game_diamond() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'id' => $id,
		);
		$lotteryConfigModel = D('lottery_config');
		$lottery_config = $lotteryConfigModel->where($map)->find();
		if (!$lottery_config) {
			exit;
		}
		/*
			$user = session('user');
			$map = array(
			    'id' => $user['id'],
			);
			$userModel = D('user');
			$user = $userModel->where($map)->find();
			if (!$user) {
			    exit;
			}
			if($lottery_config['level']>$user['level']){
			    exit('等级不够');
		*/
		$goods_list = array();
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id0']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id1']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id2']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id3']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$goods_info = M('lottery_good')->where(array('id' => $lottery_config['lottery_good_id4']))->find();
		if ($goods_info) {
			$goods_list[] = $goods_info;
		}
		$zhua_num = mt_rand(20, 30);
		$lottery_config['zhua_num'] = $zhua_num;
		$this->assign('goods_list', $goods_list);
		$this->assign('lottery_config', $lottery_config);

		$temp_map = array(
			'lottery_config_id' => $lottery_config['id'],
			'user_id' => $user['id'],
		);
		$info = M('collection')->where($temp_map)->find();
		if ($info) {
			$is_collection = 1;
		} else {
			$is_collection = 0;
		}
		$this->assign('is_collection', $is_collection);
		$this->show();
	}

	/**
	 * [actvity_game 活动游戏]
	 * @return [type] [description]
	 */
	public function actvity_game() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'id' => 1,
		);
		$lottery_activity = M('lottery_activity')->where($map)->find();
		if (!$lottery_activity || $lottery_activity['is_open'] == 0 || ($lottery_activity['over_time'] != '0000-00-00 00:00:00' && strtotime($lottery_activity['over_time']) < time())) {
			if (strtotime($lottery_activity['over_time']) < time()) {
				$data = array(
					'id' => 1,
					'is_open' => 0,
				);
				M('lottery_activity')->save($data);
			}
			$this->assign('is_open', 0); //已经关闭
		} else {
			$this->assign('is_open', 1); //正常进行
		}
		$lottery_activity_goods_map = array(
			'lottery_activity_id' => 1,
			'deleted' => 0,
		);
		$lottery_activity_goods = M('lottery_activity_goods')->where($lottery_activity_goods_map)->select();
		$return = array();
		foreach ($lottery_activity_goods as $key => $val) {
			$goods_map = array(
				'id' => $val['goods_id'],
			);
			$goods_info = M('lottery_good')->where($goods_map)->find();
			if ($goods_info) {
				$return[] = $goods_info;
			}
		}
		$zhua_num = mt_rand(20, 30);
		$lottery_activity['zhua_num'] = $zhua_num;
		$this->assign('goods_list', $return);
		$this->assign('lottery_config', $lottery_activity);

		$this->show();
	}

	public function lottery() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
		$map = array(
			'id' => $id,
		);
		$lotteryConfigModel = D('lottery_config');
		$lottery_config = $lotteryConfigModel->where($map)->find();
		if (!$lottery_config) {
			exit;
		}

		$user = session('user');
		$map = array(
			'id' => $user['id'],
		);
		$userModel = D('user');
		$user = $userModel->where($map)->find();
		if (!$user) {
			exit;
		}
		$data = array(
			'id' => $id,
			'zhua_times' => $lottery_config['zhua_times'] + 1,
		);
		$res = $lotteryConfigModel->save($data);
		if (!$res) {
			exit;
		}
		$fail_img_url = 'https://fssw.bichonfrise.cn/Public/weixin/image/fail.png';
		$res_data = array(
			'good_name' => '',
			'good_url' => $fail_img_url,
			'user_coin_num' => $user['coin_num'],
			'msg' => "很遗憾，您这次没有抓到，请再接再厉！",
		);

		if ($lottery_config['level'] > $user['level']) {
			$temp_map = array(
				'level' => $lottery_config['level'],
			);
			$vip_grade = M('vip_grade')->where($temp_map)->find();
			$res_data['msg'] = "您不是" . $vip_grade['name'] . "，请先升级为" . $vip_grade['name'] . "！";
			$this->_suc_ret($res_data);
		}
		$is_hit = 0;
		$hit_good_id = 0;
		if ($user['forbidden'] == 1) {
			$res_data['msg'] = "因恶意使用系统，您已被禁止抽奖！";
			$this->_suc_ret($res_data);
		}
		if ($user['coin_num'] - $lottery_config['coin_num'] < 0) {
			$res_data['msg'] = "糖豆不足，快去收集或充值糖豆吧~";
			$this->_suc_ret($res_data);
		}
		// 扣除用户的糖豆
		$data = array(
			'id' => $user['id'],
			'coin_num' => $user['coin_num'] - $lottery_config['coin_num'],
		);
		$res = $userModel->save($data);
		if ($res === false) {
			$res_data['msg'] = "扣除糖豆失败!";
			$this->_suc_ret($res_data);
		}
		$MAX_PERCENT_NUM = mt_getrandmax(); //2147483647
		// 开始抽奖
		$total = 0;
		$prize_arr = array(0);
		for ($i = 0; $i < 5; $i++) {
			$total += $lottery_config['percent' . $i];
			array_push($prize_arr, $total);
		}
		if ($total < $MAX_PERCENT_NUM) {
			array_push($prize_arr, $MAX_PERCENT_NUM);
		}
		//$random_num = mt_rand(0, $MAX_PERCENT_NUM);
		$random_num = mt_rand(0, $total);
		$target_index = -1;
		for ($i = 0; $i < count($prize_arr); $i++) {
			if ($prize_arr[$i] > $random_num) {
				$target_index = $i - 1;
				break;
			}
		}
		// 可能中奖
		if ($target_index < 5) {
			$lottery_good_id = $lottery_config['lottery_good_id' . $target_index];
			if ($lottery_good_id != 0) {
				$lotteryGoodModel = D('lottery_good');
				$map = array(
					'id' => $lottery_good_id,
				);
				$lottery_good = $lotteryGoodModel->where($map)->find();
				if ($lottery_good && $lottery_good['stock'] > 0) {

					/*$res_data['good_name'] = $lottery_good['name'];
						$res_data['good_url'] = $lottery_good['img_url'];
						// 添加中奖记录
						$lotteryRecordModel = D('lottery_record');
						$data = array(
							'lottery_type_id' => $lottery_config['lottery_type_id'],
							'lottery_config_id' => $lottery_config['id'],
							'user_id' => $user['id'],
							'lottery_good_id' => $lottery_good_id,
							'add_time' => date('Y-m-d H:i:s', time(0)),
						);
						$res = $lotteryRecordModel->add($data);
						// 添加记录失败提示用户没抽中，以免引起投诉
						if (!$res) {
							$res_data['good_name'] = "";
							$res_data['good_url'] = $fail_img_url;
						} else {
							$is_hit = 1;
							$hit_good_id = $lottery_good_id;
							$data = array(
								'id' => $lottery_good_id,
								'stock' => $lottery_good['stock'] - 1,
							);
							$lotteryGoodModel->save($data);
					*/
					$good_id = $lottery_good_id;
					$is_hit = 1;
				}
			}
		}
		if ($is_hit == 1) {
			//如果根据概率得出中奖
			$log_map = array(
				'deleted' => 0,
				'user_id' => $user['id'],
				'lottery_config_id' => $lottery_config['id'],
			);
			$luck_draw_log_list = M('luck_draw_log')->where($log_map)->order('add_time desc')->limit(2)->select();
			$count = M('luck_draw_log')->where($log_map)->count();
			if (!$count) {
				$count = 0;
			}
			if ($lottery_config['coin_num'] == 20) {
				$t_map = array(
					'deleted' => 0,
					'user_id' => $user['id'],
					'consume_num' => 20,
					'is_hit' => 1,
				);
				$temp_count = M('luck_draw_log')->where($t_map)->count();
				if (!$temp_count) {
					$temp_count = 0;
				}
				if ($temp_count == 0) {
					$is_hit = 1;
				} else {
					$times = $count % 3;
					if ($times == 0) {
						//第一次抽奖
						$luck_num = C('ONE_LUCK') * 100;
						$temp = rand(1, 100);
						if ($temp > 0 && $temp < $luck_num) {
							$is_hit = 1;
						} else {
							$is_hit = 0;
						}
					}
					if ($times == 1) {
						//第二次抽奖
						if ($luck_draw_log_list[0]['is_hit'] == 0) {
							//第一次没中
							$luck_num = C('TWO_LUCK') * 100;
							$temp = rand(1, 100);
							if ($temp > 0 && $temp < $luck_num) {
								$is_hit = 1;
							} else {
								$is_hit = 0;
							}
						} else {
							//第一次中了
							$is_hit = 0;
						}
					}
					if ($times == 2) {
						//第三次抽奖
						if ($luck_draw_log_list[1]['is_hit'] == 0) {
							//第二次没中
							if ($luck_draw_log_list[0]['is_hit'] == 0) {
								//第一次没中
								$luck_num = C('THREE_LUCK') * 100;
								$temp = rand(1, 100);
								if ($temp > 0 && $temp < $luck_num) {
									$is_hit = 1;
								} else {
									$is_hit = 0;
								}
							} else {
								$is_hit = 0;
							}
						} else {
							//第二次中了
							$is_hit = 0;
						}
					}
				}
			} else {
				$times = $count % 3;
				if ($times == 0) {
					//第一次抽奖
					$luck_num = C('ONE_LUCK') * 100;
					$temp = rand(1, 100);
					if ($temp > 0 && $temp < $luck_num) {
						$is_hit = 1;
					} else {
						$is_hit = 0;
					}
				}
				if ($times == 1) {
					//第二次抽奖
					if ($luck_draw_log_list[0]['is_hit'] == 0) {
						//第一次没中
						$luck_num = C('TWO_LUCK') * 100;
						$temp = rand(1, 100);
						if ($temp > 0 && $temp < $luck_num) {
							$is_hit = 1;
						} else {
							$is_hit = 0;
						}
					} else {
						//第一次中了
						$is_hit = 0;
					}
				}
				if ($times == 2) {
					//第三次抽奖
					if ($luck_draw_log_list[1]['is_hit'] == 0) {
						//第二次没中
						if ($luck_draw_log_list[0]['is_hit'] == 0) {
							//第一次没中
							$luck_num = C('THREE_LUCK') * 100;
							$temp = rand(1, 100);
							if ($temp > 0 && $temp < $luck_num) {
								$is_hit = 1;
							} else {
								$is_hit = 0;
							}
						} else {
							$is_hit = 0;
						}
					} else {
						//第二次中了
						$is_hit = 0;
					}
				}
			}

		}
		//$this->_suc_ret($is_hit);die();
		$lottery_good_name = '';
		if ($is_hit == 0) {
			$res_data['good_name'] = "";
			$res_data['good_url'] = $fail_img_url;

			$temp_data = array(
				'add_time' => date('Y-m-d H:i:s'),
				'deleted' => 0,
				'user_id' => $user['id'],
				'lottery_config_id' => $lottery_config['id'],
				'consume_num' => $lottery_config['coin_num'],
				'is_hit' => $is_hit,
				'hit_good_id' => 0,
			);
		} else {
			$lotteryGoodModel = D('lottery_good');
			$map = array(
				'id' => $good_id,
			);
			$lottery_good = $lotteryGoodModel->where($map)->find();
			$res_data['good_name'] = $lottery_good['name'];
			$res_data['good_url'] = $lottery_good['img_url'];
			// 添加中奖记录
			$lotteryRecordModel = D('lottery_record');
			$data = array(
				'lottery_type_id' => $lottery_config['lottery_type_id'],
				'lottery_config_id' => $lottery_config['id'],
				'user_id' => $user['id'],
				'lottery_good_id' => $good_id,
				'add_time' => date('Y-m-d H:i:s', time(0)),
			);
			$res = $lotteryRecordModel->add($data);
			$lottery_record_id = $res;
			// 添加记录失败提示用户没抽中，以免引起投诉
			if (!$res) {
				$is_hit = 0;
				$res_data['good_name'] = "";
				$res_data['good_url'] = $fail_img_url;
				$temp_data = array(
					'add_time' => date('Y-m-d H:i:s'),
					'deleted' => 0,
					'user_id' => $user['id'],
					'lottery_config_id' => $lottery_config['id'],
					'consume_num' => $lottery_config['coin_num'],
					'is_hit' => $is_hit,
					'hit_good_id' => 0,
				);
			} else {
				$lottery_good_name = $lottery_good['name'];
				// 通知用户中奖了
				$this->send_zhuawawa_inner_msg($user, $lottery_good['name'], $lottery_config['name']);
				$data = array(
					'id' => $good_id,
					'stock' => $lottery_good['stock'] - 1,
				);
				$lotteryGoodModel->save($data);
				if (($lottery_config['new_stock'] - 1) >= 0) {
					$data = array(
						'id' => $lottery_config['id'],
						'new_stock' => $lottery_config['new_stock'] - 1,
					);
					$lotteryConfigModel->save($data);
				}
				//更新用户中奖记录
				$user_data = array(
					'id' => $user['id'],
					'record_total' => $user['record_total'] + 1,
				);
				$res = M('user')->save($user_data);
				// 添加糖豆扣除记录
				$temp_data = array(
					'add_time' => date('Y-m-d H:i:s'),
					'deleted' => 0,
					'user_id' => $user['id'],
					'lottery_config_id' => $lottery_config['id'],
					'consume_num' => $lottery_config['coin_num'],
					'is_hit' => $is_hit,
					'hit_good_id' => $good_id,
				);

				//佣金计算
				if ($user['p_id'] != 0/* && $user['is_test'] != 1*/) {
					$p_map = array(
						'id' => $user['p_id'],
						'deleted' => 0,
					);
					$p_info = M('user')->where($p_map)->find();
					if ($p_info/*&& $p_info['is_test'] != 1*/) {
						$data = array(
							'add_time' => date('Y-m-d H:i:s'),
							'deleted' => 0,
							'user_id' => $user['id'],
							'p_id' => $p_info['id'],
							'lottery_record_id' => $lottery_record_id,
							'vip_order_id' => 0,
							'type' => 1,
						);
						if ($p_info['is_agent'] == 1) {
							$data['money'] = C('AGENT_GOOD');
						} else {
							$data['money'] = C('ORDINARY_GOOD');
						}
						$maid_res = M('maid_log')->add($data);
						if ($maid_res) {
							if (C('IS_PAY_PARENT_USER_DIRECT') == 1) {
								//$pay_res = $this->direct_send_parent_user_money($p_info, $user, $data['money']);
								$pay_res = $this->direct_send_parent_user_redpackage($p_info, $user, $data['money']);
								//付款记录添加
								$data = array(
									'id' => $maid_res,
									'is_cash' => C('IS_PAY_PARENT_USER_DIRECT'),
									'wx_callback' => $pay_res,
								);
								$temp_maid_res = M('maid_log')->save($data);
							} else {
								$user_update_data = array(
									'id' => $p_info['id'],
									'yu_e' => $p_info['yu_e'] + $data['money'],
								);
								$user_update_res = M('user')->save($user_update_data);
								$p_info = M('user')->where(array('id' => $p_info['id']))->find();
								if ($user_update_res) {
									//通知父级用户佣金到账
									$this->send_lottery_msg($user, $p_info, $data['money']);

								}
							}

						}
					}
				}
			}
		}

		$map_admin = array(
			'openid' => 'oKFOO1hc7P90MGMU39zaIgYmJh0k',
		);
		$admin_user = $userModel->where($map_admin)->find();
		$this->send_zhuawawa_inner_msg_admin($admin_user, $lottery_config, $is_hit, $user, $lottery_good_name);

		$res = M('luck_draw_log')->add($temp_data);
		//更新用户领养成长值
		$this->add_adopt_lottery($user, 3); //开始1局任意游戏
		$this->_suc_ret($res_data);
	}

	public function activity_lottery() {
		$map = array(
			'id' => 1,
		);
		$lottery_config = M('lottery_activity')->where($map)->find();
		if (!$lottery_config) {
			exit;
		}
		if ($lottery_config['is_open'] == 0) {
			exit;
		}

		$user = session('user');
		$map = array(
			'id' => $user['id'],
		);
		$userModel = D('user');
		$user = $userModel->where($map)->find();
		if (!$user) {
			exit;
		}
		//已抓取次数
		$lottery_activity_log_map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
			'lottery_activity_id' => 1,
		);
		$lottery_activity_log = M('lottery_activity_log')->where($lottery_activity_log_map)->select();
		$fail_img_url = 'https://fssw.bichonfrise.cn/Public/weixin/image/fail.png';
		$res_data = array(
			'good_name' => '',
			'good_url' => $fail_img_url,
			'user_coin_num' => $user['coin_num'],
			'msg' => "很遗憾，您这次没有抓到，请再接再厉！",
		);

		$res_data['times'] = $lottery_config['times'] - count($lottery_activity_log) - 1;
		$is_hit = 0;
		$hit_good_id = 0;
		if ($user['forbidden'] == 1) {
			$res_data['msg'] = "因恶意使用系统，您已被禁止抽奖！";
			$this->_suc_ret($res_data);
		}
		if ($lottery_config['times'] - count($lottery_activity_log) < 1) {
			$res_data['msg'] = "次数已用完~";
			$res_data['times'] = 0;
			$this->_suc_ret($res_data);
		}
		if ($user['coin_num'] < 1) {
			$res_data['msg'] = "糖豆不足，快去收集或充值糖豆吧~";
			$this->_suc_ret($res_data);
		}

		// 扣除用户的糖豆
		$data = array(
			'id' => $user['id'],
			'coin_num' => $user['coin_num'] - 1,
		);
		$res = $userModel->save($data);
		if ($res === false) {
			$res_data['msg'] = "扣除糖豆失败!";
			$this->_suc_ret($res_data);
		}
		$res_data['user_coin_num'] = $user['coin_num'] - 1;
		$MAX_PERCENT_NUM = mt_getrandmax(); //2147483647
		// 开始抽奖
		//获取中奖商品
		$map = array(
			'lottery_activity_id' => 1,
			'deleted' => 0,
		);
		$goods_list = M('lottery_activity_goods')->where($map)->select();
		$is_hit = 0;
		$hit_goods_id = 0;
		$lottery_activity_goods_info = array();
		//已抓取次数
		$lottery_activity_log_map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
			'lottery_activity_id' => 1,
			'is_hit' => 1,
		);
		$lottery_activity_log = M('lottery_activity_log')->where($lottery_activity_log_map)->select();
		if (count($lottery_activity_log) == 0) {
			for ($i = 0; $i < count($goods_list); $i++) {
				$luck_num = $goods_list[$i]['probability'] * 100;
				$temp = rand(1, 100);
				if ($temp > 0 && $temp < $luck_num && $goods_list[$i]['obtain_num'] > 0) {
					$is_hit = 1;
					$hit_goods_id = $goods_list[$i]['goods_id'];
					$goods_list[$i]['goods_info'] = M('lottery_good')->where(array('id' => $goods_list[$i]['goods_id']))->find();
					$lottery_activity_goods_info = $goods_list[$i];
					break;
				}
			}
		}
		$lottery_good_name = '';
		if ($is_hit == 0) {
			$res_data['good_name'] = "";
			$res_data['good_url'] = $fail_img_url;

			$temp_data = array(
				'add_time' => date('Y-m-d H:i:s'),
				'deleted' => 0,
				'user_id' => $user['id'],
				'lottery_config_id' => 0,
				'consume_num' => 1,
				'is_hit' => $is_hit,
				'hit_good_id' => 0,
			);
			$lottery_activity_log_data = array(
				'add_time' => date('Y-m-d H:i:s'),
				'deleted' => 0,
				'user_id' => $user['id'],
				'lottery_activity_id' => 1,
				'is_hit' => $is_hit,
				'hit_good_id' => $hit_goods_id,
			);
		} else {
			$lotteryGoodModel = D('lottery_good');
			$map = array(
				'id' => $hit_goods_id,
			);
			$lottery_good = $lotteryGoodModel->where($map)->find();
			$res_data['good_name'] = $lottery_good['name'];
			$res_data['good_url'] = $lottery_good['img_url'];
			// 添加中奖记录
			$lotteryRecordModel = D('lottery_record');
			$data = array(
				'lottery_type_id' => $lottery_activity_goods_info['goods_info']['lottery_type_id'],
				'lottery_config_id' => $lottery_activity_goods_info['lottery_config_id'],
				'user_id' => $user['id'],
				'lottery_good_id' => $hit_goods_id,
				'add_time' => date('Y-m-d H:i:s', time(0)),
				'type' => -5,
			);
			$res = $lotteryRecordModel->add($data);
			$lottery_record_id = $res;
			// 添加记录失败提示用户没抽中，以免引起投诉
			if (!$res) {
				$is_hit = 0;
				$hit_goods_id = 0;
				$res_data['good_name'] = "";
				$res_data['good_url'] = $fail_img_url;
				$temp_data = array(
					'add_time' => date('Y-m-d H:i:s'),
					'deleted' => 0,
					'user_id' => $user['id'],
					'lottery_config_id' => 0,
					'consume_num' => 1,
					'is_hit' => $is_hit,
					'hit_good_id' => 0,
				);
				$lottery_activity_log_data = array(
					'add_time' => date('Y-m-d H:i:s'),
					'deleted' => 0,
					'user_id' => $user['id'],
					'lottery_activity_id' => 1,
					'is_hit' => $is_hit,
					'hit_good_id' => $hit_goods_id,
				);
			} else {
				$lottery_good_name = $lottery_good['name'];
				// 通知用户中奖了
				$this->send_zhuawawa_inner_msg($user, $lottery_activity_goods_info['goods_info']['name'], $lottery_config['name']);

				$where = array(
					'goods_id' => $hit_goods_id,
				);
				$data = array(
					'obtain_num' => $lottery_activity_goods_info['obtain_num'] - 1,
				);
				M('lottery_activity_goods')->where($where)->save($data);
				//更新用户中奖记录
				$user_data = array(
					'id' => $user['id'],
					'record_total' => $user['record_total'] + 1,
				);
				$res = M('user')->save($user_data);
				// 添加糖豆扣除记录
				$temp_data = array(
					'add_time' => date('Y-m-d H:i:s'),
					'deleted' => 0,
					'user_id' => $user['id'],
					'lottery_config_id' => $lottery_activity_goods_info['lottery_config_id'],
					'consume_num' => 1,
					'is_hit' => $is_hit,
					'hit_good_id' => $hit_goods_id,
				);

				$lottery_activity_log_data = array(
					'add_time' => date('Y-m-d H:i:s'),
					'deleted' => 0,
					'user_id' => $user['id'],
					'lottery_activity_id' => 1,
					'is_hit' => $is_hit,
					'hit_good_id' => $hit_goods_id,
				);
			}
		}

		$map_admin = array(
			'openid' => 'oKFOO1hc7P90MGMU39zaIgYmJh0k',
		);
		$admin_user = $userModel->where($map_admin)->find();
		$this->send_zhuawawa_inner_msg_admin($admin_user, $lottery_config, $is_hit, $user, $lottery_good_name);

		$res = M('luck_draw_log')->add($temp_data);
		$res = M('lottery_activity_log')->add($lottery_activity_log_data);
		$this->_suc_ret($res_data);
	}
	public function send_wxcallpay_msg_admin($admin_user, $res_code, $res) {
		$openId = $admin_user['openid'];
		$postData = array(
			"touser" => $openId,
			"template_id" => C("ACTIVITY_COIN_GET_TMPL_ID"),
			"url" => "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index",
			"topcolor" => "#FF0000",
			"data" => array(
				"first" => array(
					"value" => "商户支付失败，错误码：" . $res_code . ",错误信息：" . $res,
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
	public function direct_send_parent_user_money($p_info, $user, $money) {
		$desc = '您邀请的好友：“' . $user['nickname'] . '”抓到了娃娃，您的奖励金已到账。';
		$res = $this->sendMoney($money, $p_info['openid'], $desc);
		return $res;
	}
	public function direct_send_parent_user_redpackage($p_info, $user, $money) {
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
			'wishing' => "您邀请的好友" . $user['nickname'] . "抓到娃娃补贴现金啦~",
			'client_ip' => $this->ip, //Ip地址
			'scene_id' => "PRODUCT_5",
			'remark' => $p_info['nickname'] . "邀请的好友" . $user['nickname'] . "抓到娃娃补贴现金啦~",
			'act_name' => "邀请好友抓到娃娃补贴",
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
		$xml = $this->arraytoxml($data);

		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack'; //调用接口
		$res = $this->curl($xml, $url);
		$return = $this->xmltoarray($res);

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
			$this->send_wxcallpay_msg_admin($admin_user, $responseObj->err_code, $responseObj->err_code_des);
		}
		return json_encode($return);
	}
	/**
	 * 测试直接转账
	 *
	 */
// 	public function ceshi_send_money() {
	// 	    $map = array(
	// 	        'deleted'=>0,
	// 	        'is_cash'=>1,
	// 	        'wx_callback'=>'{"return_code":"SUCCESS","return_msg":"\u53c2\u6570\u9519\u8bef:\u63cf\u8ff0\u4fe1\u606f\u5927\u4e8e100Bytes","result_code":"FAIL","err_code":"PARAM_ERROR","err_code_des":"\u53c2\u6570\u9519\u8bef:\u63cf\u8ff0\u4fe1\u606f\u5927\u4e8e100Bytes"}'
	// 	    );
	// 	    $temp_list = M('maid_log')->where($map)->select();
	// 	    foreach($temp_list as $key=>$val){
	// 	        $user = M('user')->where(array('id'=>$val['user_id']))->find();
	// 	        $p_info = M('user')->where(array('id'=>$val['p_id']))->find();
	// 	        $desc = '您邀请的好友：“' . $user['nickname'] . '”抓到了娃娃，您的奖励金已到账。';
	// 	        $res = $this->sendMoney(1, $p_info['openid'], $desc);
	// 	        $data = array(
	// 	            'id'=>$val['id'],
	// 	            'wx_callback'=>$res
	// 	        );
	// 	        M('maid_log')->save($data);
	// 	    }
	// 	}
	/*
		 $amount 发送的金额（分）目前发送金额不能少于1元
		 $re_openid, 发送人的 openid
		 $desc  //  企业付款描述信息 (必填)
		 $check_name    收款用户姓名 (选填)
	*/
	function sendMoney($amount, $re_openid, $desc = '', $check_name = '') {

		$total_amount = (100) * $amount;

		$data = array(
			'mch_appid' => $this->appid, //商户账号appid
			'mchid' => $this->mch_id, //商户号
			'nonce_str' => $this->createNoncestr(), //随机字符串
			'partner_trade_no' => date('YmdHis') . rand(1000, 9999), //商户订单号
			'openid' => $re_openid, //用户openid
			'check_name' => 'NO_CHECK', //校验用户姓名选项,
			're_user_name' => '', //收款用户姓名
			'amount' => $total_amount, //金额
			'desc' => $desc, //企业付款描述信息
			'spbill_create_ip' => $this->ip, //Ip地址
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
		$xml = $this->arraytoxml($data);

		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; //调用接口
		$res = $this->curl($xml, $url);
		$return = $this->xmltoarray($res);

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
			$this->send_wxcallpay_msg_admin($admin_user, $responseObj->err_code, $responseObj->err_code_des);
		}
		return json_encode($return);
	}
	function arraytoxml($data) {
		$str = '<xml>';
		foreach ($data as $k => $v) {
			$str .= '<' . $k . '>' . $v . '</' . $k . '>';
		}
		$str .= '</xml>';
		return $str;
	}
	function xmltoarray($xml) {
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$val = json_decode(json_encode($xmlstring), true);
		return $val;
	}
	function curl($param = "", $url) {

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
	public function ajax_get_lottery() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
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
		$lotteryConfigModel = D('lottery_config');
		$map = array(
			'id' => $id,
		);
		$lottery_config = $lotteryConfigModel->relation(true)->where($map)->find();
		$lottery_config['user_coin_num'] = $user['coin_num'];
		if (!$lottery_config) {
			$this->_err_ret();
		}
		$good_num = 0;
		for ($i = 0; $i < 5; $i++) {
			if ($lottery_config['lottery_good_id' . $i] != 0) {
				$good_num = $good_num + 1;
			}
		}
		$lottery_config['good_num'] = $good_num;
		$this->_suc_ret($lottery_config);
	}

	public function ajax_get_activity_lottery() {
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
		$map = array(
			'id' => 1,
		);
		$lottery_config = M('lottery_activity')->where($map)->find();
		$lottery_config['user_coin_num'] = $user['coin_num'];

		//已抓取次数
		$lottery_activity_log_map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
			'lottery_activity_id' => 1,
		);
		$lottery_activity_log = M('lottery_activity_log')->where($lottery_activity_log_map)->select();
		$lottery_config['times'] = $lottery_config['times'] - count($lottery_activity_log);
		if (!$lottery_config) {
			$this->_err_ret();
		}
		$lottery_activity_goods_map = array(
			'lottery_activity_id' => 1,
			'deleted' => 0,
		);
		$lottery_activity_goods = M('lottery_activity_goods')->where($lottery_activity_goods_map)->select();
		$return = array();
		foreach ($lottery_activity_goods as $key => $val) {
			$goods_map = array(
				'id' => $val['goods_id'],
			);
			$goods_info = M('lottery_good')->where($goods_map)->find();
			$goods_info['probability'] = $val['probability'];
			$return[] = $goods_info;
		}
		$lottery_config['good_num'] = count($lottery_activity_goods);
		for ($i = 0; $i < $lottery_config['good_num']; $i++) {
			$lottery_config['lottery_good' . $i] = $return[$i];
		}
		$lottery_config["lottery_type"] = array(
			"id" => "4",
			"add_time" => "2018-08-09 17:52:45",
			"deleted" => "0",
			"name" => "活动场",
		);
		$this->_suc_ret($lottery_config);
	}
	public function get_lottery_record() {
		$lotteryRecordModel = D('lottery_record');
		$map = array(
			'deleted' => 0,
		);
		$lottery_records = $lotteryRecordModel
			->where($map)
			->order('add_time desc')
			->relation(true)
			->limit(3)
			->select();
		$data = array();
		if ($lottery_records !== false) {
			foreach ($lottery_records as $key => $value) {
				$tmp = array(
					'user_name' => '',
					'prize_name' => '',
				);
				$tmp['user_name'] = $value['user']['nickname'];
				if (mb_strlen($value['user']['nickname']) > 6) {
					$tmp['user_name'] = substr($value['user']['nickname'], 0, 6) . "...";
				}
				$tmp['prize_name'] = $value['lottery_good']['name'];
				array_push($data, $tmp);
			}
		}
		$this->_suc_ret($data);

	}
	public function article() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
		$articleModel = D('article');
		$map = array(
			'id' => $id,
		);
		$article = $articleModel->where($map)->find();
		if (!$article) {
			exit;
		}
		$this->assign('article', $article);
		$this->show();
	}
	/**
	 * activity  活动详情
	 */
	public function activity() {
		$activity_id = I('id');
		$user = session('user');
		if (!$user) {
			exit;
		}
		if ($activity_id == '') {
			$this->assign('type', 1); //活动不存在
		}
		$map = array(
			'id' => $activity_id,
			'deleted' => 0,
		);
		$amount_config = M('amount_config')->where($map)->find();
		if (!$amount_config) {
			exit;
		}
		if ($amount_config['is_open'] == 0) {
			//exit;
		}
		$user_info = M('user')->where(array('id' => $user['id']))->find();
		$this->assign('activity_info', $amount_config);
		$user_info['show_phone'] = substr($user_info['phone'], 0, 3) . "****" . substr($user_info['phone'], 7, 4);
		$this->assign('user_info', $user_info);
		$this->show();
	}
	/**
	 * ajax_link_activity  领取活动糖豆
	 */
	public function ajax_link_activity() {
		$activity_id = I('id');
		$tel = I('tel');
		$user = session('user');
		if (!$user) {
			$this->_err_ret('您尚未登录哦~');
		}
		$user_info = M('user')->where(array('id' => $user['id']))->find();
		if (!$user_info) {
			$this->_err_ret('该用户不存在~');
		}
		if ($user_info['phone'] == '') {
			$this->_err_ret('need_bind_phone');
		}
		if ($user_info['phone'] != $tel) {
			$this->_err_ret('您输入手机号与绑定手机号不符~');
		}
		$map = array(
			'id' => $activity_id,
			'deleted' => 0,
			'style' => 0,
		);
		$amount_config = M('amount_config')->where($map)->find();
		if (!$amount_config) {
			$this->_err_ret('活动不存在~');
		}
		if ($amount_config['is_open'] == 0) {
			$this->_err_ret('活动已结束~');
		}
		if ($amount_config['type'] == 0) {
			//单人限次
			if ($activity_id == 2) {
				$temp_map = array(
					'type' => array('IN', '2,-1'),
					'user_id' => $user['id'],
					'deleted' => 0,
					'status' => 1,
				);
			} else {
				$temp_map = array(
					'type' => $activity_id,
					'user_id' => $user['id'],
					'deleted' => 0,
					'status' => 1,
				);
			}
			$temp = M('user_coin_record')->where($temp_map)->find();
			if ($temp) {
				$this->_err_ret('您已经领取过了哦~');
			} else {
				$user_info = M('user')->where(array('id' => $user['id']))->find();
				$data = array(
					'add_time' => date('Y-m-d H:i:s'),
					'deleted' => 0,
					'user_id' => $user['id'],
					'coin_config_id' => 0,
					'num' => $amount_config['config_num'],
					'before_balance' => $user_info['coin_num'],
					'after_balance' => $user_info['coin_num'] + $amount_config['config_num'],
					'status' => 1,
					'money' => 0,
					'type' => $activity_id,
				);
				$res = M('user_coin_record')->add($data);
				if ($res) {
					$data = array(
						'id' => $user['id'],
						'coin_num' => $user_info['coin_num'] + $amount_config['config_num'],
					);
					$res = M('user')->save($data);
					$data = array(
						'coin_num' => $amount_config['config_num'],
					);
					// 发送通知
					$this->send_activity_inner_coin_msg($amount_config['config_num'], $user_info);
					$this->_suc_ret($data);
				} else {
					$this->_err_ret('系统繁忙，领取失败，请稍后再试~');
				}
			}
		}
		if ($amount_config['type'] == 1) {
			//每日限次
			$map = array(
				'type' => $activity_id,
				'user_id' => $user['id'],
				'deleted' => 0,
				'status' => 1,
			);
			$map['add_time'] = array(
				array('egt', date('Y-m-d') . " 00:00:00"),
				array('elt', date('Y-m-d') . " 23:59:59"),
				'and',
			);
			$temp = M('user_coin_record')->where($map)->select();
			if (count($temp) >= $amount_config['day_num']) {
				$this->_err_ret('您今天已经领取过了哦~');
			} else {
				$user_info = M('user')->where(array('id' => $user['id']))->find();
				$data = array(
					'add_time' => date('Y-m-d H:i:s'),
					'deleted' => 0,
					'user_id' => $user['id'],
					'coin_config_id' => 0,
					'num' => $amount_config['config_num'],
					'before_balance' => $user_info['coin_num'],
					'after_balance' => $user_info['coin_num'] + $amount_config['config_num'],
					'status' => 1,
					'money' => 0,
					'type' => $activity_id,
				);
				$res = M('user_coin_record')->add($data);
				if ($res) {
					$data = array(
						'id' => $user['id'],
						'coin_num' => $user_info['coin_num'] + $amount_config['config_num'],
					);
					$res = M('user')->save($data);
					$data = array(
						'coin_num' => $amount_config['config_num'],
					);
					// 发送通知
					$this->send_activity_inner_coin_msg($amount_config['config_num'], $user_info);
					$this->_suc_ret($data);
				} else {
					$this->_err_ret('系统繁忙，领取失败，请稍后再试~');
				}
			}
		}
	}

	/**
	 * ajax_link_activity_vip  领取活动赠送会员
	 */
	public function ajax_link_activity_vip() {
		$activity_id = I('id');
		$tel = I('tel');
		$user = session('user');
		if (!$user) {
			$this->_err_ret('您尚未登录哦~');
		}
		$user = M('user')->where(array('id' => $user['id']))->find();
		if (!$user) {
			$this->_err_ret('该用户不存在~');
		}
		if ($user['phone'] == '') {
			$this->_err_ret('need_bind_phone');
		}
		if ($user['phone'] != $tel) {
			$this->_err_ret('您输入手机号与绑定手机号不符~');
		}
		$map = array(
			'id' => $activity_id,
			'deleted' => 0,
			'style' => 1,
		);
		$amount_config = M('amount_config')->where($map)->find();
		if (!$amount_config) {
			$this->_err_ret('活动不存在~');
		}
		if ($amount_config['is_open'] == 0) {
			$this->_err_ret('活动已结束~');
		}
		if ($amount_config['level'] < $user['level']) {
			$this->_err_ret('您的会员等级不符合活动要求哦~');
		}
		$vip_activity_map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
			'activity_id' => $activity_id,
		);
		$vip_activity = M('vip_activity')->where($vip_activity_map)->find();
		if ($vip_activity) {
			$this->_err_ret('您已经领取过了哦~');
		}
		$vip_activity_map['add_time'] = date('Y-m-d H:i:s');
		$res = M('vip_activity')->add($vip_activity_map);
		if (!$res) {
			$this->_err_ret('领取失败~');
		}
		$vip_end_time = date('Y-m-d H:i:s', strtotime($user['over_time']) + $amount_config['config_num'] * 86400);
		$user_data = array(
			'id' => $user['id'],
			'over_time' => $vip_end_time,
			'level' => $amount_config['level'],
		);
		$user_res = M('user')->save($user_data);
		if (!$user_res) {
			$this->_err_ret('领取失败~');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_send_code 发送验证码
	 */
	public function ajax_send_code() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$phone = I('phone');
		$map = array(
			'phone' => $phone,
			'deleted' => 0,
		);
		$user_info = M('user')->where($map)->find();
		if ($user_info) {
			$this->_err_ret('您的手机号已被绑定~');
		}
		$send_time = session('send_time');
		if (time() - $send_time < 60) {
			$this->_err_ret('验证码已被发送，如果没有收到，请60秒后点击重新发送~');
		}
		require "qcloudsms_php/src/index.php";
		// 短信应用SDK AppID
		$appid = 1400161610; // 1400开头
		// 短信应用SDK AppKey
		$appkey = "ad7ed4540d56145da3a9770a0dcb28b2";
		// 需要发送短信的手机号码
		$phoneNumbers = [$phone];
		// 短信模板ID，需要在短信应用中申请
		$templateId = 233041; // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请
		$smsSign = "哐糖"; // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`
		$code = rand(1000, 9999);
		try {
			$ssender = new SmsSingleSender($appid, $appkey);
			$params = [$code];
			$result = $ssender->sendWithParam("86", $phoneNumbers[0], $templateId,
				$params, $smsSign, "", ""); // 签名参数未提供或者为空时，会使用默认签名发送短信
			//$this->_err_ret($result);
			$rsp = json_decode($result, true);
			if ($rsp['result'] == 0) {
				session('send_time', time());
				session('sms_code', $phone . '_' . $code);
				$this->_suc_ret();
			} else {
				$this->_err_ret($rsp['errmsg']);
			}
		} catch (\Exception $e) {
			$this->_err_ret('发送失败~');
		}
	}

	/**
	 * ajax_bind_phone 绑定手机号
	 */
	public function ajax_bind_phone() {
		$user = session('user');
		if (!$user) {
			exit;
		};
		$phone = I('phone');
		$code = I('code');
		$map = array(
			'phone' => $phone,
			'deleted' => 0,
		);
		$user_info = M('user')->where($map)->find();
		if ($user_info) {
			$this->_err_ret('您的手机号已被绑定~');
		}
		$sms_code = session('sms_code');
		if ($sms_code != $phone . '_' . $code) {
			$this->_err_ret('您输入的验证码错误~');
		}
		$data = array(
			'id' => $user['id'],
			'phone' => $phone,
		);
		$res = M('user')->save($data);
		if (!$res) {
			$this->_err_ret('绑定失败，请稍后再试~');
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_get_postal_card 获取包邮卡]
	 * @return [type] [description]
	 */
	public function ajax_get_postal_card() {
		$postalCardModel = M("postal_card");
		$map = array(
			'deleted' => 0,
		);
		$postal_card = $postalCardModel->order('money asc')->where($map)->select();
		if ($postal_card === false) {
			$this->_err_ret();
		}
		$this->_suc_ret($postal_card);
	}
	/**
	 * ajax_add_address  添加用户收货地址
	 */
	public function ajax_add_address() {
		$user = session('user');
		if (!$user) {
			exit;
		};
		$province = I('province');
		$city = I('city');
		$district = I('district');
		$address = I('address');
		$remark = I('remark');
		$tel = I('tel');
		$real_name = I('real_name');
		if ( /*$province == '' || $city == '' ||*/$address == '' || $tel == '' || $real_name == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user['id'],
			'province' => $province,
			'city' => $city,
			'district' => $district,
			'address' => $address,
			'remark' => $remark,
			'tel' => $tel,
			'real_name' => $real_name,
		);
		$res = M('address')->add($data);
		if (!$res) {
			$this->_err_ret('系统繁忙，添加失败，请稍后再试哦~');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_edit_address  编辑用户收货地址
	 */
	public function ajax_edit_address() {
		$user = session('user');
		if (!$user) {
			exit;
		};
		$id = I('id');
		$province = I('province');
		$city = I('city');
		$district = I('district');
		$address = I('address');
		$remark = I('remark');
		$tel = I('tel');
		$real_name = I('real_name');
		if ($id == '' || /*$province == '' || $city == '' ||*/$address == '' || $tel == '' || $real_name == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
			'province' => $province,
			'city' => $city,
			'district' => $district,
			'address' => $address,
			'remark' => $remark,
			'tel' => $tel,
			'real_name' => $real_name,
		);
		$res = M('address')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_delete_address  删除用户收货地址
	 */
	public function ajax_delete_address() {
		$user = session('user');
		if (!$user) {
			exit;
		};
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
		);
		$info = M('address')->where($data)->find();
		if (!$info) {
			$this->_err_ret('收货地址不存在');
		}
		if ($info['user_id'] != $user['id']) {
			$this->_err_ret('不能删除他人收货地址');
		}
		$res = M('address')->where($data)->delete();
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_give_good 赠送娃娃
	 */
	public function ajax_give_good() {
		$user = session('user');
		if (!$user) {
			exit;
		};
		$user = M('user')->where(array('id' => $user['id']))->find();
		if ($user['level'] < 2) {
			$this->_err_ret('您不是会员身份，不能赠送哦~');
		}
		if ($user['forbidden'] == 1) {
			$this->_err_ret('您的身份异常，不能赠送哦~');
		}
		$lottery_record_id = I('lottery_record_id');
		$to_user_id = I('to_user_id');

		$to_user = M('user')->where(array('id' => $to_user_id))->find();
		if ($to_user['level'] < 2) {
			$this->_err_ret('接收人不是会员身份，不能赠送哦~');
		}
		if ($to_user['forbidden'] == 1) {
			$this->_err_ret('接收人身份异常，不能赠送哦~');
		}
		if ($user['id'] == $to_user_id) {
			$this->_err_ret('自己不能给自己赠送哦~');
		}
		$map = array(
			'id' => $lottery_record_id,
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$temp = M('lottery_record')->where($map)->find();
		if (!$temp) {
			$this->_err_ret('礼物不存在哦~');
		}
		if ($temp['status'] == 1) {
			$this->_err_ret('礼物已经配送，不能转赠哦~');
		}
		if ($temp['from_to'] != '') {
			$this->_err_ret('获赠礼物不能再次转赠哦~');
		}
		$data = array(
			'id' => $lottery_record_id,
			'user_id' => $to_user_id,
			'realname' => '',
			'tel' => '',
			'address' => '',
			'memo' => '',
			'from_to' => $user['id'] . '->' . $to_user_id,
		);
		$res = M('lottery_record')->save($data);
		if (!$res) {
			$this->_err_ret('赠送失败，请稍后重试哦~');
		}
		$this->_suc_ret();
	}

	/**
	 * [send_friend 赠送好友]
	 * @return [type] [description]
	 */
	public function send_friend() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
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
		$lotteryRecordModel = D('lottery_record');
		$map = array(
			'id' => $id,
		);
		$lottery_record = $lotteryRecordModel->where($map)->find();
		if (!$lottery_record) {
			exit;
		}
		$this->assign('user', $user);
		$this->assign('lottery_record', $lottery_record);
		$this->show();
	}
	public function select_address() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$addressModel = D('address');
		$addresses = $addressModel->where($map)->order('add_time desc')->select();
		if ($addresses === false) {
			exit;
		}
		$this->assign('addresses', $addresses);
		$this->assign('addresses_json', json_encode($addresses));
		$lotteryRecordModel = D('lottery_record');

		$map = array(
			'id' => $id,
		);
		$lottery_record = $lotteryRecordModel->where($map)->find();
		if (!$lottery_record) {
			exit;
		}
		$this->assign('lottery_record', $lottery_record);
		$this->show();

	}
	public function user_address() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$addressModel = D('address');
		$addresses = $addressModel->where($map)->order('add_time desc')->select();
		if ($addresses === false) {
			exit;
		}
		$this->assign('addresses', $addresses);
		$this->show();
	}

	public function invite() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'id' => $user['id'],
		);
		$userModel = D('user');
		$user = $userModel->where($map)->find();
		$this->assign('user', $user);
		$nickname = '“' . mb_substr($this->filter_Emoji($user['nickname']), 0, 6, 'utf-8') . '”' . ' 邀请你试玩在线抓娃娃';
		// 合成分享的二维码
		$share_bg_image = 'Public/weixin/image/Poster.png';
		$image = $this->_request($user['ticket']);
		$file = "Uploads/qr_code/erweima.jpg"; //设置图片名字
		file_put_contents($file, $image); //二维码保存到本地
		$share_image = "Uploads/qr_code/share_" . $user['id'] . "_new.png";
		$imageApi = new \Think\Image();
		$imageApi->open($file)->thumb(240, 240)->save($file);
		$imageApi->open($share_bg_image)->water($file, array(258, 470), 100)->save($share_image);
		$imageApi->open($share_image)->text($nickname, 'Public/FandolFang-Regular.otf', 21, '#FFFFFF', array(110, 105))->save($share_image);
		$this->assign('image_url', 'https://' . $_SERVER['SERVER_NAME'] . '/' . $share_image);
		$this->show();
	}
	public function filter_Emoji($str) {
		$str = preg_replace_callback( //执行一个正则表达式搜索并且使用一个回调进行替换
			'/./u',
			function (array $match) {
				return strlen($match[0]) >= 4 ? '' : $match[0];
			},
			$str);

		return $str;
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
	 * ajax_collection 收藏接口
	 */
	public function ajax_collection() {
		$user = session('user');
		if (!$user) {
			exit;
		};
		$lottery_config_id = I('lottery_config_id');
		if ($lottery_config_id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'id' => $lottery_config_id,
		);
		$lottery_config = M('lottery_config')->where($map)->find();
		$map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
			'lottery_config_id' => $lottery_config_id,
		);
		$temp = M('collection')->where($map)->find();
		if ($temp) {
			$res = M('collection')->where($map)->delete();
			if (!$res) {
				$this->_err_ret('取消失败~');
			} else {
				$this->_suc_ret('取消成功~');
			}
		} else {
			$map['coin_num'] = $lottery_config['coin_num'];
			$map['add_time'] = date('Y-m-d H:i:s');
			$res = M('collection')->add($map);
			if (!$res) {
				$this->_err_ret('收藏失败~');
			} else {
				$this->_suc_ret('收藏成功~');
			}
		}
	}
	public function lingyang_wawa() {
		$adoptConfigModel = D('adopt_config');
		$map = array(
			'deleted' => 0,
		);
		$adopt_configs = $adoptConfigModel->relation(true)->where($map)->order('add_time desc')->select();
		$this->assign('adopt_configs', $adopt_configs);
		$user = session('user');
		$this->assign('user', $user);
		$this->show();
	}
	public function ajax_adopt_good() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
		$user = session('user');
		$map = array(
			'id' => $user['id'],
		);
		$userModel = D('user');
		$user = $userModel->where($map)->find();
		// 增加娃娃领养次数
		$adoptConfigModel = D('adopt_config');
		$map = array(
			'id' => $id,
		);
		$adopt_config = $adoptConfigModel->where($map)->find();
		if (!$adopt_config) {
			$this->_err_ret("系统繁忙，请稍后再试！");
		}
		$data = array(
			'adopt_num' => $adopt_config['adopt_num'] + 1,
		);
		$adoptConfigModel->where($map)->save($data);
		// 处理用户原来的娃娃
		$adoptLogModel = D('adopt_log');
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$adopt_log = $adoptLogModel->where($map)->find();
		if (!$adopt_log) {
			// 没有领养过
			$data = array(
				'user_id' => $user['id'],
				'adopt_config_id' => $id,
				'add_time' => date('Y-m-d H:i:s'),
			);
			$res = $adoptLogModel->add($data);
			if (!$res) {
				$this->_err_ret("系统繁忙，请稍后再试！");
			}
			$adopt_log = $adoptLogModel->where($map)->find();
		} else {
			//领养过 更新下
			$data = array(
				'adopt_config_id' => $id,
				'adopt_val' => (int) ($adopt_log['adopt_val'] / 2),
				'add_time' => date('Y-m-d H:i:s'),
			);
			$map = array(
				'id' => $adopt_log['id'],
			);
			$res = $adoptLogModel->where($map)->save($data);
			if ($res === false) {
				$this->_err_ret("系统繁忙，请稍后再试！" . $adopt_log['adopt_val']);
			}
		}
		$this->_suc_ret();

	}
	public function adopt_wawa() {
		$user = session('user');
		$this->redirect("Index/my_wawa", array('user_id' => $user['id']));
		exit;
	}
	public function my_wawa() {
		$user = session('user');
		$map = array(
			'id' => $id,
		);
		$user_id = I('user_id');
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$is_self = 1;
		if ($user_id != "") {
			$map['id'] = $user_id;
			if ($user_id != $user['id']) {
				$is_self = 0;
			}
		}
		$this->assign('is_self', $is_self);
		$user = $userModel->where($map)->find();
		$adoptLogModel = D('adopt_log');
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$adopt_log = $adoptLogModel->where($map)->relation(true)->find();
		if (!$adopt_log) {
			// 没有领养过
			$this->redirect('Index/lingyang_wawa');
			exit;
		}
		$lotteryGoodModel = D('lottery_good');
		$map = array(
			'id' => $adopt_log['adopt_config']['lottery_good_id'],
		);
		$lottery_good = $lotteryGoodModel->where($map)->find();
		$this->assign('user', $user);
		$this->assign('lottery_good', $lottery_good);
		$last_time = 30 * 24 * 3600 - (time(0) - strtotime($adopt_log['add_time']));
		if ($last_time < 0) {
			$last_time = 0;
		}
		$adopt_log['last_time_d'] = (int) ($last_time / (24 * 3600));
		$adopt_log['last_time_h'] = (int) (($last_time % (24 * 3600)) / 3600);
		$adopt_log['last_time_m'] = (int) (($last_time % 3600) / 60);
		$this->assign('adopt_log', $adopt_log);

		$start_time = date('Y-m-d') . ' 00:00:00';
		$end_time = date('Y-m-d H:i:s');
		//1
		$map = array(
			'user_id' => $user['id'],
			'adopt_val_config_id' => 1,
			'deleted' => 0,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$res = M('adopt_val_log')->where($map)->find();
		if ($res) {
			$this->assign('one_over', 1);
		} else {
			$this->assign('one_over', 0);
		}
		//2
		$map = array(
			'id' => 2,
			'deleted' => 0,
		);
		$adopt_val_config_two = M('adopt_val_config')->where($map)->find();
		$map = array(
			'user_id' => $user['id'],
			'adopt_val_config_id' => 2,
			'deleted' => 0,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$res = M('adopt_val_log')->where($map)->select();
		if (count($res) == $adopt_val_config_two['total_count']) {
			$this->assign('two_over', 1);
		} else {
			$this->assign('two_over', 0);
		}
		$this->assign('two_finish', count($res));
		$this->assign('two_total', $adopt_val_config_two['total_count']);
		//3
		$map = array(
			'user_id' => $user['id'],
			'adopt_val_config_id' => 3,
			'deleted' => 0,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$res = M('adopt_val_log')->where($map)->find();
		if ($res) {
			$this->assign('three_over', 1);
		} else {
			$this->assign('three_over', 0);
		}
		//4
		$map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$luck_draw_log = M('luck_draw_log')->where($map)->select();
		$map = array(
			'user_id' => $user['id'],
			'adopt_val_config_id' => 4,
			'deleted' => 0,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$res = M('adopt_val_log')->where($map)->find();
		if ($res) {
			$this->assign('four_over', 1);
		} else {
			$this->assign('four_over', 0);
		}
		$four_finish = count($luck_draw_log);
		if (count($luck_draw_log) > 3) {
			$four_finish = 3;
		}
		$this->assign('four_finish', $four_finish);
		$this->assign('four_total', 3);
		//5
		$map = array(
			'user_id' => $user['id'],
			'adopt_val_config_id' => 5,
			'deleted' => 0,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$res = M('adopt_val_log')->where($map)->find();
		if ($res) {
			$this->assign('five_over', 1);
		} else {
			$this->assign('five_over', 0);
		}
		$five_finish = count($luck_draw_log);
		if (count($luck_draw_log) > 5) {
			$five_finish = 5;
		}
		$this->assign('five_finish', $five_finish);
		$this->assign('five_total', 5);
		//6
		$map = array(
			'user_id' => $user['id'],
			'adopt_val_config_id' => 6,
			'deleted' => 0,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$res = M('adopt_val_log')->where($map)->find();
		if ($res) {
			$this->assign('sex_over', 1);
		} else {
			$this->assign('sex_over', 0);
		}
		//7
		$map = array(
			'user_id' => $user['id'],
			'adopt_val_config_id' => 7,
			'deleted' => 0,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$res = M('adopt_val_log')->where($map)->find();
		if ($res) {
			$this->assign('seven_over', 1);
		} else {
			$this->assign('seven_over', 0);
		}
		$map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
			'is_hit' => 1,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$luck_draw_log_hit = M('luck_draw_log')->where($map)->select();
		$seven_finish = count($luck_draw_log_hit);
		if (count($luck_draw_log_hit) > 2) {
			$seven_finish = 2;
		}
		$this->assign('seven_finish', $seven_finish);
		$this->assign('seven_total', 2);
		//8
		$map = array(
			'user_id' => $user['id'],
			'adopt_val_config_id' => 8,
			'deleted' => 0,
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$res = M('adopt_val_log')->where($map)->find();
		if ($res) {
			$this->assign('eight_over', 1);
		} else {
			$this->assign('eight_over', 0);
		}
		$map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
			'add_time' => array(
				array('egt', $start_time),
				array('elt', $end_time),
				'and',
			),
		);
		$coin_num = M('luck_draw_log')->where($map)->sum('consume_num');
		if (!$coin_num) {
			$coin_num = 0;
		}
		if ($coin_num > 300) {
			$coin_num = 300;
		}
		$this->assign('eight_finish', $coin_num);
		$this->assign('eight_total', 300);

		//当前用户被喂养记录
		$map = array(
			'deleted' => 0,
			'to_user_id' => $user['id'],
		);
		$stroke_list = M('adopt_stroke')->where($map)->order('add_time desc')->limit(3)->select();
		$return = array();
		foreach ($stroke_list as $key => $val) {
			$user_map = array(
				'deleted' => 0,
				'id' => $val['user_id'],
			);
			$user_info = M('user')->where($user_map)->find();
			if ($user_info) {
				$reutrn[] = $user_info;
			}
		}
		$this->assign('stroke_list', $reutrn);
		$this->show();
	}
	/**
	 * 用户扫码使用会员
	 */
	public function use_qrcode() {
		$user = session('user');
		if (!$user) {
			$this->_err_ret('登录失败');
		}
		$merchant_id = I('merchant_id');
		if ($merchant_id == '') {
			$this->_err_ret('参数不完整');
		}
		$user_map = array(
			'id' => $user['id'],
			'deleted' => 0,
		);
		$user_info = M('user')->where($user_map)->find();
		$merchant_map = array(
			'id' => $merchant_id,
			'deleted' => 0,
		);
		$merchant_info = M('merchant')->where($merchant_map)->find();
		if (!$merchant_info) {
			exit("<h1>编号:" . $merchant_id . "</h1>");
		}
		if (!$user_info) {
			$this->assign('status', 1); //用户不存在
		} else {
			//
			$arrive_log_data = array(
				'add_time' => date('Y-m-d H:i:s'),
				'deleted' => 0,
				'user_id' => $user['id'],
				'merchant_id' => $merchant_id,
			);
			$temp_res = M('arrive_log')->add($arrive_log_data);
			//
			$user_data = array(
				'id' => $user_info['id'],
				'arrive_num' => $user_info['arrive_num'] + 1,
			);
			$temp_res = M('user')->save($user_data);
			//
			$merchant_data = array(
				'id' => $merchant_info['id'],
				'arrive_num' => $merchant_data['arrive_num'] + 1,
			);
			$temp_res = M('merchant')->save($merchant_data);
			//
			if ($merchant_info['is_open_card'] == 1) {
				$this->assign('status', 3); //显示卡卷页
				//判断用户是否当月已经领取
				$map = array(
					'user_id' => $user_info['id'],
					'merchant_id' => $merchant_id,
					'deleted' => 0,
					'is_used' => 1,
				);
				//最后一次领取时间
				$use_qrcode_log = M('use_qrcode_log')->where($map)->order('add_time desc')->limit(1)->find();
				//暂定一个月可使用一次
				if (!$use_qrcode_log) {
					$over_time = strtotime($user_info['over_time']);
					if (($user_info['level'] == 2 || $user_info['level'] == 3) && time() < $over_time) {
						//没有使用过
						$data = array(
							'user_id' => $user_info['id'],
							'merchant_id' => $merchant_id,
							'deleted' => 0,
							'add_time' => date('Y-m-d H:i:s'),
							'card_name' => $merchant_info['card_name'],
							'is_used' => 1,
						);
						M('use_qrcode_log')->add($data);
						$this->assign('card_over', 0);
						$card_over_time = strtotime($data['add_time']) + 86400;
						$this->assign('over_date', date('Y-m-d H:i:s', $card_over_time));
					}
				} else {
					//获取当月开始和结束时间
					$beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
					$endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
					if (strtotime($use_qrcode_log['add_time']) >= $beginThismonth) {
						$this->assign('status', 4);
						if (strtotime($use_qrcode_log['add_time']) + 86400 < time()) {
							$this->assign('card_over', 1);
							$card_over_time = strtotime($use_qrcode_log['add_time']) + 86400;
							$this->assign('over_date', date('Y-m-d H:i:s', $card_over_time));
						} else {
							$this->assign('card_over', 0);
							$card_over_time = strtotime($use_qrcode_log['add_time']) + 86400;
							$this->assign('over_date', date('Y-m-d H:i:s', $card_over_time));
						}
					} else {
						$over_time = strtotime($user_info['over_time']);
						if (($user_info['level'] == 2 || $user_info['level'] == 3) && time() < $over_time) {
							$data = array(
								'user_id' => $user_info['id'],
								'merchant_id' => $merchant_id,
								'deleted' => 0,
								'add_time' => date('Y-m-d H:i:s'),
								'card_name' => $merchant_info['card_name'],
								'is_used' => 1,
							);
							M('use_qrcode_log')->add($data);

							$this->assign('card_over', 0);
							$card_over_time = strtotime($data['add_time']) + 86400;
							$this->assign('over_date', date('Y-m-d H:i:s', $card_over_time));
						}
					}
				}

			} else {
				$this->assign('status', 5); //显示商家以及用户会员信息
			}

			// 给用户发送糖豆
			$candy_num = 0;
			if ($user_info['level'] > 1 && strtotime($user_info['over_time']) > time(0)) {
				$map_candy = array(
					'user_id' => $user_info['id'],
					'merchant_id' => $merchant_id,
				);
				$arrive_logs = M('arrive_log')->where($map_candy)->select();
				$is_arrive = 0;
				$today_start = strtotime(date('Y-m-d', time(0)));
				$today_end = $today_start + 24 * 3600;
				foreach ($arrive_logs as $key => $value) {
					if (strtotime($value['add_time']) > $today_start && strtotime($value['add_time']) < $today_end && count($arrive_logs) > 1) {
						$is_arrive = 1;
					}
				}
				if ($is_arrive == 0) {
					$candy_num = rand(5, 10);
					// 查询此用户是否今天已经领取过5次糖豆了
					$map_user_today_candy_times = array(
						'user_id' => $user_info['id'],
						'add_time' => array('egt', date('Y-m-d', time(0)) . " 00:00:00"),
					);
					$today_times = M('arrive_log')->where($map_user_today_candy_times)->count();
					if ($today_times < 5) {
						$map_user_candy = array(
							'id' => $user_info['id'],
						);
						$data = array(
							'coin_num' => $user_info['coin_num'] + $candy_num,
						);
						M('user')->where($map_user_candy)->save($data);
						$total_coin_num = $user_info['coin_num'] + $candy_num;
						// 给用户添加金币获得记录
						$data_coin_record = array(
							'add_time' => date('Y-m-d H:i:s', time()),
							'user_id' => $user_info['id'],
							'num' => $candy_num,
							'before_balance' => $user_info['coin_num'],
							'after_balance' => $total_coin_num,
							'status' => 1,
							'type' => -7, //到店消费捡糖豆
							'money' => 0,
						);
						M('user_coin_record')->add($data_coin_record);
						$notice_str = "尊敬的会员" . $user_info["nickname"] . "您本次消费获赠糖豆" . $candy_num . "个，您的糖豆已经有" . $total_coin_num . "个哦~";
						$this->send_user_free_coin_msg($user_info['openid'], $notice_str, $candy_num, "https://fssw.bichonfrise.cn/index.php/Wechat/Index/index.html");
					} else {
						$candy_num = 0;
					}
				}

			}

			$user_info['candy_num'] = $candy_num;
			// $user_info['today_times'] = $today_times;
			// $user_info['is_arrive'] = $is_arrive;
			// $user_info['log'] = M('arrive_log')->getLastSql();

		}

		$this->assign('user', $user_info); //用户信息
		$merchant_info['vip_discount_arr'] = explode('；', $merchant_info['vip_discount']);
		$this->assign('merchant', $merchant_info); //商家信息
		$level_map = array(
			'level' => $user_info['level'],
			'deleted' => 0,
		);
		$level_info = M('vip_grade')->where($level_map)->find();
		$this->assign('level', $level_info); //会员信息

		$map = array(
			'level' => $user_info['level'],
		);
		$temp = M('vip_grade')->where($map)->find();
		$this->assign('level_name', $temp['name']);
		if ($user_info['over_time'] != '0000-00-00 00:00:00') {
			$over_time = strtotime($user_info['over_time']);
			if (time() >= $over_time) {
				$is_over = 1;
			} else {
				$is_over = 0;
			}
		} else {
			$is_over = 1;
		}
		if ($user_info['level'] != 2 && $user_info['level'] != 3) {
			$is_over = 1;
		}
		$this->assign('is_over', $is_over);
		$this->show();
	}

}