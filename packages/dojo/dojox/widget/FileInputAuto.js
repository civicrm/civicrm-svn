/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.widget.FileInputAuto"]){
dojo._hasResource["dojox.widget.FileInputAuto"]=true;
dojo.provide("dojox.widget.FileInputAuto");
dojo.require("dojox.widget.FileInput");
dojo.require("dojo.io.iframe");
dojo.declare("dojox.widget.FileInputAuto",dojox.widget.FileInput,{url:"",blurDelay:2000,duration:500,uploadMessage:"Uploading ...",_sent:false,templateString:"<div class=\"dijitFileInput\">\n\t<input id=\"${id}\" name=\"${name}\" class=\"dijitFileInputReal\" type=\"file\" dojoAttachPoint=\"fileInput\" />\n\t<div class=\"dijitFakeInput\" dojoAttachPoint=\"fakeNodeHolder\">\n\t\t<input class=\"dijitFileInputVisible\" type=\"text\" dojoAttachPoint=\"focusNode, inputNode\" />\n\t\t<div class=\"dijitInline dijitFileInputText\" dojoAttachPoint=\"titleNode\">${label}</div>\n\t\t<div class=\"dijitInline dijitFileInputButton\" dojoAttachPoint=\"cancelNode\" dojoAttachEvent=\"onclick:_onClick\">${cancelText}</div>\n\t</div>\n\t<div class=\"dijitProgressOverlay\" dojoAttachPoint=\"overlay\">&nbsp;</div>\n</div>\n",startup:function(){
this._blurListener=dojo.connect(this.fileInput,"onblur",this,"_onBlur");
this._focusListener=dojo.connect(this.fileInput,"onfocus",this,"_onFocus");
this.inherited("startup",arguments);
},_onFocus:function(){
if(this._blurTimer){
clearTimeout(this._blurTimer);
}
},_onBlur:function(){
if(this._blurTimer){
clearTimeout(this._blurTimer);
}
if(!this._sent){
this._blurTimer=setTimeout(dojo.hitch(this,"_sendFile"),this.blurDelay);
}
},setMessage:function(_1){
if(!dojo.isIE){
this.overlay.innerHTML=_1;
}
},_sendFile:function(e){
if(!this.fileInput.value||this._sent){
return;
}
dojo.style(this.fakeNodeHolder,"display","none");
dojo.style(this.overlay,"opacity","0");
dojo.style(this.overlay,"display","block");
this.setMessage(this.uploadMessage);
dojo.fadeIn({node:this.overlay,duration:this.duration}).play();
var _3;
if(dojo.isIE){
_3=document.createElement("<form enctype=\"multipart/form-data\" method=\"post\">");
_3.encoding="multipart/form-data";
}else{
_3=document.createElement("form");
_3.setAttribute("enctype","multipart/form-data");
}
_3.appendChild(this.fileInput);
dojo.body().appendChild(_3);
dojo.io.iframe.send({url:this.url+"?name="+this.name,form:_3,handleAs:"json",handle:dojo.hitch(this,"_handleSend")});
},_handleSend:function(_4,_5){
if(!dojo.isIE){
this.overlay.innerHTML="";
}
this._sent=true;
dojo.style(this.overlay,"opacity","0");
dojo.style(this.overlay,"border","none");
dojo.style(this.overlay,"background","none");
this.overlay.style.backgroundImage="none";
this.fileInput.style.display="none";
this.fakeNodeHolder.style.display="none";
dojo.fadeIn({node:this.overlay,duration:this.duration}).play(250);
dojo.disconnect(this._blurListener);
dojo.disconnect(this._focusListener);
this.onComplete(_4,_5,this);
},_onClick:function(e){
if(this._blurTimer){
clearTimeout(this._blurTimer);
}
dojo.disconnect(this._blurListener);
dojo.disconnect(this._focusListener);
this.inherited("_onClick",arguments);
this._blurListener=dojo.connect(this.fileInput,"onblur",this,"_onBlur");
this._focusListener=dojo.connect(this.fileInput,"onfocus",this,"_onFocus");
},onComplete:function(_7,_8,_9){
}});
dojo.declare("dojox.widget.FileInputBlind",dojox.widget.FileInputAuto,{startup:function(){
this.inherited("startup",arguments);
this._off=dojo.style(this.inputNode,"width");
this.inputNode.style.display="none";
this._fixPosition();
},_fixPosition:function(){
if(dojo.isIE){
dojo.style(this.fileInput,"width","1px");
}else{
dojo.style(this.fileInput,"left","-"+(this._off)+"px");
}
},_onClick:function(e){
this.inherited("_onClick",arguments);
this._fixPosition();
}});
}
