<?php
namespace Wechat\Model;
use Think\Model\RelationModel;

class AdminLogModel extends RelationModel {
	protected $trueTableName = 'admin_log';
	protected $_link = array(
		'Admin' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'admin_id',
			'mapping_name' => 'admin',
		),
	);
}
?>