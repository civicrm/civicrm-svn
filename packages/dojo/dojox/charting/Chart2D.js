/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.charting.Chart2D"]){
dojo._hasResource["dojox.charting.Chart2D"]=true;
dojo.provide("dojox.charting.Chart2D");
dojo.require("dojox.gfx");
dojo.require("dojox.lang.functional");
dojo.require("dojox.lang.functional.fold");
dojo.require("dojox.lang.functional.reversed");
dojo.require("dojox.charting.Theme");
dojo.require("dojox.charting.Series");
dojo.require("dojox.charting.axis2d.Default");
dojo.require("dojox.charting.plot2d.Default");
dojo.require("dojox.charting.plot2d.Lines");
dojo.require("dojox.charting.plot2d.Areas");
dojo.require("dojox.charting.plot2d.Markers");
dojo.require("dojox.charting.plot2d.MarkersOnly");
dojo.require("dojox.charting.plot2d.Scatter");
dojo.require("dojox.charting.plot2d.Stacked");
dojo.require("dojox.charting.plot2d.StackedLines");
dojo.require("dojox.charting.plot2d.StackedAreas");
dojo.require("dojox.charting.plot2d.Columns");
dojo.require("dojox.charting.plot2d.StackedColumns");
dojo.require("dojox.charting.plot2d.ClusteredColumns");
dojo.require("dojox.charting.plot2d.Bars");
dojo.require("dojox.charting.plot2d.StackedBars");
dojo.require("dojox.charting.plot2d.ClusteredBars");
dojo.require("dojox.charting.plot2d.Grid");
dojo.require("dojox.charting.plot2d.Pie");
(function(){
var df=dojox.lang.functional,dc=dojox.charting,_3=df.lambda("item.clear()"),_4=df.lambda("item.purgeGroup()"),_5=df.lambda("item.destroy()"),_6=df.lambda("item.dirty = false"),_7=df.lambda("item.dirty = true");
dojo.declare("dojox.charting.Chart2D",null,{constructor:function(_8,_9){
if(!_9){
_9={};
}
this.margins=_9.margins?_9.margins:{l:10,t:10,r:10,b:10};
this.stroke=_9.stroke;
this.fill=_9.fill;
this.theme=null;
this.axes={};
this.stack=[];
this.plots={};
this.series=[];
this.runs={};
this.dirty=true;
this.coords=null;
this.node=dojo.byId(_8);
var _a=dojo.marginBox(_8);
this.surface=dojox.gfx.createSurface(this.node,_a.w,_a.h);
},destroy:function(){
dojo.forEach(this.series,_5);
dojo.forEach(this.stack,_5);
df.forIn(this.axes,_5);
},getCoords:function(){
if(!this.coords){
this.coords=dojo.coords(this.node,true);
}
return this.coords;
},setTheme:function(_b){
this.theme=_b;
this.dirty=true;
return this;
},addAxis:function(_c,_d){
var _e;
if(!_d||!("type" in _d)){
_e=new dc.axis2d.Default(this,_d);
}else{
_e=typeof _d.type=="string"?new dc.axis2d[_d.type](this,_d):new _d.type(this,_d);
}
_e.name=_c;
_e.dirty=true;
if(_c in this.axes){
this.axes[_c].destroy();
}
this.axes[_c]=_e;
this.dirty=true;
return this;
},addPlot:function(_f,_10){
var _11;
if(!_10||!("type" in _10)){
_11=new dc.plot2d.Default(this,_10);
}else{
_11=typeof _10.type=="string"?new dc.plot2d[_10.type](this,_10):new _10.type(this,_10);
}
_11.name=_f;
_11.dirty=true;
if(_f in this.plots){
this.stack[this.plots[_f]].destroy();
this.stack[this.plots[_f]]=_11;
}else{
this.plots[_f]=this.stack.length;
this.stack.push(_11);
}
this.dirty=true;
return this;
},addSeries:function(_12,_13,_14){
var run=new dc.Series(this,_13,_14);
if(_12 in this.runs){
this.series[this.runs[_12]].destroy();
this.series[this.runs[_12]]=run;
}else{
this.runs[_12]=this.series.length;
this.series.push(run);
}
this.dirty=true;
if(!("ymin" in run)&&"min" in run){
run.ymin=run.min;
}
if(!("ymax" in run)&&"max" in run){
run.ymax=run.max;
}
return this;
},updateSeries:function(_16,_17){
if(_16 in this.runs){
var run=this.series[this.runs[_16]],_19=this.stack[this.plots[run.plot]],_1a;
run.data=_17;
run.dirty=true;
if(_19.hAxis){
_1a=this.axes[_19.hAxis];
if(_1a.dependOnData()){
_1a.dirty=true;
dojo.forEach(this.stack,function(p){
if(p.hAxis&&p.hAxis==_19.hAxis){
p.dirty=true;
}
});
}
}else{
_19.dirty=true;
}
if(_19.vAxis){
_1a=this.axes[_19.vAxis];
if(_1a.dependOnData()){
_1a.dirty=true;
dojo.forEach(this.stack,function(p){
if(p.vAxis&&p.vAxis==_19.vAxis){
p.dirty=true;
}
});
}
}else{
_19.dirty=true;
}
}
return this;
},resize:function(_1d,_1e){
var box;
switch(arguments.length){
case 0:
box=dojo.marginBox(this.node);
break;
case 1:
box=_1d;
break;
default:
box={w:_1d,h:_1e};
break;
}
dojo.marginBox(this.node,box);
this.surface.setDimensions(box.w,box.h);
this.dirty=true;
this.coords=null;
return this.render();
},render:function(){
if(this.dirty){
return this.fullRender();
}
dojo.forEach(this.stack,function(_20){
if(_20.dirty||(_20.hAxis&&this.axes[_20.hAxis].dirty)||(_20.vAxis&&this.axes[_20.vAxis].dirty)){
_20.calculateAxes(this.plotArea);
}
},this);
df.forEachRev(this.stack,function(_21){
_21.render(this.dim,this.offsets);
},this);
df.forIn(this.axes,function(_22){
_22.render(this.dim,this.offsets);
},this);
this._makeClean();
if(this.surface.render){
this.surface.render();
}
return this;
},fullRender:function(){
this._makeDirty();
dojo.forEach(this.stack,_3);
dojo.forEach(this.series,_4);
df.forIn(this.axes,_4);
dojo.forEach(this.stack,_4);
this.surface.clear();
dojo.forEach(this.series,function(run){
if(!(run.plot in this.plots)){
var _24=new dc.plot2d.Default(this,{});
_24.name=run.plot;
this.plots[run.plot]=this.stack.length;
this.stack.push(_24);
}
this.stack[this.plots[run.plot]].addSeries(run);
},this);
dojo.forEach(this.stack,function(_25){
if(_25.hAxis){
_25.setAxis(this.axes[_25.hAxis]);
}
if(_25.vAxis){
_25.setAxis(this.axes[_25.vAxis]);
}
},this);
if(!this.theme){
this.theme=new dojox.charting.Theme(dojox.charting._def);
}
var _26=df.foldl(this.stack,"z + plot.getRequiredColors()",0);
this.theme.defineColors({num:_26,cache:false});
var dim=this.dim=this.surface.getDimensions();
dim.width=dojox.gfx.normalizedLength(dim.width);
dim.height=dojox.gfx.normalizedLength(dim.height);
df.forIn(this.axes,_3);
dojo.forEach(this.stack,function(_28){
_28.calculateAxes(dim);
});
var _29=this.offsets={l:0,r:0,t:0,b:0};
df.forIn(this.axes,function(_2a){
df.forIn(_2a.getOffsets(),function(o,i){
_29[i]+=o;
});
});
df.forIn(this.margins,function(o,i){
_29[i]+=o;
});
this.plotArea={width:dim.width-_29.l-_29.r,height:dim.height-_29.t-_29.b};
df.forIn(this.axes,_3);
dojo.forEach(this.stack,function(_2f){
_2f.calculateAxes(this.plotArea);
},this);
var t=this.theme,_31=this.fill?this.fill:(t.chart&&t.chart.fill),_32=this.stroke?this.stroke:(t.chart&&t.chart.stroke);
if(_31){
this.surface.createRect({width:dim.width,height:dim.height}).setFill(_31);
}
if(_32){
this.surface.createRect({width:dim.width-1,height:dim.height-1}).setStroke(_32);
}
_31=t.plotarea&&t.plotarea.fill;
_32=t.plotarea&&t.plotarea.stroke;
if(_31){
this.surface.createRect({x:_29.l,y:_29.t,width:dim.width-_29.l-_29.r,height:dim.height-_29.t-_29.b}).setFill(_31);
}
if(_32){
this.surface.createRect({x:_29.l,y:_29.t,width:dim.width-_29.l-_29.r-1,height:dim.height-_29.t-_29.b-1}).setStroke(_32);
}
df.foldr(this.stack,function(z,_34){
return _34.render(dim,_29),0;
},0);
df.forIn(this.axes,function(_35){
_35.render(dim,_29);
});
this._makeClean();
return this;
},_makeClean:function(){
dojo.forEach(this.axes,_6);
dojo.forEach(this.stack,_6);
dojo.forEach(this.series,_6);
this.dirty=false;
},_makeDirty:function(){
dojo.forEach(this.axes,_7);
dojo.forEach(this.stack,_7);
dojo.forEach(this.series,_7);
this.dirty=true;
}});
})();
}
