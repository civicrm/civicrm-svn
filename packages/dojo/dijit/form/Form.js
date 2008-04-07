/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dijit.form.Form"]){
dojo._hasResource["dijit.form.Form"]=true;
dojo.provide("dijit.form.Form");
dojo.require("dijit._Widget");
dojo.require("dijit._Templated");
dojo.declare("dijit.form._FormMixin",null,{reset:function(){
dojo.forEach(this.getDescendants(),function(_1){
if(_1.reset){
_1.reset();
}
});
},validate:function(){
var _2=false;
return dojo.every(dojo.map(this.getDescendants(),function(_3){
_3._hasBeenBlurred=true;
var _4=!_3.validate||_3.validate();
if(!_4&&!_2){
dijit.scrollIntoView(_3.containerNode||_3.domNode);
_3.focus();
_2=true;
}
return _4;
}),"return item;");
},setValues:function(_5){
var _6={};
dojo.forEach(this.getDescendants(),function(_7){
if(!_7.name){
return;
}
var _8=_6[_7.name]||(_6[_7.name]=[]);
_8.push(_7);
});
for(var _9 in _6){
var _a=_6[_9],_b=dojo.getObject(_9,false,_5);
if(!dojo.isArray(_b)){
_b=[_b];
}
if(typeof _a[0].checked=="boolean"){
dojo.forEach(_a,function(w,i){
w.setValue(dojo.indexOf(_b,w.value)!=-1);
});
}else{
if(_a[0]._multiValue){
_a[0].setValue(_b);
}else{
dojo.forEach(_a,function(w,i){
w.setValue(_b[i]);
});
}
}
}
},getValues:function(){
var obj={};
dojo.forEach(this.getDescendants(),function(_11){
var _12=_11.name;
if(!_12){
return;
}
var _13=(_11.getValue&&!_11._getValueDeprecated)?_11.getValue():_11.value;
if(typeof _11.checked=="boolean"){
if(/Radio/.test(_11.declaredClass)){
if(_13!==false){
dojo.setObject(_12,_13,obj);
}
}else{
var ary=dojo.getObject(_12,false,obj);
if(!ary){
ary=[];
dojo.setObject(_12,ary,obj);
}
if(_13!==false){
ary.push(_13);
}
}
}else{
dojo.setObject(_12,_13,obj);
}
});
return obj;
},isValid:function(){
return dojo.every(this.getDescendants(),function(_15){
return !_15.isValid||_15.isValid();
});
}});
dojo.declare("dijit.form.Form",[dijit._Widget,dijit._Templated,dijit.form._FormMixin],{name:"",action:"",method:"",encType:"","accept-charset":"",accept:"",target:"",templateString:"<form dojoAttachPoint='containerNode' dojoAttachEvent='onreset:_onReset,onsubmit:_onSubmit' name='${name}'></form>",attributeMap:dojo.mixin(dojo.clone(dijit._Widget.prototype.attributeMap),{action:"",method:"",encType:"","accept-charset":"",accept:"",target:""}),execute:function(_16){
},onExecute:function(){
},setAttribute:function(_17,_18){
this.inherited(arguments);
switch(_17){
case "encType":
if(dojo.isIE){
this.domNode.encoding=_18;
}
}
},postCreate:function(){
if(dojo.isIE&&this.srcNodeRef&&this.srcNodeRef.attributes){
var _19=this.srcNodeRef.attributes.getNamedItem("encType");
if(_19&&!_19.specified&&(typeof _19.value=="string")){
this.setAttribute("encType",_19.value);
}
}
this.inherited(arguments);
},onReset:function(e){
return true;
},_onReset:function(e){
var _1c={returnValue:true,preventDefault:function(){
this.returnValue=false;
},stopPropagation:function(){
},currentTarget:e.currentTarget,target:e.target};
if(!(this.onReset(_1c)===false)&&_1c.returnValue){
this.reset();
}
dojo.stopEvent(e);
return false;
},_onSubmit:function(e){
var fp=dijit.form.Form.prototype;
if(this.execute!=fp.execute||this.onExecute!=fp.onExecute){
dojo.deprecated("dijit.form.Form:execute()/onExecute() are deprecated. Use onSubmit() instead.","","2.0");
this.onExecute();
this.execute(this.getValues());
}
if(this.onSubmit(e)===false){
dojo.stopEvent(e);
}
},onSubmit:function(e){
return this.isValid();
},submit:function(){
if(!(this.onSubmit()===false)){
this.containerNode.submit();
}
}});
}
