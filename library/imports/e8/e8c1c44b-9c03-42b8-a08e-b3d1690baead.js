"use strict";
cc._RF.push(module, 'e8c1cRLnANCuKCOs9FpC66t', 'good');
// good.js

"use strict";

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
		good: {
			type: cc.Sprite,
			default: null
		},
		good_name: {
			type: cc.Label,
			default: null
		}
	},

	// LIFE-CYCLE CALLBACKS:

	onLoad: function onLoad() {},
	setData: function setData(name, url) {
		var that = this;
		this.good_name.string = name;
		cc.loader.load(url, function (err, texture) {
			var spriteFrame = new cc.SpriteFrame(texture);
			that.good.spriteFrame = spriteFrame;
		});
	},
	start: function start() {}
}

// update (dt) {},
);

cc._RF.pop();