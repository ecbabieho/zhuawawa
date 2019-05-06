"use strict";
cc._RF.push(module, 'aa21dQ/CZ1OurlI1XXO6pNt', 'result');
// result.js

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
        msg: cc.Label,
        good: cc.Sprite,
        audio_click: {
            url: cc.AudioClip,
            default: null
        },
        gameCallback: null
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},
    setData: function setData(msg, url, game_callback) {
        var that = this;
        this.msg.string = msg;
        this.gameCallback = game_callback;
        cc.loader.load(url, function (err, texture) {
            var spriteFrame = new cc.SpriteFrame(texture);
            that.good.spriteFrame = spriteFrame;
        });
    },
    start: function start() {},
    ok: function ok() {
        cc.audioEngine.play(this.audio_click, false);
        this.node.destroy();
        this.gameCallback();
    }
}
// update (dt) {},
);

cc._RF.pop();