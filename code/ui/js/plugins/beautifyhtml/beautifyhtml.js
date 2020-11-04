(function(){function B(k,d,Z,b){var g,r,h,n,X,A;d=d||{};r=d.indent_size||4;h=d.indent_char||" ";X=d.brace_style||"collapse";n=d.max_char===0?Infinity:d.max_char||250;A=d.unformatted||["a","span","bdo","em","strong","dfn","code","samp","kbd","var","cite","abbr","acronym","q","sub","sup","tt","i","b","big","small","u","s","strike","font","ins","del","pre","address","dt","h1","h2","h3","h4","h5","h6"];function i(){this.pos=0;this.token="";this.current_mode="CONTENT";this.tags={parent:"parent1",parentcount:1,parent1:""};this.tag_type="";this.token_text=this.last_token=this.last_text=this.token_type="";this.Utils={whitespace:"\n\r\t ".split(""),single_token:"br,input,link,meta,!doctype,basefont,base,area,hr,wbr,param,img,isindex,?xml,embed,?php,?,?=".split(","),extra_liners:"head,body,/html".split(","),in_array:function(C,E){for(var D=0;D<E.length;D++){if(C===E[D]){return true}}return false}};this.get_content=function(){var E="",F=[],C=false;while(this.input.charAt(this.pos)!=="<"){if(this.pos>=this.input.length){return F.length?F.join(""):["","TK_EOF"]}E=this.input.charAt(this.pos);this.pos++;this.line_char_count++;if(this.Utils.in_array(E,this.Utils.whitespace)){if(F.length){C=true}this.line_char_count--;continue}else{if(C){if(this.line_char_count>=this.max_char){F.push("\n");for(var D=0;D<this.indent_level;D++){F.push(this.indent_string)}this.line_char_count=0}else{F.push(" ");this.line_char_count++}C=false}}F.push(E)}return F.length?F.join(""):""};this.get_contents_to=function(D){if(this.pos===this.input.length){return["","TK_EOF"]}var F="";var H="";var G=new RegExp("</"+D+"\\s*>","igm");G.lastIndex=this.pos;var E=G.exec(this.input);var C=E?E.index:this.input.length;if(this.pos<C){H=this.input.substring(this.pos,C);this.pos=C}return H};this.record_tag=function(C){if(this.tags[C+"count"]){this.tags[C+"count"]++;this.tags[C+this.tags[C+"count"]]=this.indent_level}else{this.tags[C+"count"]=1;this.tags[C+this.tags[C+"count"]]=this.indent_level}this.tags[C+this.tags[C+"count"]+"parent"]=this.tags.parent;this.tags.parent=C+this.tags[C+"count"]};this.retrieve_tag=function(C){if(this.tags[C+"count"]){var D=this.tags.parent;while(D){if(C+this.tags[C+"count"]===D){break}D=this.tags[D+"parent"]}if(D){this.indent_level=this.tags[C+this.tags[C+"count"]];this.tags.parent=this.tags[D+"parent"]}delete this.tags[C+this.tags[C+"count"]+"parent"];delete this.tags[C+this.tags[C+"count"]];if(this.tags[C+"count"]===1){delete this.tags[C+"count"]}else{this.tags[C+"count"]--}}};this.get_tag=function(K){var D="",H=[],I="",F=false,E,M,J=this.pos,G=this.line_char_count;K=K!==undefined?K:false;do{if(this.pos>=this.input.length){if(K){this.pos=J;this.line_char_count=G}return H.length?H.join(""):["","TK_EOF"]}D=this.input.charAt(this.pos);this.pos++;this.line_char_count++;if(this.Utils.in_array(D,this.Utils.whitespace)){F=true;this.line_char_count--;continue}if(D==="'"||D==='"'){if(!H[1]||H[1]!=="!"){D+=this.get_unformatted(D);F=true}}if(D==="="){F=false}if(H.length&&H[H.length-1]!=="="&&D!==">"&&F){if(this.line_char_count>=this.max_char){this.print_newline(false,H);this.line_char_count=0}else{H.push(" ");this.line_char_count++}F=false}if(D==="<"){E=this.pos-1}H.push(D)}while(D!==">");var L=H.join("");var N;if(L.indexOf(" ")!==-1){N=L.indexOf(" ")}else{N=L.indexOf(">")}var C=L.substring(1,N).toLowerCase();if(L.charAt(L.length-2)==="/"||this.Utils.in_array(C,this.Utils.single_token)){if(!K){this.tag_type="SINGLE"}}else{if(C==="script"){if(!K){this.record_tag(C);this.tag_type="SCRIPT"}}else{if(C==="style"){if(!K){this.record_tag(C);this.tag_type="STYLE"}}else{if(this.is_unformatted(C,A)){I=this.get_unformatted("</"+C+">",L);H.push(I);if(E>0&&this.Utils.in_array(this.input.charAt(E-1),this.Utils.whitespace)){H.splice(0,0,this.input.charAt(E-1))}M=this.pos-1;if(this.Utils.in_array(this.input.charAt(M+1),this.Utils.whitespace)){H.push(this.input.charAt(M+1))}this.tag_type="SINGLE"}else{if(C.charAt(0)==="!"){if(C.indexOf("[if")!==-1){if(L.indexOf("!IE")!==-1){I=this.get_unformatted("-->",L);H.push(I)}if(!K){this.tag_type="START"}}else{if(C.indexOf("[endif")!==-1){this.tag_type="END";this.unindent()}else{if(C.indexOf("[cdata[")!==-1){I=this.get_unformatted("]]>",L);H.push(I);if(!K){this.tag_type="SINGLE"}}else{I=this.get_unformatted("-->",L);H.push(I);this.tag_type="SINGLE"}}}}else{if(!K){if(C.charAt(0)==="/"){this.retrieve_tag(C.substring(1));this.tag_type="END"}else{this.record_tag(C);this.tag_type="START"}if(this.Utils.in_array(C,this.Utils.extra_liners)){this.print_newline(true,this.output)}}}}}}}if(K){this.pos=J;this.line_char_count=G}return H.join("")};this.get_unformatted=function(G,D){if(D&&D.toLowerCase().indexOf(G)!==-1){return""}var E="";var C="";var F=true;do{if(this.pos>=this.input.length){return C}E=this.input.charAt(this.pos);this.pos++;if(this.Utils.in_array(E,this.Utils.whitespace)){if(!F){this.line_char_count--;continue}if(E==="\n"||E==="\r"){C+="\n";this.line_char_count=0;continue}}C+=E;this.line_char_count++;F=true}while(C.toLowerCase().indexOf(G)===-1);return C};this.get_token=function(){var E;if(this.last_token==="TK_TAG_SCRIPT"||this.last_token==="TK_TAG_STYLE"){var D=this.last_token.substr(7);E=this.get_contents_to(D);if(typeof E!=="string"){return E}return[E,"TK_"+D]}if(this.current_mode==="CONTENT"){E=this.get_content();if(typeof E!=="string"){return E}else{return[E,"TK_CONTENT"]}}if(this.current_mode==="TAG"){E=this.get_tag();if(typeof E!=="string"){return E}else{var C="TK_TAG_"+this.tag_type;return[E,C]}}};this.get_full_indent=function(C){C=this.indent_level+C||0;if(C<1){return""}return Array(C+1).join(this.indent_string)};this.is_unformatted=function(C,E){if(!this.Utils.in_array(C,E)){return false}if(C.toLowerCase()!=="a"||!this.Utils.in_array("a",E)){return true}var F=this.get_tag(true);var D=(F||"").match(/^\s*<\s*\/?([a-z]*)\s*[^>]*>\s*$/);if(!D||this.Utils.in_array(D,E)){return true}else{return false}};this.printer=function(D,H,F,E,C){this.input=D||"";this.output=[];this.indent_character=H;this.indent_string="";this.indent_size=F;this.brace_style=C;this.indent_level=0;this.max_char=E;this.line_char_count=0;for(var G=0;G<this.indent_size;G++){this.indent_string+=this.indent_character}this.print_newline=function(J,K){this.line_char_count=0;if(!K||!K.length){return}if(!J){while(this.Utils.in_array(K[K.length-1],this.Utils.whitespace)){K.pop()}}K.push("\n");for(var I=0;I<this.indent_level;I++){K.push(this.indent_string)}};this.print_token=function(I){this.output.push(I)};this.indent=function(){this.indent_level++};this.unindent=function(){if(this.indent_level>0){this.indent_level--}}};return this}g=new i();g.printer(k,h,r,n,X);while(true){var f=g.get_token();g.token_text=f[0];g.token_type=f[1];if(g.token_type==="TK_EOF"){break}switch(g.token_type){case"TK_TAG_START":g.print_newline(false,g.output);g.print_token(g.token_text);g.indent();g.current_mode="CONTENT";break;case"TK_TAG_STYLE":case"TK_TAG_SCRIPT":g.print_newline(false,g.output);g.print_token(g.token_text);g.current_mode="CONTENT";break;case"TK_TAG_END":if(g.last_token==="TK_CONTENT"&&g.last_text===""){var e=g.token_text.match(/\w+/)[0];var o=g.output[g.output.length-1].match(/<\s*(\w+)/);if(o===null||o[1]!==e){g.print_newline(true,g.output)}}g.print_token(g.token_text);g.current_mode="CONTENT";break;case"TK_TAG_SINGLE":var m=g.token_text.match(/^\s*<([a-z]+)/i);if(!m||!g.Utils.in_array(m[1],A)){g.print_newline(false,g.output)}g.print_token(g.token_text);g.current_mode="CONTENT";break;case"TK_CONTENT":if(g.token_text!==""){g.print_token(g.token_text)}g.current_mode="TAG";break;case"TK_STYLE":case"TK_SCRIPT":if(g.token_text!==""){g.output.push("\n");var l=g.token_text,Y,p=1;if(g.token_type==="TK_SCRIPT"){Y=typeof Z==="function"&&Z}else{if(g.token_type==="TK_STYLE"){Y=typeof b==="function"&&b}}if(d.indent_scripts==="keep"){p=0}else{if(d.indent_scripts==="separate"){p=-g.indent_level}}var j=g.get_full_indent(p);if(Y){l=Y(l.replace(/^\s*/,j),d)}else{var q=l.match(/^\s*/)[0];var c=q.match(/[^\n\r]*$/)[0].split(g.indent_string).length-1;var a=g.get_full_indent(p-c);l=l.replace(/^\s*/,j).replace(/\r\n|\r|\n/g,"\n"+a).replace(/\s*$/,"")}if(l){g.print_token(l);g.print_newline(true,g.output)}}g.current_mode="TAG";break}g.last_token=g.token_type;g.last_text=g.token_text}return g.output.join("")}window.html_beautify=function(A,D){return B(A,D,window.js_beautify,window.css_beautify)}}());