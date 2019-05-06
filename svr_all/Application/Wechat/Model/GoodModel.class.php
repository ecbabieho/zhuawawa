<?php
namespace Wechat\Model;
use Think\Model\RelationModel;

class GoodModel extends RelationModel {
	protected $trueTableName = 'good';
	protected $_link = array(
	);
	public function getGoodDetail($good) {
		$good['images'] = json_decode(urldecode($good['images']), true);
		return $good;
	}
}
?>