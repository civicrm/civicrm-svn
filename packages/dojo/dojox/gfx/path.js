/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.gfx.path"]){
dojo._hasResource["dojox.gfx.path"]=true;
dojo.provide("dojox.gfx.path");
dojo.require("dojox.gfx.shape");
dojo.declare("dojox.gfx.path.Path",dojox.gfx.Shape,{constructor:function(_1){
this.shape=dojo.clone(dojox.gfx.defaultPath);
this.segments=[];
this.absolute=true;
this.last={};
this.rawNode=_1;
},setAbsoluteMode:function(_2){
this.absolute=typeof _2=="string"?(_2=="absolute"):_2;
return this;
},getAbsoluteMode:function(){
return this.absolute;
},getBoundingBox:function(){
return (this.bbox&&("l" in this.bbox))?{x:this.bbox.l,y:this.bbox.t,width:this.bbox.r-this.bbox.l,height:this.bbox.b-this.bbox.t}:null;
},getLastPosition:function(){
return "x" in this.last?this.last:null;
},_updateBBox:function(x,y){
if(this.bbox&&("l" in this.bbox)){
if(this.bbox.l>x){
this.bbox.l=x;
}
if(this.bbox.r<x){
this.bbox.r=x;
}
if(this.bbox.t>y){
this.bbox.t=y;
}
if(this.bbox.b<y){
this.bbox.b=y;
}
}else{
this.bbox={l:x,b:y,r:x,t:y};
}
},_updateWithSegment:function(_5){
var n=_5.args,l=n.length;
switch(_5.action){
case "M":
case "L":
case "C":
case "S":
case "Q":
case "T":
for(var i=0;i<l;i+=2){
this._updateBBox(n[i],n[i+1]);
}
this.last.x=n[l-2];
this.last.y=n[l-1];
this.absolute=true;
break;
case "H":
for(var i=0;i<l;++i){
this._updateBBox(n[i],this.last.y);
}
this.last.x=n[l-1];
this.absolute=true;
break;
case "V":
for(var i=0;i<l;++i){
this._updateBBox(this.last.x,n[i]);
}
this.last.y=n[l-1];
this.absolute=true;
break;
case "m":
var _9=0;
if(!("x" in this.last)){
this._updateBBox(this.last.x=n[0],this.last.y=n[1]);
_9=2;
}
for(var i=_9;i<l;i+=2){
this._updateBBox(this.last.x+=n[i],this.last.y+=n[i+1]);
}
this.absolute=false;
break;
case "l":
case "t":
for(var i=0;i<l;i+=2){
this._updateBBox(this.last.x+=n[i],this.last.y+=n[i+1]);
}
this.absolute=false;
break;
case "h":
for(var i=0;i<l;++i){
this._updateBBox(this.last.x+=n[i],this.last.y);
}
this.absolute=false;
break;
case "v":
for(var i=0;i<l;++i){
this._updateBBox(this.last.x,this.last.y+=n[i]);
}
this.absolute=false;
break;
case "c":
for(var i=0;i<l;i+=6){
this._updateBBox(this.last.x+n[i],this.last.y+n[i+1]);
this._updateBBox(this.last.x+n[i+2],this.last.y+n[i+3]);
this._updateBBox(this.last.x+=n[i+4],this.last.y+=n[i+5]);
}
this.absolute=false;
break;
case "s":
case "q":
for(var i=0;i<l;i+=4){
this._updateBBox(this.last.x+n[i],this.last.y+n[i+1]);
this._updateBBox(this.last.x+=n[i+2],this.last.y+=n[i+3]);
}
this.absolute=false;
break;
case "A":
for(var i=0;i<l;i+=7){
this._updateBBox(n[i+5],n[i+6]);
}
this.last.x=n[l-2];
this.last.y=n[l-1];
this.absolute=true;
break;
case "a":
for(var i=0;i<l;i+=7){
this._updateBBox(this.last.x+=n[i+5],this.last.y+=n[i+6]);
}
this.absolute=false;
break;
}
var _a=[_5.action];
for(var i=0;i<l;++i){
_a.push(dojox.gfx.formatNumber(n[i],true));
}
if(typeof this.shape.path=="string"){
this.shape.path+=_a.join("");
}else{
var l=_a.length,a=this.shape.path;
for(var i=0;i<l;++i){
a.push(_a[i]);
}
}
},_validSegments:{m:2,l:2,h:1,v:1,c:6,s:4,q:4,t:2,a:7,z:0},_pushSegment:function(_c,_d){
var _e=this._validSegments[_c.toLowerCase()];
if(typeof _e=="number"){
if(_e){
if(_d.length>=_e){
var _f={action:_c,args:_d.slice(0,_d.length-_d.length%_e)};
this.segments.push(_f);
this._updateWithSegment(_f);
}
}else{
var _f={action:_c,args:[]};
this.segments.push(_f);
this._updateWithSegment(_f);
}
}
},_collectArgs:function(_10,_11){
for(var i=0;i<_11.length;++i){
var t=_11[i];
if(typeof t=="boolean"){
_10.push(t?1:0);
}else{
if(typeof t=="number"){
_10.push(t);
}else{
if(t instanceof Array){
this._collectArgs(_10,t);
}else{
if("x" in t&&"y" in t){
_10.push(t.x,t.y);
}
}
}
}
}
},moveTo:function(){
var _14=[];
this._collectArgs(_14,arguments);
this._pushSegment(this.absolute?"M":"m",_14);
return this;
},lineTo:function(){
var _15=[];
this._collectArgs(_15,arguments);
this._pushSegment(this.absolute?"L":"l",_15);
return this;
},hLineTo:function(){
var _16=[];
this._collectArgs(_16,arguments);
this._pushSegment(this.absolute?"H":"h",_16);
return this;
},vLineTo:function(){
var _17=[];
this._collectArgs(_17,arguments);
this._pushSegment(this.absolute?"V":"v",_17);
return this;
},curveTo:function(){
var _18=[];
this._collectArgs(_18,arguments);
this._pushSegment(this.absolute?"C":"c",_18);
return this;
},smoothCurveTo:function(){
var _19=[];
this._collectArgs(_19,arguments);
this._pushSegment(this.absolute?"S":"s",_19);
return this;
},qCurveTo:function(){
var _1a=[];
this._collectArgs(_1a,arguments);
this._pushSegment(this.absolute?"Q":"q",_1a);
return this;
},qSmoothCurveTo:function(){
var _1b=[];
this._collectArgs(_1b,arguments);
this._pushSegment(this.absolute?"T":"t",_1b);
return this;
},arcTo:function(){
var _1c=[];
this._collectArgs(_1c,arguments);
this._pushSegment(this.absolute?"A":"a",_1c);
return this;
},closePath:function(){
this._pushSegment("Z",[]);
return this;
},_setPath:function(_1d){
var p=dojo.isArray(_1d)?_1d:_1d.match(dojox.gfx.pathSvgRegExp);
this.segments=[];
this.absolute=true;
this.bbox={};
this.last={};
if(!p){
return;
}
var _1f="",_20=[],l=p.length;
for(var i=0;i<l;++i){
var t=p[i],x=parseFloat(t);
if(isNaN(x)){
if(_1f){
this._pushSegment(_1f,_20);
}
_20=[];
_1f=t;
}else{
_20.push(x);
}
}
this._pushSegment(_1f,_20);
},setShape:function(_25){
dojox.gfx.Shape.prototype.setShape.call(this,typeof _25=="string"?{path:_25}:_25);
var _26=this.shape.path;
this.shape.path=[];
this._setPath(_26);
this.shape.path=this.shape.path.join("");
return this;
},_2PI:Math.PI*2});
dojo.declare("dojox.gfx.path.TextPath",dojox.gfx.path.Path,{constructor:function(_27){
if(!("text" in this)){
this.text=dojo.clone(dojox.gfx.defaultTextPath);
}
if(!("fontStyle" in this)){
this.fontStyle=dojo.clone(dojox.gfx.defaultFont);
}
},getText:function(){
return this.text;
},setText:function(_28){
this.text=dojox.gfx.makeParameters(this.text,typeof _28=="string"?{text:_28}:_28);
this._setText();
return this;
},getFont:function(){
return this.fontStyle;
},setFont:function(_29){
this.fontStyle=typeof _29=="string"?dojox.gfx.splitFontString(_29):dojox.gfx.makeParameters(dojox.gfx.defaultFont,_29);
this._setFont();
return this;
}});
}
