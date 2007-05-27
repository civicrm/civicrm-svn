/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/


dojo.provide("dojo.widget.ValidationTextbox");
dojo.require("dojo.widget.Textbox");
dojo.require("dojo.i18n.common");
dojo.widget.defineWidget("dojo.widget.ValidationTextbox",dojo.widget.Textbox,function(){
this.flags={};
},{required:false,rangeClass:"range",invalidClass:"invalid",missingClass:"missing",classPrefix:"dojoValidate",size:"",maxlength:"",promptMessage:"",invalidMessage:"",missingMessage:"",rangeMessage:"",listenOnKeyPress:true,htmlfloat:"none",lastCheckedValue:null,templateString:"<span style='float:${this.htmlfloat};'>\n\t<input dojoAttachPoint='textbox' type='${this.type}' dojoAttachEvent='onblur;onfocus;onkeyup'\n\t\tid='${this.widgetId}' name='${this.name}' size='${this.size}' maxlength='${this.maxlength}'\n\t\tclass='${this.className}' style=''>\n\t<span dojoAttachPoint='invalidSpan' class='${this.invalidClass}'>${this.messages.invalidMessage}</span>\n\t<span dojoAttachPoint='missingSpan' class='${this.missingClass}'>${this.messages.missingMessage}</span>\n\t<span dojoAttachPoint='rangeSpan' class='${this.rangeClass}'>${this.messages.rangeMessage}</span>\n</span>\n",templateCssString:".dojoValidateEmpty{\n\tbackground-color: #00FFFF;\n}\n.dojoValidateValid{\n\tbackground-color: #cfc;\n}\n.dojoValidateInvalid{\n\tbackground-color: #fcc;\n}\n.dojoValidateRange{\n\tbackground-color: #ccf;\n}\n",templateCssPath:dojo.uri.moduleUri("dojo.widget","templates/Validate.css"),invalidSpan:null,missingSpan:null,rangeSpan:null,getValue:function(){
return this.textbox.value;
},setValue:function(_1){
this.textbox.value=_1;
this.update();
},isValid:function(){
return true;
},isInRange:function(){
return true;
},isEmpty:function(){
return (/^\s*$/.test(this.textbox.value));
},isMissing:function(){
return (this.required&&this.isEmpty());
},update:function(){
this.lastCheckedValue=this.textbox.value;
this.missingSpan.style.display="none";
this.invalidSpan.style.display="none";
this.rangeSpan.style.display="none";
var _2=this.isEmpty();
var _3=true;
if(this.promptMessage!=this.textbox.value){
_3=this.isValid();
}
var _4=this.isMissing();
if(_4){
this.missingSpan.style.display="";
}else{
if(!_2&&!_3){
this.invalidSpan.style.display="";
}else{
if(!_2&&!this.isInRange()){
this.rangeSpan.style.display="";
}
}
}
this.highlight();
},updateClass:function(_5){
var _6=this.classPrefix;
dojo.html.removeClass(this.textbox,_6+"Empty");
dojo.html.removeClass(this.textbox,_6+"Valid");
dojo.html.removeClass(this.textbox,_6+"Invalid");
dojo.html.addClass(this.textbox,_6+_5);
},highlight:function(){
if(this.isEmpty()){
this.updateClass("Empty");
}else{
if(this.isValid()&&this.isInRange()){
this.updateClass("Valid");
}else{
if(this.textbox.value!=this.promptMessage){
this.updateClass("Invalid");
}else{
this.updateClass("Empty");
}
}
}
},onfocus:function(_7){
if(!this.listenOnKeyPress){
this.updateClass("Empty");
}
},onblur:function(_8){
this.filter();
this.update();
},onkeyup:function(_9){
if(this.listenOnKeyPress){
this.update();
}else{
if(this.textbox.value!=this.lastCheckedValue){
this.updateClass("Empty");
}
}
},postMixInProperties:function(_a,_b){
dojo.widget.ValidationTextbox.superclass.postMixInProperties.apply(this,arguments);
this.messages=dojo.i18n.getLocalization("dojo.widget","validate",this.lang);
dojo.lang.forEach(["invalidMessage","missingMessage","rangeMessage"],function(_c){
if(this[_c]){
this.messages[_c]=this[_c];
}
},this);
},fillInTemplate:function(){
dojo.widget.ValidationTextbox.superclass.fillInTemplate.apply(this,arguments);
this.textbox.isValid=function(){
this.isValid.call(this);
};
this.textbox.isMissing=function(){
this.isMissing.call(this);
};
this.textbox.isInRange=function(){
this.isInRange.call(this);
};
dojo.html.setClass(this.invalidSpan,this.invalidClass);
this.update();
this.filter();
if(dojo.render.html.ie){
dojo.html.addClass(this.domNode,"ie");
}
if(dojo.render.html.moz){
dojo.html.addClass(this.domNode,"moz");
}
if(dojo.render.html.opera){
dojo.html.addClass(this.domNode,"opera");
}
if(dojo.render.html.safari){
dojo.html.addClass(this.domNode,"safari");
}
}});
