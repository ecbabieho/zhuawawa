<?php
namespace Admin\Controller;
use Think\Controller;

class ShopController extends BaseController {
	/**
	 * [merchant_list 商家列表]
	 * @return [type] [description]
	 */
	public function merchant_list() {
		$this->show();
	}
	/**
	 * [ajax_get_merchant 获取商家接口]
	 * @return [type] [description]
	 */
	public function ajax_get_merchant() {
		$quotationsModel = M("merchant");
		$map = array(
			'deleted' => 0,
		);
		//加入条件
		$name = I("name");
		if ($name != "") {
			$map['name'] = array('like', "%" . $name . "%");
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
		foreach ($articles as $key => $val) {
			$map = array(
				'id' => $val['category_id'],
			);
			$category = M('merchant_category')->where($map)->find();
			$articles[$key]['merchant_category_name'] = $category['name'];
			$articles[$key]['category_id'] = $category['id'];
			if ($val['qrcode'] == '') {
				//微信授权登录
				$appId = "";
				$appSecret = "";

				$res = $this->getAccessToken($appId, $appSecret);
				$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $res;
				$data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "' . strval($val['id']) . '_merchant"' . '}}}';
				$res = $this->http_request($url, $data);
				$res = json_decode($res, true);
				$data = array(
					'id' => $val['id'],
					'qrcode' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($res['ticket']),
				);
				$res = M('merchant')->save($data);
				$articles[$key]['qrcode'] = $data['qrcode'];
			}
			if ($val['use_qrcode'] == '') {
//核销二维码
				//ob_clean();
				// 生成二维码
				$qr_data = "https://" . $_SERVER['HTTP_HOST'] . "/index.php/Wechat/Index/use_qrcode?merchant_id=" . $val['id'];
				$temp = "Uploads/qr_code/merchant/" . "merchant_use_" . $val['id'] . ".png";
				vendor('PHPQRcode.PHPQRcode');
				\QRcode::png($qr_data, $temp, 'H', 6, 2);
				$path = 'https://' . $_SERVER['SERVER_NAME'] . '/' . $temp;
				$data = array(
					'id' => $val['id'],
					'use_qrcode' => $path,
				);
				$res = M('merchant')->save($data);
				$articles[$key]['use_qrcode'] = $data['use_qrcode'];
			}
		}
		$this->_tb_suc_ret($articles, $count);

	}

	/**
	 * ajax_public_config 商家隐藏商家
	 */
	public function ajax_public_config() {
		$merchant = session('merchant');
		$id = I('id');
		$type = I('type');
		if ($id == '' || $type == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
			'is_fabu' => $type,
		);
		$res = M('merchant')->save($data);
		if (!$res) {
			$this->_err_ret('更新失败');
		}
		$this->_suc_ret();
	}
	/**
	 * edit_merchant_view 编辑页面
	 */
	public function edit_merchant_view() {
		$admin = session('admin');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'id' => $id,
		);
		$merchant = M('merchant')->where($map)->find();
		if (!$merchant) {
			$this->_err_ret('商家不存在');
		}
		$this->assign('merchant', $merchant);
		$map = array(
			'deleted' => 0,
		);
		$category_list = M('merchant_category')->where($map)->select();
		$this->assign('category_list', $category_list);
		$this->show();
	}
	/**
	 * ajax_edit_merchant 编辑
	 */
	public function ajax_edit_merchant() {
		$lottery_config = I('lottery_config');
		if ($lottery_config == '') {
			exit();
		}
		$lottery_config = json_decode(urldecode($lottery_config), true);
		$data = array(
			'id' => $lottery_config['id'],
			'name' => $lottery_config['name'],
			'link_name' => $lottery_config['link_name'],
			'link_tel' => $lottery_config['link_tel'],
			'address' => $lottery_config['address'],
			//'lat_lng' => $lottery_config['lat_lng'],
		    'image' => $lottery_config['image'],
		    'info_image' => $lottery_config['info_image'],
			'content' => $lottery_config['content'],
			'category_id' => $lottery_config['merchant_category_id'],
			'trading_area' => $lottery_config['trading_area'],
			'main_camp' => $lottery_config['main_camp'],
			'business_time' => $lottery_config['business_time'],
			'vip_discount' => $lottery_config['vip_discount'],
			'recommend' => $lottery_config['recommend'],
			'bind_user_id' => $lottery_config['bind_user_id'],
		);
		if ($lottery_config['lat_lng'] != '') {
			$data['lat_lng'] = $lottery_config['lat_lng'];
		}
		$res = M('merchant')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * add_merchant_view 添加页面
	 */
	public function add_merchant_view() {
		$map = array(
			'deleted' => 0,
		);
		$category_list = M('merchant_category')->where($map)->select();
		$this->assign('category_list', $category_list);
		$this->show();
	}

	/**
	 * ajax_add_merchant 添加
	 */
	public function ajax_add_merchant() {
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
			'name' => $lottery_config['name'],
			'link_name' => $lottery_config['link_name'],
			'link_tel' => $lottery_config['link_tel'],
			'address' => $lottery_config['address'],
			//'lat_lng' => $lottery_config['lat_lng'],
		    'image' => $lottery_config['image'],
		    'info_image' => $lottery_config['info_image'],
			'content' => $lottery_config['content'],
			'category_id' => $lottery_config['merchant_category_id'],
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'trading_area' => $lottery_config['trading_area'],
			'main_camp' => $lottery_config['main_camp'],
			'business_time' => $lottery_config['business_time'],
			'vip_discount' => $lottery_config['vip_discount'],
			'recommend' => $lottery_config['recommend'],
			'bind_user_id' => $lottery_config['bind_user_id'],
		);
		if ($lottery_config['lat_lng'] != '') {
			$data['lat_lng'] = $lottery_config['lat_lng'];
		}
		$res = M('merchant')->add($data);
		if (!$res) {
			$this->_err_ret('添加失败');
		}
		$merchant_id = $res;
		//微信授权登录
		$appId = "";
		$appSecret = "";

		$res = $this->getAccessToken($appId, $appSecret);
		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $res;
		$data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "' . strval($merchant_id) . '_merchant"' . '}}}';
		$res = $this->http_request($url, $data);
		$res = json_decode($res, true);
		$data = array(
			'id' => $merchant_id,
			'qrcode' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($res['ticket']),
		);
		$res = M('merchant')->save($data);

		$this->_suc_ret();
	}
	/**
	 * ajax_delete_merchant  删除
	 */
	public function ajax_delete_merchant() {
		$admin = session('admin');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $id,
		);
		$res = M('merchant')->where($map)->delete();
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		$this->_suc_ret();
	}
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
		$lotteryGoodModel = M("merchant_banner");
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
	public function edit_banner_view() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'id' => $id,
		);
		$lottery_goods = M('merchant_banner')->where($map)->find();
		if (!$lottery_goods) {
			$this->_err_ret('轮播图不存在');
		}
		$this->assign('banner', $lottery_goods);
		$this->show();
	}
	/**
	 * ajax_edit_banner 编辑轮播图
	 */
	public function ajax_edit_banner() {
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
		);
		$res = M('merchant_banner')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * add_banner_view 添加轮播图页面
	 */
	public function add_banner_view() {
		$this->show();
	}

	/**
	 * ajax_add_banner 添加轮播图
	 */
	public function ajax_add_banner() {
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
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('merchant_banner')->add($data);
		if (!$res) {
			$this->_err_ret('添加失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_delete_banner  删除轮播图
	 */
	public function ajax_delete_banner() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $id,
		);
		$res = M('merchant_banner')->where($map)->delete();
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		$this->_suc_ret();
	}
	public function merchant_category() {
		$this->show();
	}
	public function ajax_get_merchant_category() {
		$lotteryTypeModel = M("merchant_category");
		$map = array(
			'deleted' => 0,
		);
		$name = I('name');
		if ($name != '') {
			$map['name'] = array('like', "%$name%");
		}
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$lottery_types = $lotteryTypeModel
			->limit($start_index, $limit)
			->where($map)
			->order('sort asc')
			->select();
		if ($lottery_types === false) {
			$this->_err_ret();
		}
		$count = $lotteryTypeModel->where($map)->count();
		$this->_tb_suc_ret($lottery_types, $count);

	}
	/**
	 * [ajax_add_merchant_category 添加分类]
	 * @return [type] [description]
	 */
	public function ajax_add_merchant_category() {
		$name = I('name');
		$image = I('image');
		$sort = I('sort');
		if ($name == '' || $image == '' || $sort == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'name' => $name,
			'sort' => $sort,
			'image' => $image,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('merchant_category')->add($data);
		if ($res) {
			//$this->insertMerchantUserLog("添加分类ID：".$res);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_edit_merchant_category 编辑分类]
	 * @return [type] [description]
	 */
	public function ajax_edit_merchant_category() {
		$id = I('id');
		$name = I('name');
		$sort = I('sort');
		$image = I('image');
		if ($name == '' || $id == '' || $image == '' || $sort == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
			'name' => $name,
			'image' => $image,
			'sort' => $sort,
		);
		$res = M('merchant_category')->save($data);
		if ($res) {
			//$this->insertMerchantUserLog("修改分类ID：".$id);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_delete_merchant_category 删除分类]
	 * @return [type] [description]
	 */
	public function ajax_delete_merchant_category() {
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
		);
		$res = M('merchant_category')->where($data)->delete();
		if ($res) {
			//$this->insertMerchantUserLog("删除分类ID：".$id);
		}
		$this->_suc_ret();
	}
	public function merchant_search() {
		$this->show();
	}
	public function ajax_get_merchant_search() {
		$lotteryTypeModel = M("merchant_search");
		$map = array(
			'deleted' => 0,
		);
		$name = I('name');
		if ($name != '') {
			$map['name'] = array('like', "%$name%");
		}
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$lottery_types = $lotteryTypeModel
			->limit($start_index, $limit)
			->where($map)
			->order('id desc')
			->select();
		if ($lottery_types === false) {
			$this->_err_ret();
		}
		$count = $lotteryTypeModel->where($map)->count();
		$this->_tb_suc_ret($lottery_types, $count);

	}
	/**
	 * [ajax_add_merchant_search 添加搜索关键字]
	 * @return [type] [description]
	 */
	public function ajax_add_merchant_search() {
		$name = I('name');
		if ($name == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'name' => $name,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('merchant_search')->add($data);
		if (!$res) {
			$this->_err_ret('添加失败');
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_edit_merchant_search 编辑搜索关键字]
	 * @return [type] [description]
	 */
	public function ajax_edit_merchant_search() {
		$id = I('id');
		$name = I('name');
		if ($name == '' || $id == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
			'name' => $name,
		);
		$res = M('merchant_search')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_delete_merchant_search 删除分类]
	 * @return [type] [description]
	 */
	public function ajax_delete_merchant_search() {
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
		);
		$res = M('merchant_search')->where($data)->delete();
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		$this->_suc_ret();
	}

	/********************商家活动***************************/

	/**
	 * [merchant_activity 商家活动]
	 * @return [type] [description]
	 */
	public function merchant_activity() {
		$id = I('id');
		if ($id == '') {
			exit();
		}
		$this->assign('id', $id);
		$this->show();
	}
	/**
	 * [ajax_get_merchant_activity 获取商家活动接口]
	 * @return [type] [description]
	 */
	public function ajax_get_merchant_activity() {
		$id = I('id');
		if ($id == '') {
			exit();
		}
		$quotationsModel = M("merchant_activity");
		$map = array(
			'deleted' => 0,
			'merchant_id' => $id,
		);
		//加入条件
		$name = I("name");
		if ($name != "") {
			$map['title'] = array('like', "%" . $name . "%");
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
	 * edit_merchant_activity_view 编辑商家活动页面
	 */
	public function edit_merchant_activity_view() {
		$admin = session('admin');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'id' => $id,
		);
		$merchant = M('merchant_activity')->where($map)->find();
		if (!$merchant) {
			$this->_err_ret('商家活动不存在');
		}
		$this->assign('merchant_activity', $merchant);
		$this->show();
	}
	/**
	 * ajax_edit_merchant_activity 编辑商家活动
	 */
	public function ajax_edit_merchant_activity() {
		$lottery_config = I('lottery_config');
		if ($lottery_config == '') {
			exit();
		}
		$lottery_config = json_decode(urldecode($lottery_config), true);
		$data = array(
			'id' => $lottery_config['id'],
			'title' => $lottery_config['title'],
			'brief' => $lottery_config['brief'],
			'activity_time' => $lottery_config['activity_time'],
			'content' => $lottery_config['content'],
			'cover_image' => $lottery_config['cover_image'],
		);
		$res = M('merchant_activity')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * add_merchant_activity_view 添加商家活动页面
	 */
	public function add_merchant_activity_view() {
		$id = I('id');
		if ($id == '') {
			exit();
		}
		$this->assign('id', $id);
		$map = array(
			'deleted' => 0,
		);
		$category_list = M('merchant_category')->where($map)->select();
		$this->assign('category_list', $category_list);
		$this->show();
	}

	/**
	 * ajax_add_merchant_activity 添加商家活动
	 */
	public function ajax_add_merchant_activity() {
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
			'merchant_id' => $lottery_config['merchant_id'],
			'title' => $lottery_config['title'],
			'brief' => $lottery_config['brief'],
			'activity_time' => $lottery_config['activity_time'],
			'content' => $lottery_config['content'],
			'cover_image' => $lottery_config['cover_image'],
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('merchant_activity')->add($data);
		if (!$res) {
			$this->_err_ret('添加失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_delete_merchant_activity  删除商家活动
	 */
	public function ajax_delete_merchant_activity() {
		$admin = session('admin');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $id,
		);
		$res = M('merchant_activity')->where($map)->delete();
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		$this->_suc_ret();
	}
	/**
	 * [merchant_apply 商家申请列表]
	 * @return [type] [description]
	 */
	public function merchant_apply() {
		$this->show();
	}
	/**
	 * [ajax_get_merchant_apply 获取商家申请数据接口]
	 * @return [type] [description]
	 */
	public function ajax_get_merchant_apply() {
		$quotationsModel = M("merchant_apply");
		$map = array(
			'deleted' => 0,
		);
		//加入条件
		$name = I("name");
		if ($name != "") {
			$map['merchant_name'] = array('like', "%" . $name . "%");
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
		foreach ($articles as $key => $val) {
			$user_map = array(
				'id' => $val['user_id'],
				'deleted' => 0,
			);
			$user_info = M('user')->where($user_map)->find();
			if ($user_info) {
				$articles[$key]['user_nickname'] = $user_info['id'] . '-' . $user_info['nickname'];
			} else {
				$articles[$key]['user_nickname'] = '';
			}
		}
		$this->_tb_suc_ret($articles, $count);

	}

	/**
	 * ajax_delete_merchant_apply  删除商家申请
	 */
	public function ajax_delete_merchant_apply() {
		$admin = session('admin');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $id,
		);
		$res = M('merchant_apply')->where($map)->delete();
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		$this->_suc_ret();
	}
	/**************************商家标签*********************************/
	/**
	 * [merchant_label 商家活动]
	 * @return [type] [description]
	 */
	public function merchant_label() {
		$id = I('id');
		if ($id == '') {
			exit();
		}
		$this->assign('id', $id);
		$merchant_info = M('merchant')->where(array('id' => $id))->find();
		$this->assign('merchant', $merchant_info);
		$this->show();
	}
	/**
	 * [ajax_get_merchant_label 获取商家标签接口]
	 * @return [type] [description]
	 */
	public function ajax_get_merchant_label() {
		$id = I('id');
		if ($id == '') {
			exit();
		}
		$quotationsModel = M("merchant_label");
		$map = array(
			'deleted' => 0,
			'merchant_id' => $id,
		);
		//加入条件
		$name = I("name");
		if ($name != "") {
			$map['name'] = array('like', "%" . $name . "%");
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
	 * ajax_edit_merchant_label 编辑商家活动
	 */
	public function ajax_edit_merchant_label() {
		$id = I('id');
		$name = I('name');
		if ($id == '' || $name == '') {
			exit();
		}
		$data = array(
			'id' => $id,
			'name' => $name,
		);
		$res = M('merchant_label')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_add_merchant_label 添加商家标签
	 */
	public function ajax_add_merchant_label() {
		$admin = session('admin');
		if (!$admin) {
			exit;
		}
		$merchant_id = I('merchant_id');
		$name = I('name');
		if ($merchant_id == '' || $name == '') {
			exit();
		}
		$data = array(
			'merchant_id' => $merchant_id,
			'name' => $name,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('merchant_label')->add($data);
		if (!$res) {
			$this->_err_ret('添加失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_delete_merchant_label  删除商家标签
	 */
	public function ajax_delete_merchant_label() {
		$admin = session('admin');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $id,
		);
		$res = M('merchant_label')->where($map)->delete();
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		/**
		 * 需要同步删除用户标签记录
		 */

		$this->_suc_ret();
	}

	/*********************商家卡卷*******************************/
	/**
	 * ajax_public_open_card 商家开启卡卷
	 */
	public function ajax_public_open_card() {
		$merchant = session('merchant');
		$id = I('id');
		$type = I('type');
		if ($id == '' || $type == '') {
			$this->_err_ret('参数不完整');
		}
		$merchant_map = array(
			'deleted' => 0,
			'id' => $id,
		);
		$merchant = M('merchant')->where($merchant_map)->find();
		if (!$merchant) {
			$this->_err_ret('商家不存在或已关闭');
		}
		$data = array(
			'id' => $id,
			'is_open_card' => $type,
		);
		$res = M('merchant')->save($data);
		if (!$res) {
			$this->_err_ret('更新失败');
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_edit_merchant_card 编辑卡卷名称]
	 * @return [type] [description]
	 */
	public function ajax_edit_merchant_card() {
		$id = I('id');
		$card_name = I('card_name');
		$card_detail = I('card_detail');
		if ($card_name == '' || $id == '' || $card_detail == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
			'card_name' => $card_name,
			'card_detail' => $card_detail,
		);
		$res = M('merchant')->save($data);
		if (!$res) {
			//$this->insertMerchantUserLog("修改分类ID：".$id);
			$this->_err_ret('更新失败');
		}
		$this->_suc_ret();
	}
	public function user_arrive_log() {
		$userModel = M('user');
		$users = $userModel->select();
		$this->assign('users', $users);
		$merchantModel = M('merchant');
		$merchants = $merchantModel->select();
		$this->assign('merchants', $merchants);
		$this->show();
	}
	public function ajax_get_user_arrive_logs() {
		$merchant_id = I('merchant_id');
		$user_id = I('user_id');
		$arriveLogModel = D('arrive_log');
		$map = array(
			'deleted' => 0,
		);
		if ($merchant_id != "") {
			$map['merchant_id'] = $merchant_id;
		}
		if ($user_id != "") {
			$map['user_id'] = $user_id;
		}
		$arrive_logs = $arriveLogModel
			->where($map)
			->relation(true)
			->select();
		foreach ($arrive_logs as $key => $value) {
			$arrive_logs[$key]['user_nickname'] = $value['user']['nickname'];
			$arrive_logs[$key]['user_head_image'] = $value['user']['headimgurl'];
			$arrive_logs[$key]['user_arrive_times'] = $value['user']['arrive_num'];
			$arrive_logs[$key]['merchant_name'] = $value['merchant']['name'];
			$arrive_logs[$key]['merchant_image'] = $value['merchant']['image'];
			$arrive_logs[$key]['merchant_arrive_times'] = $value['merchant']['arrive_num'];
		}
		$this->_tb_suc_ret($arrive_logs);
	}
}