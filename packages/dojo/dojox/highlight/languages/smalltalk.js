if(!dojo._hasResource["dojox.highlight.languages.smalltalk"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojox.highlight.languages.smalltalk"] = true;
dojo.provide("dojox.highlight.languages.smalltalk"); 
//
// Smalltalk definition (c) Vladimir Gubarkov <xonixx@gmail.com>
// released BSD, contributed under CLA to the Dojo Foundation
//
(function(){

	var SMALLTALK_KEYWORDS = {'self': 1, 'super': 1, 'nil': 1, 'true': 1, 'false': 1, 'thisContext': 1}; // only 6
	var VAR_IDENT_RE = '[a-z][a-zA-Z0-9_]*';
	
	dojo.mixin(dojox.highlight.LANGUAGES,{
		smalltalk : {
			defaultMode: {
				lexems: [UNDERSCORE_IDENT_RE],
				contains: ['comment', 'string', 'class', 'method',
			    		'number', 'symbol', 'char', 'localvars', 'array'],
				keywords: SMALLTALK_KEYWORDS
	     		},
	     		modes: [
				{
					className: 'class',
					begin: '\\b[A-Z][A-Za-z0-9_]*', end: '^',
					relevance: 0
				},
				{
					className: 'symbol',
					begin: '#' + UNDERSCORE_IDENT_RE, end: '^'
				},
				C_NUMBER_MODE,
				APOS_STRING_MODE,
				{
					className: 'comment',
					begin: '"', end: '"',
					relevance: 0
				},
				{
					className: 'method',
					begin: VAR_IDENT_RE+':', end:'^'
				},
				{
					className: 'char',
					begin: '\\$.{1}', end: '^'
				},
				{
					className: 'localvars',
					begin: '\\|\\s*(('+VAR_IDENT_RE+')\\s*)+\\|', end: '^',
					relevance: 10
				},
				{
					className: 'array',
					begin: '\\#\\(', end: '\\)',
					contains: ['string', 'char', 'number', 'symbol']
				}
			]
		}
	});
})();

}
