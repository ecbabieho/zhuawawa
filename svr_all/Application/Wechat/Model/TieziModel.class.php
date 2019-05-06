<?php
namespace Wechat\Model;
use Think\Model\RelationModel;

class TieziModel extends RelationModel {
	protected $trueTableName = 'tiezi';
	protected $_link = array(
		'User' => array(
			'mapping_type' => self::BELONGS_TO,
			'foreign_key' => 'user_id',
			'mapping_name' => 'user',
		),
	);
	public function dealTiezi($tiezi) {
		$tiezi['images'] = json_decode(urldecode($tiezi['images']));
		$tiezi['images_count'] = count($tiezi['images']);
		$tiezi['brief'] = str_replace(PHP_EOL, '', $tiezi['content']);
		for ($i = 0; $i < count($tiezi['images']); $i++) {
			$tiezi['brief'] = str_replace("[图片" . ($i + 1) . "]", '', $tiezi['brief']);
		}
		$tiezi['add_time'] = $this->update_add_time($tiezi['add_time']);
		return $tiezi;
	}
	public function dealOneTiezi($tiezi) {
		$tiezi['images'] = json_decode(urldecode($tiezi['images']));
		$tiezi['content'] = str_replace(PHP_EOL, '<br />', $tiezi['content']);
		for ($i = 0; $i < count($tiezi['images']); $i++) {
			$tiezi['content'] = str_replace("[图片" . ($i + 1) . "]", '<img src="' . $tiezi['images'][$i] . '">', $tiezi['content']);
		}
		$tiezi['add_time'] = $this->update_add_time($tiezi['add_time']);
		return $tiezi;

	}
	public function update_add_time($add_time){
	    $add_time = strtotime($add_time);
	    if(time()-$add_time>0 && time()-$add_time<60){
	        return '刚刚';
	    }else if(time()-$add_time>=60 && time()-$add_time<120){
	        return '1分钟前';
	    }else if(time()-$add_time>=120 && time()-$add_time<180){
	        return '2分钟前';
	    }else if(time()-$add_time>=180 && time()-$add_time<240){
	        return '3分钟前';
	    }else if(time()-$add_time>=240 && time()-$add_time<300){
	        return '4分钟前';
	    }else if(time()-$add_time>=300 && time()-$add_time<360){
	        return '5分钟前';
	    }else if(time()-$add_time>=360 && time()-$add_time<420){
	        return '6分钟前';
	    }else if(time()-$add_time>=420 && time()-$add_time<480){
	        return '7分钟前';
	    }else if(time()-$add_time>=480 && time()-$add_time<540){
	        return '8分钟前';
	    }else if(time()-$add_time>=540 && time()-$add_time<600){
	        return '9分钟前';
	    }else if(time()-$add_time>=600 && time()-$add_time<900){
	        return '10分钟前';
	    }else if(time()-$add_time>=900 && time()-$add_time<1200){
	        return '15分钟前';
	    }else if(time()-$add_time>=1200 && time()-$add_time<1500){
	        return '20分钟前';
	    }else if(time()-$add_time>=1500 && time()-$add_time<1800){
	        return '25分钟前';
	    }else if(time()-$add_time>=1800 && time()-$add_time<3600){
	        return '30分钟前';
	    }else if(time()-$add_time>=3600 && time()-$add_time<7200){
	        return '1小时前';
	    }else if(time()-$add_time>=7200 && time()-$add_time<10800){
	        return '2小时前';
	    }else{
	        return date('m-d H:i',$add_time);
	    }
	}
}
?>