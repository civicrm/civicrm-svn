/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.gfx.silverlight"]){
dojo._hasResource["dojox.gfx.silverlight"]=true;
dojo.provide("dojox.gfx.silverlight");
dojo.require("dojox.gfx._base");
dojo.require("dojox.gfx.shape");
dojo.require("dojox.gfx.path");
dojo.experimental("dojox.gfx.silverlight");
dojox.gfx.silverlight.dasharray={solid:"none",shortdash:[4,1],shortdot:[1,1],shortdashdot:[4,1,1,1],shortdashdotdot:[4,1,1,1,1,1],dot:[1,3],dash:[4,3],longdash:[8,3],dashdot:[4,3,1,3],longdashdot:[8,3,1,3],longdashdotdot:[8,3,1,3,1,3]};
dojox.gfx.silverlight.fontweight={normal:400,bold:700};
dojox.gfx.silverlight.caps={butt:"Flat",round:"Round",square:"Square"};
dojox.gfx.silverlight.joins={bevel:"Bevel",round:"Round"};
dojox.gfx.silverlight.fonts={serif:"Times New Roman",times:"Times New Roman","sans-serif":"Arial",helvetica:"Arial",monotone:"Courier New",courier:"Courier New"};
dojox.gfx.silverlight.hexColor=function(_1){
var c=dojox.gfx.normalizeColor(_1),t=c.toHex(),a=Math.round(c.a*255);
a=(a<0?0:a>255?255:a).toString(16);
return "#"+(a.length<2?"0"+a:a)+t.slice(1);
};
dojo.extend(dojox.gfx.Shape,{setFill:function(_5){
var p=this.rawNode.getHost().content,r=this.rawNode,f;
if(!_5){
this.fillStyle=null;
this._setFillAttr(null);
return this;
}
if(typeof (_5)=="object"&&"type" in _5){
switch(_5.type){
case "linear":
this.fillStyle=f=dojox.gfx.makeParameters(dojox.gfx.defaultLinearGradient,_5);
var _9=p.createFromXaml("<LinearGradientBrush/>");
_9.mappingMode="Absolute";
_9.startPoint=f.x1+","+f.y1;
_9.endPoint=f.x2+","+f.y2;
dojo.forEach(f.colors,function(c){
var t=p.createFromXaml("<GradientStop/>");
t.offset=c.offset;
t.color=dojox.gfx.silverlight.hexColor(c.color);
_9.gradientStops.add(t);
});
this._setFillAttr(_9);
break;
case "radial":
this.fillStyle=f=dojox.gfx.makeParameters(dojox.gfx.defaultRadialGradient,_5);
var _c=p.createFromXaml("<RadialGradientBrush/>"),w=r.width,h=r.height,l=this.rawNode["Canvas.Left"],t=this.rawNode["Canvas.Top"];
_c.center=(f.cx-l)/w+","+(f.cy-t)/h;
_c.radiusX=f.r/w;
_c.radiusY=f.r/h;
dojo.forEach(f.colors,function(c){
var t=p.createFromXaml("<GradientStop/>");
t.offset=c.offset;
t.color=dojox.gfx.silverlight.hexColor(c.color);
_c.gradientStops.add(t);
});
this._setFillAttr(_c);
break;
case "pattern":
this.fillStyle=null;
this._setFillAttr(null);
break;
}
return this;
}
this.fillStyle=f=dojox.gfx.normalizeColor(_5);
var scb=p.createFromXaml("<SolidColorBrush/>");
scb.color=f.toHex();
scb.opacity=f.a;
this._setFillAttr(scb);
return this;
},_setFillAttr:function(f){
this.rawNode.fill=f;
},setStroke:function(_15){
var p=this.rawNode.getHost().content,r=this.rawNode;
if(!_15){
this.strokeStyle=null;
r.stroke=null;
return this;
}
if(typeof _15=="string"){
_15={color:_15};
}
var s=this.strokeStyle=dojox.gfx.makeParameters(dojox.gfx.defaultStroke,_15);
s.color=dojox.gfx.normalizeColor(s.color);
if(s){
var scb=p.createFromXaml("<SolidColorBrush/>");
scb.color=s.color.toHex();
scb.opacity=s.color.a;
r.stroke=scb;
r.strokeThickness=s.width;
r.strokeStartLineCap=r.strokeEndLineCap=r.strokeDashCap=dojox.gfx.silverlight.caps[s.cap];
if(typeof s.join=="number"){
r.strokeLineJoin="Miter";
r.strokeMiterLimit=s.join;
}else{
r.strokeLineJoin=dojox.gfx.silverlight.joins[s.join];
}
var da=s.style.toLowerCase();
if(da in dojox.gfx.silverlight.dasharray){
da=dojox.gfx.silverlight.dasharray[da];
}
if(da instanceof Array){
da=dojo.clone(da);
if(s.cap!="butt"){
for(var i=0;i<da.length;i+=2){
--da[i];
if(da[i]<1){
da[i]=1;
}
}
for(var i=1;i<da.length;i+=2){
++da[i];
}
}
r.strokeDashArray=da.join(",");
}else{
r.strokeDashArray=null;
}
}
return this;
},_getParentSurface:function(){
var _1c=this.parent;
for(;_1c&&!(_1c instanceof dojox.gfx.Surface);_1c=_1c.parent){
}
return _1c;
},_applyTransform:function(){
var tm=this.matrix,r=this.rawNode;
if(tm){
var p=this.rawNode.getHost().content,m=p.createFromXaml("<MatrixTransform/>"),mm=p.createFromXaml("<Matrix/>");
mm.m11=tm.xx;
mm.m21=tm.xy;
mm.m12=tm.yx;
mm.m22=tm.yy;
mm.offsetX=tm.dx;
mm.offsetY=tm.dy;
m.matrix=mm;
r.renderTransform=m;
}else{
r.renderTransform=null;
}
return this;
},setRawNode:function(_22){
_22.fill=null;
_22.stroke=null;
this.rawNode=_22;
},_moveToFront:function(){
var c=this.parent.rawNode.children,r=this.rawNode;
c.remove(r);
c.add(r);
return this;
},_moveToBack:function(){
var c=this.parent.rawNode.children,r=this.rawNode;
c.remove(r);
c.insert(0,r);
return this;
}});
dojo.declare("dojox.gfx.Group",dojox.gfx.Shape,{constructor:function(){
dojox.gfx.silverlight.Container._init.call(this);
},setRawNode:function(_27){
this.rawNode=_27;
}});
dojox.gfx.Group.nodeType="Canvas";
dojo.declare("dojox.gfx.Rect",dojox.gfx.shape.Rect,{setShape:function(_28){
this.shape=dojox.gfx.makeParameters(this.shape,_28);
this.bbox=null;
var r=this.rawNode,n=this.shape;
r["Canvas.Left"]=n.x;
r["Canvas.Top"]=n.y;
r.width=n.width;
r.height=n.height;
r.radiusX=r.radiusY=n.r;
return this;
}});
dojox.gfx.Rect.nodeType="Rectangle";
dojo.declare("dojox.gfx.Ellipse",dojox.gfx.shape.Ellipse,{setShape:function(_2b){
this.shape=dojox.gfx.makeParameters(this.shape,_2b);
this.bbox=null;
var r=this.rawNode,n=this.shape;
r["Canvas.Left"]=n.cx-n.rx;
r["Canvas.Top"]=n.cy-n.ry;
r.width=2*n.rx;
r.height=2*n.ry;
return this;
}});
dojox.gfx.Ellipse.nodeType="Ellipse";
dojo.declare("dojox.gfx.Circle",dojox.gfx.shape.Circle,{setShape:function(_2e){
this.shape=dojox.gfx.makeParameters(this.shape,_2e);
this.bbox=null;
var r=this.rawNode,n=this.shape;
r["Canvas.Left"]=n.cx-n.r;
r["Canvas.Top"]=n.cy-n.r;
r.width=r.height=2*n.r;
return this;
}});
dojox.gfx.Circle.nodeType="Ellipse";
dojo.declare("dojox.gfx.Line",dojox.gfx.shape.Line,{setShape:function(_31){
this.shape=dojox.gfx.makeParameters(this.shape,_31);
this.bbox=null;
var r=this.rawNode,n=this.shape;
r.x1=n.x1;
r.y1=n.y1;
r.x2=n.x2;
r.y2=n.y2;
return this;
}});
dojox.gfx.Line.nodeType="Line";
dojo.declare("dojox.gfx.Polyline",dojox.gfx.shape.Polyline,{setShape:function(_34,_35){
if(_34&&_34 instanceof Array){
this.shape=dojox.gfx.makeParameters(this.shape,{points:_34});
if(_35&&this.shape.points.length){
this.shape.points.push(this.shape.points[0]);
}
}else{
this.shape=dojox.gfx.makeParameters(this.shape,_34);
}
this.box=null;
var p=this.shape.points,rp=[];
for(var i=0;i<p.length;++i){
if(typeof p[i]=="number"){
rp.push(p[i],p[++i]);
}else{
rp.push(p[i].x,p[i].y);
}
}
this.rawNode.points=rp.join(",");
return this;
}});
dojox.gfx.Polyline.nodeType="Polyline";
dojo.declare("dojox.gfx.Image",dojox.gfx.shape.Image,{setShape:function(_39){
this.shape=dojox.gfx.makeParameters(this.shape,_39);
this.bbox=null;
var r=this.rawNode,n=this.shape;
r["Canvas.Left"]=n.x;
r["Canvas.Top"]=n.y;
r.width=n.width;
r.height=n.height;
r.source=n.src;
return this;
},setRawNode:function(_3c){
this.rawNode=_3c;
}});
dojox.gfx.Image.nodeType="Image";
dojo.declare("dojox.gfx.Text",dojox.gfx.shape.Text,{setShape:function(_3d){
this.shape=dojox.gfx.makeParameters(this.shape,_3d);
this.bbox=null;
var r=this.rawNode,s=this.shape;
r.text=s.text;
r.textDecorations=s.decoration=="underline"?"Underline":"None";
r["Canvas.Left"]=-10000;
r["Canvas.Top"]=-10000;
window.setTimeout(dojo.hitch(this,"_delayAlignment"),0);
return this;
},_delayAlignment:function(){
var r=this.rawNode,s=this.shape,w=r.actualWidth,h=r.actualHeight,x=s.x,y=s.y-h*0.75;
switch(s.align){
case "middle":
x-=w/2;
break;
case "end":
x-=w;
break;
}
var a=this.matrix?dojox.gfx.matrix.multiplyPoint(this.matrix,x,y):{x:x,y:y};
r["Canvas.Left"]=a.x;
r["Canvas.Top"]=a.y;
},setStroke:function(){
return this;
},_setFillAttr:function(f){
this.rawNode.foreground=f;
},setRawNode:function(_48){
this.rawNode=_48;
},_applyTransform:function(){
var tm=this.matrix,r=this.rawNode;
if(tm){
tm=dojox.gfx.matrix.normalize([1/100,tm,100]);
var p=this.rawNode.getHost().content,m=p.createFromXaml("<MatrixTransform/>"),mm=p.createFromXaml("<Matrix/>");
mm.m11=tm.xx;
mm.m21=tm.xy;
mm.m12=tm.yx;
mm.m22=tm.yy;
mm.offsetX=tm.dx;
mm.offsetY=tm.dy;
m.matrix=mm;
r.renderTransform=m;
}else{
r.renderTransform=null;
}
return this;
},getTextWidth:function(){
return this.rawNode.actualWidth;
}});
dojox.gfx.Text.nodeType="TextBlock";
dojo.declare("dojox.gfx.Path",dojox.gfx.path.Path,{_updateWithSegment:function(_4e){
dojox.gfx.Path.superclass._updateWithSegment.apply(this,arguments);
var p=this.shape.path;
if(typeof (p)=="string"){
this.rawNode.data=p?p:null;
}
},setShape:function(_50){
dojox.gfx.Path.superclass.setShape.apply(this,arguments);
var p=this.shape.path;
this.rawNode.data=p?p:null;
return this;
}});
dojox.gfx.Path.nodeType="Path";
dojo.declare("dojox.gfx.TextPath",dojox.gfx.path.TextPath,{_updateWithSegment:function(_52){
},setShape:function(_53){
},_setText:function(){
}});
dojox.gfx.TextPath.nodeType="text";
dojo.declare("dojox.gfx.Surface",dojox.gfx.shape.Surface,{constructor:function(){
dojox.gfx.silverlight.Container._init.call(this);
},setDimensions:function(_54,_55){
this.width=dojox.gfx.normalizedLength(_54);
this.height=dojox.gfx.normalizedLength(_55);
var p=this.rawNode&&this.rawNode.getHost();
if(p){
p.width=_54;
p.height=_55;
}
return this;
},getDimensions:function(){
var p=this.rawNode&&this.rawNode.getHost();
var t=p?{width:p.content.actualWidth,height:p.content.actualHeight}:null;
if(t.width<=0){
t.width=this.width;
}
if(t.height<=0){
t.height=this.height;
}
return t;
}});
dojox.gfx.silverlight.surfaces={};
dojox.gfx.createSurface=function(_59,_5a,_5b){
var s=new dojox.gfx.Surface();
_59=dojo.byId(_59);
var t=_59.ownerDocument.createElement("script");
t.type="text/xaml";
t.id=dojox.gfx._base._getUniqueId();
t.text="<Canvas xmlns='http://schemas.microsoft.com/client/2007' Name='"+dojox.gfx._base._getUniqueId()+"'/>";
document.body.appendChild(t);
var _5e=dojox.gfx._base._getUniqueId();
Silverlight.createObject("#"+t.id,_59,_5e,{width:String(_5a),height:String(_5b),inplaceInstallPrompt:"false",background:"transparent",isWindowless:"true",framerate:"24",version:"1.0"},{},null,null);
s.rawNode=dojo.byId(_5e).content.root;
dojox.gfx.silverlight.surfaces[s.rawNode.name]=_59;
s.width=dojox.gfx.normalizedLength(_5a);
s.height=dojox.gfx.normalizedLength(_5b);
return s;
};
dojox.gfx.silverlight.Font={_setFont:function(){
var f=this.fontStyle,r=this.rawNode,fw=dojox.gfx.silverlight.fontweight,fo=dojox.gfx.silverlight.fonts,t=f.family.toLowerCase();
r.fontStyle=f.style=="italic"?"Italic":"Normal";
r.fontWeight=f.weight in fw?fw[f.weight]:f.weight;
r.fontSize=dojox.gfx.normalizedLength(f.size);
r.fontFamily=t in fo?fo[t]:f.family;
}};
dojox.gfx.silverlight.Container={_init:function(){
dojox.gfx.shape.Container._init.call(this);
},add:function(_64){
if(this!=_64.getParent()){
dojox.gfx.shape.Container.add.apply(this,arguments);
this.rawNode.children.add(_64.rawNode);
}
return this;
},remove:function(_65,_66){
if(this==_65.getParent()){
var _67=_65.rawNode.getParent();
if(_67){
_67.children.remove(_65.rawNode);
}
dojox.gfx.shape.Container.remove.apply(this,arguments);
}
return this;
},clear:function(){
this.rawNode.children.clear();
return dojox.gfx.shape.Container.clear.apply(this,arguments);
},_moveChildToFront:dojox.gfx.shape.Container._moveChildToFront,_moveChildToBack:dojox.gfx.shape.Container._moveChildToBack};
dojo.mixin(dojox.gfx.shape.Creator,{createObject:function(_68,_69){
if(!this.rawNode){
return null;
}
var _6a=new _68();
var _6b=this.rawNode.getHost().content.createFromXaml("<"+_68.nodeType+"/>");
_6a.setRawNode(_6b);
_6a.setShape(_69);
this.add(_6a);
return _6a;
}});
dojo.extend(dojox.gfx.Text,dojox.gfx.silverlight.Font);
dojo.extend(dojox.gfx.Group,dojox.gfx.silverlight.Container);
dojo.extend(dojox.gfx.Group,dojox.gfx.shape.Creator);
dojo.extend(dojox.gfx.Surface,dojox.gfx.silverlight.Container);
dojo.extend(dojox.gfx.Surface,dojox.gfx.shape.Creator);
(function(){
var _6c=dojox.gfx.silverlight.surfaces;
var _6d=function(s,a){
var ev={target:s,currentTarget:s,preventDefault:function(){
},stopPropagation:function(){
}};
if(a){
ev.ctrlKey=a.ctrl;
ev.shiftKey=a.shift;
var p=a.getPosition(null);
ev.x=ev.offsetX=ev.layerX=p.x;
ev.y=ev.offsetY=ev.layerY=p.y;
var _72=_6c[s.getHost().content.root.name];
var t=dojo._abs(_72);
ev.clientX=t.x+p.x;
ev.clientY=t.y+p.y;
}
return ev;
};
var _74=function(s,a){
var ev={keyCode:a.platformKeyCode,ctrlKey:a.ctrl,shiftKey:a.shift};
return ev;
};
var _78={onclick:{name:"MouseLeftButtonUp",fix:_6d},onmouseenter:{name:"MouseEnter",fix:_6d},onmouseleave:{name:"MouseLeave",fix:_6d},onmousedown:{name:"MouseLeftButtonDown",fix:_6d},onmouseup:{name:"MouseLeftButtonUp",fix:_6d},onmousemove:{name:"MouseMove",fix:_6d},onkeydown:{name:"KeyDown",fix:_74},onkeyup:{name:"KeyUp",fix:_74}};
var _79={connect:function(_7a,_7b,_7c){
var _7d,n=_7a in _78?_78[_7a]:{name:_7a,fix:function(){
return {};
}};
if(arguments.length>2){
_7d=this.getEventSource().addEventListener(n.name,function(s,a){
dojo.hitch(_7b,_7c)(n.fix(s,a));
});
}else{
_7d=this.getEventSource().addEventListener(n.name,function(s,a){
_7b(n.fix(s,a));
});
}
return {name:n.name,token:_7d};
},disconnect:function(_83){
this.getEventSource().removeEventListener(_83.name,_83.token);
}};
dojo.extend(dojox.gfx.Shape,_79);
dojo.extend(dojox.gfx.Surface,_79);
dojox.gfx.equalSources=function(a,b){
return a&&b&&a.equals(b);
};
})();
}
