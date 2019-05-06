<?php
namespace Home\Model;
use Think\Model\RelationModel;

class FangchanJingjirenModel extends RelationModel {
	protected $trueTableName = 'fangchan_jingjiren';
	protected $_link = array(
	);
	public function addFangchanJingjiren($area, $name, $company, $fenhang, $touxiang, $tel, $shangquan) {
		$map = array(
			'tel' => $tel,
			'name' => $name,
			'company' => $company,
		);
		$fangchan_jingjiren = $this->where($map)->find();
		if ($fangchan_jingjiren) {
			return 1; //已经存在了
		}

		$data = array(
			'area' => $area,
			'name' => $name,
			'company' => $company,
			'fenhang' => $fenhang,
			'touxiang' => $touxiang,
			'tel' => $tel,
			'shangquan' => $shangquan,
			'created' => time(0),
		);
		$res = $this->add($data);
		if (!$res) {
			return -1; //失败了
		}
		return 0; //成功了

	}
}
?>