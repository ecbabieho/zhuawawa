<?php
namespace Wechat\Model;
use Think\Model\RelationModel;

class AdoptLogModel extends RelationModel {
	protected $trueTableName = 'adopt_log';
	protected $_link = array(
		'AdoptConfig' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'adopt_config_id',
			'mapping_name' => 'adopt_config',
		),
	);
}
?>