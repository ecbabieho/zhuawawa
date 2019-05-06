<?php
namespace Wechat\Model;
use Think\Model\RelationModel;

class UserModel extends RelationModel {
	protected $trueTableName = 'user';
	protected $_link = array(
	);
	/**
	 * [findUserById 按照ID查询用户]
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function findUserById($id) {
		$user = $this->relation(true)->find($id);
		return $user;
	}
	/**
	 * [addWechatUserNoExist 添加微信用户]
	 * @param [type] $data [description]
	 */
	public function addWechatUserNoExist($data) {
		$map = array(
			'openid' => $data->openid,
		);
		$user = $this->where($map)->relation(true)->find();
		if(!$data->unionid){
		    $data->unionid = '';
		}
		if ($user) {
			// 更新下用户信息
			$data = array(
				'id' => $user['id'],
				'openid' => $data->openid,
				'nickname' => $data->nickname,
				'sex' => $data->sex,
				'headimgurl' => $data->headimgurl,
				'province' => $data->province,
				'city' => $data->city,
				'country' => $data->country,
			    'privilege' => json_encode($data->privilege),
			    'unionid' => $data->unionid,
			);
			$res = $this->save($data);
			if ($res === false) {
				return false;
			}
			$user = $this->findUserById($user['id']);

			return $user;
		}
		$data = array(
			'add_time' => date('Y-m-d H:i:s', time()),
			'openid' => $data->openid,
			'nickname' => $data->nickname,
			'sex' => $data->sex,
			'headimgurl' => $data->headimgurl,
			'province' => $data->province,
			'city' => $data->city,
			'country' => $data->country,
		    'privilege' => json_encode($data->privilege),
		    'unionid' => $data->unionid,
		);
		$res = $this->add($data);
		if (!$res) {
			return false;
		}
		$user = $this->findUserById($res);
		return $user;
	}
}
?>