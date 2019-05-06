<?php
namespace Wechat\Model;
use Think\Model\RelationModel;

class LotteryRecordModel extends RelationModel {
	protected $trueTableName = 'lottery_record';
	protected $_link = array(
		'User' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'user_id',
			'mapping_name' => 'user',
		),
		'LotteryConfig' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'lottery_config_id',
			'mapping_name' => 'lottery_config',
		),
	    'LotteryType' => array(
	        'mapping_type' => self::BELONGS_TO,
	        'foreign_key' => 'lottery_type_id',
	        'mapping_name' => 'lottery_type',
	    ),
		'LotteryGood' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'lottery_good_id',
			'mapping_name' => 'lottery_good',
		),
	);
}
?>