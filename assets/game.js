// Learn cc.Class:
//  - [Chinese] http://docs.cocos.com/creator/manual/zh/scripting/class.html
//  - [English] http://www.cocos2d-x.org/docs/creator/en/scripting/class.html
// Learn Attribute:
//  - [Chinese] http://docs.cocos.com/creator/manual/zh/scripting/reference/attributes.html
//  - [English] http://www.cocos2d-x.org/docs/creator/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - [Chinese] http://docs.cocos.com/creator/manual/zh/scripting/life-cycle-callbacks.html
//  - [English] http://www.cocos2d-x.org/docs/creator/en/scripting/life-cycle-callbacks.html

cc.Class({
	extends: cc.Component,

	properties: {
		audio_click:{
			url:cc.AudioClip,
			default:null,
		},
		audio_readygo:{
			url:cc.AudioClip,
			default:null,
		},
		zhua_line:{
			type:cc.Sprite,
			default:null,
		},
		zhuazi:{
			type:cc.Sprite,
			default:null,
		},
		zhua_top:{
			type:cc.Sprite,
			default:null,
		},
		zhuanchang_name:{
			type:cc.Label,
			default:null,
		},
		cost_num:{
			type:cc.Label,
			default:null,
		},
		coin_num:{
			type:cc.Label,
			default:null,
		},
		wawa_prefab:{
			type:cc.Prefab,
			default:null,
		},
		result_prefab:{
			type:cc.Prefab,
			default:null,
		},
		prize:{
			type:cc.Node,
			default:null,
		},
		xiazhua_btn:cc.Button,
		shake_zhuazi_seq:null,
		shake_zhualine_seq:null,
		shake_zhuatop_seq:null,
		shake_wawa_seq:null,
		orign_zhuazi_pos:null,
		orign_zhualine_pos:null,
		orign_zhuatop_pos:null,
		prize_list_layout_pos:null,
		orign_zhualine_scale_y:0,
		prize_bar:{
			type:cc.Sprite,
			default:null,
		},
		prize_list_layout:{
			type:cc.Layout,
			default:null,
		},
		lottery_data:null,
		zhua_click:0,
	},

	// LIFE-CYCLE CALLBACKS:
	shake(){
		// 爪子开始左右晃动
		this.shake_zhuazi_seq = cc.repeatForever(
			cc.sequence(
				cc.moveBy(1, -100, 0),
				cc.moveBy(1, 100, 0),
				cc.moveBy(1, 100, 0),
				cc.moveBy(1, -100, 0),
				));
		this.shake_zhualine_seq = cc.repeatForever(
			cc.sequence(
				cc.moveBy(1, -100, 0),
				cc.moveBy(1, 100, 0),
				cc.moveBy(1, 100, 0),
				cc.moveBy(1, -100, 0),
				));
		this.shake_zhuatop_seq = cc.repeatForever(
			cc.sequence(
				cc.moveBy(1, -100, 0),
				cc.moveBy(1, 100, 0),
				cc.moveBy(1, 100, 0),
				cc.moveBy(1, -100, 0),
				));
		this.zhuazi.node.runAction(this.shake_zhuazi_seq);
		this.zhua_top.node.runAction(this.shake_zhuatop_seq);
		this.zhua_line.node.runAction(this.shake_zhualine_seq);
		this.prize_list_layout.node.setCascadeOpacityEnabled(false);
		this.prize_list_layout.node.opacity = 0;

		var container_width = this.prize_list_layout.node.width;
		console.log(container_width);
		this.shake_wawa_seq = cc.repeatForever(
			cc.sequence(
				cc.moveBy(2, -(container_width/3), 0),
				cc.moveBy(2, container_width/3, 0),
				cc.moveBy(2, container_width/3, 0),
				cc.moveBy(2, -(container_width/3), 0),
				));
		this.prize_list_layout.node.runAction(this.shake_wawa_seq);
	},
	onLoad () {
		//先获取初始位置
		this.orign_zhuazi_pos = this.zhuazi.node.position;
		this.orign_zhuatop_pos = this.zhua_top.node.position;
		this.orign_zhualine_pos = this.zhua_line.node.position;
		this.prize_list_layout_pos = this.prize_list_layout.node.position;
		this.orign_zhualine_height = this.zhua_line.node.height;
		// 获取数据
		this.initData();

	},
	sendHttpReq(url,data,_cb){
		var that = this;
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function () {
			if (xhr.readyState == 4 && (xhr.status >= 200 && xhr.status < 400)) {
				var response = xhr.responseText;
				_cb(JSON.parse(response),response,that);
			}
		};
		xhr.open("POST", url, true);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;");
		var paramArr = [];
		for(var tmp in data){
			paramArr.push(tmp + "=" + data[tmp]);
		}
		var params = paramArr.join("&");
		xhr.send(params);
	},
	getLotteryId(){
		var la = window.location.href;
		var start = la.indexOf("id/");
		var end = la.indexOf(".html");
		var id = la.substr(start+3,end-start -3);
		return id;
	},
	initData(){
		
		var id = this.getLotteryId();
		var url = "https://fssw.bichonfrise.cn/index.php/Wechat/Index/ajax_get_lottery";
		var data = {
			id:id,
		};
		this.sendHttpReq(url,data,function(respObj,resp,gameObj){
			// 设置用户金币
			gameObj.coin_num.string = "金币：" + respObj['data']['user_coin_num'];
			// 设置专场名称
			gameObj.zhuanchang_name.string = respObj['data']['name'];
			gameObj.cost_num.string = respObj['data']['coin_num'] + "金币/次";
			gameObj.lottery_data = respObj;
			// 初始化娃娃
			for (var i = 0; i < parseInt(respObj['data']['good_num']); i++) {
				var wawa = cc.instantiate(gameObj.wawa_prefab);
				wawa.setPosition(cc.p(0,0));
				wawa.getComponent('good').setData(respObj['data']['lottery_good' + i]['name'],
					respObj['data']['lottery_good' + i]['img_url'])
				gameObj.prize_list_layout.node.addChild(wawa);
			}
			setTimeout(function(){
				gameObj.shake();
			},500);
			
		})
		this.shake();
	},
	zhuawawa_go(){
		//cc.audioEngine.play(this.audio_click,false);
		this.zhuazi.node.stopAction(this.shake_zhuazi_seq);
		this.zhua_top.node.stopAction(this.shake_zhuatop_seq);
		this.zhua_line.node.stopAction(this.shake_zhualine_seq);
		this.prize_list_layout.node.stopAction(this.shake_wawa_seq);
		var prize_bar_height = this.prize_bar.node.height;
		var zhuazi_height = this.zhuazi.node.height;
		var target_y = this.prize_bar.node.position.y + prize_bar_height;
		var target_x = this.zhuazi.node.position.x;
		//var zhuazi_action = cc.moveTo(0.1,target_x,target_y);
		//this.zhuazi.node.runAction(zhuazi_action);
		var rotateX = 3;
		var wawaZhantaiHeight = 297;
		target_y += wawaZhantaiHeight;
		target_y -= prize_bar_height;
		var zhuazi_action = cc.sequence(
				cc.moveTo(0.1,target_x,target_y),
				cc.rotateTo(0.1,rotateX),
				cc.rotateTo(0.1,0.0),
				cc.rotateTo(0.1,-rotateX),
				cc.rotateTo(0.1,0.0),
			);
		this.zhuazi.node.runAction(zhuazi_action);
		var that = this;

		// setTimeout(function(){
		// 	that.prize = cc.instantiate(that.wawa_prefab);
		// 	that.prize.setPosition(cc.p(0,-zhuazi_height));
		// 	that.zhuazi.node.addChild(that.prize);
		// 	setTimeout(function(){
		// 		var result = cc.instantiate(that.result_prefab);
		// 		result.setPosition(cc.p(0,0));
		// 		result.getComponent('result').setData("恭喜您抓到了小鹿非彼，已存入背包！","",function(){
		// 			that.resetGame();
		// 		});
		// 		that.node.addChild(result);
		// 	},800)
		// })

		this.orign_zhualine_scale_y = (this.orign_zhualine_pos.y + this.zhua_line.node.height/2 - target_y - zhuazi_height/2)/this.orign_zhualine_height;
		var half_win_height = cc.director.getWinSizeInPixels().height/2;
		var win_fixed = half_win_height - this.orign_zhualine_pos.y
		var target_y = (this.orign_zhualine_pos.y+ this.zhua_line.node.height/2) 
		- (this.orign_zhualine_scale_y*this.zhua_line.node.height)/2;
		var zhualine_x = this.zhua_line.node.x;
		var zhualine_action = cc.spawn(
			cc.scaleTo(0.1,1,this.orign_zhualine_scale_y),
			cc.moveTo(0.1,zhualine_x,target_y)
			);
		this.zhua_line.node.runAction(zhualine_action);
		
		//var zhuazi_action = cc.moveTo(0.1,target_x,target_y);
		//this.zhuazi.node.runAction(zhuazi_action);
		// 请求网络数据
		var url = "https://fssw.bichonfrise.cn/index.php/Wechat/Index/lottery";
		var id = this.getLotteryId();
		var data = {
			id:id,
		}
		var that = this;
		this.sendHttpReq(url,data,function(respObj,resp,gameObj){
			gameObj.coin_num.string = "金币：" + respObj['data']['user_coin_num'];
			if (respObj['data']['good_name'] == "") {
				var result = cc.instantiate(gameObj.result_prefab);
				result.setPosition(cc.p(0,0));
				result.getComponent('result').setData(respObj['data']['msg'],respObj['data']['good_url'],function(){
					that.resetGame();
				})
				gameObj.node.addChild(result);
			}
			else{
				//抓到了
				var result = cc.instantiate(gameObj.result_prefab);
				result.setPosition(cc.p(0,0));
				result.getComponent('result').setData("恭喜您抓到了"+ respObj['data']['good_name'] + "，已存入背包！",respObj['data']['good_url'],function(){
					that.resetGame();
				});
				gameObj.node.addChild(result);
			}
			
		})
	},
	zhuawawa(){
		if (this.zhua_click == 1) {
			return;
		}
		this.zhua_click = 1;
		cc.audioEngine.play(this.audio_readygo,false);

		var that = this;
		var rotateX = 2;
		var zhuazi_action = cc.sequence(
				cc.rotateTo(0.1,rotateX),
				cc.rotateTo(0.1,0.0),
				cc.rotateTo(0.1,-rotateX),
				cc.rotateTo(0.1,0.0),
				cc.rotateTo(0.1,rotateX),
				cc.rotateTo(0.1,0.0),
				cc.rotateTo(0.1,-rotateX),
				cc.rotateTo(0.1,0.0),
				cc.rotateTo(0.1,rotateX),
				cc.rotateTo(0.1,0.0),
			);
		this.zhuazi.node.runAction(zhuazi_action);
		setTimeout(function(){
			that.zhuawawa_go();
		},1200);

	},
	resetGame(){
		cc.audioEngine.play(this.audio_click,false);
		this.zhua_line.node.height = 64;
		this.zhua_line.node.scaleY = 1;
		this.zhuazi.node.position = this.orign_zhuazi_pos;
		this.zhua_top.node.position = this.orign_zhuatop_pos;
		this.zhua_line.node.position = this.orign_zhualine_pos;
		this.prize_list_layout.node.position = this.prize_list_layout_pos;
		this.shake();
		this.zhua_click = 0;
		this.prize.destroy();
	},
	charge(){
		this.resetGame();
		//zhuawawa.charge();
	},
	detail(){
		cc.audioEngine.play(this.audio_click,false);
		zhuawawa.show_detail(this.lottery_data);
	}

	// update (dt) {},
});
