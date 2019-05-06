<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class UserCoinRecordModel extends RelationModel {
	protected $trueTableName = 'user_coin_record';
	protected $_link = array(
		'User' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'user_id',
			'mapping_name' => 'user',
		),
		'CoinConfig' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'coin_config_id',
			'mapping_name' => 'coin_config',
		),
		'AmountConfig' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'type',
			'mapping_name' => 'amount_config',
		),
	);
}
?>