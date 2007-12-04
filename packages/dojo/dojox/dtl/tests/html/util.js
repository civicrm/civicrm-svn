if(!dojo._hasResource["dojox.dtl.tests.html.util"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojox.dtl.tests.html.util"] = true;
dojo.provide("dojox.dtl.tests.html.util");

dojo.require("dojox.dtl.html");
dojo.require("dojox.string.Builder");

dojox.dtl.HtmlBuffer.prototype.onClone = function(from, to){
	if(!this._clones){
		this._clones = [];
	}
	var clones = this._clones;

	for(var i = 0, group; group = clones[i]; i++){
		for(var j = 0, item; item = group[j]; j++){
			if(item === from){
				group.push(to);
				return
			}else if(item === to){
				group.push(from);
				return;
			}
		}
	}

	clones.push([from, to]);
}
dojox.dtl.HtmlBuffer.prototype.onAddEvent = function(node, type, description){
	if(!this._events){
		this._events = [];
	}
	var events = this._events;

	var found = false;
	for(var i = 0, evt; evt = events[i]; i++){
		if(evt[0] === node){
			found = true;
			evt[1] = type;
			evt[2] = description;
		}
	}

	if(!found){
		events.push([node, type, description]);
	}
}

dojox.dtl.tests.html.util.render = function(/*HtmlTemplate*/ template, /*Context*/ context) {
	var div = document.createElement("div");
	dojo.style(div, "visibility", "hidden");
	var attach = document.createElement("div");
	div.appendChild(attach);
	dojo.body().appendChild(div);

	var buffer = template.getBuffer();
	var canvas = new dojox.dtl.render.html.Render(attach);
	canvas.render(template, context, buffer);
	
	return dojox.dtl.tests.html.util.serialize(canvas.domNode, template.tokens, buffer._clones, buffer._events).toString();
}

dojox.dtl.tests.html.util.serialize = function(node, tokens, clones, events, output) {
	var types = dojox.dtl.html.types;

	if (node.nodeType == 3) {
		output.append(node.nodeValue);
	}else{
		var name = node.nodeName.toLowerCase();

		if (!output) {
			output = new dojox.string.Builder();
		}
		output.append("<").append(name);

		// Deal with attributes
		var attributes = dojo.filter(tokens, function(token){
			if(token[0] == types.attr){
				for(var i = 0, group; group = clones[i]; i++){
					var count = 0;
					for(var j = 0, item; item = group[j]; j++){
						if(item === token[1] || item === node){
							if(count++){
								return true;
							}
						}
					}
				}
			}
		});
		for(var i = 0, attribute; attribute = attributes[i]; i++){
			var value = "";
			if(attribute[2] == "class"){
				value = node.className || value;
			}else if(attribute[2] == "for"){
				value = node.htmlFor || value;
			}else if(node.getAttribute){
				value = node.getAttribute(attribute[2], 2) || value;
				if(dojo.isIE && (attribute[2] == "href" || attribute[2] == "src")){
					if(dojo.isIE){
						var hash = location.href.lastIndexOf(location.hash);
						var href = location.href.substring(0, hash).split("/");
						href.pop();
						href = href.join("/") + "/";
						if(value.indexOf(href) == 0){
							value = value.replace(href, "");
						}
						value = decodeURIComponent(value);
					}
				}
			}
			output.append(" ").append(attribute[2]).append('="').append(value.replace(/"/g, '\\"')).append('"');
		}

		// Deal with events
		if(events){
			for(var i = 0, evt; evt = events[i]; i++){
				if(evt[0] === node){
					output.append(" ").append(evt[1]).append('="').append(evt[2]).append('"');
				}
			}
		}

		if(!node.childNodes.length){
			output.append("/>");
		}else{
			output.append(">");
			dojo.forEach(node.childNodes, function(node){
				dojox.dtl.tests.html.util.serialize(node, tokens, clones, events, output);
			});
			output.append("</").append(name).append(">");
		}

		return output;
	}
}

}
