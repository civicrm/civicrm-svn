/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.dtl._base"]){
dojo._hasResource["dojox.dtl._base"]=true;
dojo.provide("dojox.dtl._base");
dojo.require("dojox.string.Builder");
dojo.require("dojox.string.tokenize");
(function(){
var dd=dojox.dtl;
dd._Context=dojo.extend(function(_2){
dojo.mixin(this,_2||{});
this._dicts=[];
},{push:function(){
var _3={};
var _4=this.getKeys();
for(var i=0,_6;_6=_4[i];i++){
_3[_6]=this[_6];
delete this[_6];
}
this._dicts.unshift(_3);
},pop:function(){
if(!this._dicts.length){
throw new Error("pop() called on empty Context");
}
var _7=this._dicts.shift();
dojo.mixin(this,_7);
},getKeys:function(){
var _8=[];
for(var _9 in this){
if(this.hasOwnProperty(_9)&&_9!="_dicts"&&_9!="_this"){
_8.push(_9);
}
}
return _8;
},get:function(_a,_b){
if(typeof this[_a]!="undefined"){
return this._normalize(this[_a]);
}
for(var i=0,_d;_d=this._dicts[i];i++){
if(typeof _d[_a]!="undefined"){
return this._normalize(_d[_a]);
}
}
return _b;
},_normalize:function(_e){
if(_e instanceof Date){
_e.year=_e.getFullYear();
_e.month=_e.getMonth()+1;
_e.day=_e.getDate();
_e.date=_e.year+"-"+("0"+_e.month).slice(-2)+"-"+("0"+_e.day).slice(-2);
_e.hour=_e.getHours();
_e.minute=_e.getMinutes();
_e.second=_e.getSeconds();
_e.microsecond=_e.getMilliseconds();
}
return _e;
},update:function(_f){
this.push();
if(_f){
dojo.mixin(this,_f);
}
}});
var ddt=dd.text={types:{tag:-1,varr:-2,text:3},pySplit:function(str){
str=dojo.trim(str);
return (!str.length)?[]:str.split(/\s+/g);
},_get:function(_12,_13,_14){
var _15=dd.register.get(_12,_13.toLowerCase(),_14);
if(!_15){
if(!_14){
throw new Error("No tag found for "+_13);
}
return null;
}
var fn=_15[1];
var _17=_15[2];
var _18;
if(fn.indexOf(":")!=-1){
_18=fn.split(":");
fn=_18.pop();
}
dojo["require"](_17);
var _19=dojo.getObject(_17);
return _19[fn||_13]||_19[_13+"_"];
},getTag:function(_1a,_1b){
return ddt._get("tag",_1a,_1b);
},getFilter:function(_1c,_1d){
return ddt._get("filter",_1c,_1d);
},getTemplate:function(_1e){
return new dd.Template(dd.getTemplateString(_1e));
},getTemplateString:function(_1f){
return dojo._getText(_1f.toString())||"";
},_resolveLazy:function(_20,_21,_22){
if(_21){
if(_22){
return dojo.fromJson(dojo._getText(_20))||{};
}else{
return dd.text.getTemplateString(_20);
}
}else{
return dojo.xhrGet({handleAs:(_22)?"json":"text",url:_20});
}
},_resolveTemplateArg:function(arg,_24){
if(ddt._isTemplate(arg)){
if(!_24){
var d=new dojo.Deferred();
d.callback(arg);
return d;
}
return arg;
}
return ddt._resolveLazy(arg,_24);
},_isTemplate:function(arg){
return (typeof arg=="undefined")||(dojo.isString(arg)&&(arg.match(/^\s*[<{]/)||arg.indexOf(" ")!=-1));
},_resolveContextArg:function(arg,_28){
if(arg.constructor==Object){
if(!_28){
var d=new dojo.Deferred;
d.callback(arg);
return d;
}
return arg;
}
return ddt._resolveLazy(arg,_28,true);
},_re:/(?:\{\{\s*(.+?)\s*\}\}|\{%\s*(load\s*)?(.+?)\s*%\})/g,tokenize:function(str){
return dojox.string.tokenize(str,ddt._re,ddt._parseDelims);
},_parseDelims:function(_2b,_2c,tag){
var _2e=ddt.types;
if(_2b){
return [_2e.varr,_2b];
}else{
if(_2c){
var _2f=dd.text.pySplit(tag);
for(var i=0,_31;_31=_2f[i];i++){
dojo["require"](_31);
}
}else{
return [_2e.tag,tag];
}
}
}};
dd.Template=dojo.extend(function(_32){
var str=ddt._resolveTemplateArg(_32,true)||"";
var _34=ddt.tokenize(str);
var _35=new dd._Parser(_34);
this.nodelist=_35.parse();
},{update:function(_36,_37){
return ddt._resolveContextArg(_37).addCallback(this,function(_38){
var _39=this.render(new dd._Context(_38));
if(_36.forEach){
_36.forEach(function(_3a){
_3a.innerHTML=_39;
});
}else{
dojo.byId(_36).innerHTML=_39;
}
return this;
});
},render:function(_3b,_3c){
_3c=_3c||this.getBuffer();
_3b=_3b||new dd._Context({});
return this.nodelist.render(_3b,_3c)+"";
},getBuffer:function(){
dojo.require("dojox.string.Builder");
return new dojox.string.Builder();
}});
dd._Filter=dojo.extend(function(_3d){
if(!_3d){
throw new Error("Filter must be called with variable name");
}
this.contents=_3d;
var _3e=this._cache[_3d];
if(_3e){
this.key=_3e[0];
this.filters=_3e[1];
}else{
this.filters=[];
dojox.string.tokenize(_3d,this._re,this._tokenize,this);
this._cache[_3d]=[this.key,this.filters];
}
},{_cache:{},_re:/(?:^_\("([^\\"]*(?:\\.[^\\"])*)"\)|^"([^\\"]*(?:\\.[^\\"]*)*)"|^([a-zA-Z0-9_.]+)|\|(\w+)(?::(?:_\("([^\\"]*(?:\\.[^\\"])*)"\)|"([^\\"]*(?:\\.[^\\"]*)*)"|([a-zA-Z0-9_.]+)|'([^\\']*(?:\\.[^\\']*)*)'))?|^'([^\\']*(?:\\.[^\\']*)*)')/g,_values:{0:"\"",1:"\"",2:"",8:"\""},_args:{4:"\"",5:"\"",6:"",7:"'"},_tokenize:function(){
var pos,arg;
for(var i=0,has=[];i<arguments.length;i++){
has[i]=(typeof arguments[i]!="undefined"&&dojo.isString(arguments[i])&&arguments[i]);
}
if(!this.key){
for(pos in this._values){
if(has[pos]){
this.key=this._values[pos]+arguments[pos]+this._values[pos];
break;
}
}
}else{
for(pos in this._args){
if(has[pos]){
var _43=arguments[pos];
if(this._args[pos]=="'"){
_43=_43.replace(/\\'/g,"'");
}else{
if(this._args[pos]=="\""){
_43=_43.replace(/\\"/g,"\"");
}
}
arg=[!this._args[pos],_43];
break;
}
}
var fn=ddt.getFilter(arguments[3]);
if(!dojo.isFunction(fn)){
throw new Error(arguments[3]+" is not registered as a filter");
}
this.filters.push([fn,arg]);
}
},getExpression:function(){
return this.contents;
},resolve:function(_45){
var str=this.resolvePath(this.key,_45);
for(var i=0,_48;_48=this.filters[i];i++){
if(_48[1]){
if(_48[1][0]){
str=_48[0](str,this.resolvePath(_48[1][1],_45));
}else{
str=_48[0](str,_48[1][1]);
}
}else{
str=_48[0](str);
}
}
return str;
},resolvePath:function(_49,_4a){
var _4b,_4c;
var _4d=_49.charAt(0);
var _4e=_49.slice(-1);
if(!isNaN(parseInt(_4d))){
_4b=(_49.indexOf(".")==-1)?parseInt(_49):parseFloat(_49);
}else{
if(_4d=="\""&&_4d==_4e){
_4b=_49.slice(1,-1);
}else{
if(_49=="true"){
return true;
}
if(_49=="false"){
return false;
}
if(_49=="null"||_49=="None"){
return null;
}
_4c=_49.split(".");
_4b=_4a.get(_4c[0]);
for(var i=1;i<_4c.length;i++){
var _50=_4c[i];
if(_4b){
if(dojo.isObject(_4b)&&_50=="items"&&typeof _4b[_50]=="undefined"){
var _51=[];
for(var key in _4b){
_51.push([key,_4b[key]]);
}
_4b=_51;
continue;
}
if(_4b.get&&dojo.isFunction(_4b.get)){
_4b=_4b.get(_50);
}else{
if(typeof _4b[_50]=="undefined"){
_4b=_4b[_50];
break;
}else{
_4b=_4b[_50];
}
}
if(dojo.isFunction(_4b)){
if(_4b.alters_data){
_4b="";
}else{
_4b=_4b();
}
}
}else{
return "";
}
}
}
}
return _4b;
}});
dd._TextNode=dd._Node=dojo.extend(function(obj){
this.contents=obj;
},{set:function(_54){
this.contents=_54;
},render:function(_55,_56){
return _56.concat(this.contents);
}});
dd._NodeList=dojo.extend(function(_57){
this.contents=_57||[];
this.last="";
},{push:function(_58){
this.contents.push(_58);
},render:function(_59,_5a){
for(var i=0;i<this.contents.length;i++){
_5a=this.contents[i].render(_59,_5a);
if(!_5a){
throw new Error("Template must return buffer");
}
}
return _5a;
},dummyRender:function(_5c){
return this.render(_5c,dd.Template.prototype.getBuffer()).toString();
},unrender:function(){
return arguments[1];
},clone:function(){
return this;
}});
dd._VarNode=dojo.extend(function(str){
this.contents=new dd._Filter(str);
},{render:function(_5e,_5f){
var str=this.contents.resolve(_5e);
return _5f.concat(str);
}});
dd._noOpNode=new function(){
this.render=this.unrender=function(){
return arguments[1];
};
this.clone=function(){
return this;
};
};
dd._Parser=dojo.extend(function(_61){
this.contents=_61;
},{i:0,parse:function(_62){
var _63=ddt.types;
var _64={};
_62=_62||[];
for(var i=0;i<_62.length;i++){
_64[_62[i]]=true;
}
var _66=new dd._NodeList();
while(this.i<this.contents.length){
token=this.contents[this.i++];
if(dojo.isString(token)){
_66.push(new dd._TextNode(token));
}else{
var _67=token[0];
var _68=token[1];
if(_67==_63.varr){
_66.push(new dd._VarNode(_68));
}else{
if(_67==_63.tag){
if(_64[_68]){
--this.i;
return _66;
}
var cmd=_68.split(/\s+/g);
if(cmd.length){
cmd=cmd[0];
var fn=ddt.getTag(cmd);
if(fn){
_66.push(fn(this,_68));
}
}
}
}
}
}
if(_62.length){
throw new Error("Could not find closing tag(s): "+_62.toString());
}
this.contents.length=0;
return _66;
},next:function(){
var _6b=this.contents[this.i++];
return {type:_6b[0],text:_6b[1]};
},skipPast:function(_6c){
var _6d=ddt.types;
while(this.i<this.contents.length){
var _6e=this.contents[this.i++];
if(_6e[0]==_6d.tag&&_6e[1]==_6c){
return;
}
}
throw new Error("Unclosed tag found when looking for "+_6c);
},getVarNodeConstructor:function(){
return dd._VarNode;
},getTextNodeConstructor:function(){
return dd._TextNode;
},getTemplate:function(_6f){
return new dd.Template(_6f);
}});
dd.register={_registry:{attributes:[],tags:[],filters:[]},get:function(_70,_71){
var _72=dd.register._registry[_70+"s"];
for(var i=0,_74;_74=_72[i];i++){
if(dojo.isString(_74[0])){
if(_74[0]==_71){
return _74;
}
}else{
if(_71.match(_74[0])){
return _74;
}
}
}
},getAttributeTags:function(){
var _75=[];
var _76=dd.register._registry.attributes;
for(var i=0,_78;_78=_76[i];i++){
if(_78.length==3){
_75.push(_78);
}else{
var fn=dojo.getObject(_78[1]);
if(fn&&dojo.isFunction(fn)){
_78.push(fn);
_75.push(_78);
}
}
}
return _75;
},_any:function(_7a,_7b,_7c){
for(var _7d in _7c){
for(var i=0,fn;fn=_7c[_7d][i];i++){
var key=fn;
if(dojo.isArray(fn)){
key=fn[0];
fn=fn[1];
}
if(dojo.isString(key)){
if(key.substr(0,5)=="attr:"){
var _81=fn;
if(_81.substr(0,5)=="attr:"){
_81=_81.slice(5);
}
dd.register._registry.attributes.push([_81,_7b+"."+_7d+"."+_81]);
}
key=key.toLowerCase();
}
dd.register._registry[_7a].push([key,fn,_7b+"."+_7d]);
}
}
},tags:function(_82,_83){
dd.register._any("tags",_82,_83);
},filters:function(_84,_85){
dd.register._any("filters",_84,_85);
}};
dd.register.tags("dojox.dtl.tag",{"date":["now"],"logic":["if","for","ifequal","ifnotequal"],"loader":["extends","block","include","load","ssi"],"misc":["comment","debug","filter","firstof","spaceless","templatetag","widthratio","with"],"loop":["cycle","ifchanged","regroup"]});
dd.register.filters("dojox.dtl.filter",{"dates":["date","time","timesince","timeuntil"],"htmlstrings":["escape","linebreaks","linebreaksbr","removetags","striptags"],"integers":["add","get_digit"],"lists":["dictsort","dictsortreversed","first","join","length","length_is","random","slice","unordered_list"],"logic":["default","default_if_none","divisibleby","yesno"],"misc":["filesizeformat","pluralize","phone2numeric","pprint"],"strings":["addslashes","capfirst","center","cut","fix_ampersands","floatformat","iriencode","linenumbers","ljust","lower","make_list","rjust","slugify","stringformat","title","truncatewords","truncatewords_html","upper","urlencode","urlize","urlizetrunc","wordcount","wordwrap"]});
})();
}
