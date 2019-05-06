<?php
namespace Wechat\Controller;
use Qcloud\Sms\SmsSingleSender;
use Think\Controller;

class ActController extends AppController {
	public function send_tel_sms() {

		$userModel = M('user');
		$map = array(
			'phone' => array('neq', ""),
			//'tel'=>'15091762578',
		);
		$users = $userModel->where($map)->select();
		$data = array(
			'suc_num' => 0,
			'fail_num' => 0,
			'result' => array(),
		);
		//$this->_suc_ret($users);
		foreach ($users as $key => $value) {
			$res = $this->send_sms($value['phone']);
			if ($res === "success") {
				$data['suc_num'] = $data['suc_num'] + 1;
			} else {
				$data['fail_num'] = $data['fail_num'] + 1;
			}
			$result_tmp = array(
				'tel' => $value['tel'],
				'res' => $res,
			);
			array_push($data['result'], $result_tmp);
		}

		$this->_suc_ret($data);
	}
	public function send_sms($tel) {
		require "qcloudsms_php/src/index.php";
		// 短信应用SDK AppID
		$appid = 1400161610; // 1400开头
		// 短信应用SDK AppKey
		$appkey = "ad7ed4540d56145da3a9770a0dcb28b2";
		// 需要发送短信的手机号码
		$phoneNumbers = [$tel];
		// 短信模板ID，需要在短信应用中申请
		$templateId = 180995; // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请
		$smsSign = "哐糖"; // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`
		try {
			$ssender = new SmsSingleSender($appid, $appkey);
			$params = [];
			$result = $ssender->sendWithParam("86", $phoneNumbers[0], $templateId,
				$params, $smsSign, "", ""); // 签名参数未提供或者为空时，会使用默认签名发送短信
			//$this->_err_ret($result);
			$rsp = json_decode($result, true);
			if ($rsp['result'] == 0) {
				return "success";
			} else {
				return $result;
			}
		} catch (\Exception $e) {
			return "发送失败~";
		}
	}
	public function guangfa_act() {
		// 获取总用户数
		$map = array(
			'deleted' => 0,
			'status' => 1,
		);
		$bank_user_count = M('bank_user')->where($map)->count();
		if (!$bank_user_count) {
			$bank_user_count = 0;
		}
		$this->assign('user_count', $bank_user_count);
		// 获取当前赠送糖豆数量
		if ($bank_user_count == 0) {
			$this->assign('total_coin', C('BANK_USER_COIN'));
		} else {
			$total_coin = 0;
			if ($bank_user_count <= 50) {
				$total_coin = $bank_user_count * C('BANK_USER_COIN');
			} else {
				$total_coin = $bank_user_count * (C('BANK_USER_COIN') + C('BANK_USER_COIN_MORE'));
			}
			$total_coin = $total_coin / $bank_user_count;
			$this->assign('total_coin', $total_coin);
		}
		$this->show();
	}
	public function ajax_sub_xinyongka_act() {

		$real_name = I('real_name');
		$tel = I('tel');
		$company_name = I('company_name');
		$company_tel = I('company_tel');
		$type = I('type');
		$data = array(
			'add_time' => date('Y-m-d H:i:s'),
			'deleted' => 0,
			'real_name' => $real_name,
			'tel' => $tel,
			'company_name' => $company_name,
			'company_tel' => $company_tel,
			'company_address' => '',
			'type' => $type,
			'status' => 0,
			'user_id' => 571,
		);
		$res = M('bank_user')->add($data);
		if (!$res) {
			$this->_err_ret('提交失败');
		}
		$this->_suc_ret();
	}
}