/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dijit._base.place"]){
dojo._hasResource["dijit._base.place"]=true;
dojo.provide("dijit._base.place");
dijit.getViewport=function(){
var _1=dojo.global;
var _2=dojo.doc;
var w=0,h=0;
var de=_2.documentElement;
var _6=de.clientWidth,_7=de.clientHeight;
if(dojo.isMozilla){
var _8,_9,_a,_b;
var _c=_2.body.clientWidth;
if(_c>_6){
_8=_6;
_a=_c;
}else{
_a=_6;
_8=_c;
}
var _d=_2.body.clientHeight;
if(_d>_7){
_9=_7;
_b=_d;
}else{
_b=_7;
_9=_d;
}
w=(_a>_1.innerWidth)?_8:_a;
h=(_b>_1.innerHeight)?_9:_b;
}else{
if(!dojo.isOpera&&_1.innerWidth){
w=_1.innerWidth;
h=_1.innerHeight;
}else{
if(dojo.isIE&&de&&_7){
w=_6;
h=_7;
}else{
if(dojo.body().clientWidth){
w=dojo.body().clientWidth;
h=dojo.body().clientHeight;
}
}
}
}
var _e=dojo._docScroll();
return {w:w,h:h,l:_e.x,t:_e.y};
};
dijit.placeOnScreen=function(_f,pos,_11,_12){
var _13=dojo.map(_11,function(_14){
return {corner:_14,pos:pos};
});
return dijit._place(_f,_13);
};
dijit._place=function(_15,_16,_17){
var _18=dijit.getViewport();
if(!_15.parentNode||String(_15.parentNode.tagName).toLowerCase()!="body"){
dojo.body().appendChild(_15);
}
var _19=null;
dojo.some(_16,function(_1a){
var _1b=_1a.corner;
var pos=_1a.pos;
if(_17){
_17(_15,_1a.aroundCorner,_1b);
}
var _1d=_15.style;
var _1e=_1d.display;
var _1f=_1d.visibility;
_1d.visibility="hidden";
_1d.display="";
var mb=dojo.marginBox(_15);
_1d.display=_1e;
_1d.visibility=_1f;
var _21=(_1b.charAt(1)=="L"?pos.x:Math.max(_18.l,pos.x-mb.w)),_22=(_1b.charAt(0)=="T"?pos.y:Math.max(_18.t,pos.y-mb.h)),_23=(_1b.charAt(1)=="L"?Math.min(_18.l+_18.w,_21+mb.w):pos.x),_24=(_1b.charAt(0)=="T"?Math.min(_18.t+_18.h,_22+mb.h):pos.y),_25=_23-_21,_26=_24-_22,_27=(mb.w-_25)+(mb.h-_26);
if(_19==null||_27<_19.overflow){
_19={corner:_1b,aroundCorner:_1a.aroundCorner,x:_21,y:_22,w:_25,h:_26,overflow:_27};
}
return !_27;
});
_15.style.left=_19.x+"px";
_15.style.top=_19.y+"px";
if(_19.overflow&&_17){
_17(_15,_19.aroundCorner,_19.corner);
}
return _19;
};
dijit.placeOnScreenAroundElement=function(_28,_29,_2a,_2b){
_29=dojo.byId(_29);
var _2c=_29.style.display;
_29.style.display="";
var _2d=_29.offsetWidth;
var _2e=_29.offsetHeight;
var _2f=dojo.coords(_29,true);
_29.style.display=_2c;
var _30=[];
for(var _31 in _2a){
_30.push({aroundCorner:_31,corner:_2a[_31],pos:{x:_2f.x+(_31.charAt(1)=="L"?0:_2d),y:_2f.y+(_31.charAt(0)=="T"?0:_2e)}});
}
return dijit._place(_28,_30,_2b);
};
}
