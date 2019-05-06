<?php
namespace Admin\Controller;
use Think\Controller;

class IndexController extends BaseController {
	/**
	 * [index 数据报表]
	 * @return [type] [description]
	 */
	public function index() {
		//M('lottery_record')->where(array('add_time'=>array('elt','2018-09-06 23:59:59')))->save(array('status'=>1));
		//M('lottery_record')->where(array('add_time'=>array('egt','2018-09-06 23:59:59')))->save(array('status'=>0));
		$days = I('days');
		$time_map = array();
		if ($days != "") {
			$time_now = time(0);
			$time_start = $time_now - (int) $days * (24 * 3600);
			$time_start = date('Y-m-d H:i:s', $time_start);
			$time_map = array(
				array('egt', $time_start),
				array('elt', date('Y-m-d H:i:s')),
				'and',
			);
		}
		//总充值金额 total_charge_amount
		$total_charge_amount_map = array(
			'deleted' => 0,
			'type' => array(0,-8),
			'status' => 1,
		);
		if ($time_map != array()) {
			$total_charge_amount_map['add_time'] = $time_map;
		}
		$total_charge_amount = M('user_coin_record')->where($total_charge_amount_map)->sum('money');
		if (!$total_charge_amount) {
			$total_charge_amount = 0;
		}
		$this->assign('total_charge_amount', $total_charge_amount);
		//总糖豆数量 total_coin_amount
		$total_coin_amount_map = array(
			'deleted' => 0,
			'is_test' => 0,
		);
		if ($time_map != array()) {
			$total_coin_amount_map['add_time'] = $time_map;
		}
		$total_coin_amount = M('user')->where($total_coin_amount_map)->sum('coin_num');
		if (!$total_coin_amount) {
			$total_coin_amount = 0;
		}
		$this->assign('total_coin_amount', $total_coin_amount);
		//总抓娃娃次数 total_lottery_amount
		$total_lottery_amount_map = array(
			'deleted' => 0,
		);
		if ($time_map != array()) {
			$total_lottery_amount_map['add_time'] = $time_map;
		}
		$total_lottery_amount = M('luck_draw_log')->where($total_lottery_amount_map)->count();
		if (!$total_lottery_amount) {
			$total_lottery_amount = 0;
		}
		$this->assign('total_lottery_amount', $total_lottery_amount);
		//总用户个数 total_user_amount
		$total_user_amount_map = array(
			'deleted' => 0,
		);
		if ($time_map != array()) {
			$total_user_amount_map['add_time'] = $time_map;
		}
		$total_user_amount = M('user')->where($total_user_amount_map)->count();
		if (!$total_user_amount) {
			$total_user_amount = 0;
		}
		$this->assign('total_user_amount', $total_user_amount);
		//总会员个数 total_vip_amount
		$total_vip_amount_map = array(
			'deleted' => 0,
			'level' => array('gt', 1),
		);

		if ($time_map != array()) {
			$total_vip_amount_map['add_time'] = $time_map;
		}
		$total_vip_amount = M('user')->where($total_vip_amount_map)->count();
		$this->assign('total_vip_amount', $total_vip_amount);

		//总商家个数 total_vip_amount
		$total_shop_amount_map = array(
			'deleted' => 0,
		);

		if ($time_map != array()) {
			$total_shop_amount_map['add_time'] = $time_map;
		}
		$total_shop_amount = M('merchant')->where($total_shop_amount_map)->count();
		$this->assign('total_shop_amount', $total_shop_amount);

		//总赠送出糖豆数量 total_send_coin_amount
		$total_send_coin_amount_map = array(
			'user_coin_record.deleted' => 0,
			'user_coin_record.type' => -1,
			'user.is_test' => 0,
		);
		if ($time_map != array()) {
			$total_send_coin_amount_map['user_coin_record.add_time'] = $time_map;
		}
		$total_send_coin_amount = M('user_coin_record')->join('user ON user_coin_record.user_id = user.id')->where($total_send_coin_amount_map)->sum('user_coin_record.num');
		if (!$total_send_coin_amount) {
			$total_send_coin_amount = 0;
		}
		$this->assign('total_send_coin_amount', $total_send_coin_amount);
		// 累计到店次数
		$total_shop_arrive_amount_map = array(
			'deleted' => 0,
		);
		if ($time_map != array()) {
			$total_shop_arrive_amount_map['add_time'] = $time_map;
		}
		$total_shop_arrive_amount = M('merchant')->where($total_shop_arrive_amount_map)->sum('arrive_num');
		if (!$total_shop_arrive_amount) {
			$total_shop_arrive_amount = 0;
		}
		$this->assign('total_shop_arrive_amount', $total_shop_arrive_amount);
		// 累计到店赠送糖豆数量
		$total_shop_arrive_candy_amount_map = array(
			'deleted' => 0,
			'type' => -7,
		);
		if ($time_map != array()) {
			$total_shop_arrive_candy_amount_map['add_time'] = $time_map;
		}
		$total_shop_arrive_candy_amount = M('user_coin_record')->where($total_shop_arrive_candy_amount_map)->sum('num');
		$this->assign('total_shop_arrive_candy_amount', $total_shop_arrive_candy_amount);
		//总中奖数量 total_lottery_hit_amount
		$total_lottery_hit_amount_map = array(
			'deleted' => 0,
		);
		if ($time_map != array()) {
			$total_lottery_hit_amount_map['add_time'] = $time_map;
		}
		$total_lottery_hit_amount = M('lottery_record')->where($total_lottery_hit_amount_map)->count();
		if (!$total_lottery_hit_amount) {
			$total_lottery_hit_amount = 0;
		}
		$this->assign('total_lottery_hit_amount', $total_lottery_hit_amount);
		//累计取消关注用户数量 total_unsubscribe_user_amount
		$total_unsubscribe_user_amount_map = array(
			'deleted' => 0,
			'is_subscribe' => 0,
		);
		if ($time_map != array()) {
			$total_unsubscribe_user_amount_map['add_time'] = $time_map;
		}
		$total_unsubscribe_user_amount = M('user')->where($total_unsubscribe_user_amount_map)->count();
		if (!$total_unsubscribe_user_amount) {
			$total_unsubscribe_user_amount = 0;
		}
		$this->assign('total_unsubscribe_user_amount', $total_unsubscribe_user_amount);
		//累计关注用户数量 total_subscribe_user_amount
		$total_subscribe_user_amount_map = array(
			'deleted' => 0,
			'is_subscribe' => 1,
		);
		if ($time_map != array()) {
			$total_subscribe_user_amount_map['add_time'] = $time_map;
		}
		$total_subscribe_user_amount = M('user')->where($total_subscribe_user_amount_map)->count();
		if (!$total_subscribe_user_amount) {
			$total_subscribe_user_amount = 0;
		}
		$this->assign('total_subscribe_user_amount', $total_subscribe_user_amount);

		//送货地址
		$lotteryRecordModel = D("lottery_record");
		$map = array(
			'deleted' => 0,
			'status' => 0,
		);

		if ($time_map != array()) {
			$map['add_time'] = $time_map;
		}
		$lottery_record = $lotteryRecordModel
			->where($map)
			->select();
		$list = array();
		foreach ($lottery_record as $key => $val) {
			if ($val['address']) {
				$list[] = $val;
			}
		}
		$this->assign('list', $list);
		$lotteryConfigModel = D('lottery_config');
		$map = array(
			'coin_num' => array('gt', 0),
			'deleted' => 0,
		);
		$lottery_configs = $lotteryConfigModel->where($map)
			->order("zhua_times desc")
			->limit(10)
			->select();
		$this->assign('lottery_configs', $lottery_configs);

		$merchantModel = D('merchant');
		$map = array(
			'deleted' => 0,
		);
		$welcome_merchants = $merchantModel->where($map)
			->order("arrive_num desc")
			->limit(10)
			->select();
		$this->assign('welcome_merchants', $welcome_merchants);

		$userModel = D('user');
		$map = array(
			'deleted' => 0,
			'level' => array('gt', 1),
		);
		$diligent_users = $userModel->where($map)
			->order("arrive_num desc")
			->limit(10)
			->select();
		foreach ($diligent_users as $key => $value) {
			$map_candy = array(
				'user_id' => $value['id'],
				'type' => -7,
			);
			$candy_num = M('user_coin_record')->where($map_candy)->sum("num");
			if (!$candy_num) {
				$candy_num = 0;
			}
			$diligent_users[$key]['candy_num'] = $candy_num;
		}
		$this->assign('diligent_users', $diligent_users);
		$this->show();
	}
	/**
	 * [shop_distribution 商家分布]
	 * @return [type] [description]
	 */
	public function shop_distribution() {
		$category_id = I('id');
		$this->assign('id', $category_id);
		$map = array(
			'deleted' => 0,
			'is_fabu' => 1,
		);
		if ($category_id != '') {
			$map = array(
				'deleted' => 0,
				'is_fabu' => 1,
				'category_id' => $category_id,
			);
		}
		$list = M('merchant')->where($map)->select();
		foreach ($list as $key => $val) {
			$map = array(
				'deleted' => 0,
				'id' => $val['category_id'],
			);
			$category = M('merchant_category')->where($map)->find();
			$list[$key]['category_name'] = $category['name'];
		}
		$this->assign('list', $list);

		$map = array(
			'deleted' => 0,
		);
		$category = M('merchant_category')->where($map)->select();
		$this->assign('category', $category);
		$this->show();
	}
	/**
	 * [data_detail 明细查询]
	 * @return [type] [description]
	 */
	public function data_detail() {
		$this->show();
	}

