<?php
namespace Admin\Controller;
use Think\Controller;

class ArticleController extends BaseController {
	/**
	 * [article_list 文章列表]
	 * @return [type] [description]
	 */
	public function article_list() {
		$this->show();
	}
	/**
	 * [ajax_get_article 获取文章接口]
	 * @return [type] [description]
	 */
	public function ajax_get_article() {
	    $articleModel = D("article");
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
		$articles = $articleModel
			->relation(true)
			->limit($start_index, $limit)
			->where($map)
			->order('add_time desc')
			->select();
			if ($articles === false) {
			$this->_err_ret();
		}
		$count = $articleModel->where($map)->count();
		$this->_tb_suc_ret($articles, $count);

	}
	
	/**
	 * edit_article_view 编辑文章页面
	 */
	public function edit_article_view(){
	    $admin = session('admin');
	    $id = I('id');
	    if ($id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $map = array(
	        'deleted'=>0,
	        'id'=>$id
	    );
	    $article = M('article')->where($map)->find();
	    if(!$article){
	        $this->_err_ret('文章不存在');
	    }
	    $this->assign('article',$article);
	    $this->show();
	}
	/**
	 * ajax_edit_article 编辑文章
	 */
	public function ajax_edit_article(){
	    $lottery_config = I('lottery_config');
	    if ($lottery_config == '') {
	        exit();
	    }
	    $lottery_config = json_decode(urldecode($lottery_config), true);
	    $data = array(
	        'id' => $lottery_config['id'],
	        'title' => $lottery_config['title'],
	        'cover_image' => $lottery_config['cover_image'],
	        'content' => $lottery_config['content'],
	    );
	    $res = M('article')->save($data);
	    if (!$res) {
	        $this->_err_ret('编辑失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * add_article_view 添加文章
	 */
	public function add_article_view(){
	    $this->show();
	}
	
	/**
	 * ajax_add_article 添加文章
	 */
	public function ajax_add_article(){
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
	        'cover_image' => $lottery_config['cover_image'],
	        'content' => $lottery_config['content'],
	        'add_time'=>date('Y-m-d H:i:s'),
	        'deleted'=>0,
	    );
	    $res = M('article')->add($data);
	    if (!$res) {
	        $this->_err_ret('添加失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * ajax_delete_article  删除文章
	 */
	public function ajax_delete_article(){
	    $admin = session('admin');
	    $id = I('id');
	    if ($id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $map = array(
	        'id'=>$id,
	        'deleted'=>1
	    );
	    $res = M('article')->save($map);
	    if(!$res){
	        $this->_err_ret('删除失败');
	    }
	    $this->_suc_ret();
	}
	
	/**
	 * tiezi_list 攻略管理
	 */
	public function tiezi_list(){
	    $this->show();
	}
	/**
	 * [ajax_get_tiezi_list 获取攻略接口]
	 * @return [type] [description]
	 */
	public function ajax_get_tiezi_list() {
	    $map = array(
	        'deleted' => 0,
	        'type'=>1
	    );
	    //加入条件
	    $title = I("title");
	    if ($title != "") {
	        $map['title'] = array('like', "%" . $title . "%");
	    }
	    $page = I('page');
	    $limit = I('limit');
	    if ($page == ""
	        || $limit == "") {
	            exit;
	        }
	        $start_index = ((int) $page - 1) * ((int) $limit);
	        $articles = M('tiezi')
	        ->limit($start_index, $limit)
	        ->where($map)
	        ->order('add_time desc')
	        ->select();
	        if ($articles === false) {
	            $this->_err_ret();
	        }
	        $count = M('tiezi')->where($map)->count();
	        $this->_tb_suc_ret($articles, $count);
	        
	}
	/**
	 * add_tiezi_view 添加攻略
	 */
	public function add_tiezi_view(){
	    $this->show();
	}
	
	/**
	 * ajax_add_tiezi 添加攻略
	 */
	public function ajax_add_tiezi(){
	    $admin = session('admin');
	    if (!$admin) {
	        exit;
	    }
	    $title = I('title');
	    $content = I('content');
	    $images = I('images');
	    $data = array(
	        'title' => $title,
	        'content' => $content,
	        'images' => $images,
	        'user_id' => $merchant['id'],
	        'add_time' => date('Y-m-d H:i:s', time()),
	        'type'=>1
	    );
	    $res = M('tiezi')->add($data);
	    if (!$res) {
	        $this->_err_ret("发表失败，请稍后重试！");
	    }
	    $this->_suc_ret();
	}
	/**
	 * edit_tiezi_view 编辑攻略页面
	 */
	public function edit_tiezi_view(){
	    $admin = session('admin');
	    $id = I('id');
	    if ($id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $map = array(
	        'deleted'=>0,
	        'id'=>$id
	    );
	    $article = M('tiezi')->where($map)->find();
	    if(!$article){
	        $this->_err_ret('攻略不存在');
	    }
	    $this->assign('tiezi',$article);
	    $this->show();
	}
	/**
	 * ajax_edit_tiezi 编辑攻略
	 */
	public function ajax_edit_tiezi(){
	    $lottery_config = I('lottery_config');
	    if ($lottery_config == '') {
	        exit();
	    }
	    $lottery_config = json_decode(urldecode($lottery_config), true);
	    $data = array(
	        'id' => $lottery_config['id'],
	        'title' => $lottery_config['title'],
	        'cover_image' => $lottery_config['cover_image'],
	        'content' => $lottery_config['content'],
	    );
	    $res = M('article')->save($data);
	    if (!$res) {
	        $this->_err_ret('编辑失败');
	    }
	    $this->_suc_ret();
	}
	
	/**
	 * ajax_delete_tiezi  删除攻略
	 */
	public function ajax_delete_tiezi(){
	    $admin = session('admin');
	    $id = I('id');
	    if ($id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $map = array(
	        'id'=>$id,
	        'deleted'=>1
	    );
	    $res = M('article')->save($map);
	    if(!$res){
	        $this->_err_ret('删除失败');
	    }
	    $this->_suc_ret();
	}
}