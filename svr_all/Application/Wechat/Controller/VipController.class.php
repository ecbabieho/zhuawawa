<?php
namespace Wechat\Controller;
use Think\Controller;

class VipController extends BaseController {
	public function index() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$user = M('user')->where(array('id' => $user['id']))->find();
		$this->assign('user', $user);
		$lat = I('lat');
		$lng = I('lng');
		$category_id = I('category_id');
		$name = I('key');
// 		$lat = 108.895946;
		// 		$lng = 34.217844;
		// 		if ($lat == '' || $lng == '') {
		// 			exit();
		// 		}
		$this->assign('lat', $lat);
		$this->assign('lng', $lng);
		// 查询用户附近的商户
		$map = array(
			'deleted' => 0,
			'is_fabu' => 1,
		);
		if ($category_id != '') {
			$map['category_id'] = $category_id;
		}
		if ($name != '') {
			$map['name'] = array('LIKE', "%$name%");
		}
		$merchant_list = M('merchant')->where($map)->select();
		$return_arr = array();
		foreach ($merchant_list as $key => $val) {
			$lat_lng = $val['lat_lng'];
			$temp_arr = explode(',', $lat_lng);
			$m_lat = $temp_arr[1];
			$m_lng = $temp_arr[0];
			$distance = $this->getdistance($lng, $lat, $m_lng, $m_lat);
			if ($distance < 3000) {
				$return_arr[$key] = $distance;
			}
		}
		asort($return_arr);
		$return = array();
		foreach ($return_arr as $key => $val) {
			$temp = $merchant_list[$key];
			if ($val >= 1000) {
				$distance = round($val / 1000, 2) . 'km';
			} else {
				$distance = intval($val) . 'm';
			}
			$temp['distance'] = $distance;
			$map = array(
				'merchant_id' => $temp['id'],
				'deleted' => 0,
			);
			$activity = M('merchant_activity')->where($map)->order('add_time desc')->find();
			$temp['merchant_activity'] = $activity;
			$return[] = $temp;
		}
		$page_no = 1;
		$page_num = 10;
		$return = pages($return, ($page_no - 1) * $page_num, $page_num);
		$this->assign('shop_list', $return);
		//banner
		$map = array(
			'deleted' => 0,
		);
		$banner = M('merchant_banner')->where($map)->order('add_time asc')->select();
		$this->assign('banner', $banner);
		//分类
		$map = array(
			'deleted' => 0,
		);
		$merchant_category = M('merchant_category')->where($map)->order('sort asc')->select();
		$this->assign('merchant_category', $merchant_category);
		$this->show();
	}
	public function ajax_get_more_merchant() {
		$lat = I('lat');
		$lng = I('lng');
		$category_id = I('category_id');
		$name = I('key');
		$page_no = I('page_no');
		$page_num = I('page_num');
		if ($lat == '' || $lng == '' || $page_no == '' || $page_num == '') {
			exit();
		}
		// 查询用户附近的商户
		$map = array(
			'deleted' => 0,
			'is_fabu' => 1,
		);
		if ($category_id != '') {
			$map['category_id'] = $category_id;
		}
		if ($name != '') {
			$map['name'] = array('LIKE', "%$name%");
		}
		$merchant_list = M('merchant')->where($map)->select();
		$return_arr = array();
		foreach ($merchant_list as $key => $val) {
			$lat_lng = $val['lat_lng'];
			$temp_arr = explode(',', $lat_lng);
			$m_lat = $temp_arr[1];
			$m_lng = $temp_arr[0];
			$distance = $this->getdistance($lng, $lat, $m_lng, $m_lat);
			if ($distance < 30000) {
				$return_arr[$key] = $distance;
			}
		}
		asort($return_arr);
		$return = array();
		foreach ($return_arr as $key => $val) {
			$temp = $merchant_list[$key];
			if ($val >= 1000) {
				$distance = round($val / 1000, 2) . 'km';
			} else {
				$distance = intval($val) . 'm';
			}
			$temp['distance'] = $distance;
			$map = array(
				'merchant_id' => $temp['id'],
				'deleted' => 0,
			);
			$activity = M('merchant_activity')->where($map)->order('add_time desc')->find();
			$temp['merchant_activity'] = $activity;
			$return[] = $temp;
		}
		$return = pages($return, ($page_no - 1) * $page_num, $page_num);
		$this->_suc_ret($return);
	}
	public function category() {
		$lat = I('lat');
		$lng = I('lng');
		$category_id = I('category_id');
		$name = I('key');
// 		$lat = 108.895946;
		// 		$lng = 34.217844;
		if ($category_id == '') {
			exit();
		}
		// 查询用户附近的商户
		$map = array(
			'deleted' => 0,
			'is_fabu' => 1,
		);
		if ($category_id != '') {
			$map['category_id'] = $category_id;
		}
		if ($name != '') {
			$map['name'] = array('LIKE', "%$name%");
		}
		$merchant_list = M('merchant')->where($map)->select();
		$return_arr = array();
		foreach ($merchant_list as $key => $val) {
			$lat_lng = $val['lat_lng'];
			$temp_arr = explode(',', $lat_lng);
			$m_lat = $temp_arr[1];
			$m_lng = $temp_arr[0];
			$distance = $this->getdistance($lng, $lat, $m_lng, $m_lat);
			if ($distance < 3000) {
				$return_arr[$key] = $distance;
			}
		}
		asort($return_arr);
		$return = array();
		foreach ($return_arr as $key => $val) {
			$temp = $merchant_list[$key];
			if ($val >= 1000) {
				$distance = round($val / 1000, 2) . 'km';
			} else {
				$distance = intval($val) . 'm';
			}
			$temp['distance'] = $distance;
			$map = array(
				'merchant_id' => $temp['id'],
				'deleted' => 0,
			);
			$activity = M('merchant_activity')->where($map)->order('add_time desc')->find();
			$temp['merchant_activity'] = $activity;
			$return[] = $temp;
		}
		$page_no = 1;
		$page_num = 10;
		$return = pages($return, ($page_no - 1) * $page_num, $page_num);
		$this->assign('shop_list', $return);
		//banner
		$map = array(
			'deleted' => 0,
		);
		$banner = M('merchant_banner')->where($map)->order('add_time asc')->select();
		$this->assign('banner', $banner);
		//分类
		$map = array(
			'deleted' => 0,
			'id' => $category_id,
		);
		$merchant_category = M('merchant_category')->where($map)->find();
		$this->assign('merchant_category', $merchant_category);
		$this->show();
	}
	public function search() {
		$map = array(
			'deleted' => 0,
		);
		$merchant_search = M('merchant_search')->where($map)->order('add_time desc')->select();
		$this->assign('merchant_search', $merchant_search);
		$this->show();
	}
	public function search_res() {
		$lat = I('lat');
		$lng = I('lng');
		$category_id = I('category_id');
		$name = I('key');
// 		$lat = 108.895946;
		// 		$lng = 34.217844;
		// if ($lat == '' || $lng == '') {
		// 	exit();
		// }
		// $this->assign('lat', $lat);
		// $this->assign('lng', $lng);
		// 查询用户附近的商户
		$map = array(
			'deleted' => 0,
			'is_fabu' => 1,
		);
		if ($category_id != '') {
			$map['category_id'] = $category_id;
		}
		if ($name != '') {
			$map['name'] = array('LIKE', "%$name%");
		}
		$merchant_list = M('merchant')->where($map)->select();
		$return_arr = array();
		foreach ($merchant_list as $key => $val) {
			$lat_lng = $val['lat_lng'];
			$temp_arr = explode(',', $lat_lng);
			$m_lat = $temp_arr[1];
			$m_lng = $temp_arr[0];
			$distance = $this->getdistance($lng, $lat, $m_lng, $m_lat);
			if ($distance < 3000) {
				$return_arr[$key] = $distance;
			}
		}
		asort($return_arr);
		$return = array();
		foreach ($return_arr as $key => $val) {
			$temp = $merchant_list[$key];
			if ($val >= 1000) {
				$distance = round($val / 1000, 2) . 'km';
			} else {
				$distance = intval($val) . 'm';
			}
			$temp['distance'] = $distance;
			$map = array(
				'merchant_id' => $temp['id'],
				'deleted' => 0,
			);
			$activity = M('merchant_activity')->where($map)->order('add_time desc')->find();
			$temp['merchant_activity'] = $activity;
			$return[] = $temp;
		}
		$page_no = 1;
		$page_num = 10;
		$return = pages($return, ($page_no - 1) * $page_num, $page_num);
		$this->assign('shop_list', $return);
		//banner
		$map = array(
			'deleted' => 0,
		);
		$banner = M('merchant_banner')->where($map)->order('add_time asc')->select();
		$this->assign('banner', $banner);
		//分类
		$map = array(
			'deleted' => 0,
			'id' => $category_id,
		);
		$merchant_category = M('merchant_category')->where($map)->find();
		$this->assign('merchant_category', $merchant_category);
		$this->show();
	}
	public function detail() {
		$this->show();
	}
	public function tousu() {
		$this->show();
	}
	public function shop_detail() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'id' => $user['id'],
		);
		$user = M('user')->where($map)->find();
		$this->assign('user', $user);
		$id = I('id');
		if ($id == '') {
			exit();
		}
		$map = array(
			'id' => $id,
		);
		$merchant = M('merchant')->where($map)->find();
		$recommend = explode(' ', $merchant['recommend']);
		$merchant['recommend_arr'] = $recommend;
		$map = array(
			'deleted' => 0,
			'merchant_id' => $id,
		);
		$merchant_activity = M('merchant_activity')->where($map)->order('add_time desc')->limit(10)->select();
		$merchant['vip_discount_arr'] = explode('；', $merchant['vip_discount']);
		//$this->_suc_ret($merchant['vip_discount']);
		$this->assign('merchant', $merchant);
		$this->assign('merchant_activity', $merchant_activity);
		//附近推荐
		$temp_lat_lng = $merchant['lat_lng'];
		$temp_arr = explode(',', $temp_lat_lng);
		$lat = $temp_arr[1];
		$lng = $temp_arr[0];
		// 查询用户附近的商户
		$map = array(
			'deleted' => 0,
			'id' => array('neq', $merchant['id']),
		);
		if ($category_id != '') {
			$map['category_id'] = $category_id;
		}
		if ($name != '') {
			$map['name'] = array('LIKE', "%$name%");
		}
		$merchant_list = M('merchant')->where($map)->select();
		$return_arr = array();
		foreach ($merchant_list as $key => $val) {
			$lat_lng = $val['lat_lng'];
			$temp_arr = explode(',', $lat_lng);
			$m_lat = $temp_arr[1];
			$m_lng = $temp_arr[0];
			$distance = $this->getdistance($lng, $lat, $m_lng, $m_lat);
			if ($distance < 3000) {
				$return_arr[$key] = $distance;
			}
		}
		asort($return_arr);
		$return = array();
		foreach ($return_arr as $key => $val) {
			$temp = $merchant_list[$key];
			if ($val >= 1000) {
				$distance = round($val / 1000, 2) . 'km';
			} else {
				$distance = intval($val) . 'm';
			}
			$temp['distance'] = $distance;
			$map = array(
				'merchant_id' => $temp['id'],
				'deleted' => 0,
			);
			$activity = M('merchant_activity')->where($map)->order('add_time desc')->find();
			$temp['merchant_activity'] = $activity;
			$return[] = $temp;
		}
		$page_no = 1;
		$page_num = 3;
		$return = pages($return, ($page_no - 1) * $page_num, $page_num);
		$this->assign('shop_list', $return);
		//判断用户是否收藏
		$map = array(
			'merchant_id' => $merchant['id'],
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$merchant_collection = M('merchant_collection')->where($map)->find();
		if ($merchant_collection) {
			$this->assign('is_collection', 1);
		} else {
			$this->assign('is_collection', 0);
		}
		//增加浏览记录
		$map = array(
			'merchant_id' => $merchant['id'],
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$merchant_browse_log = M('merchant_browse_log')->where($map)->delete();
		$data = array(
			'merchant_id' => $merchant['id'],
			'user_id' => $user['id'],
			'deleted' => 0,
			'add_time' => date('Y-m-d H:i:s'),
		);
		$res = M('merchant_browse_log')->add($data);
		$this->show();
	}
	public function act_detail() {
		$id = I('id');
		if ($id == '') {
			exit();
		}
		$map = array(
			'deleted' => 0,
			'id' => $id,
		);
		$merchant_activity = M('merchant_activity')->where($map)->find();
		$this->assign('merchant_activity', $merchant_activity);
		$map = array(
			'id' => $merchant_activity['merchant_id'],
		);
		$merchant = M('merchant')->where($map)->find();
		$this->assign('merchant', $merchant);
		$this->show();
	}
	public function ruzhu() {
		$this->show();
	}
	/**
	 * personal  个人中心
	 */
	public function personal() {
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
			if (time() >= $over_time) {
				$is_over = 1;
			} else {
				$is_over = 0;
			}
		} else {
			$is_over = 1;
		}
		$this->assign('is_over', $is_over);
		//获取收藏个数
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$collection_count = M('merchant_collection')->where($map)->count();
		if (!$collection_count) {
			$collection_count = 0;
		}
		$this->assign('user', $user);
		$this->assign('collection_count', $collection_count);
		$this->show();
	}
	/**
	 * 求两个已知经纬度之间的距离,单位为米
	 *
	 * @param lng1 $ ,lng2 经度
	 * @param lat1 $ ,lat2 纬度
	 * @return float 距离，单位米
	 * @author www.Alixixi.com
	 */
	function getdistance($lng1, $lat1, $lng2, $lat2) {
		// 将角度转为狐度
		$radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
		$radLat2 = deg2rad($lat2);
		$radLng1 = deg2rad($lng1);
		$radLng2 = deg2rad($lng2);
		$a = $radLat1 - $radLat2;
		$b = $radLng1 - $radLng2;
		$s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
		return $s;
	}
	/**
	 * ajax_merchant_collection 收藏接口
	 */
	public function ajax_merchant_collection() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$merchant_id = I('merchant_id');
		if ($merchant_id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'user_id' => $user['id'],
			'merchant_id' => $merchant_id,
			'deleted' => 0,
		);
		$res = M('merchant_collection')->where($map)->find();
		if ($res) {
			$result = M('merchant_collection')->where($map)->delete();
			if ($result) {
				$this->_suc_ret();
			} else {
				$this->_err_ret('取消收藏失败');
			}
		} else {
			$data = array(
				'user_id' => $user['id'],
				'merchant_id' => $merchant_id,
				'deleted' => 0,
				'add_time' => date('Y-m-d H:i:s'),
			);
			$result = M('merchant_collection')->add($data);
			if ($result) {
				$this->_suc_ret();
			} else {
				$this->_err_ret('添加收藏失败');
			}
		}
	}
	public function history() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$mam = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$collect = M('merchant_browse_log')->where($map)->order('add_time desc')->limit(10)->select();
		$return = array();
		foreach ($collect as $key => $val) {
			$map = array(
				'id' => $val['merchant_id'],
				'deleted' => 0,
				'is_fabu' => 1,
			);
			$merchant = M('merchant')->where($map)->find();
			if ($merchant) {
				$map = array(
					'merchant_id' => $val['merchant_id'],
					'deleted' => 0,
				);
				$activity = M('merchant_activity')->where($map)->order('add_time desc')->find();
				$merchant['merchant_activity'] = $activity;
				$return[] = $merchant;
			}
		}
		$this->assign('shop_list', $return);
		$this->show();
	}
	public function collect() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$mam = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$collect = M('merchant_collection')->where($map)->order('add_time desc')->limit(10)->select();
		$return = array();
		foreach ($collect as $key => $val) {
			$map = array(
				'id' => $val['merchant_id'],
				'deleted' => 0,
				'is_fabu' => 1,
			);
			$merchant = M('merchant')->where($map)->find();
			if ($merchant) {
				$map = array(
					'merchant_id' => $val['merchant_id'],
					'deleted' => 0,
				);
				$activity = M('merchant_activity')->where($map)->order('add_time desc')->find();
				$merchant['merchant_activity'] = $activity;
				$return[] = $merchant;
			}
		}
		$this->assign('shop_list', $return);
		$this->show();
	}
	public function ajax_get_more_collect() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$page_no = I('page_no');
		$page_num = I('page_num');
		$lat = I('lat');
		$lng = I('lng');
		if ($page_no == '' || $page_num == '') {
			exit();
		}
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$collect = M('merchant_collection')->where($map)->order('add_time desc')->limit(($page_no - 1) * $page_num, $page_num)->select();
		$return = array();
		foreach ($collect as $key => $val) {
			$map = array(
				'id' => $val['merchant_id'],
				'deleted' => 0,
				'is_fabu' => 1,
			);
			$merchant = M('merchant')->where($map)->find();
			if ($merchant) {
				$lat_lng = $merchant['lat_lng'];
				$temp_arr = explode(',', $lat_lng);
				$m_lat = $temp_arr[1];
				$m_lng = $temp_arr[0];
				$map = array(
					'merchant_id' => $val['merchant_id'],
					'deleted' => 0,
				);
				$activity = M('merchant_activity')->where($map)->order('add_time desc')->find();
				$merchant['merchant_activity'] = $activity;
				$distance = $this->getdistance($lng, $lat, $m_lng, $m_lat);
				if ($distance >= 1000) {
					$distance = round($distance / 1000, 2) . 'km';
				} else {
					$distance = intval($distance) . 'm';
				}
				$merchant['distance'] = $distance;
				$return[] = $merchant;
			}
		}
		$this->_suc_ret($return);
	}
	public function ajax_get_more_history() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$page_no = I('page_no');
		$page_num = I('page_num');
		$lat = I('lat');
		$lng = I('lng');
		if ($page_no == '' || $page_num == '') {
			exit();
		}
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$collect = M('merchant_browse_log')->where($map)->order('add_time desc')->limit(($page_no - 1) * $page_num, $page_num)->select();
		$return = array();
		foreach ($collect as $key => $val) {
			$map = array(
				'id' => $val['merchant_id'],
				'deleted' => 0,
				'is_fabu' => 1,
			);
			$merchant = M('merchant')->where($map)->find();
			if ($merchant) {
				$lat_lng = $merchant['lat_lng'];
				$temp_arr = explode(',', $lat_lng);
				$m_lat = $temp_arr[1];
				$m_lng = $temp_arr[0];
				$map = array(
					'merchant_id' => $val['merchant_id'],
					'deleted' => 0,
				);
				$activity = M('merchant_activity')->where($map)->order('add_time desc')->find();
				$merchant['merchant_activity'] = $activity;
				$distance = $this->getdistance($lng, $lat, $m_lng, $m_lat);
				if ($distance >= 1000) {
					$distance = round($distance / 1000, 2) . 'km';
				} else {
					$distance = intval($distance) . 'm';
				}
				$merchant['distance'] = $distance;
				$return[] = $merchant;
			}
		}
		$this->_suc_ret($return);
	}
	public function ajax_ruzhu() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$merchant_name = I('name');
		$tel = I('tel');
		$address = I('address');
		$link_name = I('link_name');
		$business_time = I('business_time');
		$recommend = I('recommend');
		$cover_image = I('cover_image');
		$discount = I('discount');
		if ($merchant_name == '' || $tel == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'merchant_name' => $merchant_name,
			'user_id' => $user['id'],
			'tel' => $tel,
			'address' => $address,
		);
		$res = M('merchant_apply')->where($data)->find();
		if ($res) {
			$this->_err_ret('您已经提交过了哦~');
		}
		$data['deleted'] = 0;
		$data['add_time'] = date('Y-m-d H:i:s');
		$data['link_name'] = $link_name;
		$data['business_time'] = $business_time;
		$data['recommend'] = $recommend;
		$data['cover_image'] = $cover_image;
		$data['discount'] = $discount;
		$res = M('merchant_apply')->add($data);
		if (!$res) {
			$this->_err_ret('申请失败，请稍后重试');
		}
		$this->_suc_ret();
	}
	/***********************连续签到赠送糖豆活动************************************/
	public function sign() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$sign_log_info = M('sign_log')->where($map)->order('add_time desc')->limit(1)->find();
		//获取昨天是否签到
		$date_time = date("Y-m-d", strtotime("-1 day"));
		$star_time = $date_time . ' 00:00:00';
		$end_time = $date_time . ' 23:59:59';
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
			'add_time' => array(
				array('EGT', $star_time),
				array('ELT', $end_time),
			),
		);
		$temp_sign_log_info = M('sign_log')->where($map)->find();
		$times = 0;
		//今天有没有签到
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
			'add_time' => array(
				array('EGT', date('Y-m-d') . ' 00:00:00'),
				array('ELT', date('Y-m-d') . ' 23:59:59'),
			),
		);
		$sign_log_today = M('sign_log')->where($map)->find();

		if ($temp_sign_log_info) {
			$times = $sign_log_info['continuity_times'] + 1;
			$is_set = $temp_sign_log_info['continuity_times'] + 1;
		} else {
			if (!$sign_log_today) {
				$times = 1;
			} else {
				$times = 2;
			}
			$is_set = 1;
		}
		if (($sign_log_info['continuity_times'] + 1) == 8) {
//如果是第七天，判断是不是今日
			$map = array(
				'user_id' => $user['id'],
				'deleted' => 0,
				'add_time' => array(
					array('EGT', date('Y-m-d') . ' 00:00:00'),
					array('ELT', date('Y-m-d') . ' 23:59:59'),
				),
			);
			$sign_log_temp = M('sign_log')->where($map)->find();
			if (!$sign_log_temp) {
				$times = 1;
			}
		}
		$this->assign('times', $times);
		$this->assign('is_set', $is_set);
		$this->show();
	}
	public function ajax_sign() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$user = M('user')->where(array('id' => $user['id']))->find();
		if (!$user) {
			$this->_err_ret('用户不存在');
		}
		$ajax_times = I('times');
		if ($ajax_times == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
		);
		$sign_log_info = M('sign_log')->where($map)->order('add_time desc')->limit(1)->find();
		//获取昨天是否签到
		$date_time = date("Y-m-d", strtotime("-1 day"));
		$star_time = $date_time . ' 00:00:00';
		$end_time = $date_time . ' 23:59:59';
		$map = array(
			'user_id' => $user['id'],
			'deleted' => 0,
			'add_time' => array(
				array('EGT', $star_time),
				array('ELT', $end_time),
			),
		);
		$temp_sign_log_info = M('sign_log')->where($map)->find();
		$times = 0;
		if ($temp_sign_log_info) {
			$times = $sign_log_info['continuity_times'] + 1;
		} else {
			$map = array(
				'user_id' => $user['id'],
				'deleted' => 0,
				'add_time' => array(
					array('EGT', date('Y-m-d') . ' 00:00:00'),
					array('ELT', date('Y-m-d') . ' 23:59:59'),
				),
			);
			$sign_log_temp = M('sign_log')->where($map)->find();
			if (!$sign_log_temp) {
				$times = 1;
			} else {
				$times = 2;
			}
		}
		if (($sign_log_info['continuity_times'] + 1) == 8) {
//如果是第七天，判断是不是今日
			$map = array(
				'user_id' => $user['id'],
				'deleted' => 0,
				'add_time' => array(
					array('EGT', date('Y-m-d') . ' 00:00:00'),
					array('ELT', date('Y-m-d') . ' 23:59:59'),
				),
			);
			$sign_log_temp = M('sign_log')->where($map)->find();
			if (!$sign_log_temp) {
				$times = 1;
			}
		}
		//$this->_err_ret($times.'----'.$ajax_times);
		if ($ajax_times != $times) {
			$this->_err_ret('参数有误~');
		}
		$map = array(
			'deleted' => 0,
			'add_time' => array(
				array('EGT', date('Y-m-d') . ' 00:00:00'),
				array('ELT', date('Y-m-d') . ' 23:59:59'),
			),
			'user_id' => $user['id'],
		);
		$sign_log_temp = M('sign_log')->where($map)->find();
		if ($sign_log_temp) {
			$this->_err_ret('今日已经签过到了哦~');
		}
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user['id'],
			'continuity_times' => $times,
		);
		$res = M('sign_log')->add($data);
		if (!$res) {
			$this->_err_ret('签到失败，请稍候重试~');
		}
		if ($times == 1) {
			$num = 1;
		}
		if ($times == 2) {
			$num = 1;
		}
		if ($times == 3) {
			$num = 3;
		}
		if ($times == 4) {
			$num = 3;
		}
		if ($times == 5) {
			$num = 5;
		}
		if ($times == 6) {
			$num = 5;
		}
		if ($times == 7) {
			$num = 5;
		}
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'user_id' => $user['id'],
			'coin_config_id' => 0,
			'num' => $num,
			'before_balance' => $user['coin_num'],
			'after_balance' => $user['coin_num'] + $num,
			'status' => 1,
			'money' => 0,
			'type' => -6,
			'luck_num' => 0,
		);
		$user_coin_record_res = M('user_coin_record')->add($data);
		if (!$user_coin_record_res) {
			M('sign_log')->where(array('id' => $res))->delete();
			$this->_err_ret('签到失败，请稍候重试~');
		}
		$user_data = array(
			'id' => $user['id'],
			'coin_num' => $user['coin_num'] + $num,
		);
		$user_res = M('user')->save($user_data);
		if (!$user_res) {
			M('sign_log')->where(array('id' => $res))->delete();
			M('user_coin_record')->where(array('id' => $user_coin_record_res))->delete();
			$this->_err_ret('签到失败，请稍候重试~');
		}
		$data = array(
			'num' => $num,
		);
		$this->_suc_ret($data);
	}
	/**
	 * [vip_expire_notice 会员过期提醒]
	 * @return [type] [description]
	 */
	public function vip_expire_notice() {
		$userModel = M('user');
		$map = array(
			'deleted' => 0,
			'level' => array('gt', 1),
			//'id' => 571,
		);
		$vip_users = $userModel->where($map)->select();
		$time_today_start = strtotime(date('Y-m-d', time(0)));
		$time_tomorrow_start = $time_today_start + 24 * (3600);
		$time_tomorrow_end = $time_tomorrow_start + 24 * (3600);
		$users = array();
		foreach ($vip_users as $key => $value) {
			$vip_end_time = strtotime($value['over_time']);
			if ($vip_end_time >= $time_tomorrow_start
				&& $vip_end_time <= $time_tomorrow_end) {
				//发送消息
				$this->send_user_vip_expire_msg($value);
				array_push($users, $value);
			}
		}
		$this->_suc_ret($users);
	}
}