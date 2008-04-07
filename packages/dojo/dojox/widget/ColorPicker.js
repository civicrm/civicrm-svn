/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.widget.ColorPicker"]){
dojo._hasResource["dojox.widget.ColorPicker"]=true;
dojo.provide("dojox.widget.ColorPicker");
dojo.experimental("dojox.widget.ColorPicker");
dojo.require("dijit.form._FormWidget");
dojo.require("dojo.dnd.move");
dojo.require("dojo.fx");
dojo.declare("dojox.widget.ColorPicker",dijit.form._FormWidget,{showRgb:true,showHsv:true,showHex:true,webSafe:true,animatePoint:true,slideDuration:250,_underlay:dojo.moduleUrl("dojox.widget","ColorPicker/images/underlay.png"),templateString:"<div class=\"dojoxColorPicker\">\n\t<div class=\"dojoxColorPickerBox\">\n\t\t<div dojoAttachPoint=\"cursorNode\" class=\"dojoxColorPickerPoint\"></div>\n\t\t<img dojoAttachPoint=\"colorUnderlay\" dojoAttachEvent=\"onclick: _setPoint\" class=\"dojoxColorPickerUnderlay\" src=\"${_underlay}\">\n\t</div>\n\t<div class=\"dojoxHuePicker\">\n\t\t<div dojoAttachPoint=\"hueCursorNode\" class=\"dojoxHuePickerPoint\"></div>\n\t\t<div dojoAttachPoint=\"hueNode\" class=\"dojoxHuePickerUnderlay\" dojoAttachEvent=\"onclick: _setHuePoint\"></div>\n\t</div>\n\t<div dojoAttachPoint=\"previewNode\" class=\"dojoxColorPickerPreview\"></div>\n\t<div dojoAttachPoint=\"safePreviewNode\" class=\"dojoxColorPickerWebSafePreview\"></div>\n\t<div class=\"dojoxColorPickerOptional\">\n\t\t<div class=\"dijitInline dojoxColorPickerRgb\" dojoAttachPoint=\"rgbNode\">\n\t\t\t<table>\n\t\t\t<tr><td>r</td><td><input dojoAttachPoint=\"Rval\" size=\"1\"></td></tr>\n\t\t\t<tr><td>g</td><td><input dojoAttachPoint=\"Gval\" size=\"1\"></td></tr>\n\t\t\t<tr><td>b</td><td><input dojoAttachPoint=\"Bval\" size=\"1\"></td></tr>\n\t\t\t</table>\n\t\t</div>\n\t\t<div class=\"dijitInline dojoxColorPickerHsv\" dojoAttachPoint=\"hsvNode\">\n\t\t\t<table>\n\t\t\t<tr><td>h</td><td><input dojoAttachPoint=\"Hval\"size=\"1\"> &deg;</td></tr>\n\t\t\t<tr><td>s</td><td><input dojoAttachPoint=\"Sval\" size=\"1\"> %</td></tr>\n\t\t\t<tr><td>v</td><td><input dojoAttachPoint=\"Vval\" size=\"1\"> %</td></tr>\n\t\t\t</table>\n\t\t</div>\n\t\t<div class=\"dojoxColorPickerHex\" dojoAttachPoint=\"hexNode\">\t\n\t\t\thex: <input dojoAttachPoint=\"hexCode, focusNode\" size=\"6\" class=\"dojoxColorPickerHexCode\">\n\t\t</div>\n\t</div>\n</div>\n",postCreate:function(){
if(dojo.isIE&&dojo.isIE<7){
this.colorUnderlay.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+this._underlay+"', sizingMethod='scale')";
this.colorUnderlay.src=dojo.moduleUrl("dojo","resources/blank.gif").toString();
}
if(!this.showRgb){
this.rgbNode.style.display="none";
}
if(!this.showHsv){
this.hsvNode.style.display="none";
}
if(!this.showHex){
this.hexNode.style.display="none";
}
if(!this.webSafe){
this.safePreviewNode.style.display="none";
}
},startup:function(){
this._offset=0;
this._mover=new dojo.dnd.Moveable(this.cursorNode,{mover:dojo.dnd.boxConstrainedMover({t:0,l:0,w:150,h:150})});
this._hueMover=new dojo.dnd.Moveable(this.hueCursorNode,{mover:dojo.dnd.boxConstrainedMover({t:0,l:0,w:0,h:150})});
dojo.subscribe("/dnd/move/stop",dojo.hitch(this,"_clearTimer"));
dojo.subscribe("/dnd/move/start",dojo.hitch(this,"_setTimer"));
this._sc=(1/dojo.coords(this.colorUnderlay).w);
this._hueSc=(255/(dojo.coords(this.hueNode).h+this._offset));
this._updateColor();
},_setTimer:function(_1){
this._timer=setInterval(dojo.hitch(this,"_updateColor"),45);
},_clearTimer:function(_2){
clearInterval(this._timer);
this.onChange(this.value);
},_setHue:function(h){
var _4=dojo.colorFromArray(this._hsv2rgb(h,1,1,{inputRange:1})).toHex();
dojo.style(this.colorUnderlay,"backgroundColor",_4);
},_updateColor:function(){
var h=Math.round((255+(this._offset))-((dojo.style(this.hueCursorNode,"top")+this._offset)*this._hueSc));
var s=Math.round((dojo.style(this.cursorNode,"left")*this._sc)*100);
var v=Math.round(100-(dojo.style(this.cursorNode,"top")*this._sc)*100);
if(h!=this._hue){
this._setHue(h);
}
var _8=this._hsv2rgb(h,s/100,v/100,{inputRange:1});
var _9=(dojo.colorFromArray(_8).toHex());
this.previewNode.style.backgroundColor=_9;
if(this.webSafe){
this.safePreviewNode.style.backgroundColor=_9;
}
if(this.showHex){
this.hexCode.value=_9;
}
if(this.showRgb){
this.Rval.value=_8[0];
this.Gval.value=_8[1];
this.Bval.value=_8[2];
}
if(this.showHsv){
this.Hval.value=Math.round((h*360)/255);
this.Sval.value=s;
this.Vval.value=v;
}
this.value=_9;
if(!this._timer&&!(arguments[1])){
this.setValue(this.value);
this.onChange(this.value);
}
},_setHuePoint:function(_a){
if(this.animatePoint){
dojo.fx.slideTo({node:this.hueCursorNode,duration:this.slideDuration,top:_a.layerY,left:0,onEnd:dojo.hitch(this,"_updateColor")}).play();
}else{
dojo.style(this.hueCursorNode,"top",(_a.layerY)+"px");
this._updateColor(false);
}
},_setPoint:function(_b){
if(this.animatePoint){
dojo.fx.slideTo({node:this.cursorNode,duration:this.slideDuration,top:_b.layerY-this._offset,left:_b.layerX-this._offset,onEnd:dojo.hitch(this,"_updateColor")}).play();
}else{
dojo.style(this.cursorNode,"left",(_b.layerX-this._offset)+"px");
dojo.style(this.cursorNode,"top",(_b.layerY-this._offset)+"px");
this._updateColor(false);
}
},_hsv2rgb:function(h,s,v,_f){
if(dojo.isArray(h)){
if(s){
_f=s;
}
v=h[2]||0;
s=h[1]||0;
h=h[0]||0;
}
var opt={inputRange:(_f&&_f.inputRange)?_f.inputRange:[255,255,255],outputRange:(_f&&_f.outputRange)?_f.outputRange:255};
switch(opt.inputRange[0]){
case 1:
h=h*360;
break;
case 100:
h=(h/100)*360;
break;
case 360:
h=h;
break;
default:
h=(h/255)*360;
}
if(h==360){
h=0;
}
switch(opt.inputRange[1]){
case 100:
s/=100;
break;
case 255:
s/=255;
}
switch(opt.inputRange[2]){
case 100:
v/=100;
break;
case 255:
v/=255;
}
var r=null;
var g=null;
var b=null;
if(s==0){
r=v;
g=v;
b=v;
}else{
var _14=h/60;
var i=Math.floor(_14);
var f=_14-i;
var p=v*(1-s);
var q=v*(1-(s*f));
var t=v*(1-(s*(1-f)));
switch(i){
case 0:
r=v;
g=t;
b=p;
break;
case 1:
r=q;
g=v;
b=p;
break;
case 2:
r=p;
g=v;
b=t;
break;
case 3:
r=p;
g=q;
b=v;
break;
case 4:
r=t;
g=p;
b=v;
break;
case 5:
r=v;
g=p;
b=q;
break;
}
}
switch(opt.outputRange){
case 1:
r=dojo.math.round(r,2);
g=dojo.math.round(g,2);
b=dojo.math.round(b,2);
break;
case 100:
r=Math.round(r*100);
g=Math.round(g*100);
b=Math.round(b*100);
break;
default:
r=Math.round(r*255);
g=Math.round(g*255);
b=Math.round(b*255);
}
return [r,g,b];
}});
}
