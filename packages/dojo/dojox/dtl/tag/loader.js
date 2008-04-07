/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.dtl.tag.loader"]){
dojo._hasResource["dojox.dtl.tag.loader"]=true;
dojo.provide("dojox.dtl.tag.loader");
dojo.require("dojox.dtl._base");
(function(){
var dd=dojox.dtl;
var _2=dd.tag.loader;
_2.BlockNode=dojo.extend(function(_3,_4){
this.name=_3;
this.nodelist=_4;
},{render:function(_5,_6){
var _7=this.name;
var _8=this.nodelist;
if(_6.blocks){
var _9=_6.blocks[_7];
if(_9){
_8=_9.nodelist;
_9.used=true;
}
}
this.rendered=_8;
return _8.render(_5,_6,this);
},unrender:function(_a,_b){
return this.rendered.unrender(_a,_b);
},clone:function(_c){
return new this.constructor(this.name,this.nodelist.clone(_c));
},setOverride:function(_d){
if(!this.override){
this.override=_d;
}
},toString:function(){
return "dojox.dtl.tag.loader.BlockNode";
}});
_2.ExtendsNode=dojo.extend(function(_e,_f,_10,_11,key){
this.getTemplate=_e;
this.nodelist=_f;
this.shared=_10;
this.parent=_11;
this.key=key;
},{parents:{},getParent:function(_13){
if(!this.parent){
this.parent=_13.get(this.key,false);
if(!this.parent){
throw new Error("extends tag used a variable that did not resolve");
}
if(typeof this.parent=="object"){
if(this.parent.url){
if(this.parent.shared){
this.shared=true;
}
this.parent=this.parent.url.toString();
}else{
this.parent=this.parent.toString();
}
}
if(this.parent&&this.parent.indexOf("shared:")==0){
this.shared=true;
this.parent=this.parent.substring(7,_14.length);
}
}
var _14=this.parent;
if(!_14){
throw new Error("Invalid template name in 'extends' tag.");
}
if(_14.render){
return _14;
}
if(this.parents[_14]){
return this.parents[_14];
}
this.parent=this.getTemplate(dojox.dtl.text.getTemplateString(_14));
if(this.shared){
this.parents[_14]=this.parent;
}
return this.parent;
},render:function(_15,_16){
var _17=this.getParent(_15);
_16.blocks=_16.blocks||{};
for(var i=0,_19;_19=this.nodelist.contents[i];i++){
if(_19 instanceof dojox.dtl.tag.loader.BlockNode){
_16.blocks[_19.name]={shared:this.shared,nodelist:_19.nodelist,used:false};
}
}
this.rendered=_17;
_16=_17.nodelist.render(_15,_16,this);
var _1a=false;
for(var _1b in _16.blocks){
var _1c=_16.blocks[_1b];
if(!_1c.used){
_1a=true;
_17.nodelist[0].nodelist.append(_1c.nodelist);
}
}
if(_1a){
_16=_17.nodelist.render(_15,_16,this);
}
return _16;
},unrender:function(_1d,_1e){
return this.rendered.unrender(_1d,_1e,this);
},toString:function(){
return "dojox.dtl.block.ExtendsNode";
}});
_2.IncludeNode=dojo.extend(function(_1f,_20,_21,_22,_23){
this._path=_1f;
this.constant=_20;
this.path=(_20)?_1f:new dd._Filter(_1f);
this.getTemplate=_21;
this.TextNode=_22;
this.parsed=(arguments.length==5)?_23:true;
},{_cache:[{},{}],render:function(_24,_25){
var _26=((this.constant)?this.path:this.path.resolve(_24)).toString();
var _27=Number(this.parsed);
var _28=false;
if(_26!=this.last){
_28=true;
if(this.last){
_25=this.unrender(_24,_25);
}
this.last=_26;
}
var _29=this._cache[_27];
if(_27){
if(!_29[_26]){
_29[_26]=dd.text._resolveTemplateArg(_26,true);
}
if(_28){
var _2a=this.getTemplate(_29[_26]);
this.rendered=_2a.nodelist;
}
return this.rendered.render(_24,_25,this);
}else{
if(this.TextNode==dd._TextNode){
if(_28){
this.rendered=new this.TextNode("");
this.rendered.set(dd.text._resolveTemplateArg(_26,true));
}
return this.rendered.render(_24,_25);
}else{
if(!_29[_26]){
var _2b=[];
var div=document.createElement("div");
div.innerHTML=dd.text._resolveTemplateArg(_26,true);
var _2d=div.childNodes;
while(_2d.length){
var _2e=div.removeChild(_2d[0]);
_2b.push(_2e);
}
_29[_26]=_2b;
}
if(_28){
this.nodelist=[];
var _2f=true;
for(var i=0,_31;_31=_29[_26][i];i++){
this.nodelist.push(_31.cloneNode(true));
}
}
for(var i=0,_32;_32=this.nodelist[i];i++){
_25=_25.concat(_32);
}
}
}
return _25;
},unrender:function(_33,_34){
if(this.rendered){
_34=this.rendered.unrender(_33,_34);
}
if(this.nodelist){
for(var i=0,_36;_36=this.nodelist[i];i++){
_34=_34.remove(_36);
}
}
return _34;
},clone:function(_37){
return new this.constructor(this._path,this.constant,this.getTemplate,this.TextNode,this.parsed);
}});
dojo.mixin(_2,{block:function(_38,_39){
var _3a=_39.split(" ");
var _3b=_3a[1];
_38._blocks=_38._blocks||{};
_38._blocks[_3b]=_38._blocks[_3b]||[];
_38._blocks[_3b].push(_3b);
var _3c=_38.parse(["endblock","endblock "+_3b]);
_38.next();
return new dojox.dtl.tag.loader.BlockNode(_3b,_3c);
},extends_:function(_3d,_3e){
var _3f=_3e.split(" ");
var _40=false;
var _41=null;
var key=null;
if(_3f[1].charAt(0)=="\""||_3f[1].charAt(0)=="'"){
_41=_3f[1].substring(1,_3f[1].length-1);
}else{
key=_3f[1];
}
if(_41&&_41.indexOf("shared:")==0){
_40=true;
_41=_41.substring(7,_41.length);
}
var _43=_3d.parse();
return new dojox.dtl.tag.loader.ExtendsNode(_3d.getTemplate,_43,_40,_41,key);
},include:function(_44,_45){
var _46=dd.text.pySplit(_45);
if(_46.length!=2){
throw new Error(_46[0]+" tag takes one argument: the name of the template to be included");
}
var _47=_46[1];
var _48=false;
if((_47.charAt(0)=="\""||_47.slice(-1)=="'")&&_47.charAt(0)==_47.slice(-1)){
_47=_47.slice(1,-1);
_48=true;
}
return new _2.IncludeNode(_47,_48,_44.getTemplate,_44.getTextNodeConstructor());
},ssi:function(_49,_4a){
var _4b=dd.text.pySplit(_4a);
var _4c=false;
if(_4b.length==3){
_4c=(_4b.pop()=="parsed");
if(!_4c){
throw new Error("Second (optional) argument to ssi tag must be 'parsed'");
}
}
var _4d=_2.include(_49,_4b.join(" "));
_4d.parsed=_4c;
return _4d;
}});
})();
}
