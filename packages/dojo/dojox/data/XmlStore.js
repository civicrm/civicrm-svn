/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.data.XmlStore"]){
dojo._hasResource["dojox.data.XmlStore"]=true;
dojo.provide("dojox.data.XmlStore");
dojo.provide("dojox.data.XmlItem");
dojo.require("dojo.data.util.simpleFetch");
dojo.require("dojo.data.util.filter");
dojo.require("dojox.data.dom");
dojo.declare("dojox.data.XmlStore",null,{constructor:function(_1){
console.log("XmlStore()");
if(_1){
this.url=_1.url;
this.rootItem=(_1.rootItem||_1.rootitem||this.rootItem);
this.keyAttribute=(_1.keyAttribute||_1.keyattribute||this.keyAttribute);
this._attributeMap=(_1.attributeMap||_1.attributemap);
this.label=_1.label||this.label;
this.sendQuery=(_1.sendQuery||_1.sendquery||this.sendQuery);
}
this._newItems=[];
this._deletedItems=[];
this._modifiedItems=[];
},url:"",rootItem:"",keyAttribute:"",label:"",sendQuery:false,getValue:function(_2,_3,_4){
var _5=_2.element;
if(_3==="tagName"){
return _5.nodeName;
}else{
if(_3==="childNodes"){
for(var i=0;i<_5.childNodes.length;i++){
var _7=_5.childNodes[i];
if(_7.nodeType===1){
return this._getItem(_7);
}
}
return _4;
}else{
if(_3==="text()"){
for(var i=0;i<_5.childNodes.length;i++){
var _7=_5.childNodes[i];
if(_7.nodeType===3||_7.nodeType===4){
return _7.nodeValue;
}
}
return _4;
}else{
_3=this._getAttribute(_5.nodeName,_3);
if(_3.charAt(0)==="@"){
var _8=_3.substring(1);
var _9=_5.getAttribute(_8);
return (_9!==undefined)?_9:_4;
}else{
for(var i=0;i<_5.childNodes.length;i++){
var _7=_5.childNodes[i];
if(_7.nodeType===1&&_7.nodeName===_3){
return this._getItem(_7);
}
}
return _4;
}
}
}
}
},getValues:function(_a,_b){
var _c=_a.element;
if(_b==="tagName"){
return [_c.nodeName];
}else{
if(_b==="childNodes"){
var _d=[];
for(var i=0;i<_c.childNodes.length;i++){
var _f=_c.childNodes[i];
if(_f.nodeType===1){
_d.push(this._getItem(_f));
}
}
return _d;
}else{
if(_b==="text()"){
var _d=[];
for(var i=0;i<_c.childNodes.length;i++){
var _f=childNodes[i];
if(_f.nodeType===3){
_d.push(_f.nodeValue);
}
}
return _d;
}else{
_b=this._getAttribute(_c.nodeName,_b);
if(_b.charAt(0)==="@"){
var _10=_b.substring(1);
var _11=_c.getAttribute(_10);
return (_11!==undefined)?[_11]:[];
}else{
var _d=[];
for(var i=0;i<_c.childNodes.length;i++){
var _f=_c.childNodes[i];
if(_f.nodeType===1&&_f.nodeName===_b){
_d.push(this._getItem(_f));
}
}
return _d;
}
}
}
}
},getAttributes:function(_12){
var _13=_12.element;
var _14=[];
_14.push("tagName");
if(_13.childNodes.length>0){
var _15={};
var _16=true;
var _17=false;
for(var i=0;i<_13.childNodes.length;i++){
var _19=_13.childNodes[i];
if(_19.nodeType===1){
var _1a=_19.nodeName;
if(!_15[_1a]){
_14.push(_1a);
_15[_1a]=_1a;
}
_16=true;
}else{
if(_19.nodeType===3){
_17=true;
}
}
}
if(_16){
_14.push("childNodes");
}
if(_17){
_14.push("text()");
}
}
for(var i=0;i<_13.attributes.length;i++){
_14.push("@"+_13.attributes[i].nodeName);
}
if(this._attributeMap){
for(var key in this._attributeMap){
var i=key.indexOf(".");
if(i>0){
var _1c=key.substring(0,i);
if(_1c===_13.nodeName){
_14.push(key.substring(i+1));
}
}else{
_14.push(key);
}
}
}
return _14;
},hasAttribute:function(_1d,_1e){
return (this.getValue(_1d,_1e)!==undefined);
},containsValue:function(_1f,_20,_21){
var _22=this.getValues(_1f,_20);
for(var i=0;i<_22.length;i++){
if((typeof _21==="string")){
if(_22[i].toString&&_22[i].toString()===_21){
return true;
}
}else{
if(_22[i]===_21){
return true;
}
}
}
return false;
},isItem:function(_24){
if(_24&&_24.element&&_24.store&&_24.store===this){
return true;
}
return false;
},isItemLoaded:function(_25){
return this.isItem(_25);
},loadItem:function(_26){
},getFeatures:function(){
var _27={"dojo.data.api.Read":true,"dojo.data.api.Write":true};
return _27;
},getLabel:function(_28){
if((this.label!=="")&&this.isItem(_28)){
var _29=this.getValue(_28,this.label);
if(_29){
return _29.toString();
}
}
return undefined;
},getLabelAttributes:function(_2a){
if(this.label!==""){
return [this.label];
}
return null;
},_fetchItems:function(_2b,_2c,_2d){
var url=this._getFetchUrl(_2b);
console.log("XmlStore._fetchItems(): url="+url);
if(!url){
_2d(new Error("No URL specified."));
return;
}
var _2f=(!this.sendQuery?_2b:null);
var _30=this;
var _31={url:url,handleAs:"xml",preventCache:true};
var _32=dojo.xhrGet(_31);
_32.addCallback(function(_33){
var _34=_30._getItems(_33,_2f);
console.log("XmlStore._fetchItems(): length="+(_34?_34.length:0));
if(_34&&_34.length>0){
_2c(_34,_2b);
}else{
_2c([],_2b);
}
});
_32.addErrback(function(_35){
_2d(_35,_2b);
});
},_getFetchUrl:function(_36){
if(!this.sendQuery){
return this.url;
}
var _37=_36.query;
if(!_37){
return this.url;
}
if(dojo.isString(_37)){
return this.url+_37;
}
var _38="";
for(var _39 in _37){
var _3a=_37[_39];
if(_3a){
if(_38){
_38+="&";
}
_38+=(_39+"="+_3a);
}
}
if(!_38){
return this.url;
}
var _3b=this.url;
if(_3b.indexOf("?")<0){
_3b+="?";
}else{
_3b+="&";
}
return _3b+_38;
},_getItems:function(_3c,_3d){
var _3e=null;
if(_3d){
_3e=_3d.query;
}
var _3f=[];
var _40=null;
console.log("Looking up root item: "+this.rootItem);
if(this.rootItem!==""){
_40=_3c.getElementsByTagName(this.rootItem);
}else{
_40=_3c.documentElement.childNodes;
}
for(var i=0;i<_40.length;i++){
var _42=_40[i];
if(_42.nodeType!=1){
continue;
}
var _43=this._getItem(_42);
if(_3e){
var _44=true;
var _45=_3d.queryOptions?_3d.queryOptions.ignoreCase:false;
var _46={};
for(var key in _3e){
var _48=_3e[key];
if(typeof _48==="string"){
_46[key]=dojo.data.util.filter.patternToRegExp(_48,_45);
}
}
for(var _49 in _3e){
var _48=this.getValue(_43,_49);
if(_48){
var _4a=_3e[_49];
if((typeof _48)==="string"&&(_46[_49])){
if((_48.match(_46[_49]))!==null){
continue;
}
}else{
if((typeof _48)==="object"){
if(_48.toString&&(_46[_49])){
var _4b=_48.toString();
if((_4b.match(_46[_49]))!==null){
continue;
}
}else{
if(_4a==="*"||_4a===_48){
continue;
}
}
}
}
}
_44=false;
break;
}
if(!_44){
continue;
}
}
_3f.push(_43);
}
dojo.forEach(_3f,function(_4c){
_4c.element.parentNode.removeChild(_4c.element);
},this);
return _3f;
},close:function(_4d){
},newItem:function(_4e){
console.log("XmlStore.newItem()");
_4e=(_4e||{});
var _4f=_4e.tagName;
if(!_4f){
_4f=this.rootItem;
if(_4f===""){
return null;
}
}
var _50=this._getDocument();
var _51=_50.createElement(_4f);
for(var _52 in _4e){
if(_52==="tagName"){
continue;
}else{
if(_52==="text()"){
var _53=_50.createTextNode(_4e[_52]);
_51.appendChild(_53);
}else{
_52=this._getAttribute(_4f,_52);
if(_52.charAt(0)==="@"){
var _54=_52.substring(1);
_51.setAttribute(_54,_4e[_52]);
}else{
var _55=_50.createElement(_52);
var _53=_50.createTextNode(_4e[_52]);
_55.appendChild(_53);
_51.appendChild(_55);
}
}
}
}
var _56=this._getItem(_51);
this._newItems.push(_56);
return _56;
},deleteItem:function(_57){
console.log("XmlStore.deleteItem()");
var _58=_57.element;
if(_58.parentNode){
this._backupItem(_57);
_58.parentNode.removeChild(_58);
return true;
}
this._forgetItem(_57);
this._deletedItems.push(_57);
return true;
},setValue:function(_59,_5a,_5b){
if(_5a==="tagName"){
return false;
}
this._backupItem(_59);
var _5c=_59.element;
if(_5a==="childNodes"){
var _5d=_5b.element;
_5c.appendChild(_5d);
}else{
if(_5a==="text()"){
while(_5c.firstChild){
_5c.removeChild(_5c.firstChild);
}
var _5e=this._getDocument(_5c).createTextNode(_5b);
_5c.appendChild(_5e);
}else{
_5a=this._getAttribute(_5c.nodeName,_5a);
if(_5a.charAt(0)==="@"){
var _5f=_5a.substring(1);
_5c.setAttribute(_5f,_5b);
}else{
var _5d=null;
for(var i=0;i<_5c.childNodes.length;i++){
var _61=_5c.childNodes[i];
if(_61.nodeType===1&&_61.nodeName===_5a){
_5d=_61;
break;
}
}
var _62=this._getDocument(_5c);
if(_5d){
while(_5d.firstChild){
_5d.removeChild(_5d.firstChild);
}
}else{
_5d=_62.createElement(_5a);
_5c.appendChild(_5d);
}
var _5e=_62.createTextNode(_5b);
_5d.appendChild(_5e);
}
}
}
return true;
},setValues:function(_63,_64,_65){
if(_64==="tagName"){
return false;
}
this._backupItem(_63);
var _66=_63.element;
if(_64==="childNodes"){
while(_66.firstChild){
_66.removeChild(_66.firstChild);
}
for(var i=0;i<_65.length;i++){
var _68=_65[i].element;
_66.appendChild(_68);
}
}else{
if(_64==="text()"){
while(_66.firstChild){
_66.removeChild(_66.firstChild);
}
var _69="";
for(var i=0;i<_65.length;i++){
_69+=_65[i];
}
var _6a=this._getDocument(_66).createTextNode(_69);
_66.appendChild(_6a);
}else{
_64=this._getAttribute(_66.nodeName,_64);
if(_64.charAt(0)==="@"){
var _6b=_64.substring(1);
_66.setAttribute(_6b,_65[0]);
}else{
for(var i=_66.childNodes.length-1;i>=0;i--){
var _6c=_66.childNodes[i];
if(_6c.nodeType===1&&_6c.nodeName===_64){
_66.removeChild(_6c);
}
}
var _6d=this._getDocument(_66);
for(var i=0;i<_65.length;i++){
var _68=_6d.createElement(_64);
var _6a=_6d.createTextNode(_65[i]);
_68.appendChild(_6a);
_66.appendChild(_68);
}
}
}
}
return true;
},unsetAttribute:function(_6e,_6f){
if(_6f==="tagName"){
return false;
}
this._backupItem(_6e);
var _70=_6e.element;
if(_6f==="childNodes"||_6f==="text()"){
while(_70.firstChild){
_70.removeChild(_70.firstChild);
}
}else{
_6f=this._getAttribute(_70.nodeName,_6f);
if(_6f.charAt(0)==="@"){
var _71=_6f.substring(1);
_70.removeAttribute(_71);
}else{
for(var i=_70.childNodes.length-1;i>=0;i--){
var _73=_70.childNodes[i];
if(_73.nodeType===1&&_73.nodeName===_6f){
_70.removeChild(_73);
}
}
}
}
return true;
},save:function(_74){
if(!_74){
_74={};
}
for(var i=0;i<this._modifiedItems.length;i++){
this._saveItem(this._modifiedItems[i],_74,"PUT");
}
for(var i=0;i<this._newItems.length;i++){
var _76=this._newItems[i];
if(_76.element.parentNode){
this._newItems.splice(i,1);
i--;
continue;
}
this._saveItem(this._newItems[i],_74,"POST");
}
for(var i=0;i<this._deletedItems.length;i++){
this._saveItem(this._deletedItems[i],_74,"DELETE");
}
},revert:function(){
console.log("XmlStore.revert() _newItems="+this._newItems.length);
console.log("XmlStore.revert() _deletedItems="+this._deletedItems.length);
console.log("XmlStore.revert() _modifiedItems="+this._modifiedItems.length);
this._newItems=[];
this._restoreItems(this._deletedItems);
this._deletedItems=[];
this._restoreItems(this._modifiedItems);
this._modifiedItems=[];
return true;
},isDirty:function(_77){
if(_77){
var _78=this._getRootElement(_77.element);
return (this._getItemIndex(this._newItems,_78)>=0||this._getItemIndex(this._deletedItems,_78)>=0||this._getItemIndex(this._modifiedItems,_78)>=0);
}else{
return (this._newItems.length>0||this._deletedItems.length>0||this._modifiedItems.length>0);
}
},_saveItem:function(_79,_7a,_7b){
if(_7b==="PUT"){
url=this._getPutUrl(_79);
}else{
if(_7b==="DELETE"){
url=this._getDeleteUrl(_79);
}else{
url=this._getPostUrl(_79);
}
}
if(!url){
if(_7a.onError){
_7a.onError.call(_7c,new Error("No URL for saving content: "+postContent));
}
return;
}
var _7d={url:url,method:(_7b||"POST"),contentType:"text/xml",handleAs:"xml"};
var _7e;
if(_7b==="PUT"){
_7d.putData=this._getPutContent(_79);
saveHandler=dojo.rawXhrPut(_7d);
}else{
if(_7b==="DELETE"){
saveHandler=dojo.xhrDelete(_7d);
}else{
_7d.postData=this._getPostContent(_79);
saveHandler=dojo.rawXhrPost(_7d);
}
}
var _7c=(_7a.scope||dojo.global);
var _7f=this;
saveHandler.addCallback(function(_80){
_7f._forgetItem(_79);
if(_7a.onComplete){
_7a.onComplete.call(_7c);
}
});
saveHandler.addErrback(function(_81){
if(_7a.onError){
_7a.onError.call(_7c,_81);
}
});
},_getPostUrl:function(_82){
return this.url;
},_getPutUrl:function(_83){
return this.url;
},_getDeleteUrl:function(_84){
if(!this.url!==""){
return this.url;
}
var url=this.url;
if(_84&&this.keyAttribute!==""){
var _86=this.getValue(_84,this.keyAttribute);
if(_86){
url=url+"?"+this.keyAttribute+"="+_86;
}
}
return url;
},_getPostContent:function(_87){
var _88=_87.element;
var _89="<?xml version=\"1.0\"?>";
return _89+dojox.data.dom.innerXML(_88);
},_getPutContent:function(_8a){
var _8b=_8a.element;
var _8c="<?xml version=\"1.0\"?>";
return _8c+dojox.data.dom.innerXML(_8b);
},_getAttribute:function(_8d,_8e){
if(this._attributeMap){
var key=_8d+"."+_8e;
var _90=this._attributeMap[key];
if(_90){
_8e=_90;
}else{
_90=this._attributeMap[_8e];
if(_90){
_8e=_90;
}
}
}
return _8e;
},_getItem:function(_91){
return new dojox.data.XmlItem(_91,this);
},_getItemIndex:function(_92,_93){
for(var i=0;i<_92.length;i++){
if(_92[i].element===_93){
return i;
}
}
return -1;
},_backupItem:function(_95){
var _96=this._getRootElement(_95.element);
if(this._getItemIndex(this._newItems,_96)>=0||this._getItemIndex(this._modifiedItems,_96)>=0){
return;
}
if(_96!=_95.element){
_95=this._getItem(_96);
}
_95._backup=_96.cloneNode(true);
this._modifiedItems.push(_95);
},_restoreItems:function(_97){
dojo.forEach(_97,function(_98){
if(_98._backup){
_98.element=_98._backup;
_98._backup=null;
}
},this);
},_forgetItem:function(_99){
var _9a=_99.element;
var _9b=this._getItemIndex(this._newItems,_9a);
if(_9b>=0){
this._newItems.splice(_9b,1);
}
_9b=this._getItemIndex(this._deletedItems,_9a);
if(_9b>=0){
this._deletedItems.splice(_9b,1);
}
_9b=this._getItemIndex(this._modifiedItems,_9a);
if(_9b>=0){
this._modifiedItems.splice(_9b,1);
}
},_getDocument:function(_9c){
if(_9c){
return _9c.ownerDocument;
}else{
if(!this._document){
return dojox.data.dom.createDocument();
}
}
},_getRootElement:function(_9d){
while(_9d.parentNode){
_9d=_9d.parentNode;
}
return _9d;
}});
dojo.declare("dojox.data.XmlItem",null,{constructor:function(_9e,_9f){
this.element=_9e;
this.store=_9f;
},toString:function(){
var str="";
if(this.element){
for(var i=0;i<this.element.childNodes.length;i++){
var _a2=this.element.childNodes[i];
if(_a2.nodeType===3){
str=_a2.nodeValue;
break;
}
}
}
return str;
}});
dojo.extend(dojox.data.XmlStore,dojo.data.util.simpleFetch);
}
