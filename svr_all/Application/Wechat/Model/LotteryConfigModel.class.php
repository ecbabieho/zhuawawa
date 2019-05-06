<?php
namespace Wechat\Model;
use Think\Model\RelationModel;

class LotteryConfigModel extends RelationModel {
	protected $trueTableName = 'lottery_config';
	protected $_link = array(
		'LotteryType' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'lottery_type_id',
			'mapping_name' => 'lottery_type',
		),
		'LotteryGood1' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'lottery_good_id0',
			'mapping_name' => 'lottery_good0',
			'class_name' => 'LotteryGood',
		),
		'LotteryGood2' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'lottery_good_id1',
			'mapping_name' => 'lottery_good1',
			'class_name' => 'LotteryGood',
		),
		'LotteryGood3' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'lottery_good_id2',
			'mapping_name' => 'lottery_good2',
			'class_name' => 'LotteryGood',
		),
		'LotteryGood4' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'lottery_good_id3',
			'mapping_name' => 'lottery_good3',
			'class_name' => 'LotteryGood',
		),
		'LotteryGood' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'lottery_good_id4',
			'mapping_name' => 'lottery_good4',
			'class_name' => 'LotteryGood',
		),
	);
}
?>