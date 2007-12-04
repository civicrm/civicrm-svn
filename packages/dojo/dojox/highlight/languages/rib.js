if(!dojo._hasResource["dojox.highlight.languages.rib"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojox.highlight.languages.rib"] = true;
dojo.provide("dojox.highlight.languages.rib"); 
//
// RenderMan Interface Bytestream (c) Konstantin Evdokimenko <qewerty@gmail.com>
// Released BSD, Contributed under CLA to the Dojo Foundation
//
dojo.mixin(dojox.highlight.LANGUAGES,{
  // summary: RenderMan Inferface Bytestream highlight definitions
  rib  : {
    defaultMode: {
      lexems: [UNDERSCORE_IDENT_RE],
      illegal: '</',
      contains: ['comment', 'string', 'number'],
      keywords: {
        'keyword': {'ReadArchive': 1, 'FrameBegin': 1, 'FrameEnd': 1, 'WorldBegin': 1, 'WorldEnd': 1,
                    'Attribute': 1, 'Display': 1, 'Option': 1, 'Format': 1, 'ShadingRate': 1,
                    'PixelFilter': 1, 'PixelSamples': 1, 'Projection': 1, 'Scale': 1, 'ConcatTransform': 1,
                    'Transform': 1, 'Translate': 1, 'Rotate': 1,
                    'Surface': 1, 'Displacement': 1, 'Atmosphere': 1,
                    'Interior': 1, 'Exterior': 1},
        'commands': {'WorldBegin': 1, 'WorldEnd': 1, 'FrameBegin': 1, 'FrameEnd': 1,
                     'ReadArchive': 1, 'ShadingRate': 1}
      }
    },
    modes: [
      HASH_COMMENT_MODE,
      C_NUMBER_MODE,
      APOS_STRING_MODE,
      QUOTE_STRING_MODE,
      BACKSLASH_ESCAPE
    ]
  }
});//rib

}
