function bbforms_escape_regexp(s) {
  return s.replace(/([\[\]\.\-\+\<\>\?\:\(\)\{\}])/g, '\\$1');
}

wp.CodeMirror.defineSimpleMode("bbforms", {
  start: [
    {regex: new RegExp("(?:" + bbforms_escape_regexp( bbforms_code.tags.join("|") ) + ")"), token: "def"},

    // String
    //{regex: /"(?:[^\\]|\\.)*?(?:"|$)/, token: "string"},
    {regex: /(?:\"|\')/, token: "string string-quote"},
    // BBCode
    {regex: /(?:\[)/, token: "literal"},
    {regex: /(?:\/)/, token: "literal"},
    {regex: /(?:\[\/)\b/, token: "literal"}, // Not working caused by bbcode mixer
    {regex: /(?:\])/, token: "literal"},
    // Keywords
    {regex: new RegExp("(?:" + bbforms_escape_regexp( bbforms_code.bbcodes.join("|") ) + ")\\b"), token: "tag"},
    {regex: new RegExp("(?:" + bbforms_escape_regexp( bbforms_code.fields.join("|") ) + ")\\b"), token: "tag"},
    {regex: new RegExp("(?:" + bbforms_escape_regexp( bbforms_code.actions.join("|") ) + ")\\b"), token: "tag"},
    {regex: new RegExp("(?:" + bbforms_escape_regexp( bbforms_code.options.join("|") ) + ")\\b"), token: "attribute"},
    // * Required
    {regex: /(?:\*)/, token: "required"},
    // In settings, set yes, no, true, false as enabled, disabled
    {regex: /(=)(?:yes|true)\b/, token: "setting-enabled"},
    {regex: /(=)(?:no|false)\b/, token: "setting-disabled"},
    // Attributes
    {regex: /(\w+)(?==)/, token: "attribute"},
    {regex: /(?<=\=)(\w+)/g, token: "string"}, // Attribute value, not working
    // Numbers
    //{regex: /0x[a-f\d]+|[-+]?(?:\.\d+|\d+\.?\d*)(?:e[-+]?\d+)?/i, token: "number"},
    // Comments
    //{regex: /\/\/.*/, token: "comment"},
    //{regex: /\/\*/, token: "comment", next: "comment"},
    // Operators
    {regex: /[=]+/, token: "operator"},
    {regex: /(?:==|===|!=|!==|>|>=|<|<=|<>|<=>|\*=|!\*=|\*==|!\*==|^=|!^=|^==|!^==|$=|!$=|$==|!$==)+/, token: "operator comparison"},
    {regex: /(?:AND|OR)+/, token: "atom"},
    //{regex: /[-+*=<>!:]+/, token: "operator"},
    {regex: /[|]+/, token: "separator"},
    // Everything not matched is a variable
    {regex: /[0-9A-Za-z@.:,;\-_!¡¿?#\\+{}$][\w$]*/, token: "string"},
  ],
  // Multi-line comment state.
  comment: [
    {regex: /.*?\*\//, token: "comment", next: "start"},
    {regex: /.*/, token: "comment"}
  ],
});