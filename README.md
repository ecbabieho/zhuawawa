# zhuawawa
在线抓娃娃客户端cocoscreator源码
# 包含更新的服务端Php源码 在svr_all目录中
### 服务端配置配置方法：

## 按照如下步骤进行配置即可：

### 数据库配置文件：
    在Application/Common/Conf/config.php中填写以下字段：
```
    1.DB_NAME     数据库名称
    2.DB_USER     数据库用户名
    3.DB_PWD      数据库密码
    4.DB_PORT     数据库端口号
```

### 公众号/支付等信息配置：
    在Application文件夹中的文件中全局搜索替换以下变量为自行申请的变量值即可：
```
    appId       微信公众号appId 
    mch_id      微信支付商户ID
    appSecret   微信公众号秘钥
    ip          服务端IP
```
#### 上面的变量写的比较垃圾，遍地都是，有空可以改改，改到配置文件里面去
    管理系统地址：
```
https://www.domain.com/index.php/Admin
默认账号：18729292929
默认密码：1
```
### 推荐配置：
```
cetos6.8+lamp
```
# 产品模块目录 
## svr_all/Application/Wechat目录为微信公众号文件目录 svr_all/Application/Admin为管理系统文件目录 其他目录结构请参考Thinkphp目录结构
## 抓娃娃
### 1.代理分销在Agent Controller下
#### 代理商赠送会员/金币,查看已邀请下级，充值会员，所赚返现，提现等。
### 2.活动在Activity Controller下

#### 宝箱活动（box_game），广发银行办信用卡活动(guangfa_act)，种水果活动(lvguonongchang_zyf)
### 3.抓娃娃主模块:品牌活动领取金币（act）、收货地址(address)、背包（bag）、充值(charge)、普通场（game）、黄金场（game_gloden）、钻石场（game_diamond）、娃娃列表（index）、邀请海报（invite）、领养娃娃游戏（lingyang_wawa）、赠送好友娃娃（send_friend）、会员充值(vip_charge)等。
## 线下一卡通会员
### 1.会员卡在Vip下，包含商户列表(index)、商户搜索(search)、商户简介(shop_detail)、会员续费(recharge)、个人中心(personal)、商户入驻申请(ruzhu)、商户活动(act_detail)、每日签到得金币(sign)等。
## 社区
### 社区包含发帖评论，结合抓娃娃里面的有索要娃娃发帖
#### 1.社区在Community Controller下
