<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class ArriveLogModel extends RelationModel {
	protected $trueTableName = 'arrive_log';
	protected $_link = array(
		'Merchant' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'merchant_id',
			'mapping_name' => 'merchant',
		),
		'User' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'user_id',
			'mapping_name' => 'user',
		),

	);
}
?>