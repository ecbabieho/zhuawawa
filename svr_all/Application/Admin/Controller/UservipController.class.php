<?php
namespace Admin\Controller;
use Think\Controller;

class UservipController extends BaseController {
	/**
	 * [grade_list 等级列表]
	 * @return [type] [description]
	 */
    public function grade_list() {
		$this->show();
	}
	/**
	 * [ajax_get_grade 获取等级列表接口]
	 * @return [type] [description]
	 */
	public function ajax_get_grade() {
	    $vipGradeModel = M("vip_grade");
		$map = array(
			'deleted' => 0,
		);
		//加入条件
		$list = $vipGradeModel->where($map)->order('level desc')->select();
		if ($list === false) {
			$this->_err_ret();
		}
		$this->_tb_suc_ret($list);

	}
	/**
	 * [ajax_add_grade 添加会员等级]
	 * @return [type] [description]
	 */
	public function ajax_add_grade() {
	    $merchant = session('merchant');
	    $name = I('name');
	    if ($name == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $count = M('vip_grade')->where(array('deleted'=>0))->count();
	    if(!$count){
	        $count = 0;
	    }
	    $data = array(
	        'name' => $name,
	        'level' => $count + 1,
	        'add_time' => date('Y-m-d H:i:s'),
	        'deleted' => 0,
	    );
	    $res = M('vip_grade')->add($data);
	    $data = array(
	        'grade_id' => $res,
	        'day_30' => 0,
	        'day_60' => 0,
	        'day_90' => 0,
	        'day_180' => 0,
	        'day_360' => 0,
	        'day_720' => 0,
	        'add_time' => date('Y-m-d H:i:s'),
	        'deleted' => 0,
	    );
	    $res = M('vip_pay_config')->add($data);
	    if ($res) {
	        //$this->insertMerchantUserLog("添加分类ID：".$res);
	    }
	    $this->_suc_ret();
	}
	/**
	 * [ajax_edit_grade 编辑会员等级]
	 * @return [type] [description]
	 */
	public function ajax_edit_grade() {
	    $merchant = session('merchant');
	    $id = I('id');
	    $name = I('name');
	    $level = I('level');
	    if ($name == '' || $id == '' || $level == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $info = M('vip_grade')->where(array('id'=>$id))->find();
	    //查找等级存不存在
	    $map = array(
	        'deleted'=>0,
	        'level'=>$level
	    );
	    $temp = M('vip_grade')->where($map)->find();
	    if(!$temp){
	        $data = array(
    	        'id' => $id,
	            'name' => $name,
	            'level'=>$level
    	    );
	       $res = M('vip_grade')->save($data);
	    }else{
	        if($info['level'] == $temp['level']){
	            $data = array(
	                'id' => $id,
	                'name' => $name,
	            );
	            $res = M('vip_grade')->save($data);
	        }else{
	            $map = array(
	                'deleted'=>0,
	                'level'=>array('EGT',$info['level'])
	            );
	            $temp_list = M('vip_grade')->where($map)->select();
	            foreach($temp_list as $key=>$val){
	                $data = array(
	                    'id' => $val['id'],
	                    'level' => $val['level']-1,
	                );
	                $res = M('vip_grade')->save($data);
	            }
	            $map = array(
	                'deleted'=>0,
	                'level'=>array('EGT',$level)
	            );
	            $temp_list = M('vip_grade')->where($map)->select();
	            foreach($temp_list as $key=>$val){
	                $data = array(
	                    'id' => $val['id'],
	                    'level' => $val['level']+1,
	                );
	                $res = M('vip_grade')->save($data);
	            }
	            
	            $data = array(
	                'id' => $id,
	                'name' => $name,
	                'level'=>$level
	            );
	            $res = M('vip_grade')->save($data);
	        }
	    }
	    if ($res) {
	        //$this->insertMerchantUserLog("修改分类ID：".$id);
	    }
	    $this->_suc_ret();
	}
	/**
	 * [ajax_delete_grade 删除会员等级]
	 * @return [type] [description]
	 */
	public function ajax_delete_grade() {
	    $merchant = session('merchant');
	    $id = I('id');
	    if ($id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $data = array(
	        'id' => $id,
	    );
	    $info = M('vip_grade')->where($data)->find();
	    $res = M('vip_grade')->where($data)->delete();
	    $map = array(
	        'grade_id'=>$id,
	    );
	    $res = M('vip_pay_config')->where($map)->delete();
	    $map = array(
	        'deleted'=>0,
	        'level'=>array('EGT',$info['level'])
	    );
	    $temp_list = M('vip_grade')->where($map)->select();
	    foreach($temp_list as $key=>$val){
	        $data = array(
	            'id' => $val['id'],
	            'level' => $val['level']-1,
	        );
	        $res = M('vip_grade')->save($data);
	    }
	    if ($res) {
	        //$this->insertMerchantUserLog("删除分类ID：".$id);
	    }
	    $this->_suc_ret();
	}
	/**
	 * [pay_config 购买管理]
	 * @return [type] [description]
	 */
	public function pay_config() {
	    $this->assign('id',I('id'));
		$this->show();
	}
	/**
	 * [ajax_get_pay_config 获取购买管理接口]
	 * @return [type] [description]
	 */
	public function ajax_get_pay_config() {
	    $id = I('id');
	    if($id == ''){
	        $this->_err_ret('参数不完整');
	    }
	    $vip_pay_configModel = M("vip_pay_config");
		$map = array(
			'deleted' => 0,
		    'grade_id'=>$id
		);
		//加入条件
		$list = $vip_pay_configModel->where($map)->order('days asc')->select();
		if ($list === false) {
			$this->_err_ret();
		}
		$this->_tb_suc_ret($list);

	}
	/**
	 * [ajax_edit_pay_config 编辑会员购买配置]
	 * @return [type] [description]
	 */
	public function ajax_edit_pay_config() {
	    $merchant = session('merchant');
	    $id = I('id');
	    $name = I('name');
	    $days = I('days');
	    $price = I('price');
	    $discount_price = I('discount_price');
	    $info = I('info');
	    if ($id == '' || $name == '' || $days == '' || $price == '' || $discount_price == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $data = array(
	        'id' => $id,
	        'name' => $name,
	        'days' => $days,
	        'price' => $price,
	        'discount_price' => $discount_price,
	        'info' => $info,
	    );
	    $res = M('vip_pay_config')->save($data);
	    
	    if ($res) {
	        //$this->insertMerchantUserLog("添加分类ID：".$res);
	    }
	    $this->_suc_ret();
	}
	/**
	 * [ajax_add_pay_config 添加会员购买配置]
	 * @return [type] [description]
	 */
	public function ajax_add_pay_config() {
	    $merchant = session('merchant');
	    $grade_id = I('grade_id');
	    $name = I('name');
	    $days = I('days');
	    $price = I('price');
	    $discount_price = I('discount_price');
	    $info = I('info');
	    if ($grade_id == '' || $name == '' || $days == '' || $price == '' || $discount_price == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $data = array(
	        'grade_id' => $grade_id,
	        'name' => $name,
	        'days' => $days,
	        'price' => $price,
	        'discount_price' => $discount_price,
	        'info' => $info,
	        'add_time'=>date('Y-m-d H:i:s'),
	        'deleted'=>0
	    );
	    $res = M('vip_pay_config')->add($data);
	    
	    if ($res) {
	        //$this->insertMerchantUserLog("添加分类ID：".$res);
	    }
	    $this->_suc_ret();
	}
	/**
	 * [ajax_delete_pay_config 删除会员购买配置]
	 * @return [type] [description]
	 */
	public function ajax_delete_pay_config() {
	    $merchant = session('merchant');
	    $id = I('id');
	    if ($id == '') {
	        $this->_err_ret('参数不完整');
	    }
	    $data = array(
	        'id' => $id,
	    );
	    $res = M('vip_pay_config')->where($data)->delete();
	    
	    if ($res) {
	        //$this->insertMerchantUserLog("删除分类ID：".$id);
	    }
	    $this->_suc_ret();
	}
}