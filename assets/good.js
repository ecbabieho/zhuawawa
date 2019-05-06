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
		good:{
			type:cc.Sprite,
			default:null,
		},
		good_name:{
			type:cc.Label,
			default:null,
		},
	},

	// LIFE-CYCLE CALLBACKS:

	onLoad () {},
	setData(name,url){
		var that = this;
		this.good_name.string = name;
		cc.loader.load(url,function (err, texture) {
		   var spriteFrame = new cc.SpriteFrame(texture);
		   that.good.spriteFrame = spriteFrame;
	   });
	},
	start () {

	},

	// update (dt) {},
});
