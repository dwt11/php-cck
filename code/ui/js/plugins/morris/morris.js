(function(){var R,J,O,K,P=[].slice,N=function(B,A){return function(){return B.apply(A,arguments)}},M={}.hasOwnProperty,L=function(A,B){for(var D in B){if(M.call(B,D)){A[D]=B[D]}}function C(){this.constructor=A}C.prototype=B.prototype;A.prototype=new C();A.__super__=B.prototype;return A},Q=[].indexOf||function(B){for(var C=0,A=this.length;C<A;C++){if(C in this&&this[C]===B){return C}}return -1};J=window.Morris={};R=jQuery;J.EventEmitter=(function(){function A(){}A.prototype.on=function(C,B){if(this.handlers==null){this.handlers={}}if(this.handlers[C]==null){this.handlers[C]=[]}this.handlers[C].push(B);return this};A.prototype.fire=function(){var G,H,E,F,B,D,C;E=arguments[0],G=2<=arguments.length?P.call(arguments,1):[];if((this.handlers!=null)&&(this.handlers[E]!=null)){D=this.handlers[E];C=[];for(F=0,B=D.length;F<B;F++){H=D[F];C.push(H.apply(null,G))}return C}};return A})();J.commas=function(C){var E,D,B,A;if(C!=null){B=C<0?"-":"";E=Math.abs(C);D=Math.floor(E).toFixed(0);B+=D.replace(/(?=(?:\d{3})+$)(?!^)/g,",");A=E.toString();if(A.length>D.length){B+=A.slice(D.length)}return B}else{return"-"}};J.pad2=function(A){return(A<10?"0":"")+A};J.Grid=(function(B){L(A,B);function A(D){this.resizeHandler=N(this.resizeHandler,this);var C=this;if(typeof D.element==="string"){this.el=R(document.getElementById(D.element))}else{this.el=R(D.element)}if((this.el==null)||this.el.length===0){throw new Error("Graph container element not found")}if(this.el.css("position")==="static"){this.el.css("position","relative")}this.options=R.extend({},this.gridDefaults,this.defaults||{},D);if(typeof this.options.units==="string"){this.options.postUnits=D.units}this.raphael=new Raphael(this.el[0]);this.elementWidth=null;this.elementHeight=null;this.dirty=false;this.selectFrom=null;if(this.init){this.init()}this.setData(this.options.data);this.el.bind("mousemove",function(I){var H,G,T,F,E;G=C.el.offset();E=I.pageX-G.left;if(C.selectFrom){H=C.data[C.hitTest(Math.min(E,C.selectFrom))]._x;T=C.data[C.hitTest(Math.max(E,C.selectFrom))]._x;F=T-H;return C.selectionRect.attr({x:H,width:F})}else{return C.fire("hovermove",E,I.pageY-G.top)}});this.el.bind("mouseleave",function(E){if(C.selectFrom){C.selectionRect.hide();C.selectFrom=null}return C.fire("hoverout")});this.el.bind("touchstart touchmove touchend",function(G){var F,E;E=G.originalEvent.touches[0]||G.originalEvent.changedTouches[0];F=C.el.offset();C.fire("hover",E.pageX-F.left,E.pageY-F.top);return E});this.el.bind("click",function(F){var E;E=C.el.offset();return C.fire("gridclick",F.pageX-E.left,F.pageY-E.top)});if(this.options.rangeSelect){this.selectionRect=this.raphael.rect(0,0,0,this.el.innerHeight()).attr({fill:this.options.rangeSelectColor,stroke:false}).toBack().hide();this.el.bind("mousedown",function(F){var E;E=C.el.offset();return C.startRange(F.pageX-E.left)});this.el.bind("mouseup",function(F){var E;E=C.el.offset();C.endRange(F.pageX-E.left);return C.fire("hovermove",F.pageX-E.left,F.pageY-E.top)})}if(this.options.resize){R(window).bind("resize",function(E){if(C.timeoutId!=null){window.clearTimeout(C.timeoutId)}return C.timeoutId=window.setTimeout(C.resizeHandler,100)})}if(this.postInit){this.postInit()}}A.prototype.gridDefaults={dateFormat:null,axes:true,grid:true,gridLineColor:"#aaa",gridStrokeWidth:0.5,gridTextColor:"#888",gridTextSize:12,gridTextFamily:"sans-serif",gridTextWeight:"normal",hideHover:false,yLabelFormat:null,xLabelAngle:0,numLines:5,padding:25,parseTime:true,postUnits:"",preUnits:"",ymax:"auto",ymin:"auto 0",goals:[],goalStrokeWidth:1,goalLineColors:["#666633","#999966","#cc6666","#663333"],events:[],eventStrokeWidth:1,eventLineColors:["#005a04","#ccffbb","#3a5f0b","#005502"],rangeSelect:null,rangeSelectColor:"#eef",resize:false};A.prototype.setData=function(e,I){var G,C,i,g,H,D,f,h,d,l,k,F,j,E,c;if(I==null){I=true}this.options.data=e;if((e==null)||e.length===0){this.data=[];this.raphael.clear();if(this.hover!=null){this.hover.hide()}return}F=this.cumulative?0:null;j=this.cumulative?0:null;if(this.options.goals.length>0){H=Math.min.apply(Math,this.options.goals);g=Math.max.apply(Math,this.options.goals);j=j!=null?Math.min(j,H):H;F=F!=null?Math.max(F,g):g}this.data=(function(){var U,T,S;S=[];for(i=U=0,T=e.length;U<T;i=++U){f=e[i];D={src:f};D.label=f[this.options.xkey];if(this.options.parseTime){D.x=J.parseDate(D.label);if(this.options.dateFormat){D.label=this.options.dateFormat(D.x)}else{if(typeof D.label==="number"){D.label=new Date(D.label).toString()}}}else{D.x=i;if(this.options.xLabelFormat){D.label=this.options.xLabelFormat(D)}}d=0;D.y=(function(){var V,W,X,Y;X=this.options.ykeys;Y=[];for(C=V=0,W=X.length;V<W;C=++V){k=X[C];E=f[k];if(typeof E==="string"){E=parseFloat(E)}if((E!=null)&&typeof E!=="number"){E=null}if(E!=null){if(this.cumulative){d+=E}else{if(F!=null){F=Math.max(E,F);j=Math.min(E,j)}else{F=j=E}}}if(this.cumulative&&(d!=null)){F=Math.max(d,F);j=Math.min(d,j)}Y.push(E)}return Y}).call(this);S.push(D)}return S}).call(this);if(this.options.parseTime){this.data=this.data.sort(function(S,T){return(S.x>T.x)-(T.x>S.x)})}this.xmin=this.data[0].x;this.xmax=this.data[this.data.length-1].x;this.events=[];if(this.options.events.length>0){if(this.options.parseTime){this.events=(function(){var S,V,T,U;T=this.options.events;U=[];for(S=0,V=T.length;S<V;S++){G=T[S];U.push(J.parseDate(G))}return U}).call(this)}else{this.events=this.options.events}this.xmax=Math.max(this.xmax,Math.max.apply(Math,this.events));this.xmin=Math.min(this.xmin,Math.min.apply(Math,this.events))}if(this.xmin===this.xmax){this.xmin-=1;this.xmax+=1}this.ymin=this.yboundary("min",j);this.ymax=this.yboundary("max",F);if(this.ymin===this.ymax){if(j){this.ymin-=1}this.ymax+=1}if(((c=this.options.axes)===true||c==="both"||c==="y")||this.options.grid===true){if(this.options.ymax===this.gridDefaults.ymax&&this.options.ymin===this.gridDefaults.ymin){this.grid=this.autoGridLines(this.ymin,this.ymax,this.options.numLines);this.ymin=Math.min(this.ymin,this.grid[0]);this.ymax=Math.max(this.ymax,this.grid[this.grid.length-1])}else{h=(this.ymax-this.ymin)/(this.options.numLines-1);this.grid=(function(){var U,S,T,V;V=[];for(l=U=S=this.ymin,T=this.ymax;h>0?U<=T:U>=T;l=U+=h){V.push(l)}return V}).call(this)}}this.dirty=true;if(I){return this.redraw()}};A.prototype.yboundary=function(E,D){var C,F;C=this.options["y"+E];if(typeof C==="string"){if(C.slice(0,4)==="auto"){if(C.length>5){F=parseInt(C.slice(5),10);if(D==null){return F}return Math[E](D,F)}else{if(D!=null){return D}else{return 0}}}else{return parseInt(C,10)}}else{return C}};A.prototype.autoGridLines=function(X,a,E){var I,G,Y,H,D,C,Z,b,F;D=a-X;F=Math.floor(Math.log(D)/Math.log(10));Z=Math.pow(10,F);G=Math.floor(X/Z)*Z;I=Math.ceil(a/Z)*Z;C=(I-G)/(E-1);if(Z===1&&C>1&&Math.ceil(C)!==C){C=Math.ceil(C);I=G+C*(E-1)}if(G<0&&I>0){G=Math.floor(X/C)*C;I=Math.ceil(a/C)*C}if(C<1){H=Math.floor(Math.log(C)/Math.log(10));Y=(function(){var S,T;T=[];for(b=S=G;C>0?S<=I:S>=I;b=S+=C){T.push(parseFloat(b.toFixed(1-H)))}return T})()}else{Y=(function(){var S,T;T=[];for(b=S=G;C>0?S<=I:S>=I;b=S+=C){T.push(b)}return T})()}return Y};A.prototype._calc=function(){var F,D,I,T,C,H,E,G;C=this.el.width();I=this.el.height();if(this.elementWidth!==C||this.elementHeight!==I||this.dirty){this.elementWidth=C;this.elementHeight=I;this.dirty=false;this.left=this.options.padding;this.right=this.elementWidth-this.options.padding;this.top=this.options.padding;this.bottom=this.elementHeight-this.options.padding;if((E=this.options.axes)===true||E==="both"||E==="y"){H=(function(){var Y,X,Z,S;Z=this.grid;S=[];for(Y=0,X=Z.length;Y<X;Y++){D=Z[Y];S.push(this.measureText(this.yAxisFormat(D)).width)}return S}).call(this);this.left+=Math.max.apply(Math,H)}if((G=this.options.axes)===true||G==="both"||G==="x"){F=(function(){var W,X,S;S=[];for(T=W=0,X=this.data.length;0<=X?W<X:W>X;T=0<=X?++W:--W){S.push(this.measureText(this.data[T].text,-this.options.xLabelAngle).height)}return S}).call(this);this.bottom-=Math.max.apply(Math,F)}this.width=Math.max(1,this.right-this.left);this.height=Math.max(1,this.bottom-this.top);this.dx=this.width/(this.xmax-this.xmin);this.dy=this.height/(this.ymax-this.ymin);if(this.calc){return this.calc()}}};A.prototype.transY=function(C){return this.bottom-(C-this.ymin)*this.dy};A.prototype.transX=function(C){if(this.data.length===1){return(this.left+this.right)/2}else{return this.left+(C-this.xmin)*this.dx}};A.prototype.redraw=function(){this.raphael.clear();this._calc();this.drawGrid();this.drawGoals();this.drawEvents();if(this.draw){return this.draw()}};A.prototype.measureText=function(E,D){var F,C;if(D==null){D=0}C=this.raphael.text(100,100,E).attr("font-size",this.options.gridTextSize).attr("font-family",this.options.gridTextFamily).attr("font-weight",this.options.gridTextWeight).rotate(D);F=C.getBBox();C.remove();return F};A.prototype.yAxisFormat=function(C){return this.yLabelFormat(C)};A.prototype.yLabelFormat=function(C){if(typeof this.options.yLabelFormat==="function"){return this.options.yLabelFormat(C)}else{return""+this.options.preUnits+(J.commas(C))+this.options.postUnits}};A.prototype.drawGrid=function(){var T,H,C,F,E,D,I,G;if(this.options.grid===false&&((E=this.options.axes)!==true&&E!=="both"&&E!=="y")){return}D=this.grid;G=[];for(C=0,F=D.length;C<F;C++){T=D[C];H=this.transY(T);if((I=this.options.axes)===true||I==="both"||I==="y"){this.drawYAxisLabel(this.left-this.options.padding/2,H,this.yAxisFormat(T))}if(this.options.grid){G.push(this.drawGridLine("M"+this.left+","+H+"H"+(this.left+this.width)))}else{G.push(void 0)}}return G};A.prototype.drawGoals=function(){var H,G,I,F,E,D,C;D=this.options.goals;C=[];for(I=F=0,E=D.length;F<E;I=++F){G=D[I];H=this.options.goalLineColors[I%this.options.goalLineColors.length];C.push(this.drawGoal(G,H))}return C};A.prototype.drawEvents=function(){var H,G,I,F,E,D,C;D=this.events;C=[];for(I=F=0,E=D.length;F<E;I=++F){G=D[I];H=this.options.eventLineColors[I%this.options.eventLineColors.length];C.push(this.drawEvent(G,H))}return C};A.prototype.drawGoal=function(C,D){return this.raphael.path("M"+this.left+","+(this.transY(C))+"H"+this.right).attr("stroke",D).attr("stroke-width",this.options.goalStrokeWidth)};A.prototype.drawEvent=function(C,D){return this.raphael.path("M"+(this.transX(C))+","+this.bottom+"V"+this.top).attr("stroke",D).attr("stroke-width",this.options.eventStrokeWidth)};A.prototype.drawYAxisLabel=function(D,C,E){return this.raphael.text(D,C,E).attr("font-size",this.options.gridTextSize).attr("font-family",this.options.gridTextFamily).attr("font-weight",this.options.gridTextWeight).attr("fill",this.options.gridTextColor).attr("text-anchor","end")};A.prototype.drawGridLine=function(C){return this.raphael.path(C).attr("stroke",this.options.gridLineColor).attr("stroke-width",this.options.gridStrokeWidth)};A.prototype.startRange=function(C){this.hover.hide();this.selectFrom=C;return this.selectionRect.attr({x:C,width:0}).show()};A.prototype.endRange=function(D){var E,C;if(this.selectFrom){C=Math.min(this.selectFrom,D);E=Math.max(this.selectFrom,D);this.options.rangeSelect.call(this.el,{start:this.data[this.hitTest(C)].x,end:this.data[this.hitTest(E)].x});return this.selectFrom=null}};A.prototype.resizeHandler=function(){this.timeoutId=null;this.raphael.setSize(this.el.width(),this.el.height());return this.redraw()};return A})(J.EventEmitter);J.parseDate=function(W){var G,F,B,C,H,D,A,E,X,V,I;if(typeof W==="number"){return W}F=W.match(/^(\d+) Q(\d)$/);C=W.match(/^(\d+)-(\d+)$/);H=W.match(/^(\d+)-(\d+)-(\d+)$/);A=W.match(/^(\d+) W(\d+)$/);E=W.match(/^(\d+)-(\d+)-(\d+)[ T](\d+):(\d+)(Z|([+-])(\d\d):?(\d\d))?$/);X=W.match(/^(\d+)-(\d+)-(\d+)[ T](\d+):(\d+):(\d+(\.\d+)?)(Z|([+-])(\d\d):?(\d\d))?$/);if(F){return new Date(parseInt(F[1],10),parseInt(F[2],10)*3-1,1).getTime()}else{if(C){return new Date(parseInt(C[1],10),parseInt(C[2],10)-1,1).getTime()}else{if(H){return new Date(parseInt(H[1],10),parseInt(H[2],10)-1,parseInt(H[3],10)).getTime()}else{if(A){V=new Date(parseInt(A[1],10),0,1);if(V.getDay()!==4){V.setMonth(0,1+((4-V.getDay())+7)%7)}return V.getTime()+parseInt(A[2],10)*604800000}else{if(E){if(!E[6]){return new Date(parseInt(E[1],10),parseInt(E[2],10)-1,parseInt(E[3],10),parseInt(E[4],10),parseInt(E[5],10)).getTime()}else{D=0;if(E[6]!=="Z"){D=parseInt(E[8],10)*60+parseInt(E[9],10);if(E[7]==="+"){D=0-D}}return Date.UTC(parseInt(E[1],10),parseInt(E[2],10)-1,parseInt(E[3],10),parseInt(E[4],10),parseInt(E[5],10)+D)}}else{if(X){I=parseFloat(X[6]);G=Math.floor(I);B=Math.round((I-G)*1000);if(!X[8]){return new Date(parseInt(X[1],10),parseInt(X[2],10)-1,parseInt(X[3],10),parseInt(X[4],10),parseInt(X[5],10),G,B).getTime()}else{D=0;if(X[8]!=="Z"){D=parseInt(X[10],10)*60+parseInt(X[11],10);if(X[9]==="+"){D=0-D}}return Date.UTC(parseInt(X[1],10),parseInt(X[2],10)-1,parseInt(X[3],10),parseInt(X[4],10),parseInt(X[5],10)+D,G,B)}}else{return new Date(parseInt(W,10),0,1).getTime()}}}}}}};J.Hover=(function(){A.defaults={"class":"morris-hover morris-default-style"};function A(B){if(B==null){B={}}this.options=R.extend({},J.Hover.defaults,B);this.el=R("<div class='"+this.options["class"]+"'></div>");this.el.hide();this.options.parent.append(this.el)}A.prototype.update=function(B,C,D){this.html;this.show();return this.moveTo(C,D)};A.prototype.html=function(B){return this.el.html(B)};A.prototype.moveTo=function(I,G){var B,H,D,E,F,C;F=this.options.parent.innerWidth();E=this.options.parent.innerHeight();H=this.el.outerWidth();B=this.el.outerHeight();D=Math.min(Math.max(0,I-H/2),F-H);if(G!=null){C=G-B-10;if(C<0){C=G+10;if(C+B>E){C=E/2-B/2}}}else{C=E/2-B/2}return this.el.css({left:D+"px",top:parseInt(C)+"px"})};A.prototype.show=function(){return this.el.show()};A.prototype.hide=function(){return this.el.hide()};return A})();J.Line=(function(A){L(B,A);function B(C){this.hilight=N(this.hilight,this);this.onHoverOut=N(this.onHoverOut,this);this.onHoverMove=N(this.onHoverMove,this);this.onGridClick=N(this.onGridClick,this);if(!(this instanceof J.Line)){return new J.Line(C)}B.__super__.constructor.call(this,C)}B.prototype.init=function(){if(this.options.hideHover!=="always"){this.hover=new J.Hover({parent:this.el});this.on("hovermove",this.onHoverMove);this.on("hoverout",this.onHoverOut);return this.on("gridclick",this.onGridClick)}};B.prototype.defaults={lineWidth:3,pointSize:4,lineColors:["#0b62a4","#7A92A3","#4da74d","#afd8f8","#edc240","#cb4b4b","#9440ed"],pointStrokeWidths:[1],pointStrokeColors:["#ffffff"],pointFillColors:[],smooth:true,xLabels:"auto",xLabelFormat:null,xLabelMargin:24,continuousLine:true,hideHover:false};B.prototype.calc=function(){this.calcPoints();return this.generatePaths()};B.prototype.calcPoints=function(){var F,E,H,G,D,C;D=this.data;C=[];for(H=0,G=D.length;H<G;H++){F=D[H];F._x=this.transX(F.x);F._y=(function(){var W,X,V,I;V=F.y;I=[];for(W=0,X=V.length;W<X;W++){E=V[W];if(E!=null){I.push(this.transY(E))}else{I.push(E)}}return I}).call(this);C.push(F._ymax=Math.min.apply(Math,[this.bottom].concat((function(){var W,X,V,I;V=F._y;I=[];for(W=0,X=V.length;W<X;W++){E=V[W];if(E!=null){I.push(E)}}return I})())))}return C};B.prototype.hitTest=function(F){var E,G,H,D,C;if(this.data.length===0){return null}C=this.data.slice(1);for(E=H=0,D=C.length;H<D;E=++H){G=C[E];if(F<(G._x+this.data[E]._x)/2){break}}return E};B.prototype.onGridClick=function(E,C){var D;D=this.hitTest(E);return this.fire("click",D,this.data[D].src,E,C)};B.prototype.onHoverMove=function(E,C){var D;D=this.hitTest(E);return this.displayHoverForRow(D)};B.prototype.onHoverOut=function(){if(this.options.hideHover!==false){return this.displayHoverForRow(null)}};B.prototype.displayHoverForRow=function(D){var C;if(D!=null){(C=this.hover).update.apply(C,this.hoverContentForRow(D));return this.hilight(D)}else{this.hover.hide();return this.hilight()}};B.prototype.hoverContentForRow=function(E){var H,I,T,G,D,C,F;T=this.data[E];H="<div class='morris-hover-row-label'>"+T.label+"</div>";F=T.y;for(I=D=0,C=F.length;D<C;I=++D){G=F[I];H+="<div class='morris-hover-point' style='color: "+(this.colorFor(T,I,"label"))+"'>\n  "+this.options.labels[I]+":\n  "+(this.yLabelFormat(G))+"\n</div>"}if(typeof this.options.hoverCallback==="function"){H=this.options.hoverCallback(E,this.options,H,T.src)}return[H,T._x,T._ymax]};B.prototype.generatePaths=function(){var E,D,G,C,F;return this.paths=(function(){var U,I,H,V;V=[];for(G=U=0,I=this.options.ykeys.length;0<=I?U<I:U>I;G=0<=I?++U:--U){F=typeof this.options.smooth==="boolean"?this.options.smooth:(H=this.options.ykeys[G],Q.call(this.options.smooth,H)>=0);D=(function(){var Y,T,S,Z;S=this.data;Z=[];for(Y=0,T=S.length;Y<T;Y++){C=S[Y];if(C._y[G]!==void 0){Z.push({x:C._x,y:C._y[G]})}}return Z}).call(this);if(this.options.continuousLine){D=(function(){var S,T,X;X=[];for(S=0,T=D.length;S<T;S++){E=D[S];if(E.y!==null){X.push(E)}}return X})()}if(D.length>1){V.push(J.Line.createPath(D,F,this.bottom))}else{V.push(null)}}return V}).call(this)};B.prototype.draw=function(){var C;if((C=this.options.axes)===true||C==="both"||C==="x"){this.drawXAxis()}this.drawSeries();if(this.options.hideHover===false){return this.displayHoverForRow(this.data.length-1)}};B.prototype.drawXAxis=function(){var D,W,X,Z,H,I,C,E,F,G,Y=this;C=this.bottom+this.options.padding/2;H=null;Z=null;D=function(d,U){var V,f,T,e,S;V=Y.drawXAxisLabel(Y.transX(U),C,d);S=V.getBBox();V.transform("r"+(-Y.options.xLabelAngle));f=V.getBBox();V.transform("t0,"+(f.height/2)+"...");if(Y.options.xLabelAngle!==0){e=-0.5*S.width*Math.cos(Y.options.xLabelAngle*Math.PI/180);V.transform("t"+e+",0...")}f=V.getBBox();if(((H==null)||H>=f.x+f.width||(Z!=null)&&Z>=f.x)&&f.x>=0&&(f.x+f.width)<Y.el.width()){if(Y.options.xLabelAngle!==0){T=1.25*Y.options.gridTextSize/Math.sin(Y.options.xLabelAngle*Math.PI/180);Z=f.x-T}return H=f.x-Y.options.xLabelMargin}else{return V.remove()}};if(this.options.parseTime){if(this.data.length===1&&this.options.xLabels==="auto"){X=[[this.data[0].label,this.data[0].x]]}else{X=J.labelSeries(this.xmin,this.xmax,this.width,this.options.xLabels,this.options.xLabelFormat)}}else{X=(function(){var U,S,V,T;V=this.data;T=[];for(U=0,S=V.length;U<S;U++){I=V[U];T.push([I.label,I.x])}return T}).call(this)}X.reverse();G=[];for(E=0,F=X.length;E<F;E++){W=X[E];G.push(D(W[0],W[1]))}return G};B.prototype.drawSeries=function(){var H,E,F,G,D,C;this.seriesPoints=[];for(H=E=G=this.options.ykeys.length-1;G<=0?E<=0:E>=0;H=G<=0?++E:--E){this._drawLineFor(H)}C=[];for(H=F=D=this.options.ykeys.length-1;D<=0?F<=0:F>=0;H=D<=0?++F:--F){C.push(this._drawPointFor(H))}return C};B.prototype._drawPointFor=function(H){var G,F,I,E,D,C;this.seriesPoints[H]=[];D=this.data;C=[];for(I=0,E=D.length;I<E;I++){F=D[I];G=null;if(F._y[H]!=null){G=this.drawLinePoint(F._x,F._y[H],this.colorFor(F,H,"point"),H)}C.push(this.seriesPoints[H].push(G))}return C};B.prototype._drawLineFor=function(C){var D;D=this.paths[C];if(D!==null){return this.drawLinePath(D,this.colorFor(null,C,"line"),C)}};B.createPath=function(g,d,l){var f,G,e,i,k,D,j,H,E,C,h,F,I,c;j="";if(d){e=J.Line.gradients(g)}H={y:null};for(i=I=0,c=g.length;I<c;i=++I){f=g[i];if(f.y!=null){if(H.y!=null){if(d){G=e[i];D=e[i-1];k=(f.x-H.x)/4;E=H.x+k;h=Math.min(l,H.y+k*D);C=f.x-k;F=Math.min(l,f.y-k*G);j+="C"+E+","+h+","+C+","+F+","+f.x+","+f.y}else{j+="L"+f.x+","+f.y}}else{if(!d||(e[i]!=null)){j+="M"+f.x+","+f.y}}}H=f}return j};B.gradients=function(H){var I,E,D,U,V,C,F,G;E=function(S,T){return(S.y-T.y)/(S.x-T.x)};G=[];for(D=C=0,F=H.length;C<F;D=++C){I=H[D];if(I.y!=null){U=H[D+1]||{y:null};V=H[D-1]||{y:null};if((V.y!=null)&&(U.y!=null)){G.push(E(V,U))}else{if(V.y!=null){G.push(E(V,I))}else{if(U.y!=null){G.push(E(I,U))}else{G.push(null)}}}}else{G.push(null)}}return G};B.prototype.hilight=function(F){var G,H,E,D,C;if(this.prevHilight!==null&&this.prevHilight!==F){for(G=H=0,D=this.seriesPoints.length-1;0<=D?H<=D:H>=D;G=0<=D?++H:--H){if(this.seriesPoints[G][this.prevHilight]){this.seriesPoints[G][this.prevHilight].animate(this.pointShrinkSeries(G))}}}if(F!==null&&this.prevHilight!==F){for(G=E=0,C=this.seriesPoints.length-1;0<=C?E<=C:E>=C;G=0<=C?++E:--E){if(this.seriesPoints[G][F]){this.seriesPoints[G][F].animate(this.pointGrowSeries(G))}}}return this.prevHilight=F};B.prototype.colorFor=function(E,C,D){if(typeof this.options.lineColors==="function"){return this.options.lineColors.call(this,E,C,D)}else{if(D==="point"){return this.options.pointFillColors[C%this.options.pointFillColors.length]||this.options.lineColors[C%this.options.lineColors.length]}else{return this.options.lineColors[C%this.options.lineColors.length]}}};B.prototype.drawXAxisLabel=function(D,C,E){return this.raphael.text(D,C,E).attr("font-size",this.options.gridTextSize).attr("font-family",this.options.gridTextFamily).attr("font-weight",this.options.gridTextWeight).attr("fill",this.options.gridTextColor)};B.prototype.drawLinePath=function(C,E,D){return this.raphael.path(C).attr("stroke",E).attr("stroke-width",this.lineWidthForSeries(D))};B.prototype.drawLinePoint=function(F,D,E,C){return this.raphael.circle(F,D,this.pointSizeForSeries(C)).attr("fill",E).attr("stroke-width",this.pointStrokeWidthForSeries(C)).attr("stroke",this.pointStrokeColorForSeries(C))};B.prototype.pointStrokeWidthForSeries=function(C){return this.options.pointStrokeWidths[C%this.options.pointStrokeWidths.length]};B.prototype.pointStrokeColorForSeries=function(C){return this.options.pointStrokeColors[C%this.options.pointStrokeColors.length]};B.prototype.lineWidthForSeries=function(C){if(this.options.lineWidth instanceof Array){return this.options.lineWidth[C%this.options.lineWidth.length]}else{return this.options.lineWidth}};B.prototype.pointSizeForSeries=function(C){if(this.options.pointSize instanceof Array){return this.options.pointSize[C%this.options.pointSize.length]}else{return this.options.pointSize}};B.prototype.pointGrowSeries=function(C){return Raphael.animation({r:this.pointSizeForSeries(C)+3},25,"linear")};B.prototype.pointShrinkSeries=function(C){return Raphael.animation({r:this.pointSizeForSeries(C)},25,"linear")};return B})(J.Grid);J.labelSeries=function(I,Z,G,e,H){var D,d,f,c,F,A,a,B,C,b,E;f=200*(Z-I)/G;d=new Date(I);a=J.LABEL_SPECS[e];if(a===void 0){E=J.AUTO_LABEL_ORDER;for(C=0,b=E.length;C<b;C++){c=E[C];A=J.LABEL_SPECS[c];if(f>=A.span){a=A;break}}}if(a===void 0){a=J.LABEL_SPECS["second"]}if(H){a=R.extend({},a,{fmt:H})}D=a.start(d);F=[];while((B=D.getTime())<=Z){if(B>=I){F.push([a.fmt(D),B])}a.incr(D)}return F};O=function(A){return{span:A*60*1000,start:function(B){return new Date(B.getFullYear(),B.getMonth(),B.getDate(),B.getHours())},fmt:function(B){return""+(J.pad2(B.getHours()))+":"+(J.pad2(B.getMinutes()))},incr:function(B){return B.setUTCMinutes(B.getUTCMinutes()+A)}}};K=function(A){return{span:A*1000,start:function(B){return new Date(B.getFullYear(),B.getMonth(),B.getDate(),B.getHours(),B.getMinutes())},fmt:function(B){return""+(J.pad2(B.getHours()))+":"+(J.pad2(B.getMinutes()))+":"+(J.pad2(B.getSeconds()))},incr:function(B){return B.setUTCSeconds(B.getUTCSeconds()+A)}}};J.LABEL_SPECS={"decade":{span:172800000000,start:function(A){return new Date(A.getFullYear()-A.getFullYear()%10,0,1)},fmt:function(A){return""+(A.getFullYear())},incr:function(A){return A.setFullYear(A.getFullYear()+10)}},"year":{span:17280000000,start:function(A){return new Date(A.getFullYear(),0,1)},fmt:function(A){return""+(A.getFullYear())},incr:function(A){return A.setFullYear(A.getFullYear()+1)}},"month":{span:2419200000,start:function(A){return new Date(A.getFullYear(),A.getMonth(),1)},fmt:function(A){return""+(A.getFullYear())+"-"+(J.pad2(A.getMonth()+1))},incr:function(A){return A.setMonth(A.getMonth()+1)}},"week":{span:604800000,start:function(A){return new Date(A.getFullYear(),A.getMonth(),A.getDate())},fmt:function(A){return""+(A.getFullYear())+"-"+(J.pad2(A.getMonth()+1))+"-"+(J.pad2(A.getDate()))},incr:function(A){return A.setDate(A.getDate()+7)}},"day":{span:86400000,start:function(A){return new Date(A.getFullYear(),A.getMonth(),A.getDate())},fmt:function(A){return""+(A.getFullYear())+"-"+(J.pad2(A.getMonth()+1))+"-"+(J.pad2(A.getDate()))},incr:function(A){return A.setDate(A.getDate()+1)}},"hour":O(60),"30min":O(30),"15min":O(15),"10min":O(10),"5min":O(5),"minute":O(1),"30sec":K(30),"15sec":K(15),"10sec":K(10),"5sec":K(5),"second":K(1)};J.AUTO_LABEL_ORDER=["decade","year","month","week","day","hour","30min","15min","10min","5min","minute","30sec","15sec","10sec","5sec","second"];J.Area=(function(A){var C;L(B,A);C={fillOpacity:"auto",behaveLikeLine:false};function B(D){var E;if(!(this instanceof J.Area)){return new J.Area(D)}E=R.extend({},C,D);this.cumulative=!E.behaveLikeLine;if(E.fillOpacity==="auto"){E.fillOpacity=E.behaveLikeLine?0.8:1}B.__super__.constructor.call(this,E)}B.prototype.calcPoints=function(){var I,H,F,G,E,D,T;D=this.data;T=[];for(G=0,E=D.length;G<E;G++){I=D[G];I._x=this.transX(I.x);H=0;I._y=(function(){var S,X,Z,Y;Z=I.y;Y=[];for(S=0,X=Z.length;S<X;S++){F=Z[S];if(this.options.behaveLikeLine){Y.push(this.transY(F))}else{H+=F||0;Y.push(this.transY(H))}}return Y}).call(this);T.push(I._ymax=Math.max.apply(Math,I._y))}return T};B.prototype.drawSeries=function(){var a,Z,F,H,X,I,E,G,Y,D,b;this.seriesPoints=[];if(this.options.behaveLikeLine){Z=(function(){Y=[];for(var S=0,T=this.options.ykeys.length-1;0<=T?S<=T:S>=T;0<=T?S++:S--){Y.push(S)}return Y}).apply(this)}else{Z=(function(){D=[];for(var S=G=this.options.ykeys.length-1;G<=0?S<=0:S>=0;G<=0?S++:S--){D.push(S)}return D}).apply(this)}b=[];for(X=0,I=Z.length;X<I;X++){a=Z[X];this._drawFillFor(a);this._drawLineFor(a);b.push(this._drawPointFor(a))}return b};B.prototype._drawFillFor=function(E){var D;D=this.paths[E];if(D!==null){D=D+("L"+(this.transX(this.xmax))+","+this.bottom+"L"+(this.transX(this.xmin))+","+this.bottom+"Z");return this.drawFilledPath(D,this.fillForSeries(E))}};B.prototype.fillForSeries=function(E){var D;D=Raphael.rgb2hsl(this.colorFor(this.data[E],E,"line"));return Raphael.hsl(D.h,this.options.behaveLikeLine?D.s*0.9:D.s*0.75,Math.min(0.98,this.options.behaveLikeLine?D.l*1.2:D.l*1.25))};B.prototype.drawFilledPath=function(D,E){return this.raphael.path(D).attr("fill",E).attr("fill-opacity",this.options.fillOpacity).attr("stroke","none")};return B})(J.Line);J.Bar=(function(A){L(B,A);function B(C){this.onHoverOut=N(this.onHoverOut,this);this.onHoverMove=N(this.onHoverMove,this);this.onGridClick=N(this.onGridClick,this);if(!(this instanceof J.Bar)){return new J.Bar(C)}B.__super__.constructor.call(this,R.extend({},C,{parseTime:false}))}B.prototype.init=function(){this.cumulative=this.options.stacked;if(this.options.hideHover!=="always"){this.hover=new J.Hover({parent:this.el});this.on("hovermove",this.onHoverMove);this.on("hoverout",this.onHoverOut);return this.on("gridclick",this.onGridClick)}};B.prototype.defaults={barSizeRatio:0.75,barGap:3,barColors:["#0b62a4","#7a92a3","#4da74d","#afd8f8","#edc240","#cb4b4b","#9440ed"],barOpacity:1,barRadius:[0,0,0,0],xLabelMargin:50};B.prototype.calc=function(){var C;this.calcBars();if(this.options.hideHover===false){return(C=this.hover).update.apply(C,this.hoverContentForRow(this.data.length-1))}};B.prototype.calcBars=function(){var G,F,H,I,E,D,C;D=this.data;C=[];for(G=I=0,E=D.length;I<E;G=++I){F=D[G];F._x=this.left+this.width*(G+0.5)/this.data.length;C.push(F._y=(function(){var Z,X,Y,W;Y=F.y;W=[];for(Z=0,X=Y.length;Z<X;Z++){H=Y[Z];if(H!=null){W.push(this.transY(H))}else{W.push(null)}}return W}).call(this))}return C};B.prototype.draw=function(){var C;if((C=this.options.axes)===true||C==="both"||C==="x"){this.drawXAxis()}return this.drawSeries()};B.prototype.drawXAxis=function(){var Y,I,C,E,F,H,d,Z,a,b,D,G,c;b=this.bottom+(this.options.xAxisLabelTopPadding||this.options.padding/2);d=null;H=null;c=[];for(Y=D=0,G=this.data.length;0<=G?D<G:D>G;Y=0<=G?++D:--D){Z=this.data[this.data.length-1-Y];I=this.drawXAxisLabel(Z._x,b,Z.label);a=I.getBBox();I.transform("r"+(-this.options.xLabelAngle));C=I.getBBox();I.transform("t0,"+(C.height/2)+"...");if(this.options.xLabelAngle!==0){F=-0.5*a.width*Math.cos(this.options.xLabelAngle*Math.PI/180);I.transform("t"+F+",0...")}if(((d==null)||d>=C.x+C.width||(H!=null)&&H>=C.x)&&C.x>=0&&(C.x+C.width)<this.el.width()){if(this.options.xLabelAngle!==0){E=1.25*this.options.gridTextSize/Math.sin(this.options.xLabelAngle*Math.PI/180);H=C.x-E}c.push(d=C.x-this.options.xLabelMargin)}else{c.push(I.remove())}}return c};B.prototype.drawSeries=function(){var D,d,c,a,f,I,F,G,Z,e,C,b,H,E;c=this.width/this.options.data.length;G=this.options.stacked!=null?1:this.options.ykeys.length;D=(c*this.options.barSizeRatio-this.options.barGap*(G-1))/G;F=c*(1-this.options.barSizeRatio)/2;E=this.ymin<=0&&this.ymax>=0?this.transY(0):null;return this.bars=(function(){var V,U,T,S;T=this.data;S=[];for(a=V=0,U=T.length;V<U;a=++V){Z=T[a];f=0;S.push((function(){var h,W,Y,X;Y=Z._y;X=[];for(e=h=0,W=Y.length;h<W;e=++h){H=Y[e];if(H!==null){if(E){b=Math.min(H,E);d=Math.max(H,E)}else{b=H;d=this.bottom}I=this.left+a*c+F;if(!this.options.stacked){I+=e*(D+this.options.barGap)}C=d-b;if(this.options.stacked){b-=f}this.drawBar(I,b,D,C,this.colorFor(Z,e,"bar"),this.options.barOpacity,this.options.barRadius);X.push(f+=C)}else{X.push(null)}}return X}).call(this))}return S}).call(this)};B.prototype.colorFor=function(E,D,F){var G,C;if(typeof this.options.barColors==="function"){G={x:E.x,y:E.y[D],label:E.label};C={index:D,key:this.options.ykeys[D],label:this.options.labels[D]};return this.options.barColors.call(this,G,C,F)}else{return this.options.barColors[D%this.options.barColors.length]}};B.prototype.hitTest=function(C){if(this.data.length===0){return null}C=Math.max(Math.min(C,this.right),this.left);return Math.min(this.data.length-1,Math.floor((C-this.left)/(this.width/this.data.length)))};B.prototype.onGridClick=function(E,C){var D;D=this.hitTest(E);return this.fire("click",D,this.data[D].src,E,C)};B.prototype.onHoverMove=function(C,D){var E,F;E=this.hitTest(C);return(F=this.hover).update.apply(F,this.hoverContentForRow(E))};B.prototype.onHoverOut=function(){if(this.options.hideHover!==false){return this.hover.hide()}};B.prototype.hoverContentForRow=function(E){var V,I,U,G,H,D,C,F;U=this.data[E];V="<div class='morris-hover-row-label'>"+U.label+"</div>";F=U.y;for(I=D=0,C=F.length;D<C;I=++D){H=F[I];V+="<div class='morris-hover-point' style='color: "+(this.colorFor(U,I,"label"))+"'>\n  "+this.options.labels[I]+":\n  "+(this.yLabelFormat(H))+"\n</div>"}if(typeof this.options.hoverCallback==="function"){V=this.options.hoverCallback(E,this.options,V,U.src)}G=this.left+(E+0.5)*this.width/this.data.length;return[V,G]};B.prototype.drawXAxisLabel=function(F,D,C){var E;return E=this.raphael.text(F,D,C).attr("font-size",this.options.gridTextSize).attr("font-family",this.options.gridTextFamily).attr("font-weight",this.options.gridTextWeight).attr("fill",this.options.gridTextColor)};B.prototype.drawBar=function(I,E,U,V,G,D,F){var C,H;C=Math.max.apply(Math,F);if(C===0||C>V){H=this.raphael.rect(I,E,U,V)}else{H=this.raphael.path(this.roundedRect(I,E,U,V,F))}return H.attr("fill",G).attr("fill-opacity",D).attr("stroke","none")};B.prototype.roundedRect=function(D,E,C,F,G){if(G==null){G=[0,0,0,0]}return["M",D,G[0]+E,"Q",D,E,D+G[0],E,"L",D+C-G[1],E,"Q",D+C,E,D+C,E+G[1],"L",D+C,E+F-G[2],"Q",D+C,E+F,D+C-G[2],E+F,"L",D+G[3],E+F,"Q",D,E+F,D,E+F-G[3],"Z"]};return B})(J.Grid);J.Donut=(function(B){L(A,B);A.prototype.defaults={colors:["#0B62A4","#3980B5","#679DC6","#95BBD7","#B0CCE1","#095791","#095085","#083E67","#052C48","#042135"],backgroundColor:"#FFFFFF",labelColor:"#000000",formatter:J.commas,resize:false};function A(D){this.resizeHandler=N(this.resizeHandler,this);this.select=N(this.select,this);this.click=N(this.click,this);var C=this;if(!(this instanceof J.Donut)){return new J.Donut(D)}this.options=R.extend({},this.defaults,D);if(typeof D.element==="string"){this.el=R(document.getElementById(D.element))}else{this.el=R(D.element)}if(this.el===null||this.el.length===0){throw new Error("Graph placeholder not found.")}if(D.data===void 0||D.data.length===0){return}this.raphael=new Raphael(this.el[0]);if(this.options.resize){R(window).bind("resize",function(E){if(C.timeoutId!=null){window.clearTimeout(C.timeoutId)}return C.timeoutId=window.setTimeout(C.resizeHandler,100)})}this.setData(D.data)}A.prototype.redraw=function(){var l,j,F,I,k,i,p,m,w,E,C,o,q,x,u,H,r,G,t,v,n,s,D;this.raphael.clear();j=this.el.width()/2;F=this.el.height()/2;q=(Math.min(j,F)-10)/3;C=0;v=this.values;for(x=0,r=v.length;x<r;x++){o=v[x];C+=o}m=5/(2*q);l=1.9999*Math.PI-m*this.data.length;i=0;k=0;this.segments=[];n=this.values;for(I=u=0,G=n.length;u<G;I=++u){o=n[I];w=i+m+l*(o/C);E=new J.DonutSegment(j,F,q*2,q,i,w,this.data[I].color||this.options.colors[k%this.options.colors.length],this.options.backgroundColor,k,this.raphael);E.render();this.segments.push(E);E.on("hover",this.select);E.on("click",this.click);i=w;k+=1}this.text1=this.drawEmptyDonutLabel(j,F-10,this.options.labelColor,15,800);this.text2=this.drawEmptyDonutLabel(j,F+10,this.options.labelColor,14);p=Math.max.apply(Math,this.values);k=0;s=this.values;D=[];for(H=0,t=s.length;H<t;H++){o=s[H];if(o===p){this.select(k);break}D.push(k+=1)}return D};A.prototype.setData=function(D){var C;this.data=D;this.values=(function(){var G,H,F,E;F=this.data;E=[];for(G=0,H=F.length;G<H;G++){C=F[G];E.push(parseFloat(C.value))}return E}).call(this);return this.redraw()};A.prototype.click=function(C){return this.fire("click",C,this.data[C])};A.prototype.select=function(H){var G,I,F,E,D,C;C=this.segments;for(E=0,D=C.length;E<D;E++){I=C[E];I.deselect()}F=this.segments[H];F.select();G=this.data[H];return this.setLabels(G.label,this.options.formatter(G.value,G))};A.prototype.setLabels=function(X,E){var F,C,W,V,I,H,G,D;F=(Math.min(this.el.width()/2,this.el.height()/2)-10)*2/3;V=1.8*F;W=F/2;C=F/3;this.text1.attr({text:X,transform:""});I=this.text1.getBBox();H=Math.min(V/I.width,W/I.height);this.text1.attr({transform:"S"+H+","+H+","+(I.x+I.width/2)+","+(I.y+I.height)});this.text2.attr({text:E,transform:""});G=this.text2.getBBox();D=Math.min(V/G.width,C/G.height);return this.text2.attr({transform:"S"+D+","+D+","+(G.x+G.width/2)+","+G.y})};A.prototype.drawEmptyDonutLabel=function(G,F,H,E,D){var C;C=this.raphael.text(G,F,"").attr("font-size",E).attr("fill",H);if(D!=null){C.attr("font-weight",D)}return C};A.prototype.resizeHandler=function(){this.timeoutId=null;this.raphael.setSize(this.el.width(),this.el.height());return this.redraw()};return A})(J.EventEmitter);J.DonutSegment=(function(B){L(A,B);function A(G,I,F,E,W,H,D,X,C,V){this.cx=G;this.cy=I;this.inner=F;this.outer=E;this.color=D;this.backgroundColor=X;this.index=C;this.raphael=V;this.deselect=N(this.deselect,this);this.select=N(this.select,this);this.sin_p0=Math.sin(W);this.cos_p0=Math.cos(W);this.sin_p1=Math.sin(H);this.cos_p1=Math.cos(H);this.is_long=(H-W)>Math.PI?1:0;this.path=this.calcSegment(this.inner+3,this.inner+this.outer-5);this.selectedPath=this.calcSegment(this.inner+3,this.inner+this.outer);this.hilight=this.calcArc(this.inner)}A.prototype.calcArcPoints=function(C){return[this.cx+C*this.sin_p0,this.cy+C*this.cos_p0,this.cx+C*this.sin_p1,this.cy+C*this.cos_p1]};A.prototype.calcSegment=function(D,G){var Y,b,a,H,F,E,X,I,Z,C;Z=this.calcArcPoints(D),Y=Z[0],a=Z[1],b=Z[2],H=Z[3];C=this.calcArcPoints(G),F=C[0],X=C[1],E=C[2],I=C[3];return("M"+Y+","+a)+("A"+D+","+D+",0,"+this.is_long+",0,"+b+","+H)+("L"+E+","+I)+("A"+G+","+G+",0,"+this.is_long+",1,"+F+","+X)+"Z"};A.prototype.calcArc=function(G){var E,H,F,D,C;C=this.calcArcPoints(G),E=C[0],F=C[1],H=C[2],D=C[3];return("M"+E+","+F)+("A"+G+","+G+",0,"+this.is_long+",0,"+H+","+D)};A.prototype.render=function(){var C=this;this.arc=this.drawDonutArc(this.hilight,this.color);return this.seg=this.drawDonutSegment(this.path,this.color,this.backgroundColor,function(){return C.fire("hover",C.index)},function(){return C.fire("click",C.index)})};A.prototype.drawDonutArc=function(D,C){return this.raphael.path(D).attr({stroke:C,"stroke-width":2,opacity:0})};A.prototype.drawDonutSegment=function(E,D,C,F,G){return this.raphael.path(E).attr({fill:D,stroke:C,"stroke-width":3}).hover(F).click(G)};A.prototype.select=function(){if(!this.selected){this.seg.animate({path:this.selectedPath},150,"<>");this.arc.animate({opacity:1},150,"<>");return this.selected=true}};A.prototype.deselect=function(){if(this.selected){this.seg.animate({path:this.path},150,"<>");this.arc.animate({opacity:0},150,"<>");return this.selected=false}};return A})(J.EventEmitter)}).call(this);