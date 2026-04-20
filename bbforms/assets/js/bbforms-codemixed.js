function bbforms_escape_regexp(s) {
  return s.replace(/([\[\]\.\-\+\<\>\?\:\(\)\{\}])/g, '\\$1');
}

wp.CodeMirror.defineMode("bbformsmixed", function(config) {
  var regs, helpers, parsers;
  htmlMixedMode = wp.CodeMirror.getMode(config, "htmlmixed");
  bbformsMode = wp.CodeMirror.getMode(config, "bbforms");

  regs = {
    bbformsDelimiter: /.*\[/,
    htmlDelimiter: /[^\<\>]*\[/,
    bbformsOpen: /\[\]/,
    bbformsClose: /\[\/\]/,
    sanitize: /([\[\]\.\-\+\<\>\?\:\(\)\{\}])/g,
    bbcodes: new RegExp("(?:" + bbforms_escape_regexp( bbforms_codemixed.bbcodes.join("|") ) + ")\\b"),
  };

  helpers = {
    chain: function(stream, state, parser) {
      state.tokenize = parser;
      return parser(stream, state);
    },
    cleanChain: function(stream, state, parser) {
      state.tokenize = null;
      state.localState = null;
      state.localMode = null;
      return (typeof parser == "string") ? (parser ? parser : null) : parser(stream, state);
    },
    maybeBackup: function(stream, pat, style) {
      pat = pat.replace(regs.sanitize, '\\$1'); // sanitize reg
      var cur = stream.current();
      var close = cur.search(pat);

      if (close > - 1) {
        stream.backUp(cur.length - close);
      } else if (cur.match(/<\/?$/)) {
        stream.backUp(cur.length);

        if (!stream.match(pat, false)) {
          stream.match(cur[0]);
        }
      }

      return style;
    }
  };

  parsers = {
    html: function(stream, state) {
      if (!state.inLiteral && stream.match(regs.htmlDelimiter, false) && state.htmlMixedState.htmlState.tagName === null) {

        state.tokenize = parsers.bbforms;
        state.localMode = bbformsMode;
        state.localState = bbformsMode.startState(htmlMixedMode.indent(state.htmlMixedState, ""));

        return helpers.maybeBackup(stream, "[", bbformsMode.token(stream, state.localState));

      } else if (!state.inLiteral && stream.match("[", false)) {

        state.tokenize = parsers.bbforms;
        state.localMode = bbformsMode;
        state.localState = bbformsMode.startState(htmlMixedMode.indent(state.htmlMixedState, ""));

        return helpers.maybeBackup(stream, "[", bbformsMode.token(stream, state.localState));
      }

      return htmlMixedMode.token(stream, state.htmlMixedState);
    },

    bbforms: function( stream, state ) {
      // Switch between inString state
      if (stream.match("'", false) || stream.match('"', false)) {
        state.inString = ! state.inString;
      }

      // BBCode open
      if (stream.match("[", false)) {
        stream.eat("[");
        //state.inLiteral = true;

        if( state.inString ) {

          if( parsers.isValidCode( stream ) ) {
            state.inValidCode = true;
            return "literal literal-open-string";
          }

          return "string";
        } else {
          return "literal literal-open";
        }

      }

      // BBCode close
      if (stream.match("]", false)) {
        stream.eat("]");
        //state.inLiteral = false;

        if( state.inString ) {
          if( state.inValidCode ) {
            state.inValidCode = false;
            return "literal literal-close-string";
          }

          return "string";
        } else {
          state.tokenize = parsers.html;
          state.localMode = htmlMixedMode;
          state.localState = state.htmlMixedState;
          return "literal literal-close";
        }


        //return bbformsMode.token(stream, state);
      }

      return helpers.maybeBackup(stream, "]", bbformsMode.token(stream, state.localState));
    },

    isValidCode: function( stream ) {
      var validCode = false;

      var str = "";

      var char = null;
      var nextChar = true;
      while ( nextChar ) {
        char = stream.next();

        if( /[/]/.test(char) ) {
          // continue we are matching [/BBCODE]
        } else if( char === undefined ) {
          nextChar = false;
        } else if( /[a-zA-Z0-9_]/.test(char) ) {
          str += char;
        } else {
          nextChar = false;
        }

      }

      if (regs.bbcodes.test(str)) {
        validCode = true;
      }

      return validCode;
    },

    inBlock: function(style, terminator) {
      return function(stream, state) {
        while (!stream.eol()) {
          if (stream.match(terminator)) {
            helpers.cleanChain(stream, state, "");
            break;
          }
          stream.next();
        }
        return style;
      };
    }
  };

  return {
    startState: function() {
      var state = htmlMixedMode.startState();
      return {
        token: parsers.html,
        localMode: null,
        localState: null,
        htmlMixedState: state,
        tokenize: null,
        inLiteral: false,
        inString: false,
        inValidCode: false,
      };
    },
    copyState: function(state) {
      var local = null;
      var tok = (state.tokenize || state.token);

      if (state.localState) {
        local = wp.CodeMirror.copyState((tok != parsers.html ? bbformsMode : htmlMixedMode), state.localState);
      }
      return {
        token: state.token,
        tokenize: state.tokenize,
        localMode: state.localMode,
        localState: local,
        htmlMixedState: wp.CodeMirror.copyState(htmlMixedMode, state.htmlMixedState),
        inLiteral: state.inLiteral,
        inString: state.inString,
        inValidCode: state.inValidCode,
      };
    },
    token: function(stream, state) {
      if ( stream.match("[", false) ) {
        if ( ! state.inLiteral && stream.match(regs.bbformsOpen, true) ) {
          state.inLiteral = true;
          return "keyword";
        } else if ( state.inLiteral && stream.match(regs.bbformsClose, true) ) {
          state.inLiteral = false;
          return "keyword";
        }
      }

      if (state.inLiteral && state.localState != state.htmlMixedState) {
        state.tokenize = parsers.html;
        state.localMode = htmlMixedMode;
        state.localState = state.htmlMixedState;
      }

      var style = (state.tokenize || state.token)(stream, state);
      return style;
    },
    indent: function(state, textAfter) {
      if (state.localMode == bbformsMode
          || (state.inLiteral && !state.localMode)
        || regs.bbformsDelimiter.test(textAfter)) {
          return wp.CodeMirror.Pass;
      }
      return htmlMixedMode.indent(state.htmlMixedState, textAfter);
    },
    innerMode: function(state) {
      return {
        state: state.localState || state.htmlMixedState,
        mode: state.localMode || htmlMixedMode
      };
    }
  };
},
"htmlmixed");

wp.CodeMirror.defineMIME("text/x-bbforms", "bbformsmixed");
