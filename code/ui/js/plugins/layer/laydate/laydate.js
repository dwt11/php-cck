!function(P){var K={path:"",defSkin:"default",format:"YYYY-MM-DD",min:"1900-01-01 00:00:00",max:"2099-12-31 23:59:59",isv:!1},O={},I=document,L="createElement",M="getElementById",N="getElementsByTagName",J=["laydate_box","laydate_void","laydate_click","LayDateSkin","skins/","/laydate.css"];P.laydate=function(B){B=B||{};try{J.event=P.event?P.event:laydate.caller.arguments[0]}catch(A){}return O.run(B),laydate},laydate.v="1.1",O.getPath=function(){var B=document.scripts,A=B[B.length-1].src;return K.path?K.path:A.substring(0,A.lastIndexOf("/")+1)}(),O.use=function(C,B){var A=I[L]("link");A.type="text/css",A.rel="stylesheet",A.href=O.getPath+C+J[5],B&&(A.id=B),I[N]("head")[0].appendChild(A),A=null},O.trim=function(A){return A=A||"",A.replace(/^\s|\s$/g,"").replace(/\s+/g," ")},O.digit=function(A){return 10>A?"0"+(0|A):A},O.stopmp=function(A){return A=A||P.event,A.stopPropagation?A.stopPropagation():A.cancelBubble=!0,this},O.each=function(C,D){for(var A=0,B=C.length;B>A&&D(A,C[A])!==!1;A++){}},O.hasClass=function(B,A){return B=B||{},new RegExp("\\b"+A+"\\b").test(B.className)},O.addClass=function(B,A){return B=B||{},O.hasClass(B,A)||(B.className+=" "+A),B.className=O.trim(B.className),this},O.removeClass=function(A,C){if(A=A||{},O.hasClass(A,C)){var B=new RegExp("\\b"+C+"\\b");A.className=A.className.replace(B,"")}return this},O.removeCssAttr=function(A,B){var C=A.style;C.removeProperty?C.removeProperty(B):C.removeAttribute(B)},O.shde=function(B,A){B.style.display=A?"none":"block"},O.query=function(E){var A,D,B,F,C;return E=O.trim(E).split(" "),D=I[M](E[0].substr(1)),D?E[1]?/^\./.test(E[1])?(F=E[1].substr(1),C=new RegExp("\\b"+F+"\\b"),A=[],B=I.getElementsByClassName?D.getElementsByClassName(F):D[N]("*"),O.each(B,function(H,G){C.test(G.className)&&A.push(G)}),A[0]?A:""):(A=D[N](E[1]),A[0]?D[N](E[1]):""):D:void 0},O.on=function(A,B,C){return A.attachEvent?A.attachEvent("on"+B,function(){C.call(A,P.even)}):A.addEventListener(B,C,!1),O},O.stopMosup=function(B,A){"mouseup"!==B&&O.on(A,"mouseup",function(C){O.stopmp(C)})},O.run=function(C){var G,A,B,F=O.query,D=J.event;try{B=D.target||D.srcElement||{}}catch(E){B={}}if(G=C.elem?F(C.elem):B,D&&B.tagName){if(!G||G===O.elem){return}O.stopMosup(D.type,G),O.stopmp(D),O.view(G,C),O.reshow()}else{A=C.event||"click",O.each((0|G.length)>0?G:[G],function(R,H){O.stopMosup(A,H),O.on(H,A,function(Q){O.stopmp(Q),H!==O.elem&&(O.view(H,C),O.reshow())})})}},O.scroll=function(A){return A=A?"scrollLeft":"scrollTop",I.body[A]|I.documentElement[A]},O.winarea=function(A){return document.documentElement[A?"clientWidth":"clientHeight"]},O.isleap=function(A){return 0===A%4&&0!==A%100||0===A%400},O.checkVoid=function(C,A,B){var D=[];return C=0|C,A=0|A,B=0|B,C<O.mins[0]?D=["y"]:C>O.maxs[0]?D=["y",1]:C>=O.mins[0]&&C<=O.maxs[0]&&(C==O.mins[0]&&(A<O.mins[1]?D=["m"]:A==O.mins[1]&&B<O.mins[2]&&(D=["d"])),C==O.maxs[0]&&(A>O.maxs[1]?D=["m",1]:A==O.maxs[1]&&B>O.maxs[2]&&(D=["d",1]))),D},O.timeVoid=function(B,A){if(O.ymd[1]+1==O.mins[1]&&O.ymd[2]==O.mins[2]){if(0===A&&B<O.mins[3]){return 1}if(1===A&&B<O.mins[4]){return 1}if(2===A&&B<O.mins[5]){return 1}}else{if(O.ymd[1]+1==O.maxs[1]&&O.ymd[2]==O.maxs[2]){if(0===A&&B>O.maxs[3]){return 1}if(1===A&&B>O.maxs[4]){return 1}if(2===A&&B>O.maxs[5]){return 1}}}return B>(A?59:23)?1:void 0},O.check=function(){var C=O.options.format.replace(/YYYY|MM|DD|hh|mm|ss/g,"\\d+\\").replace(/\\$/g,""),A=new RegExp(C),B=O.elem[J.elemv],E=B.match(/\d+/g)||[],D=O.checkVoid(E[0],E[1],E[2]);if(""!==B.replace(/\s/g,"")){if(!A.test(B)){return O.elem[J.elemv]="",O.msg("日期不符合格式，请重新选择。"),1}if(D[0]){return O.elem[J.elemv]="",O.msg("日期不在有效期内，请重新选择。"),1}D.value=O.elem[J.elemv].match(A).join(),E=D.value.match(/\d+/g),E[1]<1?(E[1]=1,D.auto=1):E[1]>12?(E[1]=12,D.auto=1):E[1].length<2&&(D.auto=1),E[2]<1?(E[2]=1,D.auto=1):E[2]>O.months[(0|E[1])-1]?(E[2]=31,D.auto=1):E[2].length<2&&(D.auto=1),E.length>3&&(O.timeVoid(E[3],0)&&(D.auto=1),O.timeVoid(E[4],1)&&(D.auto=1),O.timeVoid(E[5],2)&&(D.auto=1)),D.auto?O.creation([E[0],0|E[1],0|E[2]],1):D.value!==O.elem[J.elemv]&&(O.elem[J.elemv]=D.value)}},O.months=[31,null,31,30,31,30,31,31,30,31,30,31],O.viewDate=function(C,A,E){var D=(O.query,{}),B=new Date;C<(0|O.mins[0])&&(C=0|O.mins[0]),C>(0|O.maxs[0])&&(C=0|O.maxs[0]),B.setFullYear(C,A,E),D.ymd=[B.getFullYear(),B.getMonth(),B.getDate()],O.months[1]=O.isleap(D.ymd[0])?29:28,B.setFullYear(D.ymd[0],D.ymd[1],1),D.FDay=B.getDay(),D.PDay=O.months[0===A?11:A-1]-D.FDay+1,D.NDay=1,O.each(J.tds,function(G,T){var H,S=D.ymd[0],F=D.ymd[1]+1;T.className="",G<D.FDay?(T.innerHTML=H=G+D.PDay,O.addClass(T,"laydate_nothis"),1===F&&(S-=1),F=1===F?12:F-1):G>=D.FDay&&G<D.FDay+O.months[D.ymd[1]]?(T.innerHTML=H=G-D.FDay+1,G-D.FDay+1===D.ymd[2]&&(O.addClass(T,J[2]),D.thisDay=T)):(T.innerHTML=H=D.NDay++,O.addClass(T,"laydate_nothis"),12===F&&(S+=1),F=12===F?1:F+1),O.checkVoid(S,F,H)[0]&&O.addClass(T,J[1]),O.options.festival&&O.festival(T,F+"."+H),T.setAttribute("y",S),T.setAttribute("m",F),T.setAttribute("d",H),S=F=H=null}),O.valid=!O.hasClass(D.thisDay,J[1]),O.ymd=D.ymd,J.year.value=O.ymd[0]+"年",J.month.value=O.digit(O.ymd[1]+1)+"月",O.each(J.mms,function(H,G){var F=O.checkVoid(O.ymd[0],(0|G.getAttribute("m"))+1);"y"===F[0]||"m"===F[0]?O.addClass(G,J[1]):O.removeClass(G,J[1]),O.removeClass(G,J[2]),F=null}),O.addClass(J.mms[O.ymd[1]],J[2]),D.times=[0|O.inymd[3]||0,0|O.inymd[4]||0,0|O.inymd[5]||0],O.each(new Array(3),function(F){O.hmsin[F].value=O.digit(O.timeVoid(D.times[F],F)?0|O.mins[F+3]:0|D.times[F])}),O[O.valid?"removeClass":"addClass"](J.ok,J[1])},O.festival=function(A,B){var C;switch(B){case"1.1":C="元旦";break;case"3.8":C="妇女";break;case"4.5":C="清明";break;case"5.1":C="劳动";break;case"6.1":C="儿童";break;case"9.10":C="教师";break;case"10.1":C="国庆"}C&&(A.innerHTML=C),C=null},O.viewYears=function(A){var C=O.query,B="";O.each(new Array(14),function(D){B+=7===D?"<li "+(parseInt(J.year.value)===A?'class="'+J[2]+'"':"")+' y="'+A+'">'+A+"年</li>":'<li y="'+(A-7+D)+'">'+(A-7+D)+"年</li>"}),C("#laydate_ys").innerHTML=B,O.each(C("#laydate_ys li"),function(E,D){"y"===O.checkVoid(D.getAttribute("y"))[0]?O.addClass(D,J[1]):O.on(D,"click",function(F){O.stopmp(F).reshow(),O.viewDate(0|this.getAttribute("y"),O.ymd[1],O.ymd[2])})})},O.initDate=function(){var A=(O.query,new Date),B=O.elem[J.elemv].match(/\d+/g)||[];B.length<3&&(B=O.options.start.match(/\d+/g)||[],B.length<3&&(B=[A.getFullYear(),A.getMonth()+1,A.getDate()])),O.inymd=B,O.viewDate(B[0],B[1]-1,B[2])},O.iswrite=function(){var B=O.query,A={time:B("#laydate_hms")};O.shde(A.time,!O.options.istime),O.shde(J.oclear,!("isclear" in O.options?O.options.isclear:1)),O.shde(J.otoday,!("istoday" in O.options?O.options.istoday:1)),O.shde(J.ok,!("issure" in O.options?O.options.issure:1))},O.orien=function(C,A){var B,D=O.elem.getBoundingClientRect();C.style.left=D.left+(A?0:O.scroll(1))+"px",B=D.bottom+C.offsetHeight/1.5<=O.winarea()?D.bottom-1:D.top>C.offsetHeight/1.5?D.top-C.offsetHeight+1:O.winarea()-C.offsetHeight,C.style.top=B+(A?0:O.scroll())+"px"},O.follow=function(A){O.options.fixed?(A.style.position="fixed",O.orien(A,1)):(A.style.position="absolute",O.orien(A))},O.viewtb=function(){var E,D=[],C=["日","一","二","三","四","五","六"],B={},F=I[L]("table"),A=I[L]("thead");return A.appendChild(I[L]("tr")),B.creath=function(H){var G=I[L]("th");G.innerHTML=C[H],A[N]("tr")[0].appendChild(G),G=null},O.each(new Array(6),function(G){D.push([]),E=F.insertRow(0),O.each(new Array(7),function(H){D[G][H]=0,0===G&&B.creath(H),E.insertCell(H)})}),F.insertBefore(A,F.children[0]),F.id=F.className="laydate_table",E=D=null,F.outerHTML.toLowerCase()}(),O.view=function(C,D){var E,B=O.query,A={};D=D||C,O.elem=C,O.options=D,O.options.format||(O.options.format=K.format),O.options.start=O.options.start||"",O.mm=A.mm=[O.options.min||K.min,O.options.max||K.max],O.mins=A.mm[0].match(/\d+/g),O.maxs=A.mm[1].match(/\d+/g),J.elemv=/textarea|input/.test(O.elem.tagName.toLocaleLowerCase())?"value":"innerHTML",O.box?O.shde(O.box):(E=I[L]("div"),E.id=J[0],E.className=J[0],E.style.cssText="position: absolute;",E.setAttribute("name","laydate-v"+laydate.v),E.innerHTML=A.html='<div class="laydate_top"><div class="laydate_ym laydate_y" id="laydate_YY"><a class="laydate_choose laydate_chprev laydate_tab"><cite></cite></a><input id="laydate_y" readonly><label></label><a class="laydate_choose laydate_chnext laydate_tab"><cite></cite></a><div class="laydate_yms"><a class="laydate_tab laydate_chtop"><cite></cite></a><ul id="laydate_ys"></ul><a class="laydate_tab laydate_chdown"><cite></cite></a></div></div><div class="laydate_ym laydate_m" id="laydate_MM"><a class="laydate_choose laydate_chprev laydate_tab"><cite></cite></a><input id="laydate_m" readonly><label></label><a class="laydate_choose laydate_chnext laydate_tab"><cite></cite></a><div class="laydate_yms" id="laydate_ms">'+function(){var F="";return O.each(new Array(12),function(G){F+='<span m="'+G+'">'+O.digit(G+1)+"月</span>"}),F}()+"</div></div></div>"+O.viewtb+'<div class="laydate_bottom"><ul id="laydate_hms"><li class="laydate_sj">时间</li><li><input readonly>:</li><li><input readonly>:</li><li><input readonly></li></ul><div class="laydate_time" id="laydate_time"></div><div class="laydate_btn"><a id="laydate_clear">清空</a><a id="laydate_today">今天</a><a id="laydate_ok">确认</a></div>'+(K.isv?'<a href="http://sentsin.com/layui/laydate/" class="laydate_v" target="_blank">laydate-v'+laydate.v+"</a>":"")+"</div>",I.body.appendChild(E),O.box=B("#"+J[0]),O.events(),E=null),O.follow(O.box),D.zIndex?O.box.style.zIndex=D.zIndex:O.removeCssAttr(O.box,"z-index"),O.stopMosup("click",O.box),O.initDate(),O.iswrite(),O.check()},O.reshow=function(){return O.each(O.query("#"+J[0]+" .laydate_show"),function(B,A){O.removeClass(A,"laydate_show")}),this},O.close=function(){O.reshow(),O.shde(O.query("#"+J[0]),1),O.elem=null},O.parse=function(A,B,C){return A=A.concat(B),C=C||(O.options?O.options.format:K.format),C.replace(/YYYY|MM|DD|hh|mm|ss/g,function(){return A.index=0|++A.index,O.digit(A[A.index])})},O.creation=function(A,D){var B=(O.query,O.hmsin),C=O.parse(A,[B[0].value,B[1].value,B[2].value]);O.elem[J.elemv]=C,D||(O.close(),"function"==typeof O.options.choose&&O.options.choose(C))},O.events=function(){var B=O.query,A={box:"#"+J[0]};O.addClass(I.body,"laydate_body"),J.tds=B("#laydate_table td"),J.mms=B("#laydate_ms span"),J.year=B("#laydate_y"),J.month=B("#laydate_m"),O.each(B(A.box+" .laydate_ym"),function(D,C){O.on(C,"click",function(E){O.stopmp(E).reshow(),O.addClass(this[N]("div")[0],"laydate_show"),D||(A.YY=parseInt(J.year.value),O.viewYears(A.YY))})}),O.on(B(A.box),"click",function(){O.reshow()}),A.tabYear=function(C){0===C?O.ymd[0]--:1===C?O.ymd[0]++:2===C?A.YY-=14:A.YY+=14,2>C?(O.viewDate(O.ymd[0],O.ymd[1],O.ymd[2]),O.reshow()):O.viewYears(A.YY)},O.each(B("#laydate_YY .laydate_tab"),function(D,C){O.on(C,"click",function(E){O.stopmp(E),A.tabYear(D)})}),A.tabMonth=function(C){C?(O.ymd[1]++,12===O.ymd[1]&&(O.ymd[0]++,O.ymd[1]=0)):(O.ymd[1]--,-1===O.ymd[1]&&(O.ymd[0]--,O.ymd[1]=11)),O.viewDate(O.ymd[0],O.ymd[1],O.ymd[2])},O.each(B("#laydate_MM .laydate_tab"),function(D,C){O.on(C,"click",function(E){O.stopmp(E).reshow(),A.tabMonth(D)})}),O.each(B("#laydate_ms span"),function(D,C){O.on(C,"click",function(E){O.stopmp(E).reshow(),O.hasClass(this,J[1])||O.viewDate(O.ymd[0],0|this.getAttribute("m"),O.ymd[2])})}),O.each(B("#laydate_table td"),function(D,C){O.on(C,"click",function(E){O.hasClass(this,J[1])||(O.stopmp(E),O.creation([0|this.getAttribute("y"),0|this.getAttribute("m"),0|this.getAttribute("d")]))})}),J.oclear=B("#laydate_clear"),O.on(J.oclear,"click",function(){O.elem[J.elemv]="",O.close()}),J.otoday=B("#laydate_today"),O.on(J.otoday,"click",function(){O.elem[J.elemv]=laydate.now(0,O.options.format),O.close()}),J.ok=B("#laydate_ok"),O.on(J.ok,"click",function(){O.valid&&O.creation([O.ymd[0],O.ymd[1]+1,O.ymd[2]])}),A.times=B("#laydate_time"),O.hmsin=A.hmsin=B("#laydate_hms input"),A.hmss=["小时","分钟","秒数"],A.hmsarr=[],O.msg=function(D,C){var E='<div class="laydte_hsmtex">'+(C||"提示")+"<span>×</span></div>";"string"==typeof D?(E+="<p>"+D+"</p>",O.shde(B("#"+J[0])),O.removeClass(A.times,"laydate_time1").addClass(A.times,"laydate_msg")):(A.hmsarr[D]?E=A.hmsarr[D]:(E+='<div id="laydate_hmsno" class="laydate_hmsno">',O.each(new Array(0===D?24:60),function(F){E+="<span>"+F+"</span>"}),E+="</div>",A.hmsarr[D]=E),O.removeClass(A.times,"laydate_msg"),O[0===D?"removeClass":"addClass"](A.times,"laydate_time1")),O.addClass(A.times,"laydate_show"),A.times.innerHTML=E},A.hmson=function(F,C){var E=B("#laydate_hmsno span"),D=O.valid?null:1;O.each(E,function(H,G){D?O.addClass(G,J[1]):O.timeVoid(H,C)?O.addClass(G,J[1]):O.on(G,"click",function(){O.hasClass(this,J[1])||(F.value=O.digit(0|this.innerHTML))})}),O.addClass(E[0|F.value],"laydate_click")},O.each(A.hmsin,function(D,C){O.on(C,"click",function(E){O.stopmp(E).reshow(),O.msg(D,A.hmss[D]),A.hmson(this,D)})}),O.on(I,"mouseup",function(){var C=B("#"+J[0]);C&&"none"!==C.style.display&&(O.check()||O.close())}).on(I,"keydown",function(D){D=D||P.event;var C=D.keyCode;13===C&&O.creation([O.ymd[0],O.ymd[1]+1,O.ymd[2]])})},O.init=function(){O.use("need"),O.use(J[4]+K.defSkin,J[3]),O.skinLink=O.query("#"+J[3])}(),laydate.reset=function(){O.box&&O.elem&&O.follow(O.box)},laydate.now=function(A,C){var B=new Date(0|A?function(D){return 86400000>D?+new Date+86400000*D:D}(parseInt(A)):+new Date);return O.parse([B.getFullYear(),B.getMonth()+1,B.getDate()],[B.getHours(),B.getMinutes(),B.getSeconds()],C)},laydate.skin=function(A){O.skinLink.href=O.getPath+J[4]+A+J[5]}}(window);