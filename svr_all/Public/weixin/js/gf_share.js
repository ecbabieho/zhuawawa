
var share_data = {
	title:"",
	desc:"",
	imgUrl:"",
	link:"",
}
function fill_share_data(){
	wx.ready(function(){
		wx.onMenuShareTimeline({
			title: share_data['title'],
			link: share_data['link'],
			imgUrl: share_data['imgUrl'],
			success: function () {

			},
			cancel: function () {

			}
		});
		wx.onMenuShareAppMessage({
			title:share_data['title'],
			desc: share_data['desc'],
			link: share_data['link'],
			imgUrl:share_data['imgUrl'],
			type: 'link',
			dataUrl: '',
			success: function () {
			},
			cancel: function () {
			}
		});
		wx.onMenuShareQQ({
			title:share_data['title'],
			desc: share_data['desc'],
			link: share_data['link'],
			imgUrl:share_data['imgUrl'],
			success: function () {
			},
			cancel: function () {
			}
		});
		wx.onMenuShareWeibo({
			title:share_data['title'],
			desc: share_data['desc'],
			link: share_data['link'],
			imgUrl:share_data['imgUrl'],
			success: function () {
			},
			cancel: function () {
			}
		});
		wx.onMenuShareQZone({
			title:share_data['title'],
			desc: share_data['desc'],
			link: share_data['link'],
			imgUrl:share_data['imgUrl'],
			success: function () {
			},
			cancel: function () {
			}
		});
	});
}
function init_share(){
	var data = {
		url:window.location.href,
	};
	var url = window.location.protocol + "//" + window.location.host + "/index.php/Wechat/App/ajax_get_wx_share_package";
	$.post(url,data,function(ret){
		if (ret['code'] == 0) {
			wx.config({
				debug: false,
				appId: ret['data']['signPackage']['appId'],
				timestamp: ret['data']['signPackage']['timestamp'],
				nonceStr: ret['data']['signPackage']['nonceStr'],
				signature: ret['data']['signPackage']['signature'],
				jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone','hideMenuItems'],
			});
			share_data['title'] = "广发银行免费送糖豆邀请您来抓娃娃啦~";
			share_data['imgUrl'] = window.location.protocol + "//" + window.location.host + "/Public/weixin/image/gf_act/share.png";
			share_data['link'] = ret['data']['signPackage']['url'];
			share_data['desc'] = "免费赠送最少100糖豆！可翻倍！！";
			fill_share_data();
		}
		else{
			alert("获取分享信息失败");
		}
	})
}
$(function(){
	init_share();
})
