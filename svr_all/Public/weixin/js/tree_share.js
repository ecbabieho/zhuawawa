
/*var share_data = {
	title:"",
	desc:"",
	imgUrl:"",
	link:"",
}*/
function fill_share_data_aaa(share_data){
alert(share_data['link'])
	wx.ready(function(){
		//朋友圈
		wx.onMenuShareTimeline({
			title: share_data['title'],
			link: share_data['link'],
			imgUrl: share_data['imgUrl'],
			success: function () {
				share_callback();
			},
			cancel: function () {

			}
		});
		
		//好友
		wx.onMenuShareAppMessage({
			title:share_data['title'],
			desc: share_data['desc'],
			link: share_data['link'],
			imgUrl:share_data['imgUrl'],
			type: 'link',
			dataUrl: '',
			success: function () {
				share_callback();
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

        wx.getLocation({
            type:"gcj02",
            success:function(res){
                var latitude = res.latitude;
                var longitude = res.longitude;
                var speed = res.speed;
                var accuracy = res.accuracy;
                if(typeof(location_call_back) != "undefined"){
                	location_call_back(latitude,longitude,speed,accuracy);
                }
            	
            }
        })
	});
}


