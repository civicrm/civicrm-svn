/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/


dojo.provide("dojo.widget.InternetTextbox");
dojo.require("dojo.widget.ValidationTextbox");
dojo.require("dojo.validate.web");
dojo.widget.defineWidget("dojo.widget.IpAddressTextbox",dojo.widget.ValidationTextbox,{mixInProperties:function(_1){
dojo.widget.IpAddressTextbox.superclass.mixInProperties.apply(this,arguments);
if(_1.allowdotteddecimal){
this.flags.allowDottedDecimal=(_1.allowdotteddecimal=="true");
}
if(_1.allowdottedhex){
this.flags.allowDottedHex=(_1.allowdottedhex=="true");
}
if(_1.allowdottedoctal){
this.flags.allowDottedOctal=(_1.allowdottedoctal=="true");
}
if(_1.allowdecimal){
this.flags.allowDecimal=(_1.allowdecimal=="true");
}
if(_1.allowhex){
this.flags.allowHex=(_1.allowhex=="true");
}
if(_1.allowipv6){
this.flags.allowIPv6=(_1.allowipv6=="true");
}
if(_1.allowhybrid){
this.flags.allowHybrid=(_1.allowhybrid=="true");
}
},isValid:function(){
return dojo.validate.isIpAddress(this.textbox.value,this.flags);
}});
dojo.widget.defineWidget("dojo.widget.UrlTextbox",dojo.widget.IpAddressTextbox,{mixInProperties:function(_2){
dojo.widget.UrlTextbox.superclass.mixInProperties.apply(this,arguments);
if(_2.scheme){
this.flags.scheme=(_2.scheme=="true");
}
if(_2.allowip){
this.flags.allowIP=(_2.allowip=="true");
}
if(_2.allowlocal){
this.flags.allowLocal=(_2.allowlocal=="true");
}
if(_2.allowcc){
this.flags.allowCC=(_2.allowcc=="true");
}
if(_2.allowgeneric){
this.flags.allowGeneric=(_2.allowgeneric=="true");
}
},isValid:function(){
return dojo.validate.isUrl(this.textbox.value,this.flags);
}});
dojo.widget.defineWidget("dojo.widget.EmailTextbox",dojo.widget.UrlTextbox,{mixInProperties:function(_3){
dojo.widget.EmailTextbox.superclass.mixInProperties.apply(this,arguments);
if(_3.allowcruft){
this.flags.allowCruft=(_3.allowcruft=="true");
}
},isValid:function(){
return dojo.validate.isEmailAddress(this.textbox.value,this.flags);
}});
dojo.widget.defineWidget("dojo.widget.EmailListTextbox",dojo.widget.EmailTextbox,{mixInProperties:function(_4){
dojo.widget.EmailListTextbox.superclass.mixInProperties.apply(this,arguments);
if(_4.listseparator){
this.flags.listSeparator=_4.listseparator;
}
},isValid:function(){
return dojo.validate.isEmailAddressList(this.textbox.value,this.flags);
}});
