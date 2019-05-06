<?php
namespace Admin\Controller;
use Think\Controller;

class BaseController extends AppController {
	/**
	 * [_before_index 前置方法]
	 * @return [type] [description]
	 */
	public function _initialize() {
		if (session('?admin')) {
			$admin = session('admin');
			$this->assign('admin', $admin);
		} else {
			$this->redirect("Login/index");
		}

	}
}
