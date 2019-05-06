<?php
namespace Admin\Controller;
use Think\Controller;

class WechatmanagerController extends BaseController {
    public function menu(){
        
        if(IS_POST){
            $post_menu = $_POST['menu'];
            //查询数据库是否存在
            $menu_list = M('wx_menu')->getField('id',true);
            foreach($post_menu as $k=>$v){
                if(in_array($k,$menu_list)){
                    //更新
                    M('wx_menu')->where(array('id'=>$k))->save($v);
                }else{
                    //插入
                    M('wx_menu')->where(array('id'=>$k))->add($v);
                }
            }
            $this->success('操作成功,进入发布步骤',U('Admin/Wechatmanager/pub_menu'));
            exit;
        }
        $max_id = M('wx_menu')->order('id desc')->find()['id'];
        //获取父级菜单
        $p_menus = M('wx_menu')->where(array('pid'=>0))->order('sort ASC')->select();
        $p_menus = $this->convert_arr_key($p_menus,'id');
        //获取二级菜单
        $c_menus = M('wx_menu')->where(array('pid'=>array('gt',0)))->order('sort ASC')->select();
        $c_menus = $this->convert_arr_key($c_menus,'id');
        $this->assign('p_lists',$p_menus);
        $this->assign('c_lists',$c_menus);
        $this->assign('max_id',$max_id ? $max_id : 0);
        $this->show();
    }
    
    /**
     * @param $arr
     * @param $key_name
     * @return array
     * 将数据库中查出的列表以指定的 id 作为数组的键名
     */
    function convert_arr_key($arr, $key_name)
    {
        $arr2 = array();
        foreach($arr as $key => $val){
            $arr2[$val[$key_name]] = $val;
        }
        return $arr2;
    }
    /*
     * 删除菜单
     */
    public function del_menu(){
        $id = I('get.id');
        if(!$id){
            exit('fail');
        }
        $row = M('wx_menu')->where(array('id'=>$id))->delete();
        $row && M('wx_menu')->where(array('pid'=>$id))->delete(); //删除子类
        if($row){
            exit('success');
        }else{
            exit('fail');
        }
    }
    
    /*
     * 生成微信菜单
     */
    public function pub_menu(){
        $menu = array();
        $menu['button'][] = array(
            'name'=>'测试',
            'type'=>'view',
            'url'=>'http://wwwtp-shhop.cn'
        );
        $menu['button'][] = array(
            'name'=>'测试',
            'sub_button'=>array(
                array(
                    "type"=> "scancode_waitmsg",
                    "name"=> "系统拍照发图",
                    "key"=> "rselfmenu_1_0",
                    "sub_button"=> array()
                )
            )
        );
        
        //获取菜单
        $appId = "";
        $appSecret = "";
        //获取父级菜单
        $p_menus = M('wx_menu')->where(array('pid'=>0))->order('sort ASC')->select();
        $p_menus = $this->convert_arr_key($p_menus,'id');
        
        $post_str = $this->convert_menu($p_menus);
        // http post请求
        if(!count($p_menus) > 0){
            $this->error('没有菜单可发布',U('Wechatmanager/menu'));
            exit;
        }
        $access_token = $this->get_access_token($appId,$appSecret);
        if(!$access_token){
            $this->error('获取access_token失败',U('Wechatmanager/menu')); //  http://www.tpshop.com/index.php/Admin/Wechat/menu
            
            exit;
        }
        $url ="https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
        //        exit($post_str);
        $return = httpRequest($url,'POST',$post_str);
        $return = json_decode($return,1);
        if($return['errcode'] == 0){
            $this->success('菜单已成功生成',U('Wechatmanager/menu'));
        }else{
            echo "错误代码;".$return['errcode'];
            exit;
        }
    }
    
    public function get_access_token($appId, $appSecret) {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
        $res = json_decode($this->httpGet($url));
        $access_token = $res->access_token;
        return $access_token;
    }
    
