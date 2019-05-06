<?php
namespace Admin\Controller;
use Think\Controller;

class QuotationsController extends BaseController {
	/**
	 * [index 文章列表]
	 * @return [type] [description]
	 */
	public function index() {
		$this->show();
	}
	/**
	 * [ajax_get_quotations 获取语录接口]
	 * @return [type] [description]
	 */
	public function ajax_get_quotations() {
	    $quotationsModel = M("quotations");
		$map = array(
			'deleted' => 0,
		);
		//加入条件
		$name = I("title");
		if ($name != "") {
			$map['title'] = array('like', "%" . $name . "%");;
		}
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$articles = $quotationsModel
			->limit($start_index, $limit)
			->where($map)
			->order('add_time desc')
			->select();
			if ($articles === false) {
			$this->_err_ret();
		}
		$count = $quotationsModel->where($map)->count();
		$this->_tb_suc_ret($articles, $count);

	}
	
	/**
	 * edit_quotations_view 编辑语录页面
	 */
	public function edit_quotations_view(){
	    $admin = session('admin');
	    $id = I('id');
	    if ($id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $map = array(
	        'deleted'=>0,
	        'id'=>$id
	    );
	    $quotations = M('quotations')->where($map)->find();
	    if(!$quotations){
	        $this->_err_ret('文章不存在');
	    }
	    $this->assign('quotations',$quotations);
	    $this->show();
	}
	/**
	 * ajax_edit_quotations 编辑语录
	 */
	public function ajax_edit_quotations(){
	    $lottery_config = I('lottery_config');
	    if ($lottery_config == '') {
	        exit();
	    }
	    $lottery_config = json_decode(urldecode($lottery_config), true);
	    $data = array(
	        'id' => $lottery_config['id'],
	        'title' => $lottery_config['title'],
	        'content' => $lottery_config['content'],
	        'author' => $lottery_config['author'],
	        'cover_image' => $lottery_config['cover_image'],
	        'image_abstract' => $lottery_config['image_abstract'],
	    );
	    $res = M('quotations')->save($data);
	    if (!$res) {
	        $this->_err_ret('编辑失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * add_quotations_view 添加语录
	 */
	public function add_quotations_view(){
	    $this->show();
	}
	
	/**
	 * ajax_add_quotations 添加语录
	 */
	public function ajax_add_quotations(){
	    $admin = session('admin');
	    if (!$admin) {
	        exit;
	    }
	    $lottery_config = I('lottery_config');
	    if ($lottery_config == '') {
	        exit();
	    }
	    $lottery_config = json_decode(urldecode($lottery_config), true);
	    $data = array(
	        'title' => $lottery_config['title'],
	        'content' => $lottery_config['content'],
	        'author' => $lottery_config['author'],
	        'cover_image' => $lottery_config['cover_image'],
	        'image_abstract' => $lottery_config['image_abstract'],
	        'add_time'=>date('Y-m-d H:i:s'),
	        'deleted'=>0,
	    );
	    $res = M('quotations')->add($data);
	    if (!$res) {
	        $this->_err_ret('添加失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * ajax_delete_quotations  删除语录
	 */
	public function ajax_delete_quotations(){
	    $admin = session('admin');
	    $id = I('id');
	    if ($id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $map = array(
	        'id'=>$id,
	    );
	    $res = M('quotations')->where($map)->delete();
	    if(!$res){
	        $this->_err_ret('删除失败');
	    }
	    $this->_suc_ret();
	}
	
	
}