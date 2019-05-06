<?php
namespace Admin\Controller;
use Think\Controller;

class BannerController extends BaseController {
    /**
     * [banner_list 轮播图列表]
     * @return [type] [description]
     */
    public function banner_list() {
        $this->show();
        
    }
	/**
	 * [ajax_get_banner 获取轮播图]
	 * @return [type] [description]
	 */
    public function ajax_get_banner() {
	    $lotteryGoodModel = D("banner");
	    $map = array(
	        'deleted' => 0,
	    );
	    $name = I('name');
	    if ($name != "") {
	        $map['title'] = array('like', "%$name%");
	        }
        $page = I('page');
        $limit = I('limit');
        if ($page == ""
            || $limit == "") {
                exit;
            }
        $start_index = ((int) $page - 1) * ((int) $limit);
        $lottery_configs = $lotteryGoodModel
        ->relation(true)
        ->limit($start_index, $limit)
        ->where($map)
        ->select();
        if ($lottery_configs === false) {
            $this->_err_ret();
        }
        $count = $lotteryGoodModel->where($map)->count();
        $this->_tb_suc_ret($lottery_configs, $count);
	            
	}
	
	/**
	 * edit_banner_view 编辑轮播图
	 */
	public function edit_banner_view(){
	    $merchant = session('merchant');
	    $id = I('id');
	    if ($id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $map = array(
	        'deleted'=>0,
	        'id'=>$id
	    );
	    $lottery_goods = M('banner')->where($map)->find();
	    if(!$lottery_goods){
	        $this->_err_ret('轮播图不存在');
	    }
	    $this->assign('banner',$lottery_goods);
	    $this->show();
	}
	/**
	 * ajax_edit_banner 编辑轮播图
	 */
	public function ajax_edit_banner(){
	    $merchant = session('admin');
	    if (!$merchant) {
	        exit;
	    }
	    $lottery_config = I('lottery_config');
	    if ($lottery_config == '') {
	        exit();
	    }
	    $lottery_config = json_decode(urldecode($lottery_config), true);
	    $data = array(
	        'id' => $lottery_config['id'],
	        'title' => $lottery_config['title'],
	        'cover_image' => $lottery_config['cover_image'],
	        'url' => $lottery_config['url'],
	        'type' => $lottery_config['type'],
	    );
	    $res = M('banner')->save($data);
	    if (!$res) {
	        $this->_err_ret('编辑失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * add_banner_view 添加轮播图页面
	 */
	public function add_banner_view(){
	    $this->show();
	}
	
	/**
	 * ajax_add_banner 添加轮播图
	 */
	public function ajax_add_banner(){
	    $merchant = session('admin');
	    if (!$merchant) {
	        exit;
	    }
	    $lottery_config = I('lottery_config');
	    if ($lottery_config == '') {
	        exit();
	    }
	    $lottery_config = json_decode(urldecode($lottery_config), true);
	    $data = array(
	        'title' => $lottery_config['title'],
	        'cover_image' => $lottery_config['cover_image'],
	        'url' => $lottery_config['url'],
	        'type' => $lottery_config['type'],
	        'add_time'=>date('Y-m-d H:i:s'),
	        'deleted'=>0,
	    );
	    $res = M('banner')->add($data);
	    if (!$res) {
	        $this->_err_ret('添加失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * ajax_delete_banner  删除轮播图
	 */
	public function ajax_delete_banner(){
	    $merchant = session('merchant');
	    $id = I('id');
	    if ($id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $map = array(
	        'id'=>$id
	    );
	    $res = M('banner')->where($map)->delete();
	    if(!$res){
	        $this->_err_ret('删除失败');
	    }
	    $this->_suc_ret();
	}
}