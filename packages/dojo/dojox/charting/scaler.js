/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.charting.scaler"]){
dojo._hasResource["dojox.charting.scaler"]=true;
dojo.provide("dojox.charting.scaler");
(function(){
var _1=3;
var _2=function(_3,_4){
_3=_3.toLowerCase();
for(var i=0;i<_4.length;++i){
if(_3==_4[i]){
return true;
}
}
return false;
};
var _6=function(_7,_8,_9,_a,_b,_c,_d){
_9=dojo.clone(_9);
if(!_a){
if(_9.fixUpper=="major"){
_9.fixUpper="minor";
}
if(_9.fixLower=="major"){
_9.fixLower="minor";
}
}
if(!_b){
if(_9.fixUpper=="minor"){
_9.fixUpper="micro";
}
if(_9.fixLower=="minor"){
_9.fixLower="micro";
}
}
if(!_c){
if(_9.fixUpper=="micro"){
_9.fixUpper="none";
}
if(_9.fixLower=="micro"){
_9.fixLower="none";
}
}
var _e=_2(_9.fixLower,["major"])?Math.floor(_7/_a)*_a:_2(_9.fixLower,["minor"])?Math.floor(_7/_b)*_b:_2(_9.fixLower,["micro"])?Math.floor(_7/_c)*unit:_7,_f=_2(_9.fixUpper,["major"])?Math.ceil(_8/_a)*_a:_2(_9.fixUpper,["minor"])?Math.ceil(_8/_b)*_b:_2(_9.fixUpper,["unit"])?Math.ceil(_8/unit)*unit:_8,_10=(_2(_9.fixLower,["major"])||!_a)?_e:Math.ceil(_e/_a)*_a,_11=(_2(_9.fixLower,["major","minor"])||!_b)?_e:Math.ceil(_e/_b)*_b,_12=(_2(_9.fixLower,["major","minor","micro"])||!_c)?_e:Math.ceil(_e/_c)*_c,_13=!_a?0:(_2(_9.fixUpper,["major"])?Math.round((_f-_10)/_a):Math.floor((_f-_10)/_a))+1,_14=!_b?0:(_2(_9.fixUpper,["major","minor"])?Math.round((_f-_11)/_b):Math.floor((_f-_11)/_b))+1,_15=!_c?0:(_2(_9.fixUpper,["major","minor","micro"])?Math.round((_f-_12)/_c):Math.floor((_f-_12)/_c))+1,_16=_b?Math.round(_a/_b):0,_17=_c?Math.round(_b/_c):0,_18=_a?Math.floor(Math.log(_a)/Math.LN10):0,_19=_b?Math.floor(Math.log(_b)/Math.LN10):0,_1a=_d/(_f-_e);
if(!isFinite(_1a)){
_1a=1;
}
return {bounds:{lower:_e,upper:_f},major:{tick:_a,start:_10,count:_13,prec:_18},minor:{tick:_b,start:_11,count:_14,prec:_19},micro:{tick:_c,start:_12,count:_15,prec:0},minorPerMajor:_16,microPerMinor:_17,scale:_1a};
};
dojox.charting.scaler=function(min,max,_1d,_1e){
var h={fixUpper:"none",fixLower:"none",natural:false};
if(_1e){
if("fixUpper" in _1e){
h.fixUpper=String(_1e.fixUpper);
}
if("fixLower" in _1e){
h.fixLower=String(_1e.fixLower);
}
if("natural" in _1e){
h.natural=Boolean(_1e.natural);
}
}
if(max<=min){
return _6(min,max,h,0,0,0,_1d);
}
var mag=Math.floor(Math.log(max-min)/Math.LN10),_21=_1e&&("majorTick" in _1e)?_1e.majorTick:Math.pow(10,mag),_22=0,_23=0,_24;
if(_1e&&("minorTick" in _1e)){
_22=_1e.minorTick;
}else{
do{
_22=_21/10;
if(!h.natural||_22>0.9){
_24=_6(min,max,h,_21,_22,0,_1d);
if(_24.scale*_24.minor.tick>_1){
break;
}
}
_22=_21/5;
if(!h.natural||_22>0.9){
_24=_6(min,max,h,_21,_22,0,_1d);
if(_24.scale*_24.minor.tick>_1){
break;
}
}
_22=_21/2;
if(!h.natural||_22>0.9){
_24=_6(min,max,h,_21,_22,0,_1d);
if(_24.scale*_24.minor.tick>_1){
break;
}
}
return _6(min,max,h,_21,0,0,_1d);
}while(false);
}
if(_1e&&("microTick" in _1e)){
_23=_1e.microTick;
_24=_6(min,max,h,_21,_22,_23,_1d);
}else{
do{
_23=_22/10;
if(!h.natural||_23>0.9){
_24=_6(min,max,h,_21,_22,_23,_1d);
if(_24.scale*_24.micro.tick>_1){
break;
}
}
_23=_22/5;
if(!h.natural||_23>0.9){
_24=_6(min,max,h,_21,_22,_23,_1d);
if(_24.scale*_24.micro.tick>_1){
break;
}
}
_23=_22/2;
if(!h.natural||_23>0.9){
_24=_6(min,max,h,_21,_22,_23,_1d);
if(_24.scale*_24.micro.tick>_1){
break;
}
}
_23=0;
}while(false);
}
return _23?_24:_6(min,max,h,_21,_22,0,_1d);
};
})();
}
