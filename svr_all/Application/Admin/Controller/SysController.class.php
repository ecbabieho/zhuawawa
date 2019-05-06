<?php
namespace Admin\Controller;
use Think\Controller;

class SysController extends BaseController {
	/**
	 * [msg 系统消息]
	 * @return [type] [description]
	 */
	public function msg() {
		$this->show();
	}
	/**
	 * [log 操作日志]
	 * @return [type] [description]
	 */
	public function log() {
		$merchant = session('merchant');
		$merchantUserModel = D('merchant_user');
		$map = array(
			'merchant_id' => $merchant['merchant_id'],
			'deleted' => 0,
		);
		$merchant_users = $merchantUserModel->where($map)->select();
		$this->assign('merchant_users', $merchant_users);
		$this->show();
	}
	/**
	 * [ajax_get_logs 获取操作日志数据接口]
	 * @return [type] [description]
	 */
	public function ajax_get_logs() {
		$merchantUserLogModel = D("merchant_user_log");
		$merchant = session('merchant');
		if (!$merchant) {
			exit;
		}
		$map = array(
			'merchant_id' => $merchant['merchant_id'],
			'deleted' => 0,
		);
		//加入条件
		$merchan_user_id = I("merchan_user_id");
		if ($merchan_user_id != "") {
			$map['merchant_id'] = $merchan_user_id;
		}
		$start_time = I('start_time');
		$end_time = I('end_time');
		if ($start_time == "" && $end_time != "") {
			$map['add_time'] = array('elt', $end_time . " 23:59:59");
		}
		if ($start_time != "" && $end_time == "") {
			$map['add_time'] = array('egt', $start_time . " 00:00:00");
		}
		if ($start_time != "" && $end_time != "") {
			$map['add_time'] = array(
				array('egt', $start_time . " 00:00:00"),
				array('elt', $end_time . " 23:59:59"),
				'and',
			);
		}
		// 加入分页配置
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$merchant_user_logs = $merchantUserLogModel
			->relation(true)
			->limit($start_index, $limit)
			->where($map)
			->select();
		if ($merchant_user_logs === false) {
			$this->_err_ret();
		}
		foreach ($merchant_user_logs as $key => $value) {
			$merchant_user_logs[$key]['merchant_name'] = $value['merchant_user']['name'] . "(" . $value['merchant_user']['tel'] . ")";
		}
		$count = $merchantUserLogModel->where($map)->count();
		$this->_tb_suc_ret($merchant_user_logs, $count);
	}
	/**
	 * [change_pwd 修改密码]
	 * @return [type] [description]
	 */
	public function change_pwd() {
		$this->show();
	}
	/**
	 * [ajax_change_pwd 修改密码接口]
	 * @return [type] [description]
	 */
	public function ajax_change_pwd() {
		$merchant = session('admin');
		if (!$merchant) {
			exit;
		}
		$pwd = I('pwd');
		$pwdo = I('pwdo');
		if ($pwd == "" || $pwdo == "") {
			exit;
		}
		if ($merchant['pwd'] != md5($pwdo)) {
			$this->_err_ret("原密码错误");
		}
		$data = array(
			'id' => $merchant['id'],
			'pwd' => md5($pwd),
		);
		$adminModel = M('admin');
		$res = $adminModel->save($data);
		if ($res === false) {
			$this->_err_ret();
		}
		$this->_suc_ret();
	}
	/**
	 * [feedback 意见反馈]
	 * @return [type] [description]
	 */
	public function feedback() {
		$this->show();
	}
	/**
	 * [ajax_feedback 意见反馈接口]
	 * @return [type] [description]
	 */
	public function ajax_feedback() {
		$content = I('content');
		$tel = I('tel');
		if ($content == ""
			|| $tel == "") {
			exit;
		}
		$merchant = session('merchant');
		$feedbackModel = D('feedback');
		$data = array(
			'content' => $content,
			'tel' => $tel,
			'merchant_user_id' => $merchant['id'],
			'type' => 0,
		);
		$res = $feedbackModel->add($data);
		if (!$res) {
			$this->_err_ret();
		}
		$this->_suc_ret();

	}
	/**
	 * [recharge 充值规则]
	 * @return [type] [description]
	 */
	public function recharge() {
		$vip_grades = M('vip_grade')->select();
		$this->assign('vip_grades', $vip_grades);
		$this->show();
	}
	/**
	 * [ajax_get_charge_rules 获取储值规则接口]
	 * @return [type] [description]
	 */
	public function ajax_get_charge_rules() {
		$merchant = session('merchant');
		$map = array(
			'deleted' => 0,
		);

		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$coin_configModel = D('coin_config');
		$charge_rules = $coin_configModel
			->relation(true)
			->limit($start_index, $limit)
			->where($map)
			->order('coin_num asc')
			->select();
		if ($charge_rules === false) {
			$this->_err_ret();
		}
// 		foreach($charge_rules as $key=>$val){
		// 		    $charge_rules[$key]['luck'] = $val['luck_low'].'~'.$val['luck_high'];
		// 		}
		$count = $coin_configModel->where($map)->count();
		$this->_tb_suc_ret($charge_rules, $count);

	}
	/**
	 * [ajax_add_charge 添加规则]
	 * @return [type] [description]
	 */
	public function ajax_add_charge() {
		$merchant = session('merchant');
		$pay_num = I('pay_num');
		$coin_num = I('coin_num');
		$level = I('level');
		$vip_pay_num = I('vip_pay_num');
		$vip_day_num = I('vip_day_num');
		if ($pay_num == '' || $coin_num == '' || $level == '' || $vip_pay_num == '' || $vip_day_num == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'pay_num' => $pay_num,
			'coin_num' => $coin_num,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		    'level' => $level,
		    'vip_pay_num' => $vip_pay_num,
		    'vip_day_num' => $vip_day_num,
		);
		$res = M('coin_config')->add($data);
		if ($res) {
			//$this->insertMerchantUserLog("添加储值规则ID：".$res);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_edit_charge 编辑规则]
	 * @return [type] [description]
	 */
	public function ajax_edit_charge() {
		$merchant = session('merchant');
		$id = I('id');
		$pay_num = I('pay_num');
		$coin_num = I('coin_num');
		$vip_pay_num = I('vip_pay_num');
		$vip_day_num = I('vip_day_num');
		$level = I('level');
		if ($id == '' || $pay_num == '' || $coin_num == '' || $level == '' || $vip_pay_num == '' || $vip_day_num == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
		    'id' => $id,
		    'pay_num' => $pay_num,
		    'coin_num' => $coin_num,
		    'level' => $level,
		    'vip_pay_num' => $vip_pay_num,
		    'vip_day_num' => $vip_day_num,
		);
		$res = M('coin_config')->save($data);
		if ($res) {
			//$this->insertMerchantUserLog("修改储值规则ID：".$id);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_delete_charge 删除规则]
	 * @return [type] [description]
	 */
	public function ajax_delete_charge() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'deleted' => 1,
			'id' => $id,
		);
		$res = M('coin_config')->save($data);
		if ($res) {
			//$this->insertMerchantUserLog("删除储值规则ID：".$id);
		}
		$this->_suc_ret();
	}
}