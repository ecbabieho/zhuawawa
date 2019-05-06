var fly = {
	process_interval:5000,		//飘过时间
	reset_interval:6000,		//重置时间
	data:[
	{

		user_name:"silence",
		prize_name:"猪爸爸20cm",
	},
	{

		user_name:"比熊科技...",
		prize_name:"猪妈妈20cm",
	},
	{

		user_name:"Lolita",
		prize_name:"猪爸爸20cm",
	},
	{

		user_name:"比熊科技...",
		prize_name:"小猪佩奇20cm",
	},
	{

		user_name:"明天更好",
		prize_name:"小猪乔治20cm",
	}
	],
	reset:function(){
		var width  = $("body").width();
		$("#flys").attr('style','transition-duration: 0ms; transform: translate3d(' + width + 'px, 0px, 0px);');
	},
	flys:function(){
		var index = parseInt(Math.random()*fly.data.length);
		$("#user_name").text(fly.data[index]['user_name']);
		$("#prize_name").text(fly.data[index]['prize_name']);
		$("#flys").attr('style','transition-duration: ' + fly.process_interval + 'ms; transform: translate3d(-436px, 0px, 0px);transition-timing-function: linear;');
		setTimeout('fly.reset()',fly.process_interval);
	},
	init:function(){
		//获取数据
		$.post("https://fssw.bichonfrise.cn/index.php/Wechat/Index/get_lottery_record",{},function(ret){
			if (ret['code'] == 0 
				&& ret['data']
				&& ret['data'].length != 0) {
				fly.data = ret['data'];
		}
		$("body").append('<div class="flys" id="flys">' + 
			'<img src="https://fssw.bichonfrise.cn/Public/weixin/image/plane.png" class="plane">' + 
			'<span class="notice">' + 
			'<span class="name"  id="user_name">比熊科技🚗悦美经纪人-马超</span>' + 
			'<span class="name">狠狠地抓中了一个</span>' + 
			'<span class="prize" id="prize_name">吃鸡粽子抱枕20cm</span>' + 
			'</span>' + 
			'</div>');
		var width  = $("body").width();
		$("#flys").attr('style','transition-duration: 0ms; transform: translate3d(' + width + 'px, 0px, 0px);');
		setInterval('fly.flys()',fly.reset_interval);
	})
		
	}
}
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
			share_data['title'] = "手机也能抓娃娃啦，新手免费，快来试玩~";
			share_data['imgUrl'] = ret['data']['signPackage']['imgUrl'];
			share_data['link'] = ret['data']['signPackage']['url'];
			share_data['desc'] = "警告：史上最好抓的娃娃，拼手气首选~";
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
