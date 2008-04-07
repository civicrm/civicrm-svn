/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dijit._editor.plugins.EnterKeyHandling"]){
dojo._hasResource["dijit._editor.plugins.EnterKeyHandling"]=true;
dojo.provide("dijit._editor.plugins.EnterKeyHandling");
dojo.declare("dijit._editor.plugins.EnterKeyHandling",dijit._editor._Plugin,{blockNodeForEnter:"P",constructor:function(_1){
if(_1){
dojo.mixin(this,_1);
}
},setEditor:function(_2){
this.editor=_2;
if(this.blockNodeForEnter=="BR"){
if(dojo.isIE){
_2.contentDomPreFilters.push(dojo.hitch(this,"regularPsToSingleLinePs"));
_2.contentDomPostFilters.push(dojo.hitch(this,"singleLinePsToRegularPs"));
_2.onLoadDeferred.addCallback(dojo.hitch(this,"_fixNewLineBehaviorForIE"));
}else{
_2.onLoadDeferred.addCallback(dojo.hitch(this,function(d){
try{
this.editor.document.execCommand("insertBrOnReturn",false,true);
}
catch(e){
}
return d;
}));
}
}else{
if(this.blockNodeForEnter){
dojo["require"]("dijit._editor.range");
var h=dojo.hitch(this,this.handleEnterKey);
_2.addKeyHandler(13,0,h);
_2.addKeyHandler(13,2,h);
this.connect(this.editor,"onKeyPressed","onKeyPressed");
}
}
},connect:function(o,f,tf){
if(!this._connects){
this._connects=[];
}
this._connects.push(dojo.connect(o,f,this,tf));
},destroy:function(){
dojo.forEach(this._connects,dojo.disconnect);
this._connects=[];
},onKeyPressed:function(e){
if(this._checkListLater){
if(dojo.withGlobal(this.editor.window,"isCollapsed",dijit)){
if(!dojo.withGlobal(this.editor.window,"hasAncestorElement",dijit._editor.selection,["LI"])){
dijit._editor.RichText.prototype.execCommand.apply(this.editor,["formatblock",this.blockNodeForEnter]);
var _9=dojo.withGlobal(this.editor.window,"getAncestorElement",dijit._editor.selection,[this.blockNodeForEnter]);
if(_9){
_9.innerHTML=this.bogusHtmlContent;
if(dojo.isIE){
var r=this.editor.document.selection.createRange();
r.move("character",-1);
r.select();
}
}else{
alert("onKeyPressed: Can not find the new block node");
}
}
}
this._checkListLater=false;
}else{
if(this._pressedEnterInBlock){
this.removeTrailingBr(this._pressedEnterInBlock.previousSibling);
delete this._pressedEnterInBlock;
}
}
},bogusHtmlContent:"&nbsp;",blockNodes:/^(?:H1|H2|H3|H4|H5|H6|LI)$/,handleEnterKey:function(e){
if(!this.blockNodeForEnter){
return true;
}
var _c,_d,_e;
if(e.shiftKey||this.blockNodeForEnter=="BR"){
var _f=dojo.withGlobal(this.editor.window,"getParentElement",dijit._editor.selection);
var _10=dijit.range.getAncestor(_f,this.editor.blockNodes);
if(_10){
if(_10.tagName=="LI"){
return true;
}
_c=dijit.range.getSelection(this.editor.window);
_d=_c.getRangeAt(0);
if(!_d.collapsed){
_d.deleteContents();
}
if(dijit.range.atBeginningOfContainer(_10,_d.startContainer,_d.startOffset)){
dojo.place(this.editor.document.createElement("br"),_10,"before");
}else{
if(dijit.range.atEndOfContainer(_10,_d.startContainer,_d.startOffset)){
dojo.place(this.editor.document.createElement("br"),_10,"after");
_e=dijit.range.create();
_e.setStartAfter(_10);
_c.removeAllRanges();
_c.addRange(_e);
}else{
return true;
}
}
}else{
dijit._editor.RichText.prototype.execCommand.call(this.editor,"inserthtml","<br>");
}
return false;
}
var _11=true;
_c=dijit.range.getSelection(this.editor.window);
_d=_c.getRangeAt(0);
if(!_d.collapsed){
_d.deleteContents();
}
var _12=dijit.range.getBlockAncestor(_d.endContainer,null,this.editor.editNode);
if((this._checkListLater=(_12.blockNode&&_12.blockNode.tagName=="LI"))){
return true;
}
if(!_12.blockNode){
this.editor.document.execCommand("formatblock",false,this.blockNodeForEnter);
_12={blockNode:dojo.withGlobal(this.editor.window,"getAncestorElement",dijit._editor.selection,[this.blockNodeForEnter]),blockContainer:this.editor.editNode};
if(_12.blockNode){
if(!(_12.blockNode.textContent||_12.blockNode.innerHTML).replace(/^\s+|\s+$/g,"").length){
this.removeTrailingBr(_12.blockNode);
return false;
}
}else{
_12.blockNode=this.editor.editNode;
}
_c=dijit.range.getSelection(this.editor.window);
_d=_c.getRangeAt(0);
}
var _13=this.editor.document.createElement(this.blockNodeForEnter);
_13.innerHTML=this.bogusHtmlContent;
this.removeTrailingBr(_12.blockNode);
if(dijit.range.atEndOfContainer(_12.blockNode,_d.endContainer,_d.endOffset)){
if(_12.blockNode===_12.blockContainer){
_12.blockNode.appendChild(_13);
}else{
dojo.place(_13,_12.blockNode,"after");
}
_11=false;
_e=dijit.range.create();
_e.setStart(_13,0);
_c.removeAllRanges();
_c.addRange(_e);
if(this.editor.height){
_13.scrollIntoView(false);
}
}else{
if(dijit.range.atBeginningOfContainer(_12.blockNode,_d.startContainer,_d.startOffset)){
dojo.place(_13,_12.blockNode,_12.blockNode===_12.blockContainer?"first":"before");
if(this.editor.height){
_13.scrollIntoView(false);
}
_11=false;
}else{
if(dojo.isMoz){
this._pressedEnterInBlock=_12.blockNode;
}
}
}
return _11;
},removeTrailingBr:function(_14){
var _15=/P|DIV|LI/i.test(_14.tagName)?_14:dijit._editor.selection.getParentOfType(_14,["P","DIV","LI"]);
if(!_15){
return;
}
if(_15.lastChild){
if((_15.childNodes.length>1&&_15.lastChild.nodeType==3&&/^[\s\xAD]*$/.test(_15.lastChild.nodeValue))||(_15.lastChild&&_15.lastChild.tagName=="BR")){
dojo._destroyElement(_15.lastChild);
}
}
if(!_15.childNodes.length){
_15.innerHTML=this.bogusHtmlContent;
}
},_fixNewLineBehaviorForIE:function(d){
if(this.editor.document.__INSERTED_EDITIOR_NEWLINE_CSS===undefined){
var _17="p{margin:0 !important;}";
var _18=function(_19,doc,URI){
if(!_19){
return null;
}
if(!doc){
doc=document;
}
var _1c=doc.createElement("style");
_1c.setAttribute("type","text/css");
var _1d=doc.getElementsByTagName("head")[0];
if(!_1d){
console.debug("No head tag in document, aborting styles");
return null;
}else{
_1d.appendChild(_1c);
}
if(_1c.styleSheet){
var _1e=function(){
try{
_1c.styleSheet.cssText=_19;
}
catch(e){
console.debug(e);
}
};
if(_1c.styleSheet.disabled){
setTimeout(_1e,10);
}else{
_1e();
}
}else{
var _1f=doc.createTextNode(_19);
_1c.appendChild(_1f);
}
return _1c;
};
_18(_17,this.editor.document);
this.editor.document.__INSERTED_EDITIOR_NEWLINE_CSS=true;
return d;
}
return null;
},regularPsToSingleLinePs:function(_20,_21){
function wrapLinesInPs(el){
function wrapNodes(_23){
var _24=_23[0].ownerDocument.createElement("p");
_23[0].parentNode.insertBefore(_24,_23[0]);
dojo.forEach(_23,function(_25){
_24.appendChild(_25);
});
};
var _26=0;
var _27=[];
var _28;
while(_26<el.childNodes.length){
_28=el.childNodes[_26];
if((_28.nodeName!="BR")&&(_28.nodeType==1)&&(dojo.style(_28,"display")!="block")){
_27.push(_28);
}else{
var _29=_28.nextSibling;
if(_27.length){
wrapNodes(_27);
_26=(_26+1)-_27.length;
if(_28.nodeName=="BR"){
dojo._destroyElement(_28);
}
}
_27=[];
}
_26++;
}
if(_27.length){
wrapNodes(_27);
}
};
function splitP(el){
var _2b=null;
var _2c=[];
var _2d=el.childNodes.length-1;
for(var i=_2d;i>=0;i--){
_2b=el.childNodes[i];
if(_2b.nodeName=="BR"){
var _2f=_2b.ownerDocument.createElement("p");
dojo.place(_2f,el,"after");
if(_2c.length==0&&i!=_2d){
_2f.innerHTML="&nbsp;";
}
dojo.forEach(_2c,function(_30){
_2f.appendChild(_30);
});
dojo._destroyElement(_2b);
_2c=[];
}else{
_2c.unshift(_2b);
}
}
};
var _31=[];
var ps=_20.getElementsByTagName("p");
dojo.forEach(ps,function(p){
_31.push(p);
});
dojo.forEach(_31,function(p){
if((p.previousSibling)&&(p.previousSibling.nodeName=="P"||dojo.style(p.previousSibling,"display")!="block")){
var _35=p.parentNode.insertBefore(this.document.createElement("p"),p);
_35.innerHTML=_21?"":"&nbsp;";
}
splitP(p);
},this.editor);
wrapLinesInPs(_20);
return _20;
},singleLinePsToRegularPs:function(_36){
function getParagraphParents(_37){
var ps=_37.getElementsByTagName("p");
var _39=[];
for(var i=0;i<ps.length;i++){
var p=ps[i];
var _3c=false;
for(var k=0;k<_39.length;k++){
if(_39[k]===p.parentNode){
_3c=true;
break;
}
}
if(!_3c){
_39.push(p.parentNode);
}
}
return _39;
};
function isParagraphDelimiter(_3e){
if(_3e.nodeType!=1||_3e.tagName!="P"){
return dojo.style(_3e,"display")=="block";
}else{
if(!_3e.childNodes.length||_3e.innerHTML=="&nbsp;"){
return true;
}
}
return false;
};
var _3f=getParagraphParents(_36);
for(var i=0;i<_3f.length;i++){
var _41=_3f[i];
var _42=null;
var _43=_41.firstChild;
var _44=null;
while(_43){
if(_43.nodeType!="1"||_43.tagName!="P"){
_42=null;
}else{
if(isParagraphDelimiter(_43)){
_44=_43;
_42=null;
}else{
if(_42==null){
_42=_43;
}else{
if((!_42.lastChild||_42.lastChild.nodeName!="BR")&&(_43.firstChild)&&(_43.firstChild.nodeName!="BR")){
_42.appendChild(this.editor.document.createElement("br"));
}
while(_43.firstChild){
_42.appendChild(_43.firstChild);
}
_44=_43;
}
}
}
_43=_43.nextSibling;
if(_44){
dojo._destroyElement(_44);
_44=null;
}
}
}
return _36;
}});
}
