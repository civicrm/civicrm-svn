if(!dojo._hasResource["dojox.highlight.languages.xml"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojox.highlight.languages.xml"] = true;
dojo.provide("dojox.highlight.languages.xml");

// FIXME: these are reused  [stray globals]
var XML_COMMENT = {
	className: 'comment',
	begin: '<!--', end: '-->'
};

var XML_ATTR = {
	className: 'attribute',
	begin: ' [a-zA-Z-]+=', end: '^',
	contains: ['value']
};

var XML_VALUE = {
	className: 'value',
	begin: '"', end: '"'
};

dojo.mixin(dojox.highlight.LANGUAGES,{
	// summary: XML highlight definitions
	xml: {
		defaultMode: {
			contains: ['pi', 'comment', 'cdata', 'tag']
		},
		case_insensitive: true,
		modes: [
			{
				className: 'pi',
				begin: '<\\?', end: '\\?>',
				relevance: 10
			},
			XML_COMMENT,
			{
				className: 'cdata',
				begin: '<\\!\\[CDATA\\[', end: '\\]\\]>'
			},
			{
				className: 'tag',
				begin: '</?', end: '>',
				contains: ['title', 'tag_internal'],
				relevance: 1.5
			},
			{
				className: 'title',
				begin: '[A-Za-z:_][A-Za-z0-9\\._:-]+', end: '^',
				relevance: 0
			},
			{
				className: 'tag_internal',
				begin: '^', endsWithParent: true,
				contains: ['attribute'],
				relevance: 0,
				illegal: '[\\+\\.]'
			},
			XML_ATTR,
			XML_VALUE
		]
	}
});




}
