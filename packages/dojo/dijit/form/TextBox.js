if(!dojo._hasResource["dijit.form.TextBox"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dijit.form.TextBox"] = true;
dojo.provide("dijit.form.TextBox");

dojo.require("dijit.form._FormWidget");

dojo.declare(
	"dijit.form.TextBox",
	dijit.form._FormWidget,
	{
		// summary:
		//		A generic textbox field.
		//		Serves as a base class to derive more specialized functionality in subclasses.

		//	trim: Boolean
		//		Removes leading and trailing whitespace if true.  Default is false.
		trim: false,

		//	uppercase: Boolean
		//		Converts all characters to uppercase if true.  Default is false.
		uppercase: false,

		//	lowercase: Boolean
		//		Converts all characters to lowercase if true.  Default is false.
		lowercase: false,

		//	propercase: Boolean
		//		Converts the first character of each word to uppercase if true.
		propercase: false,

		// maxlength: String
		//		HTML INPUT tag maxlength declaration.
		maxlength: "",

		templateString:"<table style=\"display: -moz-inline-stack;\" class=\"dijit dijitReset dijitInlineTable\" cellspacing=\"0\" cellpadding=\"0\"\n\tid=\"widget_${id}\" name=\"${name}\" dojoAttachEvent=\"onmouseenter:_onMouse,onmouseleave:_onMouse\" waiRole=\"presentation\">\n\t<tr class=\"dijitReset\">\n\t\t<td class=\"dijitReset dijitInputField\" width=\"100%\"\n\t\t\t><input dojoAttachPoint='textbox,focusNode' dojoAttachEvent='onfocus:_onMouse,onblur:_onMouse,onkeyup,onkeypress:_onKeyPress' autocomplete=\"off\"\n\t\t\tstyle=\"width: 100%;\" class=\"dijitInputField\" type='${type}'\n\t\t/></td>\n\t</tr>\n</table>\n",
		baseClass: "dijitTextBox",

		attributeMap: dojo.mixin(dojo.clone(dijit.form._FormWidget.prototype.attributeMap),
			{maxlength:"focusNode"}),

		getTextValue: function(){
			return this.filter(this.textbox.value);
		},

		getValue: function(){
			return this.parse(this.getTextValue(), this.constraints);
		},

		setValue: function(value, /*Boolean, optional*/ priorityChange, /*String, optional*/ formattedValue){
			var filteredValue = this.filter(value);
			if((typeof filteredValue == typeof value) && (formattedValue == null || formattedValue == undefined)){
				formattedValue = this.format(filteredValue, this.constraints);
			}
			if(formattedValue != null && formattedValue != undefined){
				this.textbox.value = formattedValue;
			}
			dijit.form.TextBox.superclass.setValue.call(this, filteredValue, priorityChange);
		},

		forWaiValuenow: function(){
			return this.getTextValue();
		},

		format: function(/* String */ value, /* Object */ constraints){
			// summary: Replacable function to convert a value to a properly formatted string
			return ((value == null || value == undefined) ? "" : (value.toString ? value.toString() : value));
		},

		parse: function(/* String */ value, /* Object */ constraints){
			// summary: Replacable function to convert a formatted string to a value
			return value;
		},

		postCreate: function(){
			// setting the value here is needed since value="" in the template causes "undefined"
			// and setting in the DOM (instead of the JS object) helps with form reset actions
			this.textbox.setAttribute("value", this.getTextValue());
			this.inherited('postCreate', arguments);

			// textbox and domNode get the same style but the css separates the 2 using !important
			if(this.srcNodeRef){
				dojo.style(this.textbox, "cssText", this.style);
				this.textbox.className += " " + this["class"];
			}

			if(dojo.isFF == 2 && this.domNode.tagName=="TABLE"){
				// work around table sizing bugs on FF2 by forcing redraw
				var node=this.domNode, _this = this;
				setTimeout(function(){
					var oldWidth = node.style.width;
					node.style.width="30em";
					setTimeout(function(){
						node.style.width = oldWidth;
					}, 0);
				 }, 0);
			}
		},

		filter: function(val){
			// summary: Apply various filters to textbox value
			if(val == undefined || val == null){ return ""; }
			else if(typeof val != "string"){ return val; }
			if(this.trim){
				val = dojo.trim(val);
			}
			if(this.uppercase){
				val = val.toUpperCase();
			}
			if(this.lowercase){
				val = val.toLowerCase();
			}
			if(this.propercase){
				val = val.replace(/[^\s]+/g, function(word){
					return word.substring(0,1).toUpperCase() + word.substring(1);
				});
			}
			return val;
		},

		// event handlers, you can over-ride these in your own subclasses
		_onBlur: function(){
			this.setValue(this.getValue(), (this.isValid ? this.isValid() : true));
		},

		onkeyup: function(){
			// TODO: it would be nice to massage the value (ie: automatic uppercase, etc) as the user types
			// but this messes up the cursor position if you are typing into the middle of a word, and
			// also trimming doesn't work correctly (it prevents spaces between words too!)
			// this.setValue(this.getValue());
		}
	}
);

}
