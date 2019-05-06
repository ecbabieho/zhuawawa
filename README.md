# zhuawawa
在线抓娃娃客户端cocoscreator源码
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
