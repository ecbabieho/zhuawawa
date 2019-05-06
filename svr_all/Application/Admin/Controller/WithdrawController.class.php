<?php
namespace Admin\Controller;
use Think\Controller;

class WithdrawController extends BaseController {
	/**
	 * [withdraw_list 提现列表]
	 * @return [type] [description]
	 */
    public function withdraw_list() {
		$this->show();
	}
	/**
	 * [ajax_get_withdraw_list 获取提现列表接口]
	 * @return [type] [description]
	 */
	public function ajax_get_withdraw_list() {
	    $withdrawModel = M("withdraw");
	    $map = array(
	        'deleted' => 0,
	    );
	    $status = I('status');
	    $name = I('name');
	    if ($name != '') {
	        $where['tel'] = array('like', "%$name%");
	        $where['user_id'] = $name;
	        $where['_logic'] = 'OR';
	        $map['_complex'] = $where;
	    }
	    if($status != ''){
	        $map['status'] = $status;
	    }
	    $page = I('page');
	    $limit = I('limit');
	    if ($page == ""
        || $limit == "") {
            exit;
        }
        $start_index = ((int) $page - 1) * ((int) $limit);
        $lottery_types = $withdrawModel
        ->limit($start_index, $limit)
        ->where($map)
        ->order('id desc')
        ->select();
        if ($lottery_types === false) {
            $this->_err_ret();
        }
        foreach($lottery_types as $key=>$val){
            $user_map = array(
                'deleted'=>0,
                'id'=>$val['user_id']
            );
            $user_info = M('user')->where($user_map)->find();
            $lottery_types[$key]['nickname'] = $user_info['nickname'];
            $lottery_types[$key]['headimgurl'] = $user_info['headimgurl'];
        }
        $count = $withdrawModel->where($map)->count();
        $this->_tb_suc_ret($lottery_types, $count);
	        

	}
	
	/**
	 * [ajax_edit_withdraw 修改提现状态]
	 * @return [type] [description]
	 */
	public function ajax_edit_withdraw() {
	    $id = I('id');
	    $type = I('type');
	    if ($id == '' || $type == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $data = array(
	        'id' => $id,
	        'status'=>$type
	    );
	    $res = M('withdraw')->save($data);
	    
	    if ($res) {
	        //$this->insertMerchantUserLog("删除分类ID：".$id);
	    }
	    $this->_suc_ret();
	}
}