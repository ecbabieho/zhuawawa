<?php
namespace Admin\Controller;
use Think\Controller;

class UserController extends BaseController {
	/**
	 * [dashboard 设备列表]
	 * @return [type] [description]
	 */
	public function dashboard() {
		$map = array(
			'deleted' => 0,
		);
		$grade_list = M('vip_grade')->where($map)->order('level asc')->select();
		$this->assign('grade_list', $grade_list);
		$map = array(
			'deleted' => 0,
		);
		$lottery_good = M('lottery_good')->where($map)->order('id desc')->select();
		$this->assign('lottery_good', $lottery_good);
		$map = array(
			'deleted' => 0,
		);
		$lottery_config = M('lottery_config')->where($map)->order('id desc')->select();
		$this->assign('lottery_config', $lottery_config);
		$this->show();
	}
	/**
	 * [ajax_get_users 获取用户信息]
	 * @return [type] [description]
	 */
	public function ajax_get_users() {
		$userModel = D("user");
		$map = array(
			'deleted' => 0,
		);
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$name = I('name');
		if ($name != "") {
			$where['nickname'] = array('like', '%' . $name . '%');
			$where['id'] = $name;
			$where['_logic'] = 'OR';
			$map['_complex'] = $where;
		}
		$p_name = I('p_name');
		if ($p_name != "") {
		    $map['p_id'] = $p_name;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$users = $userModel
			->relation(true)
			->limit($start_index, $limit)
			->where($map)
			->order('add_time desc')
			->select();
		if ($users === false) {
			$this->_err_ret();
		}
		$count = $userModel->where($map)->count();
		foreach ($users as $key => $val) {
			$map = array(
				'level' => $val['level'],
			);
			$temp = M('vip_grade')->where($map)->find();
			if (!$temp) {
				$users[$key]['grade_name'] = '无等级';
			} else {
				$users[$key]['grade_name'] = $temp['name'];
			}
			if($val['p_id'] == 0){
			    $users[$key]['p_nickname'] = '';
			}else{
			    $p_map = array(
			        'deleted'=>0,
			        'id'=>$val['p_id']
			    );
			    $p_info = M('user')->where($p_map)->find();
			    if($p_info){
			        $users[$key]['p_nickname'] = $p_info['id'].'-'.$p_info['nickname'];
			    }else{
			        $users[$key]['p_nickname'] = '';
			    }
			}
		}
		$this->_tb_suc_ret($users, $count);
	}
	public function send_msg() {
		$this->show();
	}
	public function ajax_send_msg() {
		$notice = I('notice');
		$amount = I('amount');
		$url = I('url');
		$remark = I('remark');
		if ($notice == ""
			|| $amount == ""
			|| $url == ""
			|| $remark == "") {
			exit;
		}
		$map = array(
			'deleted' => 0,
			//'id'=>571,
		);
		$userModel = D('user');
		$users = $userModel->where($map)->select();
		$success_num = 0;
		$fail_num = 0;
		$resp = "";
		foreach ($users as $key => $value) {
			$res = $this->send_user_free_coin_msg($value['openid'], $notice, $amount, $url, $remark);
			$resp .= $res;
			$res = json_decode($res, true);
			if ($res['errcode'] == 0 && $res['errmsg'] == "ok") {
				$success_num++;
			} else {
				$fail_num++;
			}
		}

		$data = array(
			'success_num' => $success_num,
			'fail_num' => $fail_num,
			'resp' => $resp,
		);
		$this->_suc_ret($data);

	}
	public function ajax_send_gonglve() {
		$map = array(
			'deleted' => 0,
		);
		$userModel = D('user');
		$users = $userModel->where($map)->select();
		$success_num = 0;
		$fail_num = 0;
		$resp = array();
		foreach ($users as $key => $value) {
			$res = $this->send_new_user_gonglve_msg($value['openid']);
			array_push($resp, $res);
			$res = json_decode($res, true);
			if ($res['errcode'] == 0 && $res['errmsg'] == "ok") {
				$success_num++;
			} else {
				$fail_num++;
			}
		}

		$data = array(
			'success_num' => $success_num,
			'fail_num' => $fail_num,
			'resp' => $resp,
		);
		$this->_suc_ret($data);
	}
	/**
	 * ajax_forbidden_user 修改用户状态
	 */
	public function ajax_forbidden_user() {
		$userModel = D("user");
		$id = I('id');
		$type = I('type');
		if ($id == ""
			|| $type == "") {
			exit;
		}
		$data = array(
			'id' => $id,
			'forbidden' => $type,
		);
		$res = M('user')->save($data);
		if (!$res) {
			$this->_err_ret('修改失败');
		}
		$this->_suc_ret();
	}
	/**
	 * record_log 充值记录
	 */
	public function record_log() {
		$id = I('id');
		if ($id == "") {
			$this->_err_ret('参数不完整');
		}
		$this->show();
	}
	/**
	 * [ajax_get_record_log 获取用户充值记录]
	 * @return [type] [description]
	 */
	public function ajax_get_record_log() {
		$userCoinRecordModel = D("user_coin_record");
		$id = I('id');
		if ($id == "") {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'user_id' => $id,
		);
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$lottery_types = $userCoinRecordModel
			->relation(true)
			->limit($start_index, $limit)
			->where($map)
			->order('add_time desc')
			->select();
		if ($lottery_types === false) {
			$this->_err_ret();
		}
		$count = $userCoinRecordModel->where($map)->count();
		foreach ($lottery_types as $key => $val) {
			$lottery_types[$key]['user_name'] = $val['user']['nickname'];
		}
		$this->_tb_suc_ret($lottery_types, $count);
	}
	/**
	 * winning_log 中奖记录
	 */
	public function winning_log() {
		$id = I('id');
		if ($id == "") {
			$this->_err_ret('参数不完整');
		}
		$this->show();
	}
	/**
	 * [ajax_get_winning_log 获取用户中奖记录]
	 * @return [type] [description]
	 */
	public function ajax_get_winning_log() {
		$lotteryRecordModel = D("lottery_record");
		$id = I('id');
		if ($id == "") {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'user_id' => $id,
		);
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$lottery_types = $lotteryRecordModel
			->relation(true)
			->limit($start_index, $limit)
			->where($map)
			->select();
		if ($lottery_types === false) {
			$this->_err_ret();
		}
		$count = $lotteryRecordModel->where($map)->count();
		foreach ($lottery_types as $key => $val) {
			$lottery_types[$key]['lottery_type_name'] = $val['lottery_type']['name'];
			$lottery_types[$key]['user_name'] = $val['user']['nickname'];
			$lottery_types[$key]['lottery_config_name'] = $val['lottery_config']['name'];
			$lottery_types[$key]['lottery_goods_name'] = $val['lottery_good']['name'];
			$lottery_types[$key]['lottery_goods_pic'] = $val['lottery_good']['img_url'];
		}
		$this->_tb_suc_ret($lottery_types, $count);
	}
	/**
	 * ajax_give_coin  赠送糖豆
	 */
	public function ajax_give_coin() {
		$id = I('id');
		$coin_num = I('coin_num');
		if ($id == '' || $coin_num == '') {
			$this->_err_ret('参数不完整');
		}
		$user_info = M('user')->where(array('id' => $id))->find();
		if (!$user_info) {
			$this->_err_ret('用户不存在');
		}
		$data = array(
			'id' => $id,
			'coin_num' => $user_info['coin_num'] + intval($coin_num),
		);
		$res = M('user')->save($data);
		if (!$res) {
			$this->_err_ret('赠送失败');
		}
		$data = array(
			'deleted' => 0,
			'add_time' => date('Y-m-d H:i:s'),
			'user_id' => $id,
			'coin_config_id' => 0,
			'num' => $coin_num,
			'before_balance' => $user_info['coin_num'],
			'after_balance' => $user_info['coin_num'] + intval($coin_num),
			'status' => 1,
			'money' => 0,
			'type' => -1,
		);
		$res = M('user_coin_record')->add($data);
		$this->send_give_user_coin_msg($user_info,$coin_num);
		$this->_suc_ret();
	}
	/**
	 * ajax_test_user 修改用户类型(测试用户)
	 */
	public function ajax_test_user() {
		$userModel = D("user");
		$id = I('id');
		$type = I('type');
		if ($id == ""
			|| $type == "") {
			exit;
		}
		$data = array(
			'id' => $id,
			'is_test' => $type,
		);
		$res = M('user')->save($data);
		if (!$res) {
			$this->_err_ret('修改失败');
		}
		$this->_suc_ret();
	}

	/**
	 * ajax_manager_user 修改用户类型(管理员用户)
	 */
	public function ajax_manager_user() {
		$userModel = D("user");
		$id = I('id');
		$type = I('type');
		if ($id == ""
			|| $type == "") {
			exit;
		}
		$data = array(
			'id' => $id,
			'is_manager' => $type,
		);
		$res = M('user')->save($data);
		if (!$res) {
			$this->_err_ret('修改失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_agent_user 修改用户类型(代理用户)
	 */
	public function ajax_agent_user() {
		$userModel = D("user");
		$id = I('id');
		$type = I('type');
		if ($id == ""
			|| $type == "") {
			exit;
		}
		$data = array(
			'id' => $id,
			'is_agent' => $type,
		);
		$res = M('user')->save($data);
		if (!$res) {
			$this->_err_ret('修改失败');
		}
		$this->_suc_ret();
	}
	/**
	 * [grade_list 等级列表]
	 * @return [type] [description]
	 */
	public function grade_list() {
		$this->show();
	}
	/**
	 * [ajax_get_grade 获取等级列表接口]
	 * @return [type] [description]
	 */
	public function ajax_get_grade() {
		$vipGradeModel = M("vip_grade");
		$map = array(
			'deleted' => 0,
		);
		//加入条件
		$list = $vipGradeModel->where($map)->order('level desc')->select();
		if ($list === false) {
			$this->_err_ret();
		}
		$this->_tb_suc_ret($list);

	}
	/**
	 * [ajax_add_grade 添加会员等级]
	 * @return [type] [description]
	 */
	public function ajax_add_grade() {
		$merchant = session('merchant');
		$name = I('name');
		if ($name == '') {
			$this->_err_ret('参数不完整');
		}
		$count = M('vip_grade')->where(array('deleted' => 0))->count();
		if (!$count) {
			$count = 0;
		}
		$data = array(
			'name' => $name,
			'level' => $count + 1,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('vip_grade')->add($data);
		$data = array(
			'grade_id' => $res,
			'day_30' => 0,
			'day_60' => 0,
			'day_90' => 0,
			'day_180' => 0,
			'day_360' => 0,
			'day_720' => 0,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('vip_pay_config')->add($data);
		if ($res) {
			//$this->insertMerchantUserLog("添加分类ID：".$res);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_edit_grade 编辑会员等级]
	 * @return [type] [description]
	 */
	public function ajax_edit_grade() {
		$merchant = session('merchant');
		$id = I('id');
		$name = I('name');
		$level = I('level');
		if ($name == '' || $id == '' || $level == '') {
			$this->_err_ret('参数不完整');
		}
		$info = M('vip_grade')->where(array('id' => $id))->find();
		//查找等级存不存在
		$map = array(
			'deleted' => 0,
			'level' => $level,
		);
		$temp = M('vip_grade')->where($map)->find();
		if (!$temp) {
			$data = array(
				'id' => $id,
				'name' => $name,
				'level' => $level,
			);
			$res = M('vip_grade')->save($data);
		} else {
			if ($info['level'] == $temp['level']) {
				$data = array(
					'id' => $id,
					'name' => $name,
				);
				$res = M('vip_grade')->save($data);
			} else {
				$map = array(
					'deleted' => 0,
					'level' => array('EGT', $info['level']),
				);
				$temp_list = M('vip_grade')->where($map)->select();
				foreach ($temp_list as $key => $val) {
					$data = array(
						'id' => $val['id'],
						'level' => $val['level'] - 1,
					);
					$res = M('vip_grade')->save($data);
				}
				$map = array(
					'deleted' => 0,
					'level' => array('EGT', $level),
				);
				$temp_list = M('vip_grade')->where($map)->select();
				foreach ($temp_list as $key => $val) {
					$data = array(
						'id' => $val['id'],
						'level' => $val['level'] + 1,
					);
					$res = M('vip_grade')->save($data);
				}

				$data = array(
					'id' => $id,
					'name' => $name,
					'level' => $level,
				);
				$res = M('vip_grade')->save($data);
			}
		}
		if ($res) {
			//$this->insertMerchantUserLog("修改分类ID：".$id);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_delete_grade 删除会员等级]
	 * @return [type] [description]
	 */
	public function ajax_delete_grade() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
		);
		$info = M('vip_grade')->where($data)->find();
		$res = M('vip_grade')->where($data)->delete();
		$map = array(
			'grade_id' => $id,
		);
		$res = M('vip_pay_config')->where($map)->delete();
		$map = array(
			'deleted' => 0,
			'level' => array('EGT', $info['level']),
		);
		$temp_list = M('vip_grade')->where($map)->select();
		foreach ($temp_list as $key => $val) {
			$data = array(
				'id' => $val['id'],
				'level' => $val['level'] - 1,
			);
			$res = M('vip_grade')->save($data);
		}
		if ($res) {
			//$this->insertMerchantUserLog("删除分类ID：".$id);
		}
		$this->_suc_ret();
	}
	/**
	 * [pay_config 购买管理]
	 * @return [type] [description]
	 */
	public function pay_config() {
		$this->assign('id', I('id'));
		$this->show();
	}
	/**
	 * [ajax_get_pay_config 获取购买管理接口]
	 * @return [type] [description]
	 */
	public function ajax_get_pay_config() {
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$vip_pay_configModel = M("vip_pay_config");
		$map = array(
			'deleted' => 0,
			'grade_id' => $id,
		);
		//加入条件
		$list = $vip_pay_configModel->where($map)->order('days asc')->select();
		if ($list === false) {
			$this->_err_ret();
		}
		$this->_tb_suc_ret($list);

	}
	/**
	 * [ajax_edit_pay_config 编辑会员购买配置]
	 * @return [type] [description]
	 */
	public function ajax_edit_pay_config() {
		$merchant = session('merchant');
		$id = I('id');
		$name = I('name');
		$days = I('days');
		$price = I('price');
		$discount_price = I('discount_price');
		$info = I('info');
		if ($id == '' || $name == '' || $days == '' || $price == '' || $discount_price == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
			'name' => $name,
			'days' => $days,
			'price' => $price,
			'discount_price' => $discount_price,
			'info' => $info,
		);
		$res = M('vip_pay_config')->save($data);

		if ($res) {
			//$this->insertMerchantUserLog("添加分类ID：".$res);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_add_pay_config 添加会员购买配置]
	 * @return [type] [description]
	 */
	public function ajax_add_pay_config() {
		$merchant = session('merchant');
		$grade_id = I('grade_id');
		$name = I('name');
		$days = I('days');
		$price = I('price');
		$discount_price = I('discount_price');
		$info = I('info');
		if ($grade_id == '' || $name == '' || $days == '' || $price == '' || $discount_price == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'grade_id' => $grade_id,
			'name' => $name,
			'days' => $days,
			'price' => $price,
			'discount_price' => $discount_price,
			'info' => $info,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('vip_pay_config')->add($data);

		if ($res) {
			//$this->insertMerchantUserLog("添加分类ID：".$res);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_delete_pay_config 删除会员购买配置]
	 * @return [type] [description]
	 */
	public function ajax_delete_pay_config() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
		);
		$res = M('vip_pay_config')->where($data)->delete();

		if ($res) {
			//$this->insertMerchantUserLog("删除分类ID：".$id);
		}
		$this->_suc_ret();
	}
	/**
	 * vip_pay_log  会员购买记录
	 */
	public function vip_pay_log() {
		//用户
		$map = array(
			'deleted' => 0,
		);
		$user_list = M('user')->where($map)->select();
		$this->assign('user_list', $user_list);
		//等级
		$map = array(
			'deleted' => 0,
		);
		$grade_list = M('vip_grade')->where($map)->order('level asc')->select();
		$this->assign('grade_list', $grade_list);
		$this->show();
	}
	/**
	 * ajax_get_vip_pay_log 会员购买记录数据
	 */
	public function ajax_get_vip_pay_log() {
		$map = array(
			'deleted' => 0,
			'status' => 1,
		);
		$user_id = I('user_id');
		if ($user_id != '') {
			$map['user_id'] = $user_id;
		}
		$level = I('level');
		if ($level != '') {
			$map['level'] = $level;
		}
		$search_val = I('search_val');
		if ($search_val != '') {
			$map['user_id'] = $search_val;
		}
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$vip_order = M('vip_order')->limit($start_index, $limit)->where($map)->order('add_time desc')->select();
		if ($vip_order === false) {
			$this->_err_ret();
		}
		$count = M('vip_order')->where($map)->count();
		foreach ($vip_order as $key => $val) {
			$user_map = array(
				'deleted' => 0,
				'id' => $val['user_id'],
			);
			$res = M('user')->where($user_map)->find();
			$vip_order[$key]['nickname'] = $res['id'] . '-' . $res['nickname'];
			$level_map = array(
				'deleted' => 0,
				'level' => $val['level'],
			);
			$res = M('vip_grade')->where($user_map)->find();
			$vip_order[$key]['level_name'] = $res['nickname'];
		}
		$this->_tb_suc_ret($vip_order, $count);
	}
	/**
	 * ajax_give_vip 赠送会员
	 */
	public function ajax_give_vip() {
		$user_id = I('id');
		$level = I('level');
		$day_num = I('day_num');
		if ($level == '' || $day_num == '' || $user_id == '') {
			$this->_err_ret('参数不完整');
		}
		$user_map = array(
			'id' => $user_id,
		);
		$user_info = M('user')->where($user_map)->find();
		if (!$user_info) {
			$this->_err_ret('用户不存在');
		}
		if ($level < $user_info['level']) {
			$this->_err_ret('赠送等级不能小于当前用户等级');
		}
		$vip_order_data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user_id,
			'config_id' => 0,
			'level' => $level,
			'money' => 0,
			'day_num' => $day_num,
			'status' => 1,
			'type' => 1,
		);
		if ($level == $user_info['level']) {
			$vip_order_data['over_time'] = date('Y-m-d H:i:s', strtotime($user_info['over_time']) + $day_num * 86400);
		}
		if ($level > $user_info['level']) {
			$vip_order_data['over_time'] = date('Y-m-d H:i:s', time() + $day_num * 86400);
		}
		if($user_info['over_time'] == '0000-00-00 00:00:00'){
		    $vip_order_data['over_time'] = date('Y-m-d H:i:s', time() + $day_num * 86400);
		}
		$res = M('vip_order')->add($vip_order_data);
		if (!$res) {
			$this->_err_ret('赠送失败');
		}
		$user_data = array(
			'id' => $user_id,
			'over_time' => $vip_order_data['over_time'],
			'level' => $vip_order_data['level'],
		);
		$res = M('user')->save($user_data);
		if (!$res) {
			$this->_err_ret('赠送失败');
		}
		$level_info = M('vip_grade')->where(array('deleted'=>0,'level'=>$level))->find();
		$this->send_give_user_vip_msg($user_info,$level_info,$day_num,$vip_order_data['over_time']);
		$this->_suc_ret();
	}
	
	/**
	 * ajax_give_good 赠送奖品
	 */
	public function ajax_give_good() {
		$user_id = I('id');
		$good_id = I('good_id');
		$config_id = I('config_id');
		if ($good_id == '' || $user_id == '' || $config_id == '') {
			$this->_err_ret('参数不完整');
		}
		$user_map = array(
			'id' => $user_id,
		);
		$user_info = M('user')->where($user_map)->find();
		if (!$user_info) {
			$this->_err_ret('用户不存在');
		}
		$good_map = array(
			'id' => $good_id,
			'deleted' => 0,
		);
		$good_info = M('lottery_good')->where($good_map)->find();
		if (!$good_info) {
			$this->_err_ret('奖品不存在');
		}

		$config_map = array(
			'id' => $config_id,
			'deleted' => 0,
		);
		$config_info = M('lottery_config')->where($config_map)->find();
		if (!$config_info) {
			$this->_err_ret('场次不存在');
		}
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'lottery_type_id' => $config_info['lottery_type_id'],
			'lottery_config_id' => $config_id,
			'user_id' => $user_id,
			'lottery_good_id' => $good_id,
			'realname' => '',
			'tel' => '',
			'address' => '',
			'memo' => '',
			'status' => 0,
			'from_to' => '',
			'type' => 1,
		);
		$res = M('lottery_record')->add($data);
		if (!$res) {
			$this->_err_ret('赠送失败');
		}
		// 发送通知
		$this->send_user_pay_user_gift_msg($user_info['openid'], $config_info['name'], $good_info['name']);
		$this->_suc_ret();
	}
	/**
	 * ajax_get_config_good
	 */
	public function ajax_get_config_good() {
		$config_id = I('select_config');
		if ($config_id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'id' => $config_id,
		);
		$info = M('lottery_config')->where($map)->find();
		$return_arr = array();
		if ($info['lottery_good_id0'] != 0) {
			$map = array(
				'id' => $info['lottery_good_id0'],
			);
			$res = M('lottery_good')->where($map)->find();
			if ($res) {
				$return_arr[] = $res;
			}
		}
		if ($info['lottery_good_id0'] != 1) {
			$map = array(
				'id' => $info['lottery_good_id1'],
			);
			$res = M('lottery_good')->where($map)->find();
			if ($res) {
				$return_arr[] = $res;
			}
		}
		if ($info['lottery_good_id0'] != 2) {
			$map = array(
				'id' => $info['lottery_good_id2'],
			);
			$res = M('lottery_good')->where($map)->find();
			if ($res) {
				$return_arr[] = $res;
			}
		}
		if ($info['lottery_good_id0'] != 3) {
			$map = array(
				'id' => $info['lottery_good_id3'],
			);
			$res = M('lottery_good')->where($map)->find();
			if ($res) {
				$return_arr[] = $res;
			}
		}
		if ($info['lottery_good_id0'] != 4) {
			$map = array(
				'id' => $info['lottery_good_id4'],
			);
			$res = M('lottery_good')->where($map)->find();
			if ($res) {
				$return_arr[] = $res;
			}
		}
		$this->_suc_ret($return_arr);
	}
	/**
	 * agent_user 下级用户
	 */
	public function agent_user() {
		$id = I('id');
		if ($id == "") {
			$this->_err_ret('参数不完整');
		}
		$this->show();
	}
	/**
	 * [ajax_get_agent_user 获取下级用户]
	 * @return [type] [description]
	 */
	public function ajax_get_agent_user() {
		$userModel = D("user");
		$id = I('id');
		if ($id == "") {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'p_id' => $id,
		);
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$lottery_types = $userModel
			->relation(true)
			->limit($start_index, $limit)
			->where($map)
			->order('add_time desc')
			->select();
		if ($lottery_types === false) {
			$this->_err_ret();
		}
		$count = $userModel->where($map)->count();
		$this->_tb_suc_ret($lottery_types, $count);
	}
}