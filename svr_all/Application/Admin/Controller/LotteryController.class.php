<?php
namespace Admin\Controller;
use Think\Controller;

class LotteryController extends BaseController {
	/**
	 * [lottery_list 抓娃娃列表]
	 * @return [type] [description]
	 */
	public function lottery_list() {
		$lotteryTypeModel = D('lottery_type');
		$map = array(
			'deleted' => 0,
		);
		$lottery_types = $lotteryTypeModel->where($map)->select();
		$this->assign('lottery_types', $lottery_types);
		$this->show();
	}
	/**
	 * [ajax_get_lotterys 获取抓娃娃抽奖]
	 * @return [type] [description]
	 */
	public function ajax_get_lotterys() {
		$lotteryConfigModel = D("lottery_config");
		$map = array(
			'deleted' => 0,
		);
		$lottery_type_id = I('lottery_type_id');
		if ($lottery_type_id != ""
			&& $lottery_type_id != 0) {
			$map['lottery_type_id'] = $lottery_type_id;
		}
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$lottery_configs = $lotteryConfigModel
			->relation(true)
			->limit($start_index, $limit)
			->where($map)
			->order('sort asc')
			->select();
		if ($lottery_configs === false) {
			$this->_err_ret();
		}

		$count = $lotteryConfigModel->where($map)->count();
		foreach ($lottery_configs as $key => $value) {
			$lottery_configs[$key]['type_name'] = $value['lottery_type']['name'];
			$map = array(
				'level' => $value['level'],
			);
			$temp = M('vip_grade')->where($map)->find();
			if (!$temp) {
				$lottery_configs[$key]['level_name'] = '不限制';
			} else {
				$lottery_configs[$key]['level_name'] = $temp['name'];
			}

		}
		$this->_tb_suc_ret($lottery_configs, $count);

	}
	/**
	 * [good_list 娃娃列表]
	 * @return [type] [description]
	 */
	public function good_list() {
		$lotteryTypeModel = D('lottery_type');
		$map = array(
			'deleted' => 0,
		);
		$lottery_types = $lotteryTypeModel->where($map)->select();
		$this->assign('lottery_types', $lottery_types);
		$this->show();

	}
	/**
	 * [ajax_get_lottery_goods 获取娃娃]
	 * @return [type] [description]
	 */
	public function ajax_get_lottery_goods() {
		$lotteryGoodModel = D("lottery_good");
		$map = array(
			'deleted' => 0,
		);
		$lottery_type_id = I('lottery_type_id');
		if ($lottery_type_id != ""
			&& $lottery_type_id != 0) {
			$map['lottery_type_id'] = $lottery_type_id;
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
			->order('id desc')
			->select();
		if ($lottery_configs === false) {
			$this->_err_ret();
		}
		foreach ($lottery_configs as $key => $value) {
			$lottery_configs[$key]['type_name'] = $value['lottery_type']['name'];
		}
		$count = $lotteryGoodModel->where($map)->count();
		$this->_tb_suc_ret($lottery_configs, $count);

	}

	/**
	 * edit_lottery_goods_view 编辑娃娃页面
	 */
	public function edit_lottery_goods_view() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'id' => $id,
		);
		$lottery_goods = M('lottery_good')->where($map)->find();
		if (!$lottery_goods) {
			$this->_err_ret('娃娃不存在');
		}
		$map = array(
			'deleted' => 0,
		);
		$lottery_type = M('lottery_type')->where($map)->select();
		$this->assign('lottery_goods', $lottery_goods);
		$this->assign('lottery_type', $lottery_type);
		$this->show();
	}
	/**
	 * ajax_edit_lottery_goods 编辑娃娃
	 */
	public function ajax_edit_lottery_goods() {
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
			'name' => $lottery_config['name'],
			'lottery_type_id' => $lottery_config['lottery_type_id'],
			'cost' => $lottery_config['cost'],
			'remarks' => $lottery_config['remarks'],
			'stock' => $lottery_config['stock'],
			'img_url' => $lottery_config['cover_image'],
		);
		$res = M('lottery_good')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * add_lottery_goods_view 添加娃娃页面
	 */
	public function add_lottery_goods_view() {
		$map = array(
			'deleted' => 0,
		);
		$lottery_type = M('lottery_type')->where($map)->select();
		$this->assign('lottery_type', $lottery_type);
		$this->show();
	}

	/**
	 * ajax_add_lottery_goods 添加娃娃
	 */
	public function ajax_add_lottery_goods() {
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
			'name' => $lottery_config['name'],
			'lottery_type_id' => $lottery_config['lottery_type_id'],
			'img_url' => $lottery_config['cover_image'],
			'cost' => $lottery_config['cost'],
			'remarks' => $lottery_config['remarks'],
			'stock' => $lottery_config['stock'],
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('lottery_good')->add($data);
		if (!$res) {
			$this->_err_ret('添加失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_delete_lottery_goods  删除娃娃
	 */
	public function ajax_delete_lottery_goods() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $id,
		);
		$res = M('lottery_good')->where($map)->delete();
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		$this->_suc_ret();
	}
	public function lottery_type() {
		$this->show();
	}
	public function ajax_get_lottery_types() {
		$lotteryTypeModel = D("lottery_type");
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
			->relation(true)
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
	 * [ajax_add_lottery_types 添加分类]
	 * @return [type] [description]
	 */
	public function ajax_add_lottery_types() {
		$merchant = session('merchant');
		$name = I('name');
		if ($name == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'name' => $name,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('lottery_type')->add($data);
		if ($res) {
			//$this->insertMerchantUserLog("添加分类ID：".$res);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_edit_lottery_types 编辑分类]
	 * @return [type] [description]
	 */
	public function ajax_edit_lottery_types() {
		$merchant = session('merchant');
		$id = I('id');
		$name = I('name');
		if ($name == '' || $id == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
			'name' => $name,
			'add_time' => date('Y-m-d H:i:s'),
		);
		$res = M('lottery_type')->save($data);
		if ($res) {
			//$this->insertMerchantUserLog("修改分类ID：".$id);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_delete_lottery_types 删除分类]
	 * @return [type] [description]
	 */
	public function ajax_delete_lottery_types() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'deleted' => 1,
			'id' => $id,
		);
		$res = M('lottery_type')->save($data);
		if ($res) {
			//$this->insertMerchantUserLog("删除分类ID：".$id);
		}
		$this->_suc_ret();
	}
	/**
	 * lottery_record 中奖记录
	 */
	public function lottery_record() {
		//获取所有商品
		$goods_list = M('lottery_good')->select();
		$this->assign('goods_list', $goods_list);
		//用户
		$map = array(
			'deleted' => 0,
		);
		$user_list = M('user')->where($map)->select();
		$this->assign('user_list', $user_list);
		$this->show();
	}
	/**
	 * ajax_get_lottery_record 中奖记录数据
	 */
	public function ajax_get_lottery_record() {
		$lotteryRecordModel = D("lottery_record");
		$map = array(
			'deleted' => 0,
		);
		$user_id = I('user_id');
		if ($user_id != '') {
			$map['user_id'] = $user_id;
		}
		$lottery_goods_id = I('lottery_goods_id');
		if ($lottery_goods_id != '') {
			$map['lottery_good_id'] = $lottery_goods_id;
		}
		$search_val = I('search_val');
		if ($search_val != '') {
			$temp['user_id'] = array('LIKE', "%$search_val%");
			$temp['tel'] = array('LIKE', "%$search_val%");
			$temp['_logic'] = 'OR';
			$map['_complex'] = $temp;
		}
		$search_id = I('search_id');
		if ($search_id != '') {
			$map['id'] = $search_id;
		}
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
			->order('id desc')
			->select();
		if ($lottery_types === false) {
			$this->_err_ret();
		}
		$count = $lotteryRecordModel->where($map)->count();
		foreach ($lottery_types as $key => $val) {
			$lottery_types[$key]['lottery_type_name'] = $val['lottery_type']['name'];
			$lottery_types[$key]['user_name'] = $val['user']['nickname'];
			$lottery_types[$key]['user_charge_num'] = $val['user']['charge_num'];
			$lottery_types[$key]['is_subscribe'] = $val['user']['is_subscribe'];

			$lottery_types[$key]['lottery_config_name'] = $val['lottery_config']['name'];
			$lottery_types[$key]['lottery_goods_name'] = $val['lottery_good']['name'];
		}
		$this->_tb_suc_ret($lottery_types, $count);
	}
	/**
	 * edit_lottery_config_view 编辑抓娃娃页面
	 */
	public function edit_lottery_config_view() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'id' => $id,
		);
		$lottery_config = M('lottery_config')->where($map)->find();
		if (!$lottery_config) {
			$this->_err_ret('抓娃娃不存在');
		}
		$map = array(
			'deleted' => 0,
			'lottery_type_id' => $lottery_config['lottery_type_id'],
		);
		$lottery_goods = M('lottery_good')->where($map)->select();
		$map = array(
			'deleted' => 0,
		);
		$lottery_type = M('lottery_type')->where($map)->select();
		$this->assign('lottery_config', $lottery_config);
		$this->assign('lottery_goods', $lottery_goods);
		$this->assign('lottery_type', $lottery_type);
		$map = array(
			'deleted' => 0,
		);
		$vip_grade = M('vip_grade')->where($map)->order('level asc')->select();
		$this->assign('vip_grade', $vip_grade);
		$this->show();
	}
	/**
	 * edit_lottery_config_goods_view 编辑抓娃娃添加娃娃页面
	 */
	public function edit_lottery_config_goods_view() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'deleted' => 0,
			'id' => $id,
		);
		$lottery_config = M('lottery_config')->where($map)->find();
		if (!$lottery_config) {
			$this->_err_ret('抓娃娃不存在');
		}
		$map = array(
			'deleted' => 0,
			'lottery_type_id' => $lottery_config['lottery_type_id'],
		);
		$lottery_goods = M('lottery_good')->where($map)->order('add_time desc')->select();
		$this->assign('lottery_config', $lottery_config);
		$this->assign('lottery_goods', $lottery_goods);
		$this->show();
	}

	/**
	 * ajax_edit_lottery_config_goods 编辑抓娃娃奖品
	 */
	public function ajax_edit_lottery_config_goods() {
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
			'percent0' => $lottery_config['percent0'],
			'lottery_good_id0' => $lottery_config['lottery_good_id0'],
			'percent1' => $lottery_config['percent1'],
			'lottery_good_id1' => $lottery_config['lottery_good_id1'],
			'percent2' => $lottery_config['percent2'],
			'lottery_good_id2' => $lottery_config['lottery_good_id2'],
			'percent3' => $lottery_config['percent3'],
			'lottery_good_id3' => $lottery_config['lottery_good_id3'],
			'percent4' => $lottery_config['percent4'],
			'lottery_good_id4' => $lottery_config['lottery_good_id4'],
		);
		$res = M('lottery_config')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_edit_lottery_config 编辑抓娃娃
	 */
	public function ajax_edit_lottery_config() {
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
			'name' => $lottery_config['name'],
			'lottery_type_id' => $lottery_config['lottery_type_id'],
			'is_public' => $lottery_config['is_public'],
			'coin_num' => $lottery_config['coin_num'],
			'cover_image' => $lottery_config['cover_image'],
			'level' => $lottery_config['level'],
		    'sort' => $lottery_config['sort'],
		    'new_stock' => $lottery_config['new_stock'],
		);
		$res = M('lottery_config')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * add_lottery_config_view 添加抓娃娃页面
	 */
	public function add_lottery_config_view() {
		$map = array(
			'deleted' => 0,
		);
		$lottery_type = M('lottery_type')->where($map)->select();
		$this->assign('lottery_type', $lottery_type);
		$map = array(
			'deleted' => 0,
		);
		$vip_grade = M('vip_grade')->where($map)->order('level asc')->select();
		$this->assign('vip_grade', $vip_grade);
		$this->show();
	}

	/**
	 * ajax_add_lottery_config 添加抓娃娃
	 */
	public function ajax_add_lottery_config() {
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
			'name' => $lottery_config['name'],
			'lottery_type_id' => $lottery_config['lottery_type_id'],
			'is_public' => $lottery_config['is_public'],
			'coin_num' => $lottery_config['coin_num'],
			'cover_image' => $lottery_config['cover_image'],
			'level' => $lottery_config['level'],
		    'sort' => $lottery_config['sort'],
		    'new_stock' => $lottery_config['new_stock'],
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('lottery_config')->add($data);
		if (!$res) {
			$this->_err_ret('添加失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_delete_lottery_config  删除抓娃娃
	 */
	public function ajax_delete_lottery_config() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $id,
		);
		$res = M('lottery_config')->where($map)->delete();
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_del_lottery_record 删除中奖记录]
	 * @return [type] [description]
	 */
	public function ajax_del_lottery_record() {
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $id,
		);
		$res = M('lottery_record')->where($map)->delete();
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		$this->_suc_ret();

	}
	/**
	 * ajax_public_config 发布抓娃娃
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
			'is_public' => $type,
		);
		$res = M('lottery_config')->save($data);
		if (!$res) {
			$this->_err_ret('发布失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_record_status 中奖发货
	 */
	public function ajax_record_status() {
		$merchant = session('merchant');
		$id = I('id');
		$type = I('type');
		if ($id == '' || $type == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
			'status' => $type,
		);
		$res = M('lottery_record')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_delete_test_record 清空所有测试用户的中奖记录
	 */
	public function ajax_delete_test_record() {
		$map = array(
			'is_test' => 1,
			'deleted' => 0,
		);
		$temp_user = M('user')->where($map)->select();
		foreach ($temp_user as $key => $val) {
			$map = array(
				'user_id' => $val['id'],
			);
			$res = M('lottery_record')->where($map)->delete();
			$res = M('luck_draw_log')->where($map)->delete();
		}
		$this->_suc_ret();
	}
	/**
	 * record_log 进货管理
	 */
	public function record_log() {
		//获取所有商品
		$goods_list = M('lottery_good')->select();
		$this->assign('goods_list', $goods_list);
		//用户
		$map = array(
			'deleted' => 0,
		);
		$user_list = M('user')->where($map)->select();
		$this->assign('user_list', $user_list);
		$this->show();
	}
	/**
	 * ajax_get_record_log 进货管理
	 */
	public function ajax_get_record_log() {
		$lotteryRecordModel = D("lottery_record");
		$map = array(
			'deleted' => 0,
		);
		$user_id = I('user_id');
		if ($user_id != '') {
			$map['user_id'] = $user_id;
		}
		/*$lottery_goods_id = I('lottery_goods_id');
			    if ($lottery_goods_id != '') {
			        $map['lottery_good_id'] = $lottery_goods_id;
		*/
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
		$search_val = I('search_val');
		if ($search_val != '') {
			$temp['user_id'] = array('LIKE', "%$search_val%");
			$temp['tel'] = array('LIKE', "%$search_val%");
			$temp['_logic'] = 'OR';
			$map['_complex'] = $temp;
		}
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$lottery_types = $lotteryRecordModel
			->relation(true)
			->where($map)
			->order('id desc')
			->select();
		if ($lottery_types === false) {
			$this->_err_ret();
		}
		/*$count = $lotteryRecordModel->where($map)->count();
			foreach ($lottery_types as $key => $val) {
				$lottery_types[$key]['lottery_type_name'] = $val['lottery_type']['name'];
				$lottery_types[$key]['user_name'] = $val['user']['nickname'];
				$lottery_types[$key]['lottery_config_name'] = $val['lottery_config']['name'];
				$lottery_types[$key]['lottery_goods_name'] = $val['lottery_good']['name'];
		*/
		$user_id_arr = array();
		foreach ($lottery_types as $key => $val) {
			if (!in_array($val['user_id'], $user_id_arr)) {
				$user_id_arr[] = $val['user_id'];
			}
		}
		unset($map['_complex']);
		$return_arr = array();
		foreach ($user_id_arr as $key => $val) {
			$map['user_id'] = $val;
			$lottery_types_val = $lotteryRecordModel->relation(true)->where($map)->order('id desc')->select();
			if (count($lottery_types_val) >= C('FREE_POST_WAWA_COUNT')) {
				foreach ($lottery_types_val as $keys => $vals) {
					$lottery_types_val[$keys]['lottery_type_name'] = $vals['lottery_type']['name'];
					$lottery_types_val[$keys]['user_name'] = $vals['user']['nickname'];
					$lottery_types_val[$keys]['lottery_config_name'] = $vals['lottery_config']['name'];
					$lottery_types_val[$keys]['lottery_goods_name'] = $vals['lottery_good']['name'];
					$return_arr[] = $lottery_types_val[$keys];
				}
			}
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$res = pages($return_arr, $start_index, $limit);
		$this->_tb_suc_ret($res, count($return_arr));
	}

	/**
	 * export_test_record 进货管理导出表格
	 */
	public function export_test_record() {
		$lotteryRecordModel = D("lottery_record");
		$map = array(
			'deleted' => 0,
			'status' => 0,
		);
		/*$user_id = I('user_id');
			     if ($user_id != '') {
			     $map['user_id'] = $user_id;
			     }
			     $lottery_goods_id = I('lottery_goods_id');
			     if ($lottery_goods_id != '') {
			     $map['lottery_good_id'] = $lottery_goods_id;
		*/
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
		$search_val = I('search_val');
		if ($search_val != '') {
			$temp['user_id'] = array('LIKE', "%$search_val%");
			$temp['tel'] = array('LIKE', "%$search_val%");
			$temp['_logic'] = 'OR';
			$map['_complex'] = $temp;
		}
		$lottery_record = $lotteryRecordModel
			->where($map)
			->select();
		if ($lottery_record === false) {
			$this->_err_ret();
		}
		$goods_id_list = array();
		foreach ($lottery_record as $key => $val) {
			if (!in_array($val['lottery_good_id'], $goods_id_list)) {
				$goods_id_list[] = $val['lottery_good_id'];
			}
		}
		$user_id_arr = array();
		foreach ($lottery_record as $key => $val) {
			if (!in_array($val['user_id'], $user_id_arr)) {
				$user_id_arr[] = $val['user_id'];
			}
		}

		// 导出excel
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
		$objActSheet = $objPHPExcel->getActiveSheet();
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('宋体')->setSize(15)->setBold(true); //字体加粗
		$objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
		$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('A1', '中奖记录统计表');
		$objActSheet->setCellValue('A3', '中奖时间');
		$objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('B3', '中奖用户ID');
		$objPHPExcel->getActiveSheet()->getStyle('B3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('C3', '中奖用户昵称');
		$objPHPExcel->getActiveSheet()->getStyle('C3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('D3', '联系人');
		$objPHPExcel->getActiveSheet()->getStyle('D3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('E3', '联系电话');
		$objPHPExcel->getActiveSheet()->getStyle('E3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('F3', '详细地址');
		$objPHPExcel->getActiveSheet()->getStyle('F3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('G3', '备注');
		$objPHPExcel->getActiveSheet()->getStyle('G3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('H3', '发货状态');
		$objPHPExcel->getActiveSheet()->getStyle('H3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$hang = 4;
		$total_cost = 0;
		$total_num = 0;
		foreach ($goods_id_list as $k => $v) {
			$map = array(
				'deleted' => 0,
				'lottery_good_id' => $v,
				'status' => 0,
			);
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
			$goods_info = M('lottery_good')->where(array('id' => $v))->find();
			if ($goods_info) {
				$temp_record_list = $lotteryRecordModel->relation(true)->where($map)->select();
				$record_list = array();
				foreach ($temp_record_list as $keys => $vals) {
					$map['user_id'] = $vals['user_id'];
					unset($map['lottery_good_id']);
					$temp_lottery_types_val = $lotteryRecordModel->relation(true)->where($map)->order('id desc')->select();
					if (count($temp_lottery_types_val) >= C('FREE_POST_WAWA_COUNT')) {
						$temp_record_list[$keys]['lottery_type_name'] = $vals['lottery_type']['name'];
						$temp_record_list[$keys]['user_name'] = $vals['user']['nickname'];
						$temp_record_list[$keys]['lottery_config_name'] = $vals['lottery_config']['name'];
						$temp_record_list[$keys]['lottery_goods_name'] = $vals['lottery_good']['name'];
						$record_list[] = $temp_record_list[$keys];
					}
				}
				$temp_total = count($record_list) * floatval($goods_info['cost']);
				$objPHPExcel->getActiveSheet()->mergeCells('A' . $hang . ':H' . $hang);
				$objPHPExcel->getActiveSheet()->getStyle('A' . $hang)->getFont()->setName('宋体')->setSize(15)->setBold(true); //字体加粗
				$objActSheet->setCellValue('A' . $hang, '娃娃名称：' . $goods_info['name'] . '    成本：' . $goods_info['cost'] . '元    总中奖个数：' . count($record_list) . '个    总成本：' . strval($temp_total) . '元');
				$hang++;
				$total_cost = $total_cost + $temp_total;
				$total_num = $total_num + count($record_list);
				foreach ($record_list as $key => $val) {
					$status = '';
					if ($val['status'] == 0) {
						$status = '未发货';
					}
					if ($val['status'] == 1) {
						$status = '已发货';
					}
					$objActSheet->setCellValue('A' . $hang, $val['add_time']);
					$objActSheet->setCellValue('B' . $hang, $val['user_id']);
					$objActSheet->setCellValue('C' . $hang, $this->filter_Emoji($val['user_name']));
					$objActSheet->setCellValue('D' . $hang, $this->filter_Emoji($val['realname']));
					$objActSheet->setCellValue('E' . $hang, $this->filter_Emoji($val['tel']));
					$objActSheet->setCellValue('F' . $hang, $this->filter_Emoji($val['address']));
					$objActSheet->setCellValue('G' . $hang, $this->filter_Emoji($val['memo']));
					$objActSheet->setCellValue('H' . $hang, $status);
					//$objActSheet->setCellValue('I' . $hang, $this->filter_Emoji($val['lottery_goods_name']));
					$hang++;
				}
			}

		}
		$objActSheet->setCellValue('A2', '总计中奖个数：' . strval($total_num) . '    总计成本：' . strval($total_cost) . '元');
		$fileName = '中奖记录统计表';
		$fileName .= date("Y-m-d", time()) . '.xls';
		$fileName = iconv("utf-8", "gb2312", $fileName);
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=\"$fileName\"");
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');

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
	/**
	 * delivery_log 送货管理
	 */
	public function delivery_log() {
		//获取所有商品
		$goods_list = M('lottery_good')->select();
		$this->assign('goods_list', $goods_list);
		//用户
		$map = array(
			'deleted' => 0,
		);
		$user_list = M('user')->where($map)->select();
		$this->assign('user_list', $user_list);
		$this->show();
	}
	/**
	 * export_test_delivery 送货管理导出表格
	 */
	public function export_test_delivery() {
		$lotteryRecordModel = D("lottery_record");
		$map = array(
			'deleted' => 0,
			'status' => 0,
		);
		$user_id = I('user_id');
		if ($user_id != '') {
			$map['user_id'] = $user_id;
		}
		/*$lottery_goods_id = I('lottery_goods_id');
			     if ($lottery_goods_id != '') {
			     $map['lottery_good_id'] = $lottery_goods_id;
		*/
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
		$search_val = I('search_val');
		if ($search_val != '') {
			$temp['user_id'] = array('LIKE', "%$search_val%");
			$temp['tel'] = array('LIKE', "%$search_val%");
			$temp['_logic'] = 'OR';
			$map['_complex'] = $temp;
		}
		$lottery_record = $lotteryRecordModel
			->where($map)
			->select();
		if ($lottery_record === false) {
			$this->_err_ret();
		}
		$user_id_list = array();
		foreach ($lottery_record as $key => $val) {
			if (!in_array($val['user_id'], $user_id_list)) {
				$user_id_list[] = $val['user_id'];
			}
		}
		// 导出excel
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
		$objActSheet = $objPHPExcel->getActiveSheet();
		//$objDrawing = new \PHPExcel_Worksheet_Drawing();
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('宋体')->setSize(15)->setBold(true); //字体加粗
		$objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
		$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('A1', '中奖记录统计表');
		$objActSheet->setCellValue('A3', '中奖时间');
		$objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('B3', '中奖用户ID');
		$objPHPExcel->getActiveSheet()->getStyle('B3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('C3', '奖品名称');
		$objPHPExcel->getActiveSheet()->getStyle('C3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('D3', '联系人');
		$objPHPExcel->getActiveSheet()->getStyle('D3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('E3', '联系电话');
		$objPHPExcel->getActiveSheet()->getStyle('E3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('F3', '详细地址');
		$objPHPExcel->getActiveSheet()->getStyle('F3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('G3', '备注');
		$objPHPExcel->getActiveSheet()->getStyle('G3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('H3', '发货状态');
		$objPHPExcel->getActiveSheet()->getStyle('H3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('I3', '中奖ID');
		$objPHPExcel->getActiveSheet()->getStyle('I3')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$hang = 4;
		$total_cost = 0;
		foreach ($user_id_list as $k => $v) {
			$map = array(
				'deleted' => 0,
				'user_id' => $v,
				'status' => 0,
			);
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
			$user_info = M('user')->where(array('id' => $v))->find();
			if ($user_info) {
				$record_list = $lotteryRecordModel->relation(true)->where($map)->select();
				if (count($record_list) >= C('FREE_POST_WAWA_COUNT')) {
					$objPHPExcel->getActiveSheet()->mergeCells('A' . $hang . ':H' . $hang);
					$objPHPExcel->getActiveSheet()->getStyle('A' . $hang)->getFont()->setName('宋体')->setSize(15)->setBold(true); //字体加粗
					$objActSheet->setCellValue('A' . $hang, '用户ID：' . $user_info['id']);
					$hang++;
					foreach ($record_list as $key => $val) {
						$status = '';
						if ($val['status'] == 0) {
							$status = '未发货';
						}
						if ($val['status'] == 1) {
							$status = '已发货';
						}
						$goods_info = M('lottery_good')->where(array('id' => $val['lottery_good_id']))->find();
						$objActSheet->setCellValue('A' . $hang, $val['add_time']);
						$objActSheet->setCellValue('B' . $hang, $val['user_id']);
						$objActSheet->setCellValue('C' . $hang, $this->filter_Emoji($goods_info['name']));
						$objActSheet->setCellValue('D' . $hang, $this->filter_Emoji($val['realname']));
						$objActSheet->setCellValue('E' . $hang, $this->filter_Emoji($val['tel']));
						$objActSheet->setCellValue('F' . $hang, $this->filter_Emoji($val['address']));
						$objActSheet->setCellValue('G' . $hang, $this->filter_Emoji($val['memo']));
						$objActSheet->setCellValue('H' . $hang, $status);
						$objActSheet->setCellValue('I' . $hang, $val['id']);
// 						//开始设置图片啦~~
// 						$objDrawing->setPath($goods_info['img_url']);
// 						// 设置图片宽度高度
// 						$objDrawing->setHeight(80);//照片高度
// 						$objDrawing->setWidth(80); //照片宽度
// 						/*设置图片要插入的单元格*/
// 						$objDrawing->setCoordinates('J'.$hang);
// 						// 图片偏移距离
// 						$objDrawing->setOffsetX(12);
// 						$objDrawing->setOffsetY(12);
// 						$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
						$hang++;
					}
					$total_cost = $total_cost + count($record_list);
				}
			}

		}
		$objActSheet->setCellValue('A2', '总计中奖个数：' . $total_cost);
		$fileName = '送货单记录统计表';
		$fileName .= date("Y-m-d", time()) . '.xls';
		$fileName = iconv("utf-8", "gb2312", $fileName);
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=\"$fileName\"");
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');

	}
	/**
	 * luck_draw_log 抽奖记录
	 */
	public function luck_draw_log() {
		//获取所有商品
		$goods_list = M('lottery_config')->select();
		$this->assign('goods_list', $goods_list);
		//用户
		$map = array(
			'deleted' => 0,
		);
		$user_list = M('user')->where($map)->select();
		$this->assign('user_list', $user_list);
		$this->show();
	}
	/**
	 * ajax_get_luck_draw_log 抽奖记录数据
	 */
	public function ajax_get_luck_draw_log() {
		$map = array(
			'deleted' => 0,
		);
		$user_id = I('user_id');
		if ($user_id != '') {
			$map['user_id'] = $user_id;
		}
		$lottery_goods_id = I('lottery_goods_id');
		if ($lottery_goods_id != '') {
			$map['lottery_config_id'] = $lottery_goods_id;
		}
		/*$start_time = I('start_time');
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
		*/

		$search_val = I('search_val');
		if ($search_val != '') {
			//$temp['user_id'] = array('LIKE',"%$search_val%");
			//$temp['tel'] = array('LIKE',"%$search_val%");
			//$temp['_logic'] = 'OR';
			//$map['_complex'] = $temp;
			$map['user_id'] = $search_val;
		}
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$lottery_types = M('luck_draw_log')->limit($start_index, $limit)->where($map)->order('id desc')->select();
		if ($lottery_types === false) {
			$this->_err_ret();
		}
		$count = M('luck_draw_log')->where($map)->count();
		foreach ($lottery_types as $key => $val) {
			$user_map = array(
				'deleted' => 0,
				'id' => $val['user_id'],
			);
			$res = M('user')->where($user_map)->find();
			$lottery_types[$key]['user_nickname'] = $res['nickname'];

			$lottery_config_map = array(
				'deleted' => 0,
				'id' => $val['lottery_config_id'],
			);
			$res = M('lottery_config')->where($lottery_config_map)->find();
			$lottery_types[$key]['lottery_config_name'] = $res['name'];

			if ($val['is_hit'] == 0) {
				$lottery_types[$key]['is_hit'] = '未中奖';
			}
			if ($val['is_hit'] == 1) {
				$lottery_types[$key]['is_hit'] = '中奖';
			}
			if ($val['hit_good_id'] != 0) {
				$lottery_goods_map = array(
					'deleted' => 0,
					'id' => $val['hit_good_id'],
				);
				$res = M('lottery_good')->where($lottery_goods_map)->find();
				$lottery_types[$key]['hit_good_name'] = $res['name'];
			} else {
				$lottery_types[$key]['hit_good_name'] = '';
			}
		}
		$this->_tb_suc_ret($lottery_types, $count);
	}
	/**
	 * edit_record_status 一键发货
	 */
	public function edit_record_status() {
		$lotteryRecordModel = D("lottery_record");
		$map = array(
			'deleted' => 0,
		);
		$user_id = I('user_id');
		if ($user_id != '') {
			$map['user_id'] = $user_id;
		}
		$lottery_goods_id = I('lottery_goods_id');
		if ($lottery_goods_id != '') {
			$map['lottery_good_id'] = $lottery_goods_id;
		}
		if ($user_id == '' && $lottery_goods_id == '') {
			$this->_err_ret('请选择条件，一键发货用户和娃娃必须选择其一');
			exit();
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
		$search_val = I('search_val');
		if ($search_val != '') {
			$temp['user_id'] = array('LIKE', "%$search_val%");
			$temp['tel'] = array('LIKE', "%$search_val%");
			$temp['_logic'] = 'OR';
			$map['_complex'] = $temp;
		}

		$lottery_types = $lotteryRecordModel->relation(true)->where($map)->order('id desc')->select();
		$user_id_arr = array();
		foreach ($lottery_types as $key => $val) {
			if (!in_array($val['user_id'], $user_id_arr)) {
				$user_id_arr[] = $val['user_id'];
			}
		}
		unset($map['_complex']);
		$return_arr = array();
		foreach ($user_id_arr as $key => $val) {
			$map['user_id'] = $val;
			$lottery_types_val = $lotteryRecordModel->relation(true)->where($map)->order('id desc')->select();
			if (count($lottery_types_val) >= C('FREE_POST_WAWA_COUNT')) {
				$data = array(
					'status' => 1,
				);
				$lottery_record = $lotteryRecordModel->where($map)->save($data);
			}
		}
		$this->_suc_ret();
	}
	/**
	 * upload_file_msg
	 */
	public function upload_file_msg() {
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728888;
		$upload->exts = array('xls', 'xlsx');
		$upload->autoSub = true;
		$upload->subType = 'date';
		$upload->dateFormat = 'Ym';
		$path = 'inner_msg/xls/';
		$upload->savePath = $path;
		$info = $upload->uploadOne($_FILES['upload_file_msg']);
		if (!$info) {
			$this->_err_ret($upload->getError());
		} else {
			$ext = $info['ext'];
			$xiaoqu_xls_filename = './Uploads/' . $info['savepath'] . $info['savename'];
			vendor('PHPExcel.PHPExcel');
			$phpExcel = new \PHPExcel();
			if ($ext == 'xls') {
				$phpReader = new \PHPExcel_Reader_Excel5();
			} else {
				$phpReader = new \PHPExcel_Reader_Excel2007();
			}
			$phpExcel = $phpReader->load($xiaoqu_xls_filename);
			$currentSheet = $phpExcel->getSheet(0);
			$all_row_num = $currentSheet->getHighestRow();
			$all_column_num = $currentSheet->getHighestColumn();
			for ($current_row = 2; $current_row <= $all_row_num; $current_row++) {
				$tel = $currentSheet->getCell('B' . $current_row)->getValue();
				$name = $currentSheet->getCell('A' . $current_row)->getValue();
				$order_code = $currentSheet->getCell('I' . $current_row)->getValue();
				if ($tel) {
					$map = array(
						'deleted' => 0,
						'tel' => $tel,
					);
					$start_time = I('form_start_time');
					$end_time = I('form_end_time');
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
					$temp = M('lottery_record')->where($map)->find();
					if ($temp) {
						$id = $temp['user_id'];
						$user = $this->getUserById($id);
						$res = $this->send_user_package_inner_msg($user, $name, $tel, "申通", $order_code);
					}
				}
			}
		}
		$this->_suc_ret();
	}
}