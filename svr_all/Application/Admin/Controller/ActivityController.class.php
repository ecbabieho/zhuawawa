<?php
namespace Admin\Controller;
use Think\Controller;

class ActivityController extends BaseController {
	/**
	 * [activity_list 活动列表]
	 * @return [type] [description]
	 */
	public function activity_list() {
		$this->show();
	}
	/**
	 * [ajax_get_activity 获取活动接口]
	 * @return [type] [description]
	 */
	public function ajax_get_activity() {
		$map = array(
			'deleted' => 0,
		    'style'=>0
		);
		//加入条件
		$name = I("name");
		if ($name != "") {
			$map['config_name'] = array('like', "%" . $name . "%");
		}
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$articles = M('amount_config')
			->limit($start_index, $limit)
			->where($map)
			->select();
		if ($articles === false) {
			$this->_err_ret();
		}
		$count = M('amount_config')->where($map)->count();
		$this->_tb_suc_ret($articles, $count);

	}

	/**
	 * ajax_edit_activity 编辑活动
	 */
	public function ajax_edit_activity() {
		$lottery_config = I('lottery_config');
		if ($lottery_config == '') {
			exit();
		}
		$lottery_config = json_decode(urldecode($lottery_config), true);
		$data = array(
			'id' => $lottery_config['id'],
			'config_name' => $lottery_config['config_name'],
			'config_num' => $lottery_config['config_num'],
			'type' => $lottery_config['type'],
			'once_num' => $lottery_config['once_num'],
			'day_num' => $lottery_config['day_num'],
			'is_open' => $lottery_config['is_open'],
			'cover_image' => $lottery_config['cover_image'],
		    'sub_title' => $lottery_config['sub_title'],
		    'level' => $lottery_config['level'],
		);
		$res = M('amount_config')->save($data);
		if (!$res) {
			$this->_err_ret('编辑失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_add_activity 添加活动
	 */
	public function ajax_add_activity() {
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
			'config_name' => $lottery_config['config_name'],
			'config_num' => $lottery_config['config_num'],
			'type' => $lottery_config['type'],
			'once_num' => $lottery_config['once_num'],
			'day_num' => $lottery_config['day_num'],
			'is_open' => $lottery_config['is_open'],
			'cover_image' => $lottery_config['cover_image'],
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'sub_title' => $lottery_config['sub_title'],
		);
		$res = M('amount_config')->add($data);
		if (!$res) {
			$this->_err_ret('添加失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_delete_activity  删除活动
	 */
	public function ajax_delete_activity() {
		$merchant = session('merchant');
		$id = I('id');
		if ($id == '') {
			$this->_err_ret('参数不完整');
		}
		$map = array(
			'id' => $id,
			'deleted' => 1,
		);
		$res = M('amount_config')->save($map);
		if (!$res) {
			$this->_err_ret('删除失败');
		}
		$this->_suc_ret();
	}
	/**
	 * ajax_public_activity 发布活动
	 */
	public function ajax_public_activity() {
		$merchant = session('merchant');
		$id = I('id');
		$type = I('type');
		if ($id == '' || $type == '') {
			$this->_err_ret('参数不完整');
		}
		$data = array(
			'id' => $id,
			'is_open' => $type,
		);
		$res = M('amount_config')->save($data);
		if (!$res) {
			$this->_err_ret('修改失败');
		}
		$this->_suc_ret();
	}
	/**
	 * [bank_user 银行用户]
	 * @return [type] [description]
	 */
	public function bank_user() {
		$this->show();
	}
	/**
	 * [ajax_get_bank_user 获取银行用户接口]
	 * @return [type] [description]
	 */
	public function ajax_get_bank_user() {
		$map = array(
			'deleted' => 0,
		);
		//加入条件
		$type = I("type");
		if ($type != "") {
			$map['type'] = array('like', "%" . $type . "%");
		}
		$status = I("status");
		if ($status != "") {
			$map['status'] = $status;
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
		$page = I('page');
		$limit = I('limit');
		if ($page == ""
			|| $limit == "") {
			exit;
		}
		$start_index = ((int) $page - 1) * ((int) $limit);
		$bank_user = M('bank_user')
			->limit($start_index, $limit)
			->where($map)
			->order('add_time desc')
			->select();
		if ($bank_user === false) {
			$this->_err_ret();
		}
		$count = M('bank_user')->where($map)->count();
		foreach ($bank_user as $key => $val) {
			$map = array(
				'id' => $val['user_id'],
			);
			$user_info = M('user')->where($map)->find();
			$bank_user[$key]['nickname'] = $user_info['id'] . '-' . $user_info['nickname'];
		}
		$this->_tb_suc_ret($bank_user, $count);

	}
	/**
	 * ajax_bank_user 修改信息类型
	 */
	public function ajax_bank_user() {
		$userModel = D("user");
		$id = I('id');
		$status = I('status');
		if ($id == ""
			|| $status == "") {
			exit;
		}
		$data = array(
			'id' => $id,
			'status' => $status,
		);
		$res = M('bank_user')->save($data);
		if (!$res) {
			$this->_err_ret('修改失败');
		}
		$this->_suc_ret();
	}
	/**
	 * export_test_bank_user 导出表格
	 */
	public function export_test_bank_user() {
		$map = array(
			'deleted' => 0,
		);
		$type = I("type");
		if ($type != "") {
			$map['type'] = array('like', "%" . $type . "%");
		}
		$status = I("status");
		if ($status != "") {
			$map['status'] = $status;
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
		$bank_user = M('bank_user')->where($map)->order('add_time desc')->select();
		if ($bank_user === false) {
			$this->_err_ret();
		}
		foreach ($bank_user as $key => $val) {
			$map = array(
				'id' => $val['user_id'],
			);
			$user_info = M('user')->where($map)->find();
			$bank_user[$key]['nickname'] = $user_info['id'] . '-' . $user_info['nickname'];
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
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(50);
		$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('宋体')->setSize(15)->setBold(true); //字体加粗
		$objActSheet->setCellValue('A1', '银行用户统计表');
		$objActSheet->setCellValue('A2', '用户昵称');
		$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('B2', '真实姓名');
		$objPHPExcel->getActiveSheet()->getStyle('B2')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('C2', '联系电话');
		$objPHPExcel->getActiveSheet()->getStyle('C2')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('D2', '公司名称');
		$objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('E2', '公司电话');
		$objPHPExcel->getActiveSheet()->getStyle('E2')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('F2', '添加时间');
		$objPHPExcel->getActiveSheet()->getStyle('F2')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('G2', '所属银行');
		$objPHPExcel->getActiveSheet()->getStyle('G2')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$objActSheet->setCellValue('F2', '所属银行');
		$objPHPExcel->getActiveSheet()->getStyle('F2')->getFont()->setName('宋体')->setSize(11)->setBold(true); //字体加粗
		$hang = 3;
		foreach ($bank_user as $key => $val) {
			$status = '';
			if ($val['status'] == 1) {
				$status = '有效';
			}
			if ($val['status'] == 0) {
				$status = '无效';
			}
			$objActSheet->setCellValue('A' . $hang, $this->filter_Emoji($val['nickname']));
			$objActSheet->setCellValue('B' . $hang, $this->filter_Emoji($val['real_name']));
			$objActSheet->setCellValue('C' . $hang, $this->filter_Emoji($val['tel']));
			$objActSheet->setCellValue('D' . $hang, $this->filter_Emoji($val['company_name']));
			$objActSheet->setCellValue('E' . $hang, $this->filter_Emoji($val['company_tel']));
			$objActSheet->setCellValue('F' . $hang, $this->filter_Emoji($val['add_time']));
			$objActSheet->setCellValue('G' . $hang, $this->filter_Emoji($val['type']));
			$objActSheet->setCellValue('F' . $hang, $status);
			$hang++;
		}
		$fileName = '银行用户统计表';
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
	 * [box_good_list 宝箱商品]
	 * @return [type] [description]
	 */
	public function box_good_list() {
	    $map = array(
	        'deleted' => 0,
	    );
	    $lottery_good = M('lottery_good')->where($map)->order('id desc')->select();
	    $this->assign('lottery_good', $lottery_good);
	    $map = array(
	        'deleted' => 0,
	    );
	    $lottery_config = M('lottery_config')->where($map)->order('id desc')->select();
	    $this->assign('lottery_config', $lottery_config);
	    $this->show();
	}
	/**
	 * [ajax_get_box_good 获取宝箱商品]
	 * @return [type] [description]
	 */
	public function ajax_get_box_good() {
	    $map = array(
	        'deleted' => 0,
	    );
	    $page = I('page');
	    $limit = I('limit');
	    if ($page == ""
	        || $limit == "") {
	            exit;
	        }
        $start_index = ((int) $page - 1) * ((int) $limit);
        $box_good = M('box_good')
        ->limit($start_index, $limit)
        ->where($map)
        ->order('add_time desc')
        ->select();
        if ($box_good === false) {
            $this->_err_ret();
        }
        $count = M('box_good')->where($map)->count();
        foreach($box_good as $key=>$val){
            $map = array(
                'id'=>$val['lottery_config_id'],
            );
            $lottery_config = M('lottery_config')->where($map)->find();
            $box_good[$key]['lottery_config_name'] = $lottery_config['name'];
            $map = array(
                'id'=>$val['lottery_good_id'],
            );
            $lottery_good = M('lottery_good')->where($map)->find();
            $box_good[$key]['lottery_good_name'] = $lottery_good['name'];
            $box_good[$key]['cover_image'] = $lottery_good['img_url'];
        }
        $this->_tb_suc_ret($box_good, $count);
	}
	
	/**
	 * [ajax_box_good_open 修改宝箱商品状态]
	 * @return [type] [description]
	 */
	public function ajax_box_good_open() {
	    $id = I('id');
	    $is_open = I('status');
	    if ($id == "" || $is_open == "") {
            exit;
        }
        
        $data = array(
            'id' => $id,
            'is_open'=>$is_open
        );
        $res = M('box_good')->save($data);
        if (!$res) {
            $this->_err_ret('修改失败');
        }
        $this->_suc_ret();
	}
	/**
	 * [ajax_delete_box_good 删除宝箱商品]
	 * @return [type] [description]
	 */
	public function ajax_delete_box_good() {
	    $id = I('id');
	    if ($id == "") {
	        exit;
	    }
	    
	    $data = array(
	        'id' => $id,
	    );
	    $res = M('box_good')->where($data)->delete();
	    if (!$res) {
	        $this->_err_ret('删除失败');
	    }
	    $this->_suc_ret();
	}
	//添加宝箱商品
	public function ajax_add_box_good(){
	    $good_id = I('good_id');
	    $config_id = I('config_id');
	    if($good_id == '' || $config_id == ''){
	        $this->_err_ret('参数不完整');
	    }
	    $data = array(
	        'lottery_config_id'=>$config_id,
	        'lottery_good_id'=>$good_id
	    );
	    $temp = M('box_good')->where($data)->find();
	    if($temp){
	        $this->_err_ret('该商品已经添加过了');
	    }
	    $data = array(
	        'lottery_config_id'=>$config_id,
	        'lottery_good_id'=>$good_id,
	        'is_open'=>0,
	        'add_time'=>date('Y-m-d H:i:s'),
	        'deleted'=>0
	    );
	    $res = M('box_good')->add($data);
	    if(!$res){
	        $this->_err_ret('添加失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * [activity_vip 赠送会员活动]
	 * @return [type] [description]
	 */
	public function activity_vip() {
	    $map = array(
	        'deleted'=>0,
	    );
	    $vip_list = M('vip_grade')->where($map)->order('level asc')->select();
	    $this->assign('vip_list',$vip_list);
	    $this->show();
	}
	/**
	 * [ajax_get_activity 获取活动接口]
	 * @return [type] [description]
	 */
	public function ajax_get_activity_vip() {
	    $map = array(
	        'deleted' => 0,
	        'style'=>1
	    );
	    //加入条件
	    $name = I("name");
	    if ($name != "") {
	        $map['config_name'] = array('like', "%" . $name . "%");
	    }
	    $page = I('page');
	    $limit = I('limit');
	    if ($page == ""
	        || $limit == "") {
	            exit;
	        }
	        $start_index = ((int) $page - 1) * ((int) $limit);
	        $articles = M('amount_config')
	        ->limit($start_index, $limit)
	        ->where($map)
	        ->select();
	        if ($articles === false) {
	            $this->_err_ret();
	        }
	        $count = M('amount_config')->where($map)->count();
	        foreach($articles as $key=>$val){
	            $map = array(
	                'level'=>$val['level'],
	                'deleted'=>0
	            );
	            $vip_grade = M('vip_grade')->where($map)->find();
	            $articles[$key]['level_name'] = $vip_grade['name'];
	        }
	        $this->_tb_suc_ret($articles, $count);
	        
	}
	/**
	 * ajax_add_activity_vip 添加赠送会员活动
	 */
	public function ajax_add_activity_vip() {
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
	        'config_name' => $lottery_config['config_name'],
	        'config_num' => $lottery_config['config_num'],
	        'type' => $lottery_config['type'],
	        'once_num' => $lottery_config['once_num'],
	        'day_num' => $lottery_config['day_num'],
	        'is_open' => $lottery_config['is_open'],
	        'cover_image' => $lottery_config['cover_image'],
	        'add_time' => date('Y-m-d H:i:s'),
	        'deleted' => 0,
	        'sub_title' => $lottery_config['sub_title'],
	        'style' => 1,
	        'level'=>$lottery_config['level']
	    );
	    $res = M('amount_config')->add($data);
	    if (!$res) {
	        $this->_err_ret('添加失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * [adopt_config 领养活动]
	 * @return [type] [description]
	 */
	public function adopt_config() {
	    $map = array(
	        'deleted' => 0,
	    );
	    $lottery_good = M('lottery_good')->where($map)->order('id desc')->select();
	    $this->assign('lottery_good', $lottery_good);
	    $map = array(
	        'deleted' => 0,
	    );
	    $lottery_config = M('lottery_config')->where($map)->order('id desc')->select();
	    $this->assign('lottery_config', $lottery_config);
	    $this->show();
	}
	/**
	 * [ajax_get_adopt_config 获取领养活动接口]
	 * @return [type] [description]
	 */
	public function ajax_get_adopt_config() {
	    $map = array(
	        'deleted' => 0,
	    );
	    $page = I('page');
	    $limit = I('limit');
	    if ($page == "" || $limit == "") {
            exit;
        }
        $start_index = ((int) $page - 1) * ((int) $limit);
        $adopt_config = M('adopt_config')
        ->limit($start_index, $limit)
        ->where($map)
        ->select();
        if ($adopt_config === false) {
            $this->_err_ret();
        }
        $count = M('adopt_config')->where($map)->count();
        foreach($adopt_config as $key=>$val){
            $map = array(
                'id'=>$val['lottery_good_id'],
                'deleted'=>0
            );
            $lottery_good = M('lottery_good')->where($map)->find();
            $adopt_config[$key]['lottery_good_name'] = $lottery_good['name'];
            $adopt_config[$key]['cover_image'] = $lottery_good['img_url'];
        }
        $this->_tb_suc_ret($adopt_config, $count);
	}
   /**
	* [ajax_edit_adopt_config 修改领养活动]
	* @return [type] [description]
	*/
	public function ajax_edit_adopt_config() {
	    $id = I('id');
	    $adopt_val = I('adopt_val');
	    if ($id == "" || $adopt_val == "") {
	        exit;
	    }
	    
	    $data = array(
	        'id' => $id,
	        'adopt_val'=>$adopt_val
	    );
	    $res = M('adopt_config')->save($data);
	    if (!$res) {
	        $this->_err_ret('修改失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * [ajax_delete_adopt_config 删除领养活动]
	 * @return [type] [description]
	 */
	public function ajax_delete_adopt_config() {
	    $id = I('id');
	    if ($id == "") {
	        exit;
	    }
	    $data = array(
	        'id' => $id,
	    );
	    $res = M('adopt_config')->where($data)->delete();
	    if (!$res) {
	        $this->_err_ret('删除失败');
	    }
	    $this->_suc_ret();
	}
	//添加领养商品
	public function ajax_add_adopt_config(){
	    $good_id = I('good_id');
	    $adopt_val = I('adopt_val');
	    $config_id = I('config_id');
	    if($good_id == '' || $config_id == '' || $adopt_val == ''){
	        $this->_err_ret('参数不完整');
	    }
	    $data = array(
	        'deleted'=>0,
	        'lottery_good_id'=>$good_id
	    );
	    $temp = M('adopt_config')->where($data)->find();
	    if($temp){
	        $this->_err_ret('该商品已经添加过了');
	    }
	    $data = array(
	        'lottery_config_id'=>$config_id,
	        'lottery_good_id'=>$good_id,
	        'adopt_val'=>$adopt_val,
	        'add_time'=>date('Y-m-d H:i:s'),
	        'deleted'=>0
	    );
	    $res = M('adopt_config')->add($data);
	    if(!$res){
	        $this->_err_ret('添加失败');
	    }
	    $this->_suc_ret();
	}
	
	/***********************抓娃娃活动*******************************/
	public function lottery_activity(){
	    $admin = session('admin');
	    if (!$admin) {
	        exit;
	    }
	    $map = array(
	        'deleted'=>0,
	        'id'=>1
	    );
	    $lottery_activity = M('lottery_activity')->where($map)->find();
	    $lottery_activity_goods_map = array(
	        'lottery_activity_id'=>1,
	        'deleted'=>0,
	    );
	    $lottery_activity_goods = M('lottery_activity_goods')->where($lottery_activity_goods_map)->select();
	    $return = array();
	    foreach($lottery_activity_goods as $key=>$val){
	        $goods_map = array(
	            'id'=>$val['goods_id']
	        );
	        $goods_info = M('lottery_good')->where($goods_map)->find();
	        $goods_info['probability'] = $val['probability'];
	        $return[] = $goods_info;
	    }
	    $this->assign('lottery_activity',$lottery_activity);
	    $this->assign('lottery_activity_goods',$return);
	    $this->show();
	}
	public function update_lottery_activity(){
	    $lottery_config = I('lottery_config');
	    if ($lottery_config == '') {
	        exit();
	    }
	    $lottery_config = json_decode(urldecode($lottery_config), true);
	    $data = array(
	        'id'=>1,
	        'name' => $lottery_config['name'],
	        'cover_image' => $lottery_config['cover_image'],
	        'is_open' => $lottery_config['is_open'],
	        'is_open' => $lottery_config['is_open'],
	        'times' => $lottery_config['times'],
	        'over_time' => $lottery_config['over_time'],
	    );
	    $res = M('lottery_activity')->save($data);
	    if (!$res) {
	        $this->_err_ret('更新失败');
	    }
	    $this->_suc_ret();
	}
	public function lottery_activity_goods(){
	    $map = array(
	        'deleted' => 0,
	    );
	    $lottery_good = M('lottery_good')->where($map)->order('id desc')->select();
	    $this->assign('lottery_good', $lottery_good);
	    $map = array(
	        'deleted' => 0,
	    );
	    $lottery_config = M('lottery_config')->where($map)->order('id desc')->select();
	    $this->assign('lottery_config', $lottery_config);
	    $this->show();
	}
	public function ajax_get_lottery_activity_goods(){
	    $map = array(
	        'deleted' => 0,
	    );
	    $page = I('page');
	    $limit = I('limit');
	    if ($page == "" || $limit == "") {
	        exit;
	    }
	    $start_index = ((int) $page - 1) * ((int) $limit);
	    $lottery_activity_goods = M('lottery_activity_goods')
                    	    ->limit($start_index, $limit)
                    	    ->where($map)
                    	    ->select();
                    	    if ($lottery_activity_goods === false) {
	        $this->_err_ret();
	    }
	    $count = M('lottery_activity_goods')->where($map)->count();
	    foreach($lottery_activity_goods as $key=>$val){
	        $map = array(
	            'id'=>$val['goods_id'],
	            'deleted'=>0
	        );
	        $lottery_good = M('lottery_good')->where($map)->find();
	        $lottery_activity_goods[$key]['lottery_good_name'] = $lottery_good['name'];
	        $lottery_activity_goods[$key]['cover_image'] = $lottery_good['img_url'];
	    }
	    $this->_tb_suc_ret($lottery_activity_goods, $count);
	}
	
	/**
	 * [ajax_delete_lottery_activity_goods 删除活动]
	 * @return [type] [description]
	 */
	public function ajax_delete_lottery_activity_goods() {
	    $id = I('id');
	    if ($id == "") {
	        exit;
	    }
	    $data = array(
	        'id' => $id,
	    );
	    $res = M('lottery_activity_goods')->where($data)->delete();
	    if (!$res) {
	        $this->_err_ret('删除失败');
	    }
	    $this->_suc_ret();
	}
	//添加商品
	public function ajax_add_lottery_activity_goods(){
	    $goods_id = I('goods_id');
	    $probability = I('probability');
	    $obtain_num = I('obtain_num');
	    $lottery_config_id = I('lottery_config_id');
	    if($goods_id == '' || $probability == '' || $obtain_num == "" || $lottery_config_id == ""){
	        $this->_err_ret('参数不完整');
	    }
	    $data = array(
	        'deleted'=>0,
	        'goods_id'=>$goods_id,
	        'lottery_activity_id'=>1
	    );
	    $temp = M('lottery_activity_goods')->where($data)->find();
	    if($temp){
	        $this->_err_ret('该商品已经添加过了');
	    }
	    $data = array(
	        'goods_id'=>$goods_id,
	        'lottery_config_id'=>$lottery_config_id,
	        'probability'=>$probability,
	        'lottery_activity_id'=>1,
	        'obtain_num'=>$obtain_num,
	        'add_time'=>date('Y-m-d H:i:s'),
	        'deleted'=>0
	    );
	    $res = M('lottery_activity_goods')->add($data);
	    if(!$res){
	        $this->_err_ret('添加失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * [ajax_edit_lottery_activity_goods 修改活动]
	 * @return [type] [description]
	 */
	public function ajax_edit_lottery_activity_goods() {
	    $id = I('id');
	    $probability = I('probability');
	    $obtain_num = I('obtain_num');
	    if ($id == "" || $probability == "" || $obtain_num == "") {
	        exit;
	    }
	    
	    $data = array(
	        'id' => $id,
	        'probability'=>$probability,
	        'obtain_num'=>$obtain_num,
	    );
	    $res = M('lottery_activity_goods')->save($data);
	    if (!$res) {
	        $this->_err_ret('修改失败');
	    }
	    $this->_suc_ret();
	}
	
	/********************种植水果****************************/
	/**
	 * [fruit_config 水果活动]
	 * @return [type] [description]
	 */
	public function fruit_config() {
	    $map = array(
	        'deleted' => 0,
	    );
	    $lottery_good = M('lottery_good')->where($map)->order('id desc')->select();
	    $this->assign('lottery_good', $lottery_good);
	    $map = array(
	        'deleted' => 0,
	    );
	    $lottery_config = M('lottery_config')->where($map)->order('id desc')->select();
	    $this->assign('lottery_config', $lottery_config);
	    $this->show();
	}
	/**
	 * [ajax_get_fruit_config 获取水果活动接口]
	 * @return [type] [description]
	 */
	public function ajax_get_fruit_config() {
	    $map = array(
	        'deleted' => 0,
	    );
	    $page = I('page');
	    $limit = I('limit');
	    if ($page == "" || $limit == "") {
	        exit;
	    }
	    $start_index = ((int) $page - 1) * ((int) $limit);
	    $adopt_config = M('fruit_config')
	    ->limit($start_index, $limit)
	    ->where($map)
	    ->select();
	    if ($adopt_config === false) {
	        $this->_err_ret();
	    }
	    $count = M('fruit_config')->where($map)->count();
	    foreach($adopt_config as $key=>$val){
	        $map = array(
	            'id'=>$val['lottery_good_id'],
	            'deleted'=>0
	        );
	        $lottery_good = M('lottery_good')->where($map)->find();
	        $adopt_config[$key]['lottery_good_name'] = $lottery_good['name'];
	        $adopt_config[$key]['cover_image'] = $lottery_good['img_url'];
	    }
	    $this->_tb_suc_ret($adopt_config, $count);
	}
	/**
	 * [ajax_edit_fruit_config 修改水果活动]
	 * @return [type] [description]
	 */
	public function ajax_edit_fruit_config() {
	    $id = I('id');
	    $fruit_val = I('fruit_val');
	    if ($id == "" || $fruit_val == "") {
	        exit;
	    }
	    
	    $data = array(
	        'id' => $id,
	        'fruit_val'=>$fruit_val
	    );
	    $res = M('fruit_config')->save($data);
	    if (!$res) {
	        $this->_err_ret('修改失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * [ajax_delete_fruit_config 删除水果活动]
	 * @return [type] [description]
	 */
	public function ajax_delete_fruit_config() {
	    $id = I('id');
	    if ($id == "") {
	        exit;
	    }
	    $data = array(
	        'id' => $id,
	    );
	    $res = M('fruit_config')->where($data)->delete();
	    if (!$res) {
	        $this->_err_ret('删除失败');
	    }
	    $this->_suc_ret();
	}
	//添加领养商品
	public function ajax_add_fruit_config(){
	    $good_id = I('good_id');
	    $fruit_val = I('fruit_val');
	    $config_id = I('config_id');
	    if($good_id == '' || $config_id == '' || $fruit_val == ''){
	        $this->_err_ret('参数不完整');
	    }
	    $data = array(
	        'deleted'=>0,
	        'lottery_good_id'=>$good_id
	    );
	    $temp = M('fruit_config')->where($data)->find();
	    if($temp){
	        $this->_err_ret('该商品已经添加过了');
	    }
	    $data = array(
	        'lottery_config_id'=>$config_id,
	        'lottery_good_id'=>$good_id,
	        'fruit_val'=>$fruit_val,
	        'total_num'=>0,
	        'add_time'=>date('Y-m-d H:i:s'),
	        'deleted'=>0
	    );
	    $res = M('fruit_config')->add($data);
	    if(!$res){
	        $this->_err_ret('添加失败');
	    }
	    $this->_suc_ret();
	}
	
	/**
	 * ajax_edit_fruit_open 水果是否可选
	 */
	public function ajax_edit_fruit_open() {
	    $id = I('id');
	    $type = I('type');
	    if ($id == ""
	        || $type == "") {
	            exit;
	        }
	        $data = array(
	            'id' => $id,
	            'is_open' => $type,
	        );
	        $res = M('fruit_config')->save($data);
	        if (!$res) {
	            $this->_err_ret('修改失败');
	        }
	        $this->_suc_ret();
	}
	
	
	/***********************圣诞活动*******************************/
	public function christmas_activity(){
	    $admin = session('admin');
	    if (!$admin) {
	        exit;
	    }
	    $map = array(
	        'deleted'=>0,
	        'id'=>1
	    );
	    $lottery_activity = M('christmas_activity')->where($map)->find();
	    $lottery_activity_goods_map = array(
	        'lottery_activity_id'=>1,
	        'deleted'=>0,
	    );
	    $lottery_activity_goods = M('christmas_activity_goods')->where($lottery_activity_goods_map)->select();
	    $return = array();
	    foreach($lottery_activity_goods as $key=>$val){
	        $goods_map = array(
	            'id'=>$val['goods_id']
	        );
	        $goods_info = M('lottery_good')->where($goods_map)->find();
	        $goods_info['probability'] = $val['probability'];
	        $return[] = $goods_info;
	    }
	    $this->assign('lottery_activity',$lottery_activity);
	    $this->assign('lottery_activity_goods',$return);
	    $this->show();
	}
	public function update_christmas_activity(){
	    $lottery_config = I('lottery_config');
	    if ($lottery_config == '') {
	        exit();
	    }
	    $lottery_config = json_decode(urldecode($lottery_config), true);
	    $data = array(
	        'id'=>1,
	        'name' => $lottery_config['name'],
	        'cover_image' => $lottery_config['cover_image'],
	        'is_open' => $lottery_config['is_open'],
	        'coin_num' => $lottery_config['coin_num'],
	        'start_time' => $lottery_config['start_time'],
	        'end_time' => $lottery_config['end_time'],
	    );
	    $res = M('christmas_activity')->save($data);
	    if (!$res) {
	        $this->_err_ret('更新失败');
	    }
	    $this->_suc_ret();
	}
	public function christmas_activity_goods(){
	    $map = array(
	        'deleted' => 0,
	    );
	    $lottery_good = M('lottery_good')->where($map)->order('id desc')->select();
	    $this->assign('lottery_good', $lottery_good);
	    $map = array(
	        'deleted' => 0,
	    );
	    $lottery_config = M('lottery_config')->where($map)->order('id desc')->select();
	    $this->assign('lottery_config', $lottery_config);
	    $this->show();
	}
	public function ajax_get_christmas_activity_goods(){
	    $map = array(
	        'deleted' => 0,
	    );
	    $page = I('page');
	    $limit = I('limit');
	    if ($page == "" || $limit == "") {
	        exit;
	    }
	    $start_index = ((int) $page - 1) * ((int) $limit);
	    $lottery_activity_goods = M('christmas_activity_goods')
	    ->limit($start_index, $limit)
	    ->where($map)
	    ->select();
	    if ($lottery_activity_goods === false) {
	        $this->_err_ret();
	    }
	    $count = M('christmas_activity_goods')->where($map)->count();
	    foreach($lottery_activity_goods as $key=>$val){
	        $map = array(
	            'id'=>$val['goods_id'],
	            'deleted'=>0
	        );
	        $lottery_good = M('lottery_good')->where($map)->find();
	        $lottery_activity_goods[$key]['lottery_good_name'] = $lottery_good['name'];
	        $lottery_activity_goods[$key]['cover_image'] = $lottery_good['img_url'];
	    }
	    $this->_tb_suc_ret($lottery_activity_goods, $count);
	}
	
	/**
	 * [ajax_delete_christmas_activity_goods 删除活动]
	 * @return [type] [description]
	 */
	public function ajax_delete_christmas_activity_goods() {
	    $id = I('id');
	    if ($id == "") {
	        exit;
	    }
	    $data = array(
	        'id' => $id,
	    );
	    $res = M('christmas_activity_goods')->where($data)->delete();
	    if (!$res) {
	        $this->_err_ret('删除失败');
	    }
	    $this->_suc_ret();
	}
	//添加商品
	public function ajax_add_christmas_activity_goods(){
	    $goods_id = I('goods_id');
	    $probability = I('probability');
	    $obtain_num = I('obtain_num');
	    $lottery_config_id = I('lottery_config_id');
	    if($goods_id == '' || $probability == '' || $obtain_num == "" || $lottery_config_id == ""){
	        $this->_err_ret('参数不完整');
	    }
	    $data = array(
	        'deleted'=>0,
	        'goods_id'=>$goods_id,
	        'lottery_activity_id'=>1
	    );
	    $temp = M('christmas_activity_goods')->where($data)->find();
	    if($temp){
	        $this->_err_ret('该商品已经添加过了');
	    }
	    $data = array(
	        'goods_id'=>$goods_id,
	        'lottery_config_id'=>$lottery_config_id,
	        'probability'=>$probability,
	        'lottery_activity_id'=>1,
	        'obtain_num'=>$obtain_num,
	        'add_time'=>date('Y-m-d H:i:s'),
	        'deleted'=>0
	    );
	    $res = M('christmas_activity_goods')->add($data);
	    if(!$res){
	        $this->_err_ret('添加失败');
	    }
	    $this->_suc_ret();
	}
	/**
	 * [ajax_edit_christmas_activity_goods 修改活动]
	 * @return [type] [description]
	 */
	public function ajax_edit_christmas_activity_goods() {
	    $id = I('id');
	    $probability = I('probability');
	    $obtain_num = I('obtain_num');
	    if ($id == "" || $probability == "" || $obtain_num == "") {
	        exit;
	    }
	    
	    $data = array(
	        'id' => $id,
	        'probability'=>$probability,
	        'obtain_num'=>$obtain_num,
	    );
	    $res = M('christmas_activity_goods')->save($data);
	    if (!$res) {
	        $this->_err_ret('修改失败');
	    }
	    $this->_suc_ret();
	}
	
	
	
}