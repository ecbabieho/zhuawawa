<?php
namespace Wechat\Controller;
use Think\Controller;

class CommunityController extends BaseController {
	/**
	 * [index 首页]
	 * @return [type] [description]
	 */
	public function index() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();
		$this->assign('user', $user);
		$map = array(
			'deleted' => 0,
			'type' => 2,
		);
		$banner_list = M('banner')->where($map)->order('id asc')->select();
		$this->assign('banner_list', $banner_list);
		$this->show();
	}
	public function gonglve() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();

		$map = array(
			'deleted' => 0,
			'type' => 2,
		);
		$banner_list = M('banner')->where($map)->order('id asc')->select();
		$this->assign('banner_list', $banner_list);
		$this->assign('user', $user);
		$this->show();
	}
	public function ajax_get_tiezi() {
		$type = I('type');
		$page_no = I('page_no');
		$page_num = I('page_num');
		if ($type == ""
			|| $page_no == ""
			|| $page_num == "") {
			exit;
		}
		// 圈子数据
		$tieziModel = D('tiezi');
		$map = array(
			'type' => $type,
		);
		$start = ((int) $page_no - 1) * (int) $page_num;
		$tiezis = $tieziModel->where($map)
			->relation(true)
			->limit($start, $page_num)
			->order('add_time desc')
			->select();
		foreach ($tiezis as $key => $value) {
			$tiezis[$key] = $tieziModel->dealTiezi($value);
		}
		$this->_suc_ret($tiezis);
	}
	/**
	 * [fabu 发布圈子]
	 * @return [type] [description]
	 */
	public function fabu() {
		$user = session('user');
		if (!$user) {
			exit;
		}
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();
		$this->assign('user', $user);
		$lottery_good_id = I('lottery_good_id');
		$lotteryGoodModel = D('lottery_good');
		$map = array(
				'id'=>$lottery_good_id,
			);
		$lottery_good = $lotteryGoodModel->where($map)->find();
		$this->assign('lottery_good_id',$lottery_good_id);
		$this->assign('lottery_good',$lottery_good);
		$lottery_config_id = I('lottery_config_id');
		$lotteryConfigModel = D('lottery_config');
		$map = array(
				'id'=>$lottery_config_id,
			);
		$lottery_config = $lotteryConfigModel->where($map)->find();
		$this->assign('lottery_config_id',$lottery_config_id);
		$this->assign('lottery_config',$lottery_config);
		
		$lottery_config_id = I('beg_gift_id');
		$map = array(
		    'id'=>$lottery_config_id,
		);
		$lottery_config = $lotteryConfigModel->where($map)->find();
		$this->assign('beg_gift_id',$lottery_config_id);
		$this->assign('beg_gift',$lottery_config);
		$this->show();
	}
	/**
	 * [suoyao 发布索要]
	 * @return [type] [description]
	 */
	public function suoyao() {
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $userModel = D('user');
	    $map = array(
	        'id' => $user['id'],
	    );
	    $user = $userModel->where($map)->find();
	    $this->assign('user', $user);
	    $this->show();
	}
	public function ajax_fabu_tiezi() {
	    $user = session('user');
	    $title = I('title');
	    $content = I('content');
	    $images = I('images');
	    $type = I('type');
	    $lottery_config_id = I('lottery_config_id');
	    $lottery_good_id = I('lottery_good_id');
	    $lottery_record_id = I('lottery_record_id');
	    if($lottery_config_id == ''){
	        $lottery_config_id = 0;
	    }
	    if($lottery_good_id == ''){
	        $lottery_good_id = 0;
	    }
	    if($lottery_record_id == ''){
	        $lottery_record_id = 0;
	    }
	    
	    $map = array(
	        'title' => $title,
	        'user_id' => $user['id'],
	        'type' => 0,
	    );
	    if ($type != '') {
	        $data['type'] = 1;
	    }
	    $tieziModel = D('tiezi');
	    $tiezi = $tieziModel->where($map)->find();
	    if ($tiezi) {
	        $this->_err_ret("您已发表过同样的标题的帖子了哦~");
	    }
	    $data = array(
	        'title' => $title,
	        'content' => $content,
	        'images' => $images,
	        'user_id' => $user['id'],
	        'add_time' => date('Y-m-d H:i:s', time()),
	        'lottery_config_id' => $lottery_config_id,
	        'lottery_good_id' => $lottery_good_id,
	        'lottery_record_id' => $lottery_record_id,
	    );
	    if ($type != '') {
	        $data['type'] = 1;
	    }
	    
	    $res = $tieziModel->add($data);
	    if (!$res) {
	        $this->_err_ret("发表失败，请稍后重试！");
	    }
	    
	    if($lottery_good_id !=0 && $lottery_record_id !=0){
	        if($lottery_record_id != 0){
	            $lottery_record_map = array(
	                'id'=>$lottery_record_id,
	                'deleted'=>0,
	            );
	            $temp = M('lottery_record')->where($lottery_record_map)->find();
	            if($temp && $temp['is_tiezi'] == 0){
	                $user_map = array(
	                    'id'=>$user['id'],
	                );
	                $user = M('user')->where($user_map)->find();
	                $data = array(
	                    'add_time' => date('Y-m-d H:i:s'),
	                    'deleted' => 0,
	                    'user_id' => $user['id'],
	                    'coin_config_id' => 0,
	                    'num' => C('TIEZI_COIN_NUM'),
	                    'before_balance' => $user['coin_num'],
	                    'after_balance' => $user['coin_num'] + C('TIEZI_COIN_NUM'),
	                    'status' => 1,
	                    'money' => 0,
	                    'type' => -2,
	                );
	                $res = M('user_coin_record')->add($data);
	                if($res){
	                    $data = array(
	                        'id'=>$user['id'],
	                        'coin_num' => $user['coin_num'] + C('TIEZI_COIN_NUM'),
	                    );
	                    $res = M('user')->save($data);
	                }
	                $temp_data = array(
	                    'is_tiezi'=>1
	                );
	                M('lottery_record')->where($lottery_record_map)->save($temp_data);
	            }
	        }
	    }
	    $this->_suc_ret();
	}
	/**
	 * ajax_share_coin 分享赠送糖豆
	 */
	public function ajax_share_coin(){
	    $lottery_record_id = I('lottery_record_id');
	    $lottery_record_map = array(
	        'id'=>$lottery_record_id,
	        'deleted'=>0,
	    );
	    $temp = M('lottery_record')->where($lottery_record_map)->find();
	    if($temp && $temp['is_pengyouquan'] == 0){
	        $user_map = array(
	            'id'=>$user['id'],
	        );
	        $user = M('user')->where($user_map)->find();
	        $data = array(
	            'add_time' => date('Y-m-d H:i:s'),
	            'deleted' => 0,
	            'user_id' => $user['id'],
	            'coin_config_id' => 0,
	            'num' => C('TIEZI_COIN_NUM_BY_SHARE'),
	            'before_balance' => $user['coin_num'],
	            'after_balance' => $user['coin_num'] + C('TIEZI_COIN_NUM_BY_SHARE'),
	            'status' => 1,
	            'money' => 0,
	            'type' => -2,
	        );
	        $res = M('user_coin_record')->add($data);
	        if($res){
	            $data = array(
	                'id'=>$user['id'],
	                'coin_num' => $user['coin_num'] + C('TIEZI_COIN_NUM_BY_SHARE'),
	            );
	            $res = M('user')->save($data);
	        }
	        $temp_data = array(
	            'is_pengyouquan'=>1
	        );
	        M('lottery_record')->where($lottery_record_map)->save($temp_data);
	    }
	    $this->_suc_ret();
	}
	/**
	 * [detail 帖子详情]
	 * @return [type] [description]
	 */
	public function detail() {
	    $id = I('id');
	    if ($id == "") {
	        exit;
	    }
	    $tieziModel = D('tiezi');
	    $map = array(
	        'id' => $id,
	    );
	    $tiezi = $tieziModel->where($map)
	    ->relation(true)
	    ->find();
	    $tiezi = $tieziModel->dealOneTiezi($tiezi);
	    $tiezi['user_info'] = M('user')->where(array('id' => $tiezi['user_id']))->find();
	    if($tiezi['lottery_config_id'] != 0){
	        $lottery_config_map = array(
	            'id'=>$tiezi['lottery_config_id']
	        );
	        $lottery_config_info = M('lottery_config')->where($lottery_config_map)->find();
	        $lottery_type_map = array(
	            'id'=>$lottery_config_info['lottery_type_id']
	        );
	        $lottery_type_info = M('lottery_type')->where($lottery_type_map)->find();
	        $lottery_config_info['lottery_type'] = $lottery_type_info;
	        $tiezi['lottery_config'] = $lottery_config_info;
	    }
	    if($tiezi['lottery_good_id'] != 0){
	        $lottery_good_map = array(
	            'id'=>$tiezi['lottery_good_id']
	        );
	        $lottery_good_info = M('lottery_good')->where($lottery_good_map)->find();
	        $tiezi['lottery_good'] = $lottery_good_info;
	    }
	    $this->assign('tiezi', $tiezi);
	    $view_num = $tiezi['view_num'] + 1;
	    $data = array(
	        'id' => $id,
	        'view_num' => $view_num,
	    );
	    $tieziModel->save($data);
	    //
	    $map = array(
	        'tiezi_id' => $id,
	        'deleted' => 0,
	    );
	    $tiezi_back = M('tiezi_back')->where($map)->order('add_time desc')->select();
	    foreach ($tiezi_back as $key => $val) {
	        $tiezi_back[$key]['add_time'] = update_add_time($val['add_time']);
	        $tiezi_back[$key]['user_info'] = M('user')->where(array('id' => $val['user_id']))->find();
	    }
	    $this->assign('tiezi_back', $tiezi_back);
	    $map = array(
	        'id' => $tiezi['user_id'],
	    );
	    $userModel = D('user');
	    $user = $userModel->where($map)->find();
	    $this->assign('user', $user);
	    $map = array(
	        'user_id' => $user['id'],
	        'tiezi_id' => $id,
	    );
	    $res = M('thumb_up')->where($map)->find();
	    if (!$res) {
	        $is_thumb_up = 0; //没点过
	    } else {
	        $is_thumb_up = 1; //点过
	    }
	    $this->assign('is_thumb_up', $is_thumb_up);
	    $this->show();
	}
	public function comment() {
		$id = I('id');
		if ($id == "") {
			exit;
		}
		$tieziModel = D('tiezi');
		$map = array(
			'id' => $id,
		);
		$tiezi = $tieziModel->where($map)
			->relation(true)
			->find();
		$this->assign('tiezi', $tiezi);
		$this->show();
	}
	/**
	 * [paihang 排行]
	 * @return [type] [description]
	 */
	public function paihang() {
		$user = session('user');
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();
		//充值糖豆排行榜
		$map = array(
			'deleted' => 0,
			'forbidden' => 0,
		);
		$money_list = M('user')->where($map)->limit(10)->order('charge_num desc')->select();
		foreach ($money_list as $key => $val) {
			$money_list[$key]['nickname'] = $this->esub($val['nickname'], 4);
		}
		$this->assign('money_list', $money_list);
		//当前用户排名
		$temp_money_list = M('user')->where($map)->order('charge_num desc')->select();
		foreach ($temp_money_list as $key => $val) {
			if ($val['id'] == $user['id']) {
				$money_paihang = $key;
			}
		}
		//抓娃娃排行榜
		$map = array(
			'deleted' => 0,
			'forbidden' => 0,
		);
		$record_list = M('user')->where($map)->limit(10)->order('record_total desc')->select();
		foreach ($record_list as $key => $val) {
			$record_list[$key]['nickname'] = $this->esub($val['nickname'], 4);
		}
		$this->assign('record_list', $record_list);
		//当前用户排名
		$temp_record_list = M('user')->where($map)->order('record_total desc')->select();
		foreach ($temp_record_list as $key => $val) {
			if ($val['id'] == $user['id']) {
				$record_paihang = $key;
			}
		}
		$user['money_paihang'] = $money_paihang + 1;
		$user['record_paihang'] = $record_paihang + 1;
		$this->assign('user', $user);
		$map = array(
			'deleted' => 0,
			'type' => 1,
		);
		$banner_list = M('banner')->where($map)->order('id asc')->select();
		$this->assign('banner_list', $banner_list);
		$this->show();
	}
	function esub($str, $length = 0, $ext = "****") {

		if ($length < 1) {
			return $str;
		}

		//计算字符串长度
		$strlen = (strlen($str) + mb_strlen($str, "UTF-8")) / 2;
		if ($strlen < $length) {
			return $str;
		}

		if (mb_check_encoding($str, "UTF-8")) {
			$str = mb_strcut(mb_convert_encoding($str, "GBK", "UTF-8"), 0, $length, "GBK");
			$str = mb_convert_encoding($str, "UTF-8", "GBK");

		} else {

			return "不支持的文档编码";
		}

		$str = rtrim($str, " ,.。，-——（【、；‘“??《<@");
		return $str . $ext;
	}
	/**
	 * [zhanji 战绩]
	 * @return [type] [description]
	 */
	public function zhanji() {
		$user = session('user');
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();
		$this->assign('user', $user);
		$luckDrawLogModel = M("luck_draw_log");
		$map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
		);
		$lottery_record = $luckDrawLogModel->where($map)->order('add_time desc')->select();
		$res = array();
		$time_temp = array(
			'01' => '一月',
			'02' => '二月',
			'03' => '三月',
			'04' => '四月',
			'05' => '五月',
			'06' => '六月',
			'07' => '七月',
			'08' => '八月',
			'09' => '九月',
			'10' => '十月',
			'11' => '十一月',
			'12' => '十二月',
		);
		foreach ($lottery_record as $key => $val) {
			if ($val['is_hit'] == 1) {
				$map = array(
					'id' => $val['lottery_config_id'],
				);
				$config_info = M('lottery_config')->where($map)->find();
				$map = array(
					'id' => $val['hit_good_id'],
				);
				$good_info = M('lottery_good')->where($map)->find();
				$temp = array(
					'date_m' => $time_temp[date('m', strtotime($val['add_time']))],
					'date_d' => date('d', strtotime($val['add_time'])),
					'config_name' => $config_info['name'],
					'good_name' => $good_info['name'],
					'good_image' => $good_info['img_url'],
					'lottery_config_id' => $val['lottery_config_id'],
					'lottery_type_id' => $config_info['lottery_type_id'],
					'level' => $config_info['level'],
					'is_hit' => 1,
				);
				$res[] = $temp;
			}
			if ($val['is_hit'] == 0) {
				$map = array(
					'id' => $val['lottery_config_id'],
				);
				$config_info = M('lottery_config')->where($map)->find();
				$map = array(
					'id' => $config_info['lottery_good_id0'],
				);
				$good_info = M('lottery_good')->where($map)->find();
				$temp = array(
					'date_m' => $time_temp[date('m', strtotime($val['add_time']))],
					'date_d' => date('d', strtotime($val['add_time'])),
					'config_name' => $config_info['name'],
					'good_name' => $good_info['name'],
					'good_image' => $good_info['img_url'],
					'lottery_config_id' => $val['lottery_config_id'],
					'lottery_type_id' => $config_info['lottery_type_id'],
					'level' => $config_info['level'],
					'is_hit' => 0,
				);
				$res[] = $temp;
			}
		}
		$this->assign('lottery_record', $res);
		$this->show();
	}
	/**
	 * [zhanji 战绩]
	 * @return [type] [description]
	 */
	public function user_zhanji() {
		$user = session('user');
		$userModel = D('user');
		$id = I('id');
		if ($id == '') {
			exit();
		}
		$map = array(
			'id' => $id,
		);
		$user = $userModel->where($map)->find();
		$this->assign('user', $user);
		$luckDrawLogModel = M("luck_draw_log");
		$map = array(
			'deleted' => 0,
			'user_id' => $user['id'],
		);
		$lottery_record = $luckDrawLogModel->where($map)->order('add_time desc')->select();
		$res = array();
		$time_temp = array(
			'01' => '一月',
			'02' => '二月',
			'03' => '三月',
			'04' => '四月',
			'05' => '五月',
			'06' => '六月',
			'07' => '七月',
			'08' => '八月',
			'09' => '九月',
			'10' => '十月',
			'11' => '十一月',
			'12' => '十二月',
		);
		foreach ($lottery_record as $key => $val) {
			if ($val['is_hit'] == 1) {
				$map = array(
					'id' => $val['lottery_config_id'],
				);
				$config_info = M('lottery_config')->where($map)->find();
				$map = array(
					'id' => $val['hit_good_id'],
				);
				$good_info = M('lottery_good')->where($map)->find();
				$temp = array(
					'date_m' => $time_temp[date('m', strtotime($val['add_time']))],
					'date_d' => date('d', strtotime($val['add_time'])),
					'config_name' => $config_info['name'],
					'good_name' => $good_info['name'],
					'good_image' => $good_info['img_url'],
					'lottery_config_id' => $val['lottery_config_id'],
					'lottery_type_id' => $config_info['lottery_type_id'],
					'level' => $config_info['level'],
					'is_hit' => 1,
				);
				$res[] = $temp;
			}
			if ($val['is_hit'] == 0) {
				$map = array(
					'id' => $val['lottery_config_id'],
				);
				$config_info = M('lottery_config')->where($map)->find();
				$map = array(
					'id' => $config_info['lottery_good_id0'],
				);
				$good_info = M('lottery_good')->where($map)->find();
				$temp = array(
					'date_m' => $time_temp[date('m', strtotime($val['add_time']))],
					'date_d' => date('d', strtotime($val['add_time'])),
					'config_name' => $config_info['name'],
					'good_name' => $good_info['name'],
					'good_image' => $good_info['img_url'],
					'lottery_config_id' => $val['lottery_config_id'],
					'lottery_type_id' => $config_info['lottery_type_id'],
					'level' => $config_info['level'],
					'is_hit' => 0,
				);
				$res[] = $temp;
			}
		}
		$this->assign('lottery_record', $res);
		$this->show();
	}
	/**
	 * [wo 我]
	 * @return [type] [description]
	 */
	public function wo() {
		$user = session('user');
		$userModel = D('user');
		$map = array(
			'id' => $user['id'],
		);
		$user = $userModel->where($map)->find();
		$this->assign('user', $user);
		$this->show();
	}
	/**
	 * ajax_tiezi_back  回复帖子
	 */
	public function ajax_tiezi_back() {
		$user = session('user');
		$tiezi_id = I('tiezi_id');
		$content = I('content');
		if ($tiezi_id == '' || $content == '') {
			$this->_err_ret('参数不完整');
		}

		$map = array(
			'id' => $tiezi_id,
		);
		$info = M('tiezi')->where($map)->find();
		if (!$info) {
			$this->_err_ret('帖子不存在');
		}
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'tiezi_id' => $tiezi_id,
			'user_id' => $user['id'],
			'content' => $content,
		);
		$res = M('tiezi_back')->add($data);
		if (!$res) {
			$this->_err_ret('回复失败');
		}
		$data = array(
			'id' => $tiezi_id,
			'comment_num' => $info['comment_num'] + 1,
		);
		$res = M('tiezi')->save($data);
		$this->_suc_ret();
	}
	/**
	 * ajax_thumb_up  点赞帖子
	 */
	public function ajax_thumb_up() {
		$user = session('user');
		$tiezi_id = I('tiezi_id');
		if ($tiezi_id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $tiezi_id,
		);
		$info = M('tiezi')->where($map)->find();
		if (!$info) {
			$this->_err_ret('帖子不存在');
		}
		$map = array(
			'user_id' => $user['id'],
			'tiezi_id' => $tiezi_id,
		);
		$temp = M('thumb_up')->where($map)->find();
		if (!$temp) {
			$data = array(
				'id' => $tiezi_id,
				'thumb_up_num' => $info['thumb_up_num'] + 1,
			);
			$res = M('tiezi')->save($data);
			$data = array(
				'add_time' => date('Y-m-d H:i:s'),
				'deleted' => 0,
				'tiezi_id' => $tiezi_id,
				'user_id' => $user['id'],
			);
			$res = M('thumb_up')->add($data);
			if (!$res) {
				$this->_err_ret('点赞失败');
			}
			$this->_suc_ret(array('type' => 1, 'thumb_up_num' => $info['thumb_up_num'] + 1));
		} else {
			$data = array(
				'id' => $tiezi_id,
				'thumb_up_num' => $info['thumb_up_num'] - 1,
			);
			$res = M('tiezi')->save($data);
			$data = array(
				'id' => $temp['id'],
			);
			$res = M('thumb_up')->where($data)->delete();
			if (!$res) {
				$this->_err_ret('取消失败');
			}
			$this->_suc_ret(array('type' => 0, 'thumb_up_num' => $info['thumb_up_num'] - 1));
		}

	}

	/**
	 * my_log  我的记录
	 */
	public function my_log() {
		$user = session('user');
		$type = I('type');
		if ($type == '') {
			exit();
		}
		$tieziModel = D('tiezi');
		if ($type == 1) {
//我的帖子
			// 圈子数据
			$map = array(
				'deleted' => 0,
				'user_id' => $user['id'],
			);
			$tiezis = $tieziModel->where($map)
				->relation(true)
				->order('add_time desc')
				->select();
			foreach ($tiezis as $key => $value) {
				$tiezis_list[] = $tieziModel->dealTiezi($value);
			}
			$title = '我的帖子';
		}
		if ($type == 2) {
//我的回复
			$map = array(
				'user_id' => $user['id'],
				'deleted' => 0,
			);
			$temp_list = M('tiezi_back')->where($map)->select();
			$tiezi_id_array = array();
			foreach ($temp_list as $key => $val) {
				if (!in_array($val['tiezi_id'], $tiezi_id_array)) {
					$tiezi_id_array[] = $val['tiezi_id'];
				}
			}
			$tiezis_list = array();
			foreach ($tiezi_id_array as $key => $val) {
				$map = array(
					'id' => $val,
				);
				$tiezis = $tieziModel->where($map)
					->relation(true)
					->order('add_time desc')
					->select();
				$tiezis_list[] = $tieziModel->dealTiezi($tiezis[0]);
			}
			$title = '我的回复';
		}
		if ($type == 3) {
//我的点赞
			$map = array(
				'user_id' => $user['id'],
				'deleted' => 0,
			);
			$temp_list = M('thumb_up')->where($map)->select();
			$tiezi_id_array = array();
			foreach ($temp_list as $key => $val) {
				if (!in_array($val['tiezi_id'], $tiezi_id_array)) {
					$tiezi_id_array[] = $val['tiezi_id'];
				}
			}
			$tiezis_list = array();
			foreach ($tiezi_id_array as $key => $val) {
				$map = array(
					'id' => $val,
				);
				$tiezis = $tieziModel->where($map)
					->relation(true)
					->order('add_time desc')
					->select();
				$tiezis_list[] = $tieziModel->dealTiezi($tiezis[0]);
			}
			$title = '我的点赞';
		}
		$this->assign('tiezis_list', $tiezis_list);
		$this->assign('title', $title);
		$this->show();
	}
}