<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class LotteryConfigModel extends RelationModel {
	protected $trueTableName = 'lottery_config';
	protected $_link = array(
		'LotteryType' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'lottery_type_id',
			'mapping_name' => 'lottery_type',
		),
	);
}
?>