	/**
	 * [banner banner设置]
	 * @return [type] [description]
	 */
	public function banner() {
		$admin = session('admin');
		if (!$admin) {
			exit;
		}
		$bannerModel = D('banner');
		$map = array(
			'deleted' => 0,
			'merchant_id' => $admin['merchant_id'],
		);
		$banners = $bannerModel->where($map)->order('sort desc')->select();
		$this->assign('banners', $banners);
		$this->insertMerchantUserLog("查看轮播图");
		$this->show();
	}
	/**
	 * [ajax_delete_banner 删除轮播图]
	 * @return [type] [description]
	 */
	public function ajax_delete_banner() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
		$bannerModel = D('banner');
		$map = array(
			'id' => $id,
		);
		$banner = $bannerModel->where($map)->find();
		if (!$banner) {
			exit;
		}
		$data = array(
			'deleted' => 1,
			'id' => $banner['id'],
		);
		$res = $bannerModel->save($data);
		if (!$res) {
			$this->_err_ret();
		}
		$this->insertMerchantUserLog("删除轮播图：" . json_encode($banner));
		$this->_suc_ret();

	}
	/**
	 * [ajax_update_banners 更新banner数据]
	 * @return [type] [description]
	 */
	public function ajax_update_banners() {
		$admin = session('admin');
		if (!$admin) {
			exit;
		}
		$banners = I('banners');
		if ($banners == "") {
			exit;
		}
		$banners = json_decode(urldecode($banners), true);
		$bannerModel = D('banner');
		foreach ($banners as $key => $value) {
			$data = array(
				'title' => $value['title'],
				'image' => $value['image'],
				'link' => $value['link'],
				'sort' => $value['sort'],
			);
			if ($value['id'] != 0) {
				$data['id'] = $value['id'];
				$res = $bannerModel->save($data);
			} else {
				$data['add_time'] = date('Y-m-d H:i:s', time());
				$data['merchant_id'] = $admin['merchant_id'];
				$res = $bannerModel->add($data);
			}
			if ($res === false) {
				$this->_err_ret();
			}
		}
		$this->insertMerchantUserLog("更新轮播图为：" . json_encode($banners));
		$this->_suc_ret();
	}
	/**
	 * [postal_card 包邮卡管理]
	 * @return [type] [description]
	 */
	public function postal_card() {
		$this->show();

	}
	/**
	 * [ajax_get_postal_card 获取包邮卡]
	 * @return [type] [description]
	 */
	public function ajax_get_postal_card() {
		$postalCardModel = M("postal_card");
		$map = array(
			'deleted' => 0,
		);
		$postal_card = $postalCardModel->order('money asc')->where($map)->select();
		if ($postal_card === false) {
			$this->_err_ret();
		}
		$this->_tb_suc_ret($postal_card);
	}
	/**
	 * [ajax_add_postal_card 添加包邮卡]
	 * @return [type] [description]
	 */
	public function ajax_add_postal_card() {
		$merchant = session('merchant');
		$name = I('card_name');
		$money = I('money');
		$days = I('days');
		if ($name == '' || $money == '' || $days == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'card_name' => $name,
			'money' => $money,
			'days' => $days,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('postal_card')->add($data);
		if ($res) {
			//$this->insertMerchantUserLog("添加分类ID：".$res);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_edit_postal_card 编辑包邮卡]
	 * @return [type] [description]
	 */
	public function ajax_edit_postal_card() {
		$merchant = session('merchant');
		$id = I('id');
		$name = I('card_name');
		$money = I('money');
		$days = I('days');
		if ($name == '' || $id == '' || $money == '' || $days == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
			'card_name' => $name,
			'money' => $money,
			'days' => $days,
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
		);
		$res = M('postal_card')->save($data);
		if ($res) {
			//$this->insertMerchantUserLog("修改分类ID：".$id);
		}
		$this->_suc_ret();
	}
	/**
	 * [ajax_delete_postal_card 删除包邮卡]
	 * @return [type] [description]
	 */
	public function ajax_delete_postal_card() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
		);
		$res = M('postal_card')->where($data)->delete();
		if ($res) {
			//$this->insertMerchantUserLog("删除分类ID：".$id);
		}
		$this->_suc_ret();
	}

	public function menu() {

		if (IS_POST) {
			$post_menu = $_POST['menu'];
			//查询数据库是否存在
			$menu_list = M('wx_menu')->getField('id', true);
			foreach ($post_menu as $k => $v) {
				if (in_array($k, $menu_list)) {
					//更新
					M('wx_menu')->where(array('id' => $k))->save($v);
				} else {
					//插入
					M('wx_menu')->where(array('id' => $k))->add($v);
				}
			}
			$this->success('操作成功,进入发布步骤', U('Admin/Index/pub_menu'));
			exit;
		}
		$max_id = M('wx_menu')->order('id desc')->find()['id'];
		//获取父级菜单
		$p_menus = M('wx_menu')->where(array('pid' => 0))->order('sort ASC')->select();
		$p_menus = $this->convert_arr_key($p_menus, 'id');
		//获取二级菜单
		$c_menus = M('wx_menu')->where(array('pid' => array('gt', 0)))->order('sort ASC')->select();
		$c_menus = $this->convert_arr_key($c_menus, 'id');
		$this->assign('p_lists', $p_menus);
		$this->assign('c_lists', $c_menus);
		$this->assign('max_id', $max_id ? $max_id : 0);
		$this->show();
	}

	/**
	 * @param $arr
	 * @param $key_name
	 * @return array
	 * 将数据库中查出的列表以指定的 id 作为数组的键名
	 */
	function convert_arr_key($arr, $key_name) {
		$arr2 = array();
		foreach ($arr as $key => $val) {
			$arr2[$val[$key_name]] = $val;
		}
		return $arr2;
	}
	/*
		 * 删除菜单
	*/
	public function del_menu() {
		$id = I('get.id');
		if (!$id) {
			exit('fail');
		}
		$row = M('wx_menu')->where(array('id' => $id))->delete();
		$row && M('wx_menu')->where(array('pid' => $id))->delete(); //删除子类
		if ($row) {
			exit('success');
		} else {
			exit('fail');
		}
	}

	/*
		 * 生成微信菜单
	*/
	public function pub_menu() {
		$menu = array();
		$menu['button'][] = array(
			'name' => '测试',
			'type' => 'view',
			'url' => 'http://wwwtp-shhop.cn',
		);
		$menu['button'][] = array(
			'name' => '测试',
			'sub_button' => array(
				array(
					"type" => "scancode_waitmsg",
					"name" => "系统拍照发图",
					"key" => "rselfmenu_1_0",
					"sub_button" => array(),
				),
			),
		);

		//获取菜单
		$appId = "";
		$appSecret = "";
		//获取父级菜单
		$p_menus = M('wx_menu')->where(array('pid' => 0))->order('sort ASC')->select();
		$p_menus = $this->convert_arr_key($p_menus, 'id');

		$post_str = $this->convert_menu($p_menus);
		// http post请求
		if (!count($p_menus) > 0) {
			$this->error('没有菜单可发布', U('Wechat/menu'));
			exit;
		}
		$access_token = $this->get_access_token($appId, $appSecret);
		if (!$access_token) {
			$this->error('获取access_token失败', U('Index/menu')); //  http://www.tpshop.com/index.php/Admin/Wechat/menu

			exit;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
		//        exit($post_str);
		$return = httpRequest($url, 'POST', $post_str);
		$return = json_decode($return, 1);
		if ($return['errcode'] == 0) {
			$this->success('菜单已成功生成', U('Index/menu'));
		} else {
			echo "错误代码;" . $return['errcode'];
			exit;
		}
	}

	public function get_access_token($appId, $appSecret) {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
		$res = json_decode($this->httpGet($url));
		$access_token = $res->access_token;
		return $access_token;
	}

	public function httpGet($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);
		$res = curl_exec($curl);
		curl_close($curl);

		return $res;
	}
	//菜单转换
	private function convert_menu($p_menus) {
		$key_map = array(
			'scancode_waitmsg' => 'rselfmenu_0_0',
			'scancode_push' => 'rselfmenu_0_1',
			'pic_sysphoto' => 'rselfmenu_1_0',
			'pic_photo_or_album' => 'rselfmenu_1_1',
			'pic_weixin' => 'rselfmenu_1_2',
			'location_select' => 'rselfmenu_2_0',
		);
		$new_arr = array();
		$count = 0;
		$appId = "";
		foreach ($p_menus as $k => $v) {
			$new_arr[$count]['name'] = $v['name'];

			//获取子菜单
			$c_menus = M('wx_menu')->where(array('pid' => $k))->select();

			if ($c_menus) {
				foreach ($c_menus as $kk => $vv) {
					$add = array();
					$add['name'] = $vv['name'];
					$add['type'] = $vv['type'];
					// click类型
					if ($add['type'] == 'click') {
						$add['key'] = $vv['value'];
					} elseif ($add['type'] == 'view') {
						$add['url'] = $vv['value'];
					} elseif ($add['type'] == 'miniprogram') {
						$add['url'] = $vv['value'];
						$add['appid'] = $appId;
						$add['pagepath'] = '';
					} else {
						//$add['key'] = $key_map[$add['type']];
						$add['key'] = $vv['value']; //2016年9月29日01:28:37  QQ  海南大卫照明  367013672  提供
					}
					$add['sub_button'] = array();
					if ($add['name']) {
						$new_arr[$count]['sub_button'][] = $add;
					}
				}
			} else {
				$new_arr[$count]['type'] = $v['type'];
				// click类型
				if ($new_arr[$count]['type'] == 'click') {
					$new_arr[$count]['key'] = $v['value'];
				} elseif ($new_arr[$count]['type'] == 'view') {
					//跳转URL类型
					$new_arr[$count]['url'] = $v['value'];
				} else {
					//其他事件类型
					//$new_arr[$count]['key'] = $key_map[$v['type']];
					$new_arr[$count]['key'] = $v['value']; //2016年9月29日01:40:13
				}
			}
			$count++;
		}
		// return json_encode(array('button'=>$new_arr));
		return json_encode(array('button' => $new_arr), JSON_UNESCAPED_UNICODE);
	}
	public function update_user() {
		$map = array(
			'deleted' => 0,
		);
		$lottery_record_list = M('lottery_record')->where($map)->select();
		$user_list = array();
		foreach ($lottery_record_list as $key => $val) {
			if (!in_array($val['user_id'], $user_list)) {
				$user_list[] = $val['user_id'];
			}
		}
		foreach ($user_list as $key => $val) {
			$map = array(
				'user_id' => $val,
				'deleted' => 0,
			);
			$count = M('lottery_record')->where($map)->count();
			if (!$count) {
				$count = 0;
			}
			$data = array(
				'id' => $val,
				'record_total' => $count,
			);
			$res = M('user')->save($data);
		}
	}

	public function update_tiezi_comment_num() {
		$tiezi_list = M('tiezi')->select();
		foreach ($tiezi_list as $key => $val) {
			$map = array(
				'deleted' => 0,
				'tiezi_id' => $val['id'],
			);
			$comment_num = M('tiezi_back')->where($map)->count();
			if (!$comment_num) {
				$comment_num = 0;
			}
			$data = array(
				'id' => $val['id'],
				'comment_num' => $comment_num,
			);
			$res = M('tiezi')->save($data);
		}
	}
}
