(function(){function B(D){var A=B.modules[D];if(!A){throw new Error('failed to require "'+D+'"')}if(!("exports" in A)&&typeof A.definition==="function"){A.client=A.component=true;A.definition.call(this,A.exports={},A);delete A.definition}return A.exports}B.modules={};B.register=function(D,A){B.modules[D]={definition:A}};B.define=function(A,D){B.modules[A]={exports:D}};B.register("component~emitter@1.1.2",function(F,H){H.exports=G;function G(C){if(C){return A(C)}}function A(D){for(var C in G.prototype){D[C]=G.prototype[C]}return D}G.prototype.on=G.prototype.addEventListener=function(D,C){this._callbacks=this._callbacks||{};(this._callbacks[D]=this._callbacks[D]||[]).push(C);return this};G.prototype.once=function(D,E){var C=this;this._callbacks=this._callbacks||{};function J(){C.off(D,J);E.apply(this,arguments)}J.fn=E;this.on(D,J);return this};G.prototype.off=G.prototype.removeListener=G.prototype.removeAllListeners=G.prototype.removeEventListener=function(E,D){this._callbacks=this._callbacks||{};if(0==arguments.length){this._callbacks={};return this}var L=this._callbacks[E];if(!L){return this}if(1==arguments.length){delete this._callbacks[E];return this}var C;for(var K=0;K<L.length;K++){C=L[K];if(C===D||C.fn===D){L.splice(K,1);break}}return this};G.prototype.emit=function(D){this._callbacks=this._callbacks||{};var L=[].slice.call(arguments,1),E=this._callbacks[D];if(E){E=E.slice(0);for(var C=0,K=E.length;C<K;++C){E[C].apply(this,L)}}return this};G.prototype.listeners=function(C){this._callbacks=this._callbacks||{};return this._callbacks[C]||[]};G.prototype.hasListeners=function(C){return !!this.listeners(C).length}});B.register("dropzone",function(D,A){A.exports=B("dropzone/lib/dropzone.js")});B.register("dropzone/lib/dropzone.js",function(D,A){(function(){var V,U,W,O,X,T,R,S,P={}.hasOwnProperty,Q=function(G,H){for(var F in H){if(P.call(H,F)){G[F]=H[F]}}function E(){this.constructor=G}E.prototype=H.prototype;G.prototype=new E();G.__super__=H.prototype;return G},C=[].slice;U=typeof Emitter!=="undefined"&&Emitter!==null?Emitter:B("component~emitter@1.1.2");R=function(){};V=(function(F){var G;Q(E,F);E.prototype.events=["drop","dragstart","dragend","dragenter","dragover","dragleave","addedfile","removedfile","thumbnail","error","errormultiple","processing","processingmultiple","uploadprogress","totaluploadprogress","sending","sendingmultiple","success","successmultiple","canceled","canceledmultiple","complete","completemultiple","reset","maxfilesexceeded","maxfilesreached"];E.prototype.defaultOptions={url:null,method:"post",withCredentials:false,parallelUploads:2,uploadMultiple:false,maxFilesize:256,paramName:"file",createImageThumbnails:true,maxThumbnailFilesize:10,thumbnailWidth:100,thumbnailHeight:100,maxFiles:null,params:{},clickable:true,ignoreHiddenFiles:true,acceptedFiles:null,acceptedMimeTypes:null,autoProcessQueue:true,autoQueue:true,addRemoveLinks:false,previewsContainer:null,dictDefaultMessage:"Drop files here to upload",dictFallbackMessage:"Your browser does not support drag'n'drop file uploads.",dictFallbackText:"Please use the fallback form below to upload your files like in the olden days.",dictFileTooBig:"File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.",dictInvalidFileType:"You can't upload files of this type.",dictResponseError:"Server responded with {{statusCode}} code.",dictCancelUpload:"Cancel upload",dictCancelUploadConfirmation:"Are you sure you want to cancel this upload?",dictRemoveFile:"Remove file",dictRemoveFileConfirmation:null,dictMaxFilesExceeded:"You can not upload any more files.",accept:function(I,H){return H()},init:function(){return R},forceFallback:false,fallback:function(){var I,J,H,M,K,L;this.element.className=""+this.element.className+" dz-browser-not-supported";L=this.element.getElementsByTagName("div");for(M=0,K=L.length;M<K;M++){I=L[M];if(/(^| )dz-message($| )/.test(I.className)){J=I;I.className="dz-message";continue}}if(!J){J=E.createElement('<div class="dz-message"><span></span></div>');this.element.appendChild(J)}H=J.getElementsByTagName("span")[0];if(H){H.textContent=this.options.dictFallbackMessage}return this.element.appendChild(this.getFallbackForm())},resize:function(K){var J,H,I;J={srcX:0,srcY:0,srcWidth:K.width,srcHeight:K.height};H=K.width/K.height;I=this.options.thumbnailWidth/this.options.thumbnailHeight;if(K.height<this.options.thumbnailHeight||K.width<this.options.thumbnailWidth){J.trgHeight=J.srcHeight;J.trgWidth=J.srcWidth}else{if(H>I){J.srcHeight=K.height;J.srcWidth=J.srcHeight*I}else{J.srcWidth=K.width;J.srcHeight=J.srcWidth/I}}J.srcX=(K.width-J.srcWidth)/2;J.srcY=(K.height-J.srcHeight)/2;return J},drop:function(H){return this.element.classList.remove("dz-drag-hover")},dragstart:R,dragend:function(H){return this.element.classList.remove("dz-drag-hover")},dragenter:function(H){return this.element.classList.add("dz-drag-hover")},dragover:function(H){return this.element.classList.add("dz-drag-hover")},dragleave:function(H){return this.element.classList.remove("dz-drag-hover")},paste:R,reset:function(){return this.element.classList.remove("dz-started")},addedfile:function(J){var i,M,j,l,K,h,k,N,L,H,I,f,g;if(this.element===this.previewsContainer){this.element.classList.add("dz-started")}J.previewElement=E.createElement(this.options.previewTemplate.trim());J.previewTemplate=J.previewElement;this.previewsContainer.appendChild(J.previewElement);H=J.previewElement.querySelectorAll("[data-dz-name]");for(l=0,k=H.length;l<k;l++){i=H[l];i.textContent=J.name}I=J.previewElement.querySelectorAll("[data-dz-size]");for(K=0,N=I.length;K<N;K++){i=I[K];i.innerHTML=this.filesize(J.size)}if(this.options.addRemoveLinks){J._removeLink=E.createElement('<a class="dz-remove" href="javascript:undefined;" data-dz-remove>'+this.options.dictRemoveFile+"</a>");J.previewElement.appendChild(J._removeLink)}M=(function(Y){return function(Z){Z.preventDefault();Z.stopPropagation();if(J.status===E.UPLOADING){return E.confirm(Y.options.dictCancelUploadConfirmation,function(){return Y.removeFile(J)})}else{if(Y.options.dictRemoveFileConfirmation){return E.confirm(Y.options.dictRemoveFileConfirmation,function(){return Y.removeFile(J)})}else{return Y.removeFile(J)}}}})(this);f=J.previewElement.querySelectorAll("[data-dz-remove]");g=[];for(h=0,L=f.length;h<L;h++){j=f[h];g.push(j.addEventListener("click",M))}return g},removedfile:function(I){var H;if((H=I.previewElement)!=null){H.parentNode.removeChild(I.previewElement)}return this._updateMaxFilesReachedClass()},thumbnail:function(L,M){var H,K,J,N,I;L.previewElement.classList.remove("dz-file-preview");L.previewElement.classList.add("dz-image-preview");N=L.previewElement.querySelectorAll("[data-dz-thumbnail]");I=[];for(K=0,J=N.length;K<J;K++){H=N[K];H.alt=L.name;I.push(H.src=M)}return I},error:function(L,H){var M,I,K,N,J;L.previewElement.classList.add("dz-error");if(typeof H!=="String"&&H.error){H=H.error}N=L.previewElement.querySelectorAll("[data-dz-errormessage]");J=[];for(I=0,K=N.length;I<K;I++){M=N[I];J.push(M.textContent=H)}return J},errormultiple:R,processing:function(H){H.previewElement.classList.add("dz-processing");if(H._removeLink){return H._removeLink.textContent=this.options.dictCancelUpload}},processingmultiple:R,uploadprogress:function(L,I,H){var M,N,K,J,Z;J=L.previewElement.querySelectorAll("[data-dz-uploadprogress]");Z=[];for(N=0,K=J.length;N<K;N++){M=J[N];Z.push(M.style.width=""+I+"%")}return Z},totaluploadprogress:R,sending:R,sendingmultiple:R,success:function(H){return H.previewElement.classList.add("dz-success")},successmultiple:R,canceled:function(H){return this.emit("error",H,"Upload canceled.")},canceledmultiple:R,complete:function(H){if(H._removeLink){return H._removeLink.textContent=this.options.dictRemoveFile}},completemultiple:R,maxfilesexceeded:R,maxfilesreached:R,previewTemplate:'<div class="dz-preview dz-file-preview">\n  <div class="dz-details">\n    <div class="dz-filename"><span data-dz-name></span></div>\n    <div class="dz-size" data-dz-size></div>\n    <img data-dz-thumbnail />\n  </div>\n  <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>\n  <div class="dz-success-mark"><span>✔</span></div>\n  <div class="dz-error-mark"><span>✘</span></div>\n  <div class="dz-error-message"><span data-dz-errormessage></span></div>\n</div>'};G=function(){var I,K,H,L,N,M,J;L=arguments[0],H=2<=arguments.length?C.call(arguments,1):[];for(M=0,J=H.length;M<J;M++){K=H[M];for(I in K){N=K[I];L[I]=N}}return L};function E(L,I){var K,H,J;this.element=L;this.version=E.version;this.defaultOptions.previewTemplate=this.defaultOptions.previewTemplate.replace(/\n*/g,"");this.clickableElements=[];this.listeners=[];this.files=[];if(typeof this.element==="string"){this.element=document.querySelector(this.element)}if(!(this.element&&(this.element.nodeType!=null))){throw new Error("Invalid dropzone element.")}if(this.element.dropzone){throw new Error("Dropzone already attached.")}E.instances.push(this);this.element.dropzone=this;K=(J=E.optionsForElement(this.element))!=null?J:{};this.options=G({},this.defaultOptions,K,I!=null?I:{});if(this.options.forceFallback||!E.isBrowserSupported()){return this.options.fallback.call(this)}if(this.options.url==null){this.options.url=this.element.getAttribute("action")}if(!this.options.url){throw new Error("No URL provided.")}if(this.options.acceptedFiles&&this.options.acceptedMimeTypes){throw new Error("You can't provide both 'acceptedFiles' and 'acceptedMimeTypes'. 'acceptedMimeTypes' is deprecated.")}if(this.options.acceptedMimeTypes){this.options.acceptedFiles=this.options.acceptedMimeTypes;delete this.options.acceptedMimeTypes}this.options.method=this.options.method.toUpperCase();if((H=this.getExistingFallback())&&H.parentNode){H.parentNode.removeChild(H)}if(this.options.previewsContainer){this.previewsContainer=E.getElement(this.options.previewsContainer,"previewsContainer")}else{this.previewsContainer=this.element}if(this.options.clickable){if(this.options.clickable===true){this.clickableElements=[this.element]}else{this.clickableElements=E.getElements(this.options.clickable,"clickable")}}this.init()}E.prototype.getAcceptedFiles=function(){var L,I,J,K,H;K=this.files;H=[];for(I=0,J=K.length;I<J;I++){L=K[I];if(L.accepted){H.push(L)}}return H};E.prototype.getRejectedFiles=function(){var L,I,J,K,H;K=this.files;H=[];for(I=0,J=K.length;I<J;I++){L=K[I];if(!L.accepted){H.push(L)}}return H};E.prototype.getFilesWithStatus=function(M){var J,I,K,L,H;L=this.files;H=[];for(I=0,K=L.length;I<K;I++){J=L[I];if(J.status===M){H.push(J)}}return H};E.prototype.getQueuedFiles=function(){return this.getFilesWithStatus(E.QUEUED)};E.prototype.getUploadingFiles=function(){return this.getFilesWithStatus(E.UPLOADING)};E.prototype.getActiveFiles=function(){var L,I,J,K,H;K=this.files;H=[];for(I=0,J=K.length;I<J;I++){L=K[I];if(L.status===E.UPLOADING||L.status===E.QUEUED){H.push(L)}}return H};E.prototype.init=function(){var N,M,L,H,K,I,J;if(this.element.tagName==="form"){this.element.setAttribute("enctype","multipart/form-data")}if(this.element.classList.contains("dropzone")&&!this.element.querySelector(".dz-message")){this.element.appendChild(E.createElement('<div class="dz-default dz-message"><span>'+this.options.dictDefaultMessage+"</span></div>"))}if(this.clickableElements.length){L=(function(Z){return function(){if(Z.hiddenFileInput){document.body.removeChild(Z.hiddenFileInput)}Z.hiddenFileInput=document.createElement("input");Z.hiddenFileInput.setAttribute("type","file");if((Z.options.maxFiles==null)||Z.options.maxFiles>1){Z.hiddenFileInput.setAttribute("multiple","multiple")}Z.hiddenFileInput.className="dz-hidden-input";if(Z.options.acceptedFiles!=null){Z.hiddenFileInput.setAttribute("accept",Z.options.acceptedFiles)}Z.hiddenFileInput.style.visibility="hidden";Z.hiddenFileInput.style.position="absolute";Z.hiddenFileInput.style.top="0";Z.hiddenFileInput.style.left="0";Z.hiddenFileInput.style.height="0";Z.hiddenFileInput.style.width="0";document.body.appendChild(Z.hiddenFileInput);return Z.hiddenFileInput.addEventListener("change",function(){var d,f,Y,e;f=Z.hiddenFileInput.files;if(f.length){for(Y=0,e=f.length;Y<e;Y++){d=f[Y];Z.addFile(d)}}return L()})}})(this);L()}this.URL=(I=window.URL)!=null?I:window.webkitURL;J=this.events;for(H=0,K=J.length;H<K;H++){N=J[H];this.on(N,this.options[N])}this.on("uploadprogress",(function(Z){return function(){return Z.updateTotalUploadProgress()}})(this));this.on("removedfile",(function(Z){return function(){return Z.updateTotalUploadProgress()}})(this));this.on("canceled",(function(Z){return function(Y){return Z.emit("complete",Y)}})(this));this.on("complete",(function(Z){return function(Y){if(Z.getUploadingFiles().length===0&&Z.getQueuedFiles().length===0){return setTimeout((function(){return Z.emit("queuecomplete")}),0)}}})(this));M=function(Z){Z.stopPropagation();if(Z.preventDefault){return Z.preventDefault()}else{return Z.returnValue=false}};this.listeners=[{element:this.element,events:{"dragstart":(function(Z){return function(Y){return Z.emit("dragstart",Y)}})(this),"dragenter":(function(Z){return function(Y){M(Y);return Z.emit("dragenter",Y)}})(this),"dragover":(function(Z){return function(d){var Y;try{Y=d.dataTransfer.effectAllowed}catch(c){}d.dataTransfer.dropEffect="move"===Y||"linkMove"===Y?"move":"copy";M(d);return Z.emit("dragover",d)}})(this),"dragleave":(function(Z){return function(Y){return Z.emit("dragleave",Y)}})(this),"drop":(function(Z){return function(Y){M(Y);return Z.drop(Y)}})(this),"dragend":(function(Z){return function(Y){return Z.emit("dragend",Y)}})(this)}}];this.clickableElements.forEach((function(Z){return function(Y){return Z.listeners.push({element:Y,events:{"click":function(b){if((Y!==Z.element)||(b.target===Z.element||E.elementInside(b.target,Z.element.querySelector(".dz-message")))){return Z.hiddenFileInput.click()}}}})}})(this));this.enable();return this.options.init.call(this)};E.prototype.destroy=function(){var H;this.disable();this.removeAllFiles(true);if((H=this.hiddenFileInput)!=null?H.parentNode:void 0){this.hiddenFileInput.parentNode.removeChild(this.hiddenFileInput);this.hiddenFileInput=null}delete this.element.dropzone;return E.instances.splice(E.instances.indexOf(this),1)};E.prototype.updateTotalUploadProgress=function(){var H,M,L,Z,J,N,K,I;Z=0;L=0;H=this.getActiveFiles();if(H.length){I=this.getActiveFiles();for(N=0,K=I.length;N<K;N++){M=I[N];Z+=M.upload.bytesSent;L+=M.upload.total}J=100*Z/L}else{J=100}return this.emit("totaluploadprogress",J,L,Z)};E.prototype.getFallbackForm=function(){var J,K,H,I;if(J=this.getExistingFallback()){return J}H='<div class="dz-fallback">';if(this.options.dictFallbackText){H+="<p>"+this.options.dictFallbackText+"</p>"}H+='<input type="file" name="'+this.options.paramName+(this.options.uploadMultiple?"[]":"")+'" '+(this.options.uploadMultiple?'multiple="multiple"':void 0)+' /><input type="submit" value="Upload!"></div>';K=E.createElement(H);if(this.element.tagName!=="FORM"){I=E.createElement('<form action="'+this.options.url+'" enctype="multipart/form-data" method="'+this.options.method+'"></form>');I.appendChild(K)}else{this.element.setAttribute("enctype","multipart/form-data");this.element.setAttribute("method",this.options.method)}return I!=null?I:K};E.prototype.getExistingFallback=function(){var H,M,I,L,K,J;M=function(N){var b,c,d;for(c=0,d=N.length;c<d;c++){b=N[c];if(/(^| )fallback($| )/.test(b.className)){return b}}};J=["div","form"];for(L=0,K=J.length;L<K;L++){I=J[L];if(H=M(this.element.getElementsByTagName(I))){return H}}};E.prototype.setupEventListeners=function(){var K,L,H,M,J,N,I;N=this.listeners;I=[];for(M=0,J=N.length;M<J;M++){K=N[M];I.push((function(){var a,b;a=K.events;b=[];for(L in a){H=a[L];b.push(K.element.addEventListener(L,H,false))}return b})())}return I};E.prototype.removeEventListeners=function(){var K,L,H,M,J,N,I;N=this.listeners;I=[];for(M=0,J=N.length;M<J;M++){K=N[M];I.push((function(){var a,b;a=K.events;b=[];for(L in a){H=a[L];b.push(K.element.removeEventListener(L,H,false))}return b})())}return I};E.prototype.disable=function(){var L,I,J,K,H;this.clickableElements.forEach(function(M){return M.classList.remove("dz-clickable")});this.removeEventListeners();K=this.files;H=[];for(I=0,J=K.length;I<J;I++){L=K[I];H.push(this.cancelUpload(L))}return H};E.prototype.enable=function(){this.clickableElements.forEach(function(H){return H.classList.add("dz-clickable")});return this.setupEventListeners()};E.prototype.filesize=function(H){var I;if(H>=1024*1024*1024*1024/10){H=H/(1024*1024*1024*1024/10);I="TiB"}else{if(H>=1024*1024*1024/10){H=H/(1024*1024*1024/10);I="GiB"}else{if(H>=1024*1024/10){H=H/(1024*1024/10);I="MiB"}else{if(H>=1024/10){H=H/(1024/10);I="KiB"}else{H=H*10;I="b"}}}}return"<strong>"+(Math.round(H)/10)+"</strong> "+I};E.prototype._updateMaxFilesReachedClass=function(){if((this.options.maxFiles!=null)&&this.getAcceptedFiles().length>=this.options.maxFiles){if(this.getAcceptedFiles().length===this.options.maxFiles){this.emit("maxfilesreached",this.files)}return this.element.classList.add("dz-max-files-reached")}else{return this.element.classList.remove("dz-max-files-reached")}};E.prototype.drop=function(H){var J,I;if(!H.dataTransfer){return}this.emit("drop",H);J=H.dataTransfer.files;if(J.length){I=H.dataTransfer.items;if(I&&I.length&&(I[0].webkitGetAsEntry!=null)){this._addFilesFromItems(I)}else{this.handleFiles(J)}}};E.prototype.paste=function(H){var J,I;if((H!=null?(I=H.clipboardData)!=null?I.items:void 0:void 0)==null){return}this.emit("paste",H);J=H.clipboardData.items;if(J.length){return this._addFilesFromItems(J)}};E.prototype.handleFiles=function(K){var L,H,J,I;I=[];for(H=0,J=K.length;H<J;H++){L=K[H];I.push(this.addFile(L))}return I};E.prototype._addFilesFromItems=function(J){var M,L,I,K,H;H=[];for(I=0,K=J.length;I<K;I++){L=J[I];if((L.webkitGetAsEntry!=null)&&(M=L.webkitGetAsEntry())){if(M.isFile){H.push(this.addFile(L.getAsFile()))}else{if(M.isDirectory){H.push(this._addFilesFromDirectory(M,M.name))}else{H.push(void 0)}}}else{if(L.getAsFile!=null){if((L.kind==null)||L.kind==="file"){H.push(this.addFile(L.getAsFile()))}else{H.push(void 0)}}else{H.push(void 0)}}}return H};E.prototype._addFilesFromDirectory=function(I,K){var H,J;H=I.createReader();J=(function(L){return function(b){var a,N,M;for(N=0,M=b.length;N<M;N++){a=b[N];if(a.isFile){a.file(function(Y){if(L.options.ignoreHiddenFiles&&Y.name.substring(0,1)==="."){return}Y.fullPath=""+K+"/"+Y.name;return L.addFile(Y)})}else{if(a.isDirectory){L._addFilesFromDirectory(a,""+K+"/"+a.name)}}}}})(this);return H.readEntries(J,function(L){return typeof console!=="undefined"&&console!==null?typeof console.log==="function"?console.log(L):void 0:void 0})};E.prototype.accept=function(I,H){if(I.size>this.options.maxFilesize*1024*1024){return H(this.options.dictFileTooBig.replace("{{filesize}}",Math.round(I.size/1024/10.24)/100).replace("{{maxFilesize}}",this.options.maxFilesize))}else{if(!E.isValidFile(I,this.options.acceptedFiles)){return H(this.options.dictInvalidFileType)}else{if((this.options.maxFiles!=null)&&this.getAcceptedFiles().length>=this.options.maxFiles){H(this.options.dictMaxFilesExceeded.replace("{{maxFiles}}",this.options.maxFiles));return this.emit("maxfilesexceeded",I)}else{return this.options.accept.call(this,I,H)}}}};E.prototype.addFile=function(H){H.upload={progress:0,total:H.size,bytesSent:0};this.files.push(H);H.status=E.ADDED;this.emit("addedfile",H);this._enqueueThumbnail(H);return this.accept(H,(function(I){return function(J){if(J){H.accepted=false;I._errorProcessing([H],J)}else{H.accepted=true;if(I.options.autoQueue){I.enqueueFile(H)}}return I._updateMaxFilesReachedClass()}})(this))};E.prototype.enqueueFiles=function(I){var J,H,K;for(H=0,K=I.length;H<K;H++){J=I[H];this.enqueueFile(J)}return null};E.prototype.enqueueFile=function(H){if(H.status===E.ADDED&&H.accepted===true){H.status=E.QUEUED;if(this.options.autoProcessQueue){return setTimeout(((function(I){return function(){return I.processQueue()}})(this)),0)}}else{throw new Error("This file can't be queued because it has already been processed or was rejected.")}};E.prototype._thumbnailQueue=[];E.prototype._processingThumbnail=false;E.prototype._enqueueThumbnail=function(H){if(this.options.createImageThumbnails&&H.type.match(/image.*/)&&H.size<=this.options.maxThumbnailFilesize*1024*1024){this._thumbnailQueue.push(H);return setTimeout(((function(I){return function(){return I._processThumbnailQueue()}})(this)),0)}};E.prototype._processThumbnailQueue=function(){if(this._processingThumbnail||this._thumbnailQueue.length===0){return}this._processingThumbnail=true;return this.createThumbnail(this._thumbnailQueue.shift(),(function(H){return function(){H._processingThumbnail=false;return H._processThumbnailQueue()}})(this))};E.prototype.removeFile=function(H){if(H.status===E.UPLOADING){this.cancelUpload(H)}this.files=S(this.files,H);this.emit("removedfile",H);if(this.files.length===0){return this.emit("reset")}};E.prototype.removeAllFiles=function(K){var L,H,J,I;if(K==null){K=false}I=this.files.slice();for(H=0,J=I.length;H<J;H++){L=I[H];if(L.status!==E.UPLOADING||K){this.removeFile(L)}}return null};E.prototype.createThumbnail=function(J,H){var I;I=new FileReader;I.onload=(function(K){return function(){var L;L=document.createElement("img");L.onload=function(){var i,h,g,j,N,e,M,f;J.width=L.width;J.height=L.height;g=K.options.resize.call(K,J);if(g.trgWidth==null){g.trgWidth=K.options.thumbnailWidth}if(g.trgHeight==null){g.trgHeight=K.options.thumbnailHeight}i=document.createElement("canvas");h=i.getContext("2d");i.width=g.trgWidth;i.height=g.trgHeight;T(h,L,(N=g.srcX)!=null?N:0,(e=g.srcY)!=null?e:0,g.srcWidth,g.srcHeight,(M=g.trgX)!=null?M:0,(f=g.trgY)!=null?f:0,g.trgWidth,g.trgHeight);j=i.toDataURL("image/png");K.emit("thumbnail",J,j);if(H!=null){return H()}};return L.src=I.result}})(this);return I.readAsDataURL(J)};E.prototype.processQueue=function(){var H,I,K,J;I=this.options.parallelUploads;K=this.getUploadingFiles().length;H=K;if(K>=I){return}J=this.getQueuedFiles();if(!(J.length>0)){return}if(this.options.uploadMultiple){return this.processFiles(J.slice(0,I-K))}else{while(H<I){if(!J.length){return}this.processFile(J.shift());H++}}};E.prototype.processFile=function(H){return this.processFiles([H])};E.prototype.processFiles=function(I){var J,H,K;for(H=0,K=I.length;H<K;H++){J=I[H];J.processing=true;J.status=E.UPLOADING;this.emit("processing",J)}if(this.options.uploadMultiple){this.emit("processingmultiple",I)}return this.uploadFiles(I)};E.prototype._getFilesWithXhr=function(H){var J,I;return I=(function(){var L,M,K,N;K=this.files;N=[];for(L=0,M=K.length;L<M;L++){J=K[L];if(J.xhr===H){N.push(J)}}return N}).call(this)};E.prototype.cancelUpload=function(L){var H,M,N,I,K,Z,J;if(L.status===E.UPLOADING){M=this._getFilesWithXhr(L.xhr);for(N=0,K=M.length;N<K;N++){H=M[N];H.status=E.CANCELED}L.xhr.abort();for(I=0,Z=M.length;I<Z;I++){H=M[I];this.emit("canceled",H)}if(this.options.uploadMultiple){this.emit("canceledmultiple",M)}}else{if((J=L.status)===E.ADDED||J===E.QUEUED){L.status=E.CANCELED;this.emit("canceled",L);if(this.options.uploadMultiple){this.emit("canceledmultiple",[L])}}}if(this.options.autoProcessQueue){return this.processQueue()}};E.prototype.uploadFile=function(H){return this.uploadFiles([H])};E.prototype.uploadFiles=function(As){var Ab,Aj,Ae,Aa,Ad,J,At,An,Ak,Ac,x,Af,y,Ag,H,Al,Av,K,M,Au,Ar,z,Am,Ah,Ai,Aq,Ao,I,N,Ap,L;Al=new XMLHttpRequest();for(Av=0,Ar=As.length;Av<Ar;Av++){Ab=As[Av];Ab.xhr=Al}Al.open(this.options.method,this.options.url,true);Al.withCredentials=!!this.options.withCredentials;y=null;Ae=(function(Y){return function(){var b,a,Z;Z=[];for(b=0,a=As.length;b<a;b++){Ab=As[b];Z.push(Y._errorProcessing(As,y||Y.options.dictResponseError.replace("{{statusCode}}",Al.status),Al))}return Z}})(this);Ag=(function(Y){return function(h){var a,c,g,i,f,d,e,Z,b;if(h!=null){c=100*h.loaded/h.total;for(g=0,d=As.length;g<d;g++){Ab=As[g];Ab.upload={progress:c,total:h.total,bytesSent:h.loaded}}}else{a=true;c=100;for(i=0,e=As.length;i<e;i++){Ab=As[i];if(!(Ab.upload.progress===100&&Ab.upload.bytesSent===Ab.upload.total)){a=false}Ab.upload.progress=c;Ab.upload.bytesSent=Ab.upload.total}if(a){return}}b=[];for(f=0,Z=As.length;f<Z;f++){Ab=As[f];b.push(Y.emit("uploadprogress",Ab,c,Ab.upload.bytesSent))}return b}})(this);Al.onload=(function(Y){return function(a){var Z;if(As[0].status===E.CANCELED){return}if(Al.readyState!==4){return}y=Al.responseText;if(Al.getResponseHeader("content-type")&&~Al.getResponseHeader("content-type").indexOf("application/json")){try{y=JSON.parse(y)}catch(b){a=b;y="Invalid JSON response from server."}}Ag();if(!((200<=(Z=Al.status)&&Z<300))){return Ae()}else{return Y._finished(As,y,a)}}})(this);Al.onerror=(function(Y){return function(){if(As[0].status===E.CANCELED){return}return Ae()}})(this);Af=(Ao=Al.upload)!=null?Ao:Al;Af.onprogress=Ag;J={"Accept":"application/json","Cache-Control":"no-cache","X-Requested-With":"XMLHttpRequest"};if(this.options.headers){G(J,this.options.headers)}for(Aa in J){Ad=J[Aa];Al.setRequestHeader(Aa,Ad)}Aj=new FormData();if(this.options.params){I=this.options.params;for(Ac in I){H=I[Ac];Aj.append(Ac,H)}}for(K=0,z=As.length;K<z;K++){Ab=As[K];this.emit("sending",Ab,Al,Aj)}if(this.options.uploadMultiple){this.emit("sendingmultiple",As,Al,Aj)}if(this.element.tagName==="FORM"){N=this.element.querySelectorAll("input, textarea, select, button");for(M=0,Am=N.length;M<Am;M++){At=N[M];An=At.getAttribute("name");Ak=At.getAttribute("type");if(At.tagName==="SELECT"&&At.hasAttribute("multiple")){Ap=At.options;for(Au=0,Ah=Ap.length;Au<Ah;Au++){x=Ap[Au];if(x.selected){Aj.append(An,x.value)}}}else{if(!Ak||((L=Ak.toLowerCase())!=="checkbox"&&L!=="radio")||At.checked){Aj.append(An,At.value)}}}}for(Aq=0,Ai=As.length;Aq<Ai;Aq++){Ab=As[Aq];Aj.append(""+this.options.paramName+(this.options.uploadMultiple?"[]":""),Ab,Ab.name)}return Al.send(Aj)};E.prototype._finished=function(M,I,H){var J,L,K;for(L=0,K=M.length;L<K;L++){J=M[L];J.status=E.SUCCESS;this.emit("success",J,I,H);this.emit("complete",J)}if(this.options.uploadMultiple){this.emit("successmultiple",M,I,H);this.emit("completemultiple",M)}if(this.options.autoProcessQueue){return this.processQueue()}};E.prototype._errorProcessing=function(M,J,L){var H,K,I;for(K=0,I=M.length;K<I;K++){H=M[K];H.status=E.ERROR;this.emit("error",H,J,L);this.emit("complete",H)}if(this.options.uploadMultiple){this.emit("errormultiple",M,J,L);this.emit("completemultiple",M)}if(this.options.autoProcessQueue){return this.processQueue()}};return E})(U);V.version="3.8.7";V.options={};V.optionsForElement=function(E){if(E.getAttribute("id")){return V.options[W(E.getAttribute("id"))]}else{return void 0}};V.instances=[];V.forElement=function(E){if(typeof E==="string"){E=document.querySelector(E)}if((E!=null?E.dropzone:void 0)==null){throw new Error("No Dropzone found for given element. This is probably because you're trying to access it before Dropzone had the time to initialize. Use the `init` option to setup any additional observers on your Dropzone.")}return E.dropzone};V.autoDiscover=true;V.discover=function(){var J,H,G,I,F,E;if(document.querySelectorAll){G=document.querySelectorAll(".dropzone")}else{G=[];J=function(M){var Z,L,N,K;K=[];for(L=0,N=M.length;L<N;L++){Z=M[L];if(/(^| )dropzone($| )/.test(Z.className)){K.push(G.push(Z))}else{K.push(void 0)}}return K};J(document.getElementsByTagName("div"));J(document.getElementsByTagName("form"))}E=[];for(I=0,F=G.length;I<F;I++){H=G[I];if(V.optionsForElement(H)!==false){E.push(new V(H))}else{E.push(void 0)}}return E};V.blacklistedBrowsers=[/opera.*Macintosh.*version\/12/i];V.isBrowserSupported=function(){var H,I,G,F,E;H=true;if(window.File&&window.FileReader&&window.FileList&&window.Blob&&window.FormData&&document.querySelector){if(!("classList" in document.createElement("a"))){H=false}else{E=V.blacklistedBrowsers;for(G=0,F=E.length;G<F;G++){I=E[G];if(I.test(navigator.userAgent)){H=false;continue}}}}else{H=false}return H};S=function(G,H){var F,I,J,E;E=[];for(I=0,J=G.length;I<J;I++){F=G[I];if(F!==H){E.push(F)}}return E};W=function(E){return E.replace(/[\-_](\w)/g,function(F){return F.charAt(1).toUpperCase()})};V.createElement=function(F){var E;E=document.createElement("div");E.innerHTML=F;return E.childNodes[0]};V.elementInside=function(F,E){if(F===E){return true}while(F=F.parentNode){if(F===E){return true}}return false};V.getElement=function(F,E){var G;if(typeof F==="string"){G=document.querySelector(F)}else{if(F.nodeType!=null){G=F}}if(G==null){throw new Error("Invalid `"+E+"` option provided. Please provide a CSS selector or a plain HTML element.")}return G};V.getElements=function(L,K){var E,G,J,Z,M,N,H,I;if(L instanceof Array){J=[];try{for(Z=0,N=L.length;Z<N;Z++){G=L[Z];J.push(this.getElement(G,K))}}catch(F){E=F;J=null}}else{if(typeof L==="string"){J=[];I=document.querySelectorAll(L);for(M=0,H=I.length;M<H;M++){G=I[M];J.push(G)}}else{if(L.nodeType!=null){J=[L]}}}if(!((J!=null)&&J.length)){throw new Error("Invalid `"+K+"` option provided. Please provide a CSS selector, a plain HTML element or a list of those.")}return J};V.confirm=function(G,F,E){if(window.confirm(G)){return F()}else{if(E!=null){return E()}}};V.isValidFile=function(J,H){var F,G,E,I,K;if(!H){return true}H=H.split(",");G=J.type;F=G.replace(/\/.*$/,"");for(I=0,K=H.length;I<K;I++){E=H[I];E=E.trim();if(E.charAt(0)==="."){if(J.name.toLowerCase().indexOf(E.toLowerCase(),J.name.length-E.length)!==-1){return true}}else{if(/\/\*$/.test(E)){if(F===E.replace(/\/.*$/,"")){return true}}else{if(G===E){return true}}}}return false};if(typeof jQuery!=="undefined"&&jQuery!==null){jQuery.fn.dropzone=function(E){return this.each(function(){return new V(this,E)})}}if(typeof A!=="undefined"&&A!==null){A.exports=V}else{window.Dropzone=V}V.ADDED="added";V.QUEUED="queued";V.ACCEPTED=V.QUEUED;V.UPLOADING="uploading";V.PROCESSING=V.UPLOADING;V.CANCELED="canceled";V.ERROR="error";V.SUCCESS="success";X=function(J){var I,E,N,H,L,K,Z,F,G,M;Z=J.naturalWidth;K=J.naturalHeight;E=document.createElement("canvas");E.width=1;E.height=K;N=E.getContext("2d");N.drawImage(J,0,0);H=N.getImageData(0,0,1,K).data;M=0;L=K;F=K;while(F>M){I=H[(F-1)*4+3];if(I===0){L=F}else{M=F}F=(L+M)>>1}G=F/K;if(G===0){return 1}else{return G}};T=function(I,J,F,Z,K,M,L,N,E,H){var G;G=X(J);return I.drawImage(J,F,Z,K,M,L,N,E,H/G)};O=function(H,b){var N,M,E,J,a,K,F,G,L;E=false;L=true;M=H.document;G=M.documentElement;N=(M.addEventListener?"addEventListener":"attachEvent");F=(M.addEventListener?"removeEventListener":"detachEvent");K=(M.addEventListener?"":"on");J=function(Y){if(Y.type==="readystatechange"&&M.readyState!=="complete"){return}(Y.type==="load"?H:M)[F](K+Y.type,J,false);if(!E&&(E=true)){return b.call(H,Y.type||Y)}};a=function(){var Y;try{G.doScroll("left")}catch(Z){Y=Z;setTimeout(a,50);return}return J("poll")};if(M.readyState!=="complete"){if(M.createEventObject&&G.doScroll){try{L=!H.frameElement}catch(I){}if(L){a()}}M[N](K+"DOMContentLoaded",J,false);M[N](K+"readystatechange",J,false);return H[N](K+"load",J,false)}};V._autoDiscoverFunction=function(){if(V.autoDiscover){return V.discover()}};O(window,V._autoDiscoverFunction)}).call(this)});if(typeof exports=="object"){module.exports=B("dropzone")}else{if(typeof define=="function"&&define.amd){define([],function(){return B("dropzone")})}else{this["Dropzone"]=B("dropzone")}}})();