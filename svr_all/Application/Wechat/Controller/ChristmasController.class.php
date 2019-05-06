<?php
namespace Wechat\Controller;
use Qcloud\Sms\SmsSingleSender;
use Think\Controller;

class ChristmasController extends BaseController {
	private $appid = '';
	private $mch_id = '';
	private $key = '';
	private $ip = '';
	
	
	/********************************圣诞活动******************************************/
	/**
	 * [christmas_game 圣诞活动]
	 * @return [type] [description]
	 */
	public function christmas_game() {
	    $user = session('user');
	    if (!$user) {
	        exit;
	    }
	    $map = array(
	        'id' => 1,
	    );
	    $lottery_activity = M('christmas_activity')->where($map)->find();
	    if (!$lottery_activity) {
	        exit;
	    }
	    if ($lottery_activity['is_open'] == 0 || time() > strtotime($lottery_activity['end_time']) || time() < strtotime($lottery_activity['start_time'])) {
	        if (time() > strtotime($lottery_activity['end_time']) || time() < strtotime($lottery_activity['start_time'])) {
	            $data = array(
	                'id' => 1,
	                'is_open' => 0,
	            );
	            M('christmas_activity')->save($data);
	        }
	        $this->assign('is_open', 0); //已经关闭
	    } else {
	        $this->assign('is_open', 1); //正常进行
	    }
	    $lottery_activity_goods_map = array(
	        'christmas_activity_id' => 1,
	        'deleted' => 0,
	    );
	    $lottery_activity_goods = M('christmas_activity_goods')->where($lottery_activity_goods_map)->select();
	    $return = array();
	    foreach ($lottery_activity_goods as $key => $val) {
	        $goods_map = array(
	            'id' => $val['goods_id'],
	        );
	        $goods_info = M('lottery_good')->where($goods_map)->find();
	        if ($goods_info) {
	            $return[] = $goods_info;
	        }
	    }
	    $zhua_num = mt_rand(20, 30);
	    $lottery_activity['zhua_num'] = $zhua_num;
	    $this->assign('goods_list', $return);
	    $this->assign('lottery_config', $lottery_activity);
	    $this->show();
	}
	public function ajax_get_christmas_activity() {
 	    $user = session('user');
	    $userModel = D('user');
	    $map = array(
	        'id' => $user['id'],
	    );
	    $user = $userModel->where($map)->find();
	    if (!$user) {
	        exit;
	    }
	    $christmas_activity_id = 1;
	    $map = array(
	        'id' => $christmas_activity_id,
	    );
	    $lottery_config = M('christmas_activity')->where($map)->find();
	    if (!$lottery_config) {
	        $this->_err_ret();
	    }
	    $lottery_config['user_coin_num'] = $user['coin_num'];
	    
	    //已抓取次数
	    $lottery_activity_log_map = array(
	        'deleted' => 0,
	        'user_id' => $user['id'],
	        'christmas_activity_id' => $christmas_activity_id,
	    );
	    $lottery_activity_log = M('christmas_activity_log')->where($lottery_activity_log_map)->select();
	    
	    $lottery_activity_goods_map = array(
	        'christmas_activity_id' => $christmas_activity_id,
	        'deleted' => 0,
	    );
	    $lottery_activity_goods = M('christmas_activity_goods')->where($lottery_activity_goods_map)->select();
	    $return = array();
	    foreach ($lottery_activity_goods as $key => $val) {
	        $goods_map = array(
	            'id' => $val['goods_id'],
	        );
	        $goods_info = M('lottery_good')->where($goods_map)->find();
	        $goods_info['probability'] = $val['probability'];
	        $return[] = $goods_info;
	    }
	    $lottery_config['good_num'] = count($lottery_activity_goods);
	    for ($i = 0; $i < $lottery_config['good_num']; $i++) {
	        $lottery_config['lottery_good' . $i] = $return[$i];
	    }
	    $lottery_config["lottery_type"] = array(
	        "id" => "4",
	        "add_time" => "2018-08-09 17:52:45",
	        "deleted" => "0",
	        "name" => "圣诞活动场",
	    );
	    $lottery_config['coin_num'] = 0;
	    $this->_suc_ret($lottery_config);
	}
	public function christmas_activity() {
	    $christmas_activity_id = 1;
	    $map = array(
	        'id' => $christmas_activity_id,
	    );
	    $lottery_config = M('christmas_activity')->where($map)->find();
	    if (!$lottery_config) {
	        exit;
	    }
	    if ($lottery_config['is_open'] == 0) {
	        exit;
	    }
	    if(time()>strtotime($lottery_config['end_time']) || time()<strtotime($lottery_config['start_time'])){
	        exit;
	    }
	    $user = session('user');
	    $map = array(
	        'id' => $user['id'],
	    );
	    $userModel = D('user');
	    $user = $userModel->where($map)->find();
	    if (!$user) {
	        exit;
	    }
	    //已抓取次数
	    $lottery_activity_log_map = array(
	        'deleted' => 0,
	        'user_id' => $user['id'],
	        'christmas_activity_id' => $christmas_activity_id,
	    );
	    $lottery_activity_log = M('christmas_activity_log')->where($lottery_activity_log_map)->select();
	    $fail_img_url = 'https://fssw.bichonfrise.cn/Public/weixin/image/fail.png';
	    $res_data = array(
	        'good_name' => '',
	        'good_url' => $fail_img_url,
	        'user_coin_num' => $user['coin_num'],
	        'msg' => "很遗憾，您这次没有抓到，请再接再厉！",
	        'coin_num'=>$lottery_config['coin_num']
	    );
	    
	    $is_hit = 0;
	    $hit_good_id = 0;
	    if ($user['forbidden'] == 1) {
	        $res_data['msg'] = "因恶意使用系统，您已被禁止抽奖！";
	        $this->_suc_ret($res_data);
	    }
	    if(count($lottery_activity_log) == 0){
	        $res_data['user_coin_num'] = $user['coin_num'];
	    }else{
	        if ($user['coin_num'] < $lottery_config['coin_num']) {
	            $res_data['msg'] = "糖豆不足，快去收集或充值糖豆吧~";
	            $this->_suc_ret($res_data);
	        }
	        // 扣除用户的糖豆
	        $data = array(
	            'id' => $user['id'],
	            'coin_num' => $user['coin_num'] - $lottery_config['coin_num'],
	        );
	        $res = $userModel->save($data);
	        if ($res === false) {
	            $res_data['msg'] = "扣除糖豆失败!";
	            $this->_suc_ret($res_data);
	        }
	        $res_data['user_coin_num'] = $user['coin_num'] - $lottery_config['coin_num'];
	    }
	    
	    $MAX_PERCENT_NUM = mt_getrandmax(); //2147483647
	    // 开始抽奖
	    //获取中奖商品
	    $map = array(
	        'christmas_activity_id' => 1,
	        'deleted' => 0,
	    );
	    $goods_list = M('christmas_activity_goods')->where($map)->select();
	    $is_hit = 0;
	    $hit_goods_id = 0;
	    $lottery_activity_goods_info = array();
	    //已抓取次数
	    $lottery_activity_log_map = array(
	        'deleted' => 0,
	        'user_id' => $user['id'],
	        'christmas_activity_id' => 1,
	        'is_hit' => 1,
	    );
	    $lottery_activity_log = M('christmas_activity_log')->where($lottery_activity_log_map)->select();
        for ($i = 0; $i < count($goods_list); $i++) {
            $luck_num = $goods_list[$i]['probability'] * 100;
            $temp = rand(1, 100);
            if ($temp > 0 && $temp < $luck_num && $goods_list[$i]['obtain_num'] > 0) {
                $is_hit = 1;
                $hit_goods_id = $goods_list[$i]['goods_id'];
                $goods_list[$i]['goods_info'] = M('lottery_good')->where(array('id' => $goods_list[$i]['goods_id']))->find();
                $lottery_activity_goods_info = $goods_list[$i];
                break;
            }
        }
        
	    $lottery_good_name = '';
	    if ($is_hit == 0) {
	        $res_data['good_name'] = "";
	        $res_data['good_url'] = $fail_img_url;
	        
	        $temp_data = array(
	            'add_time' => date('Y-m-d H:i:s'),
	            'deleted' => 0,
	            'user_id' => $user['id'],
	            'lottery_config_id' => 0,
	            'consume_num' => 1,
	            'is_hit' => $is_hit,
	            'hit_good_id' => 0,
	        );
	        $lottery_activity_log_data = array(
	            'add_time' => date('Y-m-d H:i:s'),
	            'deleted' => 0,
	            'user_id' => $user['id'],
	            'christmas_activity_id' => 1,
	            'is_hit' => $is_hit,
	            'hit_good_id' => $hit_goods_id,
	        );
	    } else {
	        $lotteryGoodModel = D('lottery_good');
	        $map = array(
	            'id' => $hit_goods_id,
	        );
	        $lottery_good = $lotteryGoodModel->where($map)->find();
	        $res_data['good_name'] = $lottery_good['name'];
	        $res_data['good_url'] = $lottery_good['img_url'];
	        // 添加中奖记录
	        $lotteryRecordModel = D('lottery_record');
	        $data = array(
	            'lottery_type_id' => $lottery_activity_goods_info['goods_info']['lottery_type_id'],
	            'lottery_config_id' => $lottery_activity_goods_info['lottery_config_id'],
	            'user_id' => $user['id'],
	            'lottery_good_id' => $hit_goods_id,
	            'add_time' => date('Y-m-d H:i:s', time(0)),
	            'type' => -7,
	        );
	        $res = $lotteryRecordModel->add($data);
	        $lottery_record_id = $res;
	        // 添加记录失败提示用户没抽中，以免引起投诉
	        if (!$res) {
	            $is_hit = 0;
	            $hit_goods_id = 0;
	            $res_data['good_name'] = "";
	            $res_data['good_url'] = $fail_img_url;
	            $temp_data = array(
	                'add_time' => date('Y-m-d H:i:s'),
	                'deleted' => 0,
	                'user_id' => $user['id'],
	                'lottery_config_id' => 0,
	                'consume_num' => 1,
	                'is_hit' => $is_hit,
	                'hit_good_id' => 0,
	            );
	            $lottery_activity_log_data = array(
	                'add_time' => date('Y-m-d H:i:s'),
	                'deleted' => 0,
	                'user_id' => $user['id'],
	                'christmas_activity_id' => 1,
	                'is_hit' => $is_hit,
	                'hit_good_id' => $hit_goods_id,
	            );
	        } else {
	            $lottery_good_name = $lottery_good['name'];
	            // 通知用户中奖了
	            $this->send_zhuawawa_inner_msg($user, $lottery_activity_goods_info['goods_info']['name'], $lottery_config['name']);
	            
	            $where = array(
	                'goods_id' => $hit_goods_id,
	            );
	            $data = array(
	                'obtain_num' => $lottery_activity_goods_info['obtain_num'] - 1,
	            );
	            M('christmas_activity_goods')->where($where)->save($data);
	            //更新用户中奖记录
	            $user_data = array(
	                'id' => $user['id'],
	                'record_total' => $user['record_total'] + 1,
	            );
	            $res = M('user')->save($user_data);
	            // 添加糖豆扣除记录
	            $temp_data = array(
	                'add_time' => date('Y-m-d H:i:s'),
	                'deleted' => 0,
	                'user_id' => $user['id'],
	                'lottery_config_id' => $lottery_activity_goods_info['lottery_config_id'],
	                'consume_num' => 1,
	                'is_hit' => $is_hit,
	                'hit_good_id' => $hit_goods_id,
	            );
	            
	            $lottery_activity_log_data = array(
	                'add_time' => date('Y-m-d H:i:s'),
	                'deleted' => 0,
	                'user_id' => $user['id'],
	                'christmas_activity_id' => 1,
	                'is_hit' => $is_hit,
	                'hit_good_id' => $hit_goods_id,
	            );
	        }
	    }
	    
	    $map_admin = array(
	        'openid' => 'oKFOO1hc7P90MGMU39zaIgYmJh0k',
	    );
	    $admin_user = $userModel->where($map_admin)->find();
	    $this->send_zhuawawa_inner_msg_admin($admin_user, $lottery_config, $is_hit, $user, $lottery_good_name);
	    
	    $res = M('luck_draw_log')->add($temp_data);
	    $res = M('christmas_activity_log')->add($lottery_activity_log_data);
	    $this->_suc_ret($res_data);
	}
}