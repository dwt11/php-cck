(function(D,C){if(typeof define==="function"&&define.amd){define("simple-hotkeys",["jquery","simple-module"],function(A,B){return(D.returnExportsGlobal=C(A,B))})}else{if(typeof exports==="object"){module.exports=C(require("jquery"),require("simple-module"))}else{D.simple=D.simple||{};D.simple["hotkeys"]=C(jQuery,SimpleModule)}}}(this,function(G,H){var J,K,I={}.hasOwnProperty,L=function(A,B){for(var C in B){if(I.call(B,C)){A[C]=B[C]}}function D(){this.constructor=A}D.prototype=B.prototype;A.prototype=new D();A.__super__=B.prototype;return A};J=(function(B){L(A,B);function A(){return A.__super__.constructor.apply(this,arguments)}A.count=0;A.keyNameMap={8:"Backspace",9:"Tab",13:"Enter",16:"Shift",17:"Control",18:"Alt",19:"Pause",20:"CapsLock",27:"Esc",32:"Spacebar",33:"PageUp",34:"PageDown",35:"End",36:"Home",37:"Left",38:"Up",39:"Right",40:"Down",45:"Insert",46:"Del",91:"Meta",93:"Meta",48:"0",49:"1",50:"2",51:"3",52:"4",53:"5",54:"6",55:"7",56:"8",57:"9",65:"A",66:"B",67:"C",68:"D",69:"E",70:"F",71:"G",72:"H",73:"I",74:"J",75:"K",76:"L",77:"M",78:"N",79:"O",80:"P",81:"Q",82:"R",83:"S",84:"T",85:"U",86:"V",87:"W",88:"X",89:"Y",90:"Z",96:"0",97:"1",98:"2",99:"3",100:"4",101:"5",102:"6",103:"7",104:"8",105:"9",106:"Multiply",107:"Add",109:"Subtract",110:"Decimal",111:"Divide",112:"F1",113:"F2",114:"F3",115:"F4",116:"F5",117:"F6",118:"F7",119:"F8",120:"F9",121:"F10",122:"F11",123:"F12",124:"F13",125:"F14",126:"F15",127:"F16",128:"F17",129:"F18",130:"F19",131:"F20",132:"F21",133:"F22",134:"F23",135:"F24",59:";",61:"=",186:";",187:"=",188:",",190:".",191:"/",192:"`",219:"[",220:"\\",221:"]",222:"'"};A.aliases={"escape":"esc","delete":"del","return":"enter","ctrl":"control","space":"spacebar","ins":"insert","cmd":"meta","command":"meta","wins":"meta","windows":"meta"};A.normalize=function(C){var R,D,P,E,Q,F;E=C.toLowerCase().replace(/\s+/gi,"").split("+");for(R=Q=0,F=E.length;Q<F;R=++Q){D=E[R];E[R]=this.aliases[D]||D}P=E.pop();E.sort().push(P);return E.join("_")};A.prototype.opts={el:document};A.prototype._init=function(){this.id=++this.constructor.count;this._map={};this._delegate=typeof this.opts.el==="string"?document:this.opts.el;return G(this._delegate).on("keydown.simple-hotkeys-"+this.id,this.opts.el,(function(C){return function(E){var D;return(D=C._getHander(E))!=null?D.call(C,E):void 0}})(this))};A.prototype._getHander=function(D){var E,C;if(!(E=this.constructor.keyNameMap[D.which])){return}C="";if(D.altKey){C+="alt_"}if(D.ctrlKey){C+="control_"}if(D.metaKey){C+="meta_"}if(D.shiftKey){C+="shift_"}C+=E.toLowerCase();return this._map[C]};A.prototype.respondTo=function(C){if(typeof C==="string"){return this._map[this.constructor.normalize(C)]!=null}else{return this._getHander(C)!=null}};A.prototype.add=function(C,D){this._map[this.constructor.normalize(C)]=D;return this};A.prototype.remove=function(C){delete this._map[this.constructor.normalize(C)];return this};A.prototype.destroy=function(){G(this._delegate).off(".simple-hotkeys-"+this.id);this._map={};return this};return A})(H);K=function(A){return new J(A)};return K}));