    public function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        
        return $res;
    }
    //菜单转换
    private function convert_menu($p_menus){
        $key_map = array(
            'scancode_waitmsg'=>'rselfmenu_0_0',
            'scancode_push'=>'rselfmenu_0_1',
            'pic_sysphoto'=>'rselfmenu_1_0',
            'pic_photo_or_album'=>'rselfmenu_1_1',
            'pic_weixin'=>'rselfmenu_1_2',
            'location_select'=>'rselfmenu_2_0',
        );
        $new_arr = array();
        $count = 0;
        $appId = "";
        foreach($p_menus as $k => $v){
            $new_arr[$count]['name'] = $v['name'];
            
            //获取子菜单
            $c_menus = M('wx_menu')->where(array('pid'=>$k))->select();
            
            if($c_menus){
                foreach($c_menus as $kk=>$vv){
                    $add = array();
                    $add['name'] = $vv['name'];
                    $add['type'] = $vv['type'];
                    // click类型
                    if($add['type'] == 'click'){
                        $add['key'] = $vv['value'];
                    }elseif($add['type'] == 'view'){
                        $add['url'] = $vv['value'];
                    }elseif($add['type'] == 'miniprogram'){
                        $add['url'] = $vv['value'];
                        $add['appid'] = $appId;
                        $add['pagepath'] = '';
                    }else{
                        //$add['key'] = $key_map[$add['type']];
                        $add['key'] = $vv['value'];       //2016年9月29日01:28:37  QQ  海南大卫照明  367013672  提供
                    }
                    $add['sub_button'] = array();
                    if($add['name']){
                        $new_arr[$count]['sub_button'][] = $add;
                    }
                }
            }else{
                $new_arr[$count]['type'] = $v['type'];
                // click类型
                if($new_arr[$count]['type'] == 'click'){
                    $new_arr[$count]['key'] = $v['value'];
                }elseif($new_arr[$count]['type'] == 'view'){
                    //跳转URL类型
                    $new_arr[$count]['url'] = $v['value'];
                }else{
                    //其他事件类型
                    //$new_arr[$count]['key'] = $key_map[$v['type']];
                    $new_arr[$count]['key'] = $v['value'];  //2016年9月29日01:40:13
                }
            }
            $count++;
        }
        // return json_encode(array('button'=>$new_arr));
        return json_encode(array('button'=>$new_arr),JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * auto_back  自动回复配置
     */
    public function auto_back(){
        $this->show();
    }
    /**
     * ajax_get_auto_back 自动回复配置数据
     */
    public function ajax_get_auto_back() {
        $map = array(
            'deleted' => 0,
        );
        $page = I('page');
        $limit = I('limit');
        if ($page == ""
            || $limit == "") {
                exit;
            }
            $start_index = ((int) $page - 1) * ((int) $limit);
            $vip_order = M('wechat_callback_msg')->limit($start_index, $limit)->where($map)->order('add_time desc')->select();
            if ($vip_order === false) {
                $this->_err_ret();
            }
            $count = M('wechat_callback_msg')->where($map)->count();
            $this->_tb_suc_ret($vip_order, $count);
    }
    /**
     * [ajax_add_auto_back 添加自动回复]
     * @return [type] [description]
     */
    public function ajax_add_auto_back() {
        $merchant = session('merchant');
        $auto_back = I('auto_back');
        if ($auto_back == '') {
            exit();
        }
        $auto_back = json_decode(urldecode($auto_back), true);
        $data = array(
            'msg' => $auto_back['msg'],
            'callback_msg' => $auto_back['callback_msg'],
            'add_time' => date('Y-m-d H:i:s'),
            'deleted' => 0,
        );
        $res = M('wechat_callback_msg')->add($data);
        if ($res) {
            //$this->insertMerchantUserLog("添加分类ID：".$res);
        }
        $this->_suc_ret();
    }
    /**
     * [ajax_edit_auto_back 编辑自动回复]
     * @return [type] [description]
     */
    public function ajax_edit_auto_back() {
        $merchant = session('merchant');
        $auto_back = I('auto_back');
        if ($auto_back == '') {
            exit();
        }
        $auto_back = json_decode(urldecode($auto_back), true);
        $data = array(
            'msg' => $auto_back['msg'],
            'callback_msg' => $auto_back['callback_msg'],
            'id' => $auto_back['id'],
        );
        $res = M('wechat_callback_msg')->save($data);
        if ($res) {
            //$this->insertMerchantUserLog("修改分类ID：".$id);
        }
        $this->_suc_ret();
    }
    /**
     * [ajax_delete_auto_back 删除自动回复]
     * @return [type] [description]
     */
    public function ajax_delete_auto_back() {
        $merchant = session('merchant');
        $id = I('id');
        if ($id == '') {
            $this->_err_ret('参数不完整');
        }
        $data = array(
            'id' => $id,
        );
        $res = M('wechat_callback_msg')->where($data)->delete();
        if ($res) {
            //$this->insertMerchantUserLog("删除分类ID：".$id);
        }
        $this->_suc_ret();
    }
    /**
     * wechat_msg_log 推送记录
     */
    public function wechat_msg_log(){
        $this->show();
    }
    /**
     * ajax_get_wechat_msg_log 推送记录数据
     */
    public function ajax_get_wechat_msg_log() {
        $map = array(
            'deleted' => 0,
        );
        $name = I('name');
        if($name != ''){
            $map['user_id'] = $name;
        }
        $page = I('page');
        $limit = I('limit');
        if ($page == ""
            || $limit == "") {
                exit;
            }
        $start_index = ((int) $page - 1) * ((int) $limit);
        $msg_list = M('wechat_msg_log')->limit($start_index, $limit)->where($map)->order('add_time desc')->select();
        if ($msg_list === false) {
            $this->_err_ret();
        }
        $count = M('wechat_msg_log')->where($map)->count();
        foreach($msg_list as $key=>$val){
            $map = array(
                'id'=>$val['user_id'],
                'deleted'=>0
            );
            $user_info = M('user')->where($map)->find();
            if($user_info){
                $msg_list[$key]['nickname'] = $val['user_id'].'-'.$user_info['nickname'];
            }
        }
        $this->_tb_suc_ret($msg_list, $count);
    }
    /**
     * wechat_msg_config  引导语推送
     */
    public function wechat_msg_config(){
        $map = array(
            'deleted'=>0,
            'id'=>1
        );
        $wechat_msg_index = M('wechat_msg_index')->where($map)->find();
        $this->assign('wechat_msg_index',$wechat_msg_index);
        $this->show();
    }
    public function edit_wechat_msg_config(){
        
        $lottery_config = I('lottery_config');
        if ($lottery_config == '') {
            exit();
        }
        $lottery_config = json_decode(urldecode($lottery_config), true);
        $data = array(
            'id' => 1,
            'content' => $lottery_config['content'],
            'is_open' => $lottery_config['is_open'],
        );
        $res = M('wechat_msg_index')->save($data);
        if (!$res) {
            $this->_err_ret('编辑失败');
        }
        
        $this->_suc_ret();
    }
    
    public function edit_wechat_msg_config_push(){
        $lottery_config = I('lottery_config');
        if ($lottery_config == '') {
            exit();
        }
        $lottery_config = json_decode(urldecode($lottery_config), true);
        $data = array(
            'id' => 1,
            'content' => $lottery_config['content'],
            'is_open' => $lottery_config['is_open'],
        );
        $res = M('wechat_msg_index')->save($data);
//         if (!$res) {
//             $this->_err_ret('编辑失败');
//         }
        
        $appId = "";
        $appSecret = "3d917eabbf544b099d23bdd7190c108e";
        $res = $this->getAccessToken($appId, $appSecret);
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $res;
        $user_map = array(
            'deleted'=>0,
            'forbidden'=>0,
            'openid'=>array('neq',''),
        );
        $push_users = M('user')->where($user_map)->select();
//         $push_users = array(
//             array('openid'=>'oKFOO1iGQTFOkFXobxHL0pGk3ai0'),
//             array('openid'=>'oKFOO1hc7P90MGMU39zaIgYmJh0k'),
//             array('openid'=>'oKFOO1uM3vxpZcEP6SlTNsbBEly4'),
//         );
//         $return = array();
        foreach($push_users as $key=>$val){
            $postData = array(
                "touser"=>$val['openid'],
                "msgtype"=>"text",
                "text"=>array("content"=>$lottery_config['content']),
            );
            $data = '{
                        "touser":"'.$val['openid'].'",
                        "msgtype":"text",
                        "text":
                        {
                             "content":"'.$lottery_config['content'].'"
                        }
                    }';
            //$data = json_encode($postData);
            //$res = $this->https_request($url, urldecode($data));
            $res = $this->http_request($url, $data);
//             $return[] = $res;
        }
        $this->_suc_ret();
    }
}