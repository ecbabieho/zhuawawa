<?php
namespace Wechat\Model;
use Think\Model\RelationModel;

class AdoptConfigModel extends RelationModel {
	protected $trueTableName = 'adopt_config';
	protected $_link = array(
		'LotteryGood' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'lottery_good_id',
			'mapping_name' => 'lottery_good',
		),
	);
}
?>