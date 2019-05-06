<?php
namespace Admin\Controller;
use Think\Controller;

class LoginController extends AppController {
	/**
	 * [index 登录]
	 * @return [type] [description]
	 */
	public function index() {
		$this->show();
	}
	/**
	 * [ajax_login 登录接口]
	 * @return [type] [description]
	 */
	public function ajax_login() {
		$tel = I('tel');
		$pwd = I('pwd');
		if ($tel == ""
			|| $pwd == "") {
			exit;
		}
		$map = array(
			'tel' => $tel,
			'deleted' => 0,
		);
		$adminModel = D('admin');
		$admin = $adminModel->relation(true)->where($map)->find();
		if (!$admin) {
			$this->_err_ret("账号或密码错误");
		}
		if ($admin['pwd'] != md5($pwd)) {
			$this->_err_ret("账号或密码错误");
		}
		session('admin', $admin);
		$this->insertAdminLog("登录系统");
		$this->_suc_ret();
	}
	/**
	 * [logout 退出登录]
	 * @return [type] [description]
	 */
	public function logout() {
		$merchant = session('admin');
		$this->insertAdminLog("退出系统");
		session('admin', null);
		$this->redirect("Login/index");
	}
}