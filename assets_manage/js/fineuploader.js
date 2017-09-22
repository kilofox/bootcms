/**
 * Fine Uploader
 * Version: 3.8.0
 */
var qq = function(a){
	"use strict";
	return {
		hide: function(){
			return a.style.display = "none", this;
		},
		attach: function(b, c){
			return a.addEventListener ? a.addEventListener(b, c, !1) : a.attachEvent && a.attachEvent("on" + b, c), function(){
				qq(a).detach(b, c);
			};
		},
		detach: function(b, c){
			return a.removeEventListener ? a.removeEventListener(b, c, !1) : a.attachEvent && a.detachEvent("on" + b, c), this;
		},
		contains: function(b){
			return b ? a === b ? !0 : a.contains ? a.contains(b) : !!(8 & b.compareDocumentPosition(a)) : !1;
		},
		insertBefore: function(b){
			return b.parentNode.insertBefore(a, b), this;
		},
		remove: function(){
			return a.parentNode.removeChild(a), this;
		},
		css: function(b){
			return null != b.opacity && "string" !== typeof a.style.opacity && "undefined" !== typeof a.filters && (b.filter = "alpha(opacity=" + Math.round(100 * b.opacity) + ")"), qq.extend(a.style, b), this;
		},
		hasClass: function(b){
			var c = new RegExp("(^| )" + b + "( |$)");
			return c.test(a.className);
		},
		addClass: function(b){
			return qq(a).hasClass(b) || (a.className += " " + b), this;
		},
		removeClass: function(b){
			var c = new RegExp("(^| )" + b + "( |$)");
			return a.className = a.className.replace(c, " ").replace(/^\s+|\s+$/g, ""), this;
		},
		getByClass: function(b){
			var c, d = [];
			return a.querySelectorAll ? a.querySelectorAll("." + b) : (c = a.getElementsByTagName("*"), qq.each(c, function(a, c){
				qq(c).hasClass(b) && d.push(c);
			}), d);
		},
		children: function(){
			for( var b = [], c = a.firstChild; c; )
				1 === c.nodeType && b.push(c), c = c.nextSibling;
			return b;
		},
		setText: function(b){
			return a.innerText = b, a.textContent = b, this;
		},
		clearText: function(){
			return qq(a).setText('');
		}
	};
};
qq.log = function(a, b){
	"use strict";
	window.console && (b && "info" !== b ? window.console[b] ? window.console[b](a) : window.console.log("<" + b + "> " + a) : window.console.log(a));
}, qq.isObject = function(a){
	"use strict";
	return a && !a.nodeType && "[object Object]" === Object.prototype.toString.call(a);
}, qq.isFunction = function(a){
	"use strict";
	return "function" === typeof a;
}, qq.isArray = function(a){
	"use strict";
	return "[object Array]" === Object.prototype.toString.call(a);
}, qq.isItemList = function(a){
	"use strict";
	return "[object DataTransferItemList]" === Object.prototype.toString.call(a);
}, qq.isString = function(a){
	"use strict";
	return "[object String]" === Object.prototype.toString.call(a);
}, qq.trimStr = function(a){
	return String.prototype.trim ? a.trim() : a.replace(/^\s+|\s+$/g, "");
}, qq.format = function(a){
	"use strict";
	var b = Array.prototype.slice.call(arguments, 1), c = a;
	return qq.each(b, function(a, b){
		c = c.replace(/{}/, b);
	}), c;
}, qq.isFile = function(a){
	"use strict";
	return window.File && "[object File]" === Object.prototype.toString.call(a);
}, qq.isFileList = function(a){
	return window.FileList && "[object FileList]" === Object.prototype.toString.call(a);
}, qq.isFileOrInput = function(a){
	"use strict";
	return qq.isFile(a) || qq.isInput(a);
}, qq.isInput = function(a){
	return window.HTMLInputElement && "[object HTMLInputElement]" === Object.prototype.toString.call(a) && a.type && "file" === a.type.toLowerCase() ? !0 : a.tagName && "input" === a.tagName.toLowerCase() && a.type && "file" === a.type.toLowerCase() ? !0 : !1;
}, qq.isBlob = function(a){
	"use strict";
	return window.Blob && "[object Blob]" === Object.prototype.toString.call(a);
}, qq.isXhrUploadSupported = function(){
	"use strict";
	var a = document.createElement("input");
	return a.type = "file", void 0 !== a.multiple && "undefined" !== typeof File && "undefined" !== typeof FormData && "undefined" !== typeof (new XMLHttpRequest).upload;
}, qq.isFolderDropSupported = function(a){
	"use strict";
	return a.items && a.items[0].webkitGetAsEntry;
}, qq.isFileChunkingSupported = function(){
	"use strict";
	return !qq.android() && qq.isXhrUploadSupported() && (void 0 !== File.prototype.slice || void 0 !== File.prototype.webkitSlice || void 0 !== File.prototype.mozSlice);
}, qq.extend = function(a, b, c){
	"use strict";
	return qq.each(b, function(b, d){
		c && qq.isObject(d) ? (void 0 === a[b] && (a[b] = {}), qq.extend(a[b], d, !0)) : a[b] = d;
	}), a;
}, qq.override = function(a, b){
	var c = {}, d = b(c);
	return qq.each(d, function(b, d){
		void 0 !== a[b] && (c[b] = a[b]), a[b] = d;
	}), a;
}, qq.indexOf = function(a, b, c){
	"use strict";
	if ( a.indexOf )
		return a.indexOf(b, c);
	c = c || 0;
	var d = a.length;
	for( 0 > c && (c += d); d > c; c += 1 )
		if ( a.hasOwnProperty(c) && a[c] === b )
			return c;
	return -1;
}, qq.getUniqueId = function(){
	"use strict";
	return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function(a){
		var b = 0 | 16 * Math.random(), c = "x" == a ? b : 8 | 3 & b;
		return c.toString(16);
	});
}, qq.ie = function(){
	"use strict";
	return -1 !== navigator.userAgent.indexOf("MSIE");
}, qq.ie7 = function(){
	"use strict";
	return -1 !== navigator.userAgent.indexOf("MSIE 7");
}, qq.ie10 = function(){
	"use strict";
	return -1 !== navigator.userAgent.indexOf("MSIE 10");
}, qq.safari = function(){
	"use strict";
	return void 0 !== navigator.vendor && -1 !== navigator.vendor.indexOf("Apple");
}, qq.chrome = function(){
	"use strict";
	return void 0 !== navigator.vendor && -1 !== navigator.vendor.indexOf("Google");
}, qq.firefox = function(){
	"use strict";
	return -1 !== navigator.userAgent.indexOf("Mozilla") && void 0 !== navigator.vendor && "" === navigator.vendor;
}, qq.windows = function(){
	"use strict";
	return "Win32" === navigator.platform;
}, qq.android = function(){
	"use strict";
	return -1 !== navigator.userAgent.toLowerCase().indexOf("android");
}, qq.ios = function(){
	"use strict";
	return -1 !== navigator.userAgent.indexOf("iPad") || -1 !== navigator.userAgent.indexOf("iPod") || -1 !== navigator.userAgent.indexOf("iPhone");
}, qq.preventDefault = function(a){
	"use strict";
	a.preventDefault ? a.preventDefault() : a.returnValue = !1;
}, qq.toElement = function(){
	"use strict";
	var a = document.createElement("div");
	return function(b){
		a.innerHTML = b;
		var c = a.firstChild;
		return a.removeChild(c), c;
	}
}(), qq.each = function(a, b){
	"use strict";
	var c, d;
	if ( a )
		if ( window.Storage && a.constructor === window.Storage )
			for( c = 0; c < a.length && (d = b(a.key(c), a.getItem(a.key(c))), d !== !1); c++ )
				;
		else if ( qq.isArray(a) || qq.isItemList(a) )
			for( c = 0; c < a.length && (d = b(c, a[c]), d !== !1); c++ )
				;
		else if ( qq.isString(a) )
			for( c = 0; c < a.length && (d = b(c, a.charAt(c)), d !== !1); c++ )
				;
		else
			for( c in a )
				if ( Object.prototype.hasOwnProperty.call(a, c) && (d = b(c, a[c]), d === !1) )
					break;
}, qq.bind = function(a, b){
	if ( qq.isFunction(a) ){
		var c = Array.prototype.slice.call(arguments, 2);
		return function(){
			var d = qq.extend([], c);
			return arguments.length && (d = d.concat(Array.prototype.slice.call(arguments))), a.apply(b, d);
		};
	}
	throw new Error("first parameter must be a function!");
}, qq.obj2url = function(a, b, c){
	"use strict";
	var d = [], e = "&", f = function(a, c){
		var e = b ? /\[\]$/.test(b) ? b : b + "[" + c + "]" : c;
		"undefined" !== e && "undefined" !== c && d.push("object" === typeof a ? qq.obj2url(a, e, !0) : "[object Function]" === Object.prototype.toString.call(a) ? encodeURIComponent(e) + "=" + encodeURIComponent(a()) : encodeURIComponent(e) + "=" + encodeURIComponent(a));
	};
	return !c && b ? (e = /\?/.test(b) ? /\?$/.test(b) ? "" : "&" : "?", d.push(b), d.push(qq.obj2url(a))) : "[object Array]" === Object.prototype.toString.call(a) && "undefined" !== typeof a ? qq.each(a, function(a, b){
		f(b, a);
	}) : "undefined" !== typeof a && null !== a && "object" === typeof a ? qq.each(a, function(a, b){
		f(b, a);
	}) : d.push(encodeURIComponent(b) + "=" + encodeURIComponent(a)), b ? d.join(e) : d.join(e).replace(/^&/, "").replace(/%20/g, "+");
}, qq.obj2FormData = function(a, b, c){
	"use strict";
	return b || (b = new FormData), qq.each(a, function(a, d){
		a = c ? c + "[" + a + "]" : a, qq.isObject(d) ? qq.obj2FormData(d, b, a) : qq.isFunction(d) ? b.append(a, d()) : b.append(a, d);
	}), b;
}, qq.obj2Inputs = function(a, b){
	"use strict";
	var c;
	return b || (b = document.createElement("form")), qq.obj2FormData(a, {
		append: function(a, d){
			c = document.createElement("input"), c.setAttribute("name", a), c.setAttribute("value", d), b.appendChild(c);
		}
	}), b;
}, qq.setCookie = function(a, b, c){
	var d = new Date, e = "";
	c && (d.setTime(d.getTime() + 1e3 * 60 * 60 * 24 * c), e = "; expires=" + d.toGMTString()), document.cookie = a + "=" + b + e + "; path=/";
}, qq.getCookie = function(a){
	var b, c = a + "=", d = document.cookie.split(";");
	return qq.each(d, function(a, d){
		for( var e = d; " " === e.charAt(0); )
			e = e.substring(1, e.length);
		return 0 === e.indexOf(c) ? (b = e.substring(c.length, e.length), !1) : void 0;
	}), b;
}, qq.getCookieNames = function(a){
	var b = document.cookie.split(";"), c = [];
	return qq.each(b, function(b, d){
		d = qq.trimStr(d);
		var e = d.indexOf("=");
		d.match(a) && c.push(d.substr(0, e));
	}), c;
}, qq.deleteCookie = function(a){
	qq.setCookie(a, "", -1);
}, qq.areCookiesEnabled = function(){
	var a = 1e5 * Math.random(), b = "qqCookieTest:" + a;
	return qq.setCookie(b, 1), qq.getCookie(b) ? (qq.deleteCookie(b), !0) : !1;
}, qq.parseJson = function(json){
	return window.JSON && qq.isFunction(JSON.parse) ? JSON.parse(json) : eval("(" + json + ")");
}, qq.getExtension = function(a){
	var b = a.lastIndexOf(".") + 1;
	return b > 0 ? a.substr(b, a.length - b) : void 0;
}, qq.DisposeSupport = function(){
	"use strict";
	var a = [];
	return {
		dispose: function(){
			var b;
			do
				b = a.shift(), b && b();
			while( b );
		},
		attach: function(){
			var a = arguments;
			this.addDisposer(qq(a[0]).attach.apply(this, Array.prototype.slice.call(arguments, 1)));
		},
		addDisposer: function(b){
			a.push(b);
		}
	};
}, qq.version = "3.8.0", qq.supportedFeatures = function(){
	function a(){
		var a, b = !0;
		try{
			a = document.createElement("input"), a.type = "file", qq(a).hide(), a.disabled && (b = !1);
		}catch( c ){
			b = !1;
		}
		return b;
	}
	function b(){
		return qq.chrome() && void 0 !== navigator.userAgent.match(/Chrome\/[2][1-9]|Chrome\/[3-9][0-9]/);
	}
	function c(){
		return qq.chrome() && void 0 !== navigator.userAgent.match(/Chrome\/[1][4-9]|Chrome\/[2-9][0-9]/);
	}
	function d(){
		if ( window.XMLHttpRequest ){
			var a = new XMLHttpRequest;
			return void 0 !== a.withCredentials;
		}
		return !1;
	}
	function e(){
		return void 0 !== window.XDomainRequest;
	}
	function f(){
		return d() ? !0 : e();
	}
	var g, h, i, j, k, l, m, n, o, p;
	return g = a(), h = g && qq.isXhrUploadSupported(), i = h && b(), j = h && qq.isFileChunkingSupported(), k = h && j && qq.areCookiesEnabled(), l = h && c(), m = g && (void 0 !== window.postMessage || h), o = d(), n = e(), p = f(), {
		uploading: g,
		ajaxUploading: h,
		fileDrop: h,
		folderDrop: i,
		chunking: j,
		resume: k,
		uploadCustomHeaders: h,
		uploadNonMultipart: h,
		itemSizeValidation: h,
		uploadViaPaste: l,
		progressBar: h,
		uploadCors: m,
		deleteFileCorsXhr: o,
		deleteFileCorsXdr: n,
		deleteFileCors: p,
		canDetermineSize: h
	};
}(), qq.Promise = function(){
	"use strict";
	var a, b, c = [], d = [], e = [], f = 0;
	return {
		then: function(e, g){
			return 0 === f ? (e && c.push(e), g && d.push(g)) : -1 === f && g ? g.apply(null, b) : e && e.apply(null, a), this;
		},
		done: function(c){
			return 0 === f ? e.push(c) : c.apply(null, void 0 === b ? a : b), this;
		},
		success: function(){
			return f = 1, a = arguments, c.length && qq.each(c, function(b, c){
				c.apply(null, a);
			}), e.length && qq.each(e, function(b, c){
				c.apply(null, a);
			}), this;
		},
		failure: function(){
			return f = -1, b = arguments, d.length && qq.each(d, function(a, c){
				c.apply(null, b);
			}), e.length && qq.each(e, function(a, c){
				c.apply(null, b);
			}), this;
		}
	};
}, qq.isPromise = function(a){
	return a && a.then && a.done;
}, qq.UploadButton = function(a){
	"use strict";
	function b(){
		var a = document.createElement("input");
		return e.multiple && a.setAttribute("multiple", "multiple"), e.acceptFiles && a.setAttribute("accept", e.acceptFiles), a.setAttribute("type", "file"), a.setAttribute("name", e.name), qq(a).css({
			position: "absolute",
			right: 0,
			top: 0,
			fontFamily: "Arial",
			fontSize: "118px",
			margin: 0,
			padding: 0,
			cursor: "pointer",
			opacity: 0
		}), e.element.appendChild(a), d.attach(a, "change", function(){
			e.onChange(a);
		}), d.attach(a, "mouseover", function(){
			qq(e.element).addClass(e.hoverClass);
		}), d.attach(a, "mouseout", function(){
			qq(e.element).removeClass(e.hoverClass);
		}), d.attach(a, "focus", function(){
			qq(e.element).addClass(e.focusClass);
		}), d.attach(a, "blur", function(){
			qq(e.element).removeClass(e.focusClass);
		}), window.attachEvent && a.setAttribute("tabIndex", "-1"), a;
	}
	var c, d = new qq.DisposeSupport, e = {
		element: null,
		multiple: !1,
		acceptFiles: null,
		name: "qqfile",
		onChange: function(){
		},
		hoverClass: "qq-upload-button-hover",
		focusClass: "qq-upload-button-focus"
	};
	return qq.extend(e, a), qq(e.element).css({
		position: "relative",
		overflow: "hidden",
		direction: "ltr"
	}), c = b(), {
		getInput: function(){
			return c;
		},
		reset: function(){
			c.parentNode && qq(c).remove(), qq(e.element).removeClass(e.focusClass), c = b();
		}
	};
}, qq.PasteSupport = function(a){
	"use strict";
	function b(a){
		return a.type && 0 === a.type.indexOf("image/");
	}
	function c(){
		qq(e.targetElement).attach("paste", function(a){
			var c = a.clipboardData;
			c && qq.each(c.items, function(a, c){
				if ( b(c) ){
					var d = c.getAsFile();
					e.callbacks.pasteReceived(d);
				}
			});
		});
	}
	function d(){
		f && f();
	}
	var e, f;
	return e = {
		targetElement: null,
		callbacks: {
			log: function(){
			},
			pasteReceived: function(){
			}
		}
	},
	qq.extend(e, a), c(), {
		reset: function(){
			d();
		}
	};
}, qq.UploadData = function(a){
	function b(a){
		if ( qq.isArray(a) ){
			var b = [];
			return qq.each(a, function(a, c){
				b.push(f[g[c]]);
			}), b;
		}
		return f[g[a]];
	}
	function c(a){
		if ( qq.isArray(a) ){
			var b = [];
			return qq.each(a, function(a, c){
				b.push(f[h[c]]);
			}), b;
		}
		return f[h[a]];
	}
	function d(a){
		var b = [], c = [].concat(a);
		return qq.each(c, function(a, c){
			var d = i[c];
			void 0 !== d && qq.each(d, function(a, c){
				b.push(f[c]);
			});
		}), b;
	}
	var e, f = [], g = {}, h = {}, i = {};
	return e = {
		added: function(b){
			var c = a.getUuid(b), d = a.getName(b), e = a.getSize(b), j = qq.status.SUBMITTING, k = f.push({
				id: b,
				name: d,
				originalName: d,
				uuid: c,
				size: e,
				status: j
			}) - 1;
			g[b] = k, h[c] = k, void 0 === i[j] && (i[j] = [
			]), i[j].push(k), a.onStatusChange(b, void 0, j);
		},
		retrieve: function(a){
			return qq.isObject(a) && f.length ? void 0 !== a.id ? b(a.id) : void 0 !== a.uuid ? c(a.uuid) : a.status ? d(a.status) : void 0 : qq.extend([
			], f, !0);
		},
		reset: function(){
			f = [], g = {}, h = {}, i = {};
		},
		setStatus: function(b, c){
			var d = g[b], e = f[d].status, h = qq.indexOf(i[e], d);
			i[e].splice(h, 1), f[d].status = c, void 0 === i[c] && (i[c] = [
			]), i[c].push(d), a.onStatusChange(b, e, c);
		},
		uuidChanged: function(a, b){
			var c = g[a], d = f[c].uuid;
			f[c].uuid = b, h[b] = c, delete h[d];
		},
		nameChanged: function(a, b){
			var c = g[a];
			f[c].name = b;
		}
	};
}, qq.status = {
	SUBMITTING: "submitting",
	SUBMITTED: "submitted",
	REJECTED: "rejected",
	QUEUED: "queued",
	CANCELED: "canceled",
	UPLOADING: "uploading",
	UPLOAD_RETRYING: "retrying upload",
	UPLOAD_SUCCESSFUL: "upload successful",
	UPLOAD_FAILED: "upload failed",
	DELETE_FAILED: "delete failed",
	DELETING: "deleting",
	DELETED: "deleted"
},
qq.basePublicApi = {
	log: function(a, b){
		!this._options.debug || b && "info" !== b ? b && "info" !== b && qq.log("[FineUploader " + qq.version + "] " + a, b) : qq.log("[FineUploader " + qq.version + "] " + a);
	},
	setParams: function(a, b){
		null == b ? this._options.request.params = a : this._paramsStore.setParams(a, b);
	},
	setDeleteFileParams: function(a, b){
		null == b ? this._options.deleteFile.params = a : this._deleteFileParamsStore.setParams(a, b);
	},
	setEndpoint: function(a, b){
		null == b ? this._options.request.endpoint = a : this._endpointStore.setEndpoint(a, b);
	},
	getInProgress: function(){
		return this._filesInProgress.length;
	},
	getNetUploads: function(){
		return this._netUploaded;
	},
	uploadStoredFiles: function(){
		var a;
		if ( 0 === this._storedIds.length )
			this._itemError("noFilesError");
		else
			for( ; this._storedIds.length; )
				a = this._storedIds.shift(), this._filesInProgress.push(a), this._handler.upload(a);
	},
	clearStoredFiles: function(){
		this._storedIds = [];
	},
	retry: function(a){
		return this._onBeforeManualRetry(a) ? (this._netUploadedOrQueued++, this._uploadData.setStatus(a, qq.status.UPLOAD_RETRYING), this._handler.retry(a), !0) : !1;
	},
	cancel: function(a){
		this._handler.cancel(a);
	},
	cancelAll: function(){
		var a = [], b = this;
		qq.extend(a, this._storedIds), qq.each(a, function(a, c){
			b.cancel(c);
		}), this._handler.cancelAll();
	},
	reset: function(){
		this.log("Resetting uploader..."), this._handler.reset(), this._filesInProgress = [
		], this._storedIds = [], this._autoRetries = [
		], this._retryTimeouts = [], this._preventRetries = [
		], this._button.reset(), this._paramsStore.reset(), this._endpointStore.reset(), this._netUploadedOrQueued = 0, this._netUploaded = 0, this._uploadData.reset(), this._pasteHandler && this._pasteHandler.reset();
	},
	addFiles: function(a, b, c){
		var d, e, f, g = this, h = [];
		if ( a ){
			for( qq.isFileList(a) || (a = [].concat(a)), d = 0; d < a.length; d += 1 )
				if ( e = a[d], qq.isFileOrInput(e) )
					if ( qq.isInput(e) && qq.supportedFeatures.ajaxUploading )
						for( f = 0; f < e.files.length; f++ )
							h.push(e.files[f]);
					else
						h.push(e);
				else
					g.log(e + " is not a File or INPUT element! Ignoring!", "warn");
			this.log("Received " + h.length + " files or inputs."), this._prepareItemsForUpload(h, b, c);
		}
	},
	addBlobs: function(a, b, c){
		if ( a ){
			var d = [].concat(a), e = [], f = this;
			qq.each(d, function(a, b){
				qq.isBlob(b) && !qq.isFileOrInput(b) ? e.push({
					blob: b,
					name: f._options.blobs.defaultName
				}) : qq.isObject(b) && b.blob && b.name ? e.push(b) : f.log("addBlobs: entry at index " + a + " is not a Blob or a BlobData object", "error");
			}), this._prepareItemsForUpload(e, b, c);
		}else
			this.log("undefined or non-array parameter passed into addBlobs", "error");
	},
	getUuid: function(a){
		return this._handler.getUuid(a);
	},
	setUuid: function(a, b){
		return this._handler.setUuid(a, b);
	},
	getResumableFilesData: function(){
		return this._handler.getResumableFilesData();
	},
	getSize: function(a){
		return this._handler.getSize(a);
	},
	getName: function(a){
		return this._handler.getName(a);
	},
	setName: function(a, b){
		this._handler.setName(a, b), this._uploadData.nameChanged(a, b);
	},
	getFile: function(a){
		return this._handler.getFile(a);
	},
	deleteFile: function(a){
		this._onSubmitDelete(a);
	},
	setDeleteFileEndpoint: function(a, b){
		null == b ? this._options.deleteFile.endpoint = a : this._deleteFileEndpointStore.setEndpoint(a, b);
	},
	doesExist: function(a){
		return this._handler.isValid(a);
	},
	getUploads: function(a){
		return this._uploadData.retrieve(a);
	}
},
qq.basePrivateApi = {
	_handleCheckedCallback: function(a){
		var b = this, c = a.callback();
		return qq.isPromise(c) ? (this.log(a.name + " - waiting for " + a.name + " promise to be fulfilled for " + a.identifier), c.then(function(c){
			b.log(a.name + " promise success for " + a.identifier), a.onSuccess(c);
		}, function(){
			a.onFailure ? (b.log(a.name + " promise failure for " + a.identifier), a.onFailure()) : b.log(a.name + " promise failure for " + a.identifier);
		})) : (c !== !1 ? a.onSuccess(c) : a.onFailure ? (this.log(a.name + " - return value was 'false' for " + a.identifier + ". Invoking failure callback."), a.onFailure()) : this.log(a.name + " - return value was 'false' for " + a.identifier + ". Will not proceed."), c);
	},
	_createUploadButton: function(a){
		var b = this, c = new qq.UploadButton({
			element: a,
			multiple: this._options.multiple && qq.supportedFeatures.ajaxUploading,
			acceptFiles: this._options.validation.acceptFiles,
			onChange: function(a){
				b._onInputChange(a);
			},
			hoverClass: this._options.classes.buttonHover,
			focusClass: this._options.classes.buttonFocus
		});
		return this._disposeSupport.addDisposer(function(){
			c.dispose();
		}), c;
	},
	_createUploadHandler: function(a, b){
		var c = this, d = {
			debug: this._options.debug,
			maxConnections: this._options.maxConnections,
			inputName: this._options.request.inputName,
			cors: this._options.cors,
			demoMode: this._options.demoMode,
			paramsStore: this._paramsStore,
			endpointStore: this._endpointStore,
			chunking: this._options.chunking,
			resume: this._options.resume,
			blobs: this._options.blobs,
			log: function(a, b){
				c.log(a, b);
			},
			onProgress: function(a, b, d, e){
				c._onProgress(a, b, d, e), c._options.callbacks.onProgress(a, b, d, e);
			},
			onComplete: function(a, b, d, e){
				var f = c._onComplete(a, b, d, e);
				qq.isPromise(f) ? f.done(function(){
					c._options.callbacks.onComplete(a, b, d, e);
				}) : c._options.callbacks.onComplete(a, b, d, e);
			},
			onCancel: function(a, b){
				return c._handleCheckedCallback({
					name: "onCancel",
					callback: qq.bind(c._options.callbacks.onCancel, c, a, b),
					onSuccess: qq.bind(c._onCancel, c, a, b),
					identifier: a
				});
			},
			onUpload: function(a, b){
				c._onUpload(a, b), c._options.callbacks.onUpload(a, b);
			},
			onUploadChunk: function(a, b, d){
				c._options.callbacks.onUploadChunk(a, b, d);
			},
			onResume: function(a, b, d){
				return c._options.callbacks.onResume(a, b, d);
			},
			onAutoRetry: function(a, b, d, e){
				return c._preventRetries[a] = d[c._options.retry.preventRetryResponseProperty], c._shouldAutoRetry(a, b, d) ? (c._maybeParseAndSendUploadError(a, b, d, e), c._options.callbacks.onAutoRetry(a, b, c._autoRetries[a] + 1), c._onBeforeAutoRetry(a, b), c._retryTimeouts[a] = setTimeout(function(){
					c._onAutoRetry(a, b, d);
				}, 1e3 * c._options.retry.autoAttemptDelay), !0) : !1;
			},
			onUuidChanged: function(a, b){
				c._uploadData.uuidChanged(a, b);
			}
		};
		return qq.each(this._options.request, function(a, b){
			d[a] = b;
		}), a && qq.each(a, function(a, b){
			d[a] = b;
		}), new qq.UploadHandler(d, b);
	},
	_createDeleteHandler: function(){
		var a = this;
		return new qq.DeleteFileAjaxRequestor({
			method: this._options.deleteFile.method,
			maxConnections: this._options.maxConnections,
			uuidParamName: this._options.request.uuidName,
			customHeaders: this._options.deleteFile.customHeaders,
			paramsStore: this._deleteFileParamsStore,
			endpointStore: this._deleteFileEndpointStore,
			demoMode: this._options.demoMode,
			cors: this._options.cors,
			log: function(b, c){
				a.log(b, c);
			},
			onDelete: function(b){
				a._onDelete(b), a._options.callbacks.onDelete(b);
			},
			onDeleteComplete: function(b, c, d){
				a._onDeleteComplete(b, c, d), a._options.callbacks.onDeleteComplete(b, c, d);
			}
		});
	},
	_createPasteHandler: function(){
		var a = this;
		return new qq.PasteSupport({
			targetElement: this._options.paste.targetElement,
			callbacks: {
				log: function(b, c){
					a.log(b, c);
				},
				pasteReceived: function(b){
					a._handleCheckedCallback({
						name: "onPasteReceived",
						callback: qq.bind(a._options.callbacks.onPasteReceived, a, b),
						onSuccess: qq.bind(a._handlePasteSuccess, a, b),
						identifier: "pasted image"
					});
				}
			}
		});
	},
	_createUploadDataTracker: function(){
		var a = this;
		return new qq.UploadData({
			getName: function(b){
				return a.getName(b);
			},
			getUuid: function(b){
				return a.getUuid(b);
			},
			getSize: function(b){
				return a.getSize(b);
			},
			onStatusChange: function(b, c, d){
				a._onUploadStatusChange(b, c, d), a._options.callbacks.onStatusChange(b, c, d);
			}
		});
	},
	_onUploadStatusChange: function(){
	},
	_handlePasteSuccess: function(a, b){
		var c = a.type.split("/")[1], d = b;
		null == d && (d = this._options.paste.defaultName), d += "." + c, this.addBlobs({
			name: d,
			blob: a
		});
	},
	_preventLeaveInProgress: function(){
		var a = this;
		this._disposeSupport.attach(window, "beforeunload", function(b){
			if ( a._filesInProgress.length ){
				var b = b || window.event;
				return b.returnValue = a._options.messages.onLeave, a._options.messages.onLeave;
			}
		});
	},
	_onSubmit: function(a){
		this._netUploadedOrQueued++, this._options.autoUpload && this._filesInProgress.push(a);
	},
	_onProgress: function(){
	},
	_onComplete: function(a, b, c, d){
		return c.success ? (this._netUploaded++, this._uploadData.setStatus(a, qq.status.UPLOAD_SUCCESSFUL)) : (this._netUploadedOrQueued--, this._uploadData.setStatus(a, qq.status.UPLOAD_FAILED)), this._removeFromFilesInProgress(a), this._maybeParseAndSendUploadError(a, b, c, d), c.success ? !0 : !1;
	},
	_onCancel: function(a){
		this._netUploadedOrQueued--, this._removeFromFilesInProgress(a), clearTimeout(this._retryTimeouts[a]);
		var b = qq.indexOf(this._storedIds, a);
		!this._options.autoUpload && b >= 0 && this._storedIds.splice(b, 1), this._uploadData.setStatus(a, qq.status.CANCELED);
	},
	_isDeletePossible: function(){
		return this._options.deleteFile.enabled ? this._options.cors.expected ? qq.supportedFeatures.deleteFileCorsXhr ? !0 : qq.supportedFeatures.deleteFileCorsXdr && this._options.cors.allowXdr ? !0 : !1 : !0 : !1;
	},
	_onSubmitDelete: function(a, b, c){
		var d, e = this.getUuid(a);
		return b && (d = qq.bind(b, this, a, e, c)), this._isDeletePossible() ? this._handleCheckedCallback({
			name: "onSubmitDelete",
			callback: qq.bind(this._options.callbacks.onSubmitDelete, this, a),
			onSuccess: d || qq.bind(this._deleteHandler.sendDelete, this, a, e, c),
			identifier: a
		}) : (this.log("Delete request ignored for ID " + a + ", delete feature is disabled or request not possible " + "due to CORS on a user agent that does not support pre-flighting.", "warn"), !1);
	},
	_onDelete: function(a){
		this._uploadData.setStatus(a, qq.status.DELETING);
	},
	_onDeleteComplete: function(a, b, c){
		var d = this._handler.getName(a);
		c ? (this._uploadData.setStatus(a, qq.status.DELETE_FAILED), this.log("Delete request for '" + d + "' has failed.", "error"), void 0 === b.withCredentials ? this._options.callbacks.onError(a, d, "Delete request failed", b) : this._options.callbacks.onError(a, d, "Delete request failed with response code " + b.status, b)) : (this._netUploadedOrQueued--, this._netUploaded--, this._handler.expunge(a), this._uploadData.setStatus(a, qq.status.DELETED), this.log("Delete request for '" + d + "' has succeeded."));
	},
	_removeFromFilesInProgress: function(a){
		var b = qq.indexOf(this._filesInProgress, a);
		b >= 0 && this._filesInProgress.splice(b, 1);
	},
	_onUpload: function(a){
		this._uploadData.setStatus(a, qq.status.UPLOADING);
	},
	_onInputChange: function(a){
		qq.supportedFeatures.ajaxUploading ? this.addFiles(a.files) : this.addFiles(a), this._button.reset();
	},
	_onBeforeAutoRetry: function(a, b){
		this.log("Waiting " + this._options.retry.autoAttemptDelay + " seconds before retrying " + b + "...");
	},
	_onAutoRetry: function(a, b){
		this.log("Retrying " + b + "..."), this._autoRetries[a]++, this._uploadData.setStatus(a, qq.status.UPLOAD_RETRYING), this._handler.retry(a);
	},
	_shouldAutoRetry: function(a){
		return !this._preventRetries[a] && this._options.retry.enableAuto ? (void 0 === this._autoRetries[a] && (this._autoRetries[a] = 0), this._autoRetries[a] < this._options.retry.maxAutoAttempts) : !1;
	},
	_onBeforeManualRetry: function(a){
		var b = this._options.validation.itemLimit;
		if ( this._preventRetries[a] )
			return this.log("Retries are forbidden for id " + a, "warn"), !1;
		if ( this._handler.isValid(a) ){
			var c = this._handler.getName(a);
			return this._options.callbacks.onManualRetry(a, c) === !1 ? !1 : b > 0 && this._netUploadedOrQueued + 1 > b ? (this._itemError("retryFailTooManyItems"), !1) : (this.log("Retrying upload for '" + c + "' (id: " + a + ")..."), this._filesInProgress.push(a), !0);
		}
		return this.log("'" + a + "' is not a valid file ID", "error"), !1;
	},
	_maybeParseAndSendUploadError: function(a, b, c, d){
		if ( !c.success )
			if ( d && 200 !== d.status && !c.error )
				this._options.callbacks.onError(a, b, "XHR return ed response code " + d.status, d);
			else{
				var e = c.error ? c.error : this._options.text.defaultResponseError;
				this._options.callbacks.onError(a, b, e, d);
			}
	},
	_prepareItemsForUpload: function(a, b, c){
		var d = this._getValidationDescriptors(a);
		this._handleCheckedCallback({
			name: "onValidateBatch",
			callback: qq.bind(this._options.callbacks.onValidateBatch, this, d),
			onSuccess: qq.bind(this._onValidateBatchCallbackSuccess, this, d, a, b, c),
			identifier: "batch validation"
		});
	},
	_upload: function(a, b, c){
		var d = this._handler.add(a), e = this._handler.getName(d);
		this._uploadData.added(d), b && this.setParams(b, d), c && this.setEndpoint(c, d), this._handleCheckedCallback({
			name: "onSubmit",
			callback: qq.bind(this._options.callbacks.onSubmit, this, d, e),
			onSuccess: qq.bind(this._onSubmitCallbackSuccess, this, d, e),
			onFailure: qq.bind(this._fileOrBlobRejected, this, d, e),
			identifier: d
		});
	},
	_onSubmitCallbackSuccess: function(a){
		this._uploadData.setStatus(a, qq.status.SUBMITTED), this._onSubmit.apply(this, arguments), this._onSubmitted.apply(this, arguments), this._options.callbacks.onSubmitted.apply(this, arguments), this._options.autoUpload ? this._handler.upload(a) || this._uploadData.setStatus(a, qq.status.QUEUED) : this._storeForLater(a);
	},
	_onSubmitted: function(){
	},
	_storeForLater: function(a){
		this._storedIds.push(a);
	},
	_onValidateBatchCallbackSuccess: function(a, b, c, d){
		var e, f = this._options.validation.itemLimit, g = this._netUploadedOrQueued + a.length;
		0 === f || f >= g ? b.length > 0 ? this._handleCheckedCallback({
			name: "onValidate",
			callback: qq.bind(this._options.callbacks.onValidate, this, b[0]),
			onSuccess: qq.bind(this._onValidateCallbackSuccess, this, b, 0, c, d),
			onFailure: qq.bind(this._onValidateCallbackFailure, this, b, 0, c, d),
			identifier: "Item '" + b[0].name + "', size: " + b[0].size
		}) : this._itemError("noFilesError") : (e = this._options.messages.tooManyItemsError.replace(/\{netItems\}/g, g).replace(/\{itemLimit\}/g, f), this._batchError(e));
	},
	_onValidateCallbackSuccess: function(a, b, c, d){
		var e = b + 1, f = this._getValidationDescriptor(a[b]), g = !1;
		this._validateFileOrBlobData(a[b], f) && (g = !0, this._upload(a[b], c, d)), this._maybeProcessNextItemAfterOnValidateCallback(g, a, e, c, d);
	},
	_onValidateCallbackFailure: function(a, b, c, d){
		var e = b + 1;
		this._fileOrBlobRejected(void 0, a[0].name), this._maybeProcessNextItemAfterOnValidateCallback(!1, a, e, c, d);
	},
	_maybeProcessNextItemAfterOnValidateCallback: function(a, b, c, d, e){
		var f = this;
		b.length > c && (a || !this._options.validation.stopOnFirstInvalidFile) && setTimeout(function(){
			var a = f._getValidationDescriptor(b[c]);
			f._handleCheckedCallback({
				name: "onValidate",
				callback: qq.bind(f._options.callbacks.onValidate, f, b[c]),
				onSuccess: qq.bind(f._onValidateCallbackSuccess, f, b, c, d, e),
				onFailure: qq.bind(f._onValidateCallbackFailure, f, b, c, d, e),
				identifier: "Item '" + a.name + "', size: " + a.size
			});
		}, 0);
	},
	_validateFileOrBlobData: function(a, b){
		var c = b.name, d = b.size, e = !0;
		return this._options.callbacks.onValidate(b) === !1 && (e = !1), qq.isFileOrInput(a) && !this._isAllowedExtension(c) ? (this._itemError("typeError", c), e = !1) : 0 === d ? (this._itemError("emptyError", c), e = !1) : d && this._options.validation.sizeLimit && d > this._options.validation.sizeLimit ? (this._itemError("sizeError", c), e = !1) : d && d < this._options.validation.minSizeLimit && (this._itemError("minSizeError", c), e = !1), e || this._fileOrBlobRejected(void 0, c), e;
	},
	_fileOrBlobRejected: function(a){
		void 0 !== a && this._uploadData.setStatus(a, qq.status.REJECTED);
	},
	_itemError: function(a, b){
		function c(a, b){
			f = f.replace(a, b);
		}
		var d, e, f = this._options.messages[a], g = [], h = [].concat(b), i = h[0];
		return qq.each(this._options.validation.allowedExtensions, function(a, b){
			qq.isString(b) && g.push(b);
		}), d = g.join(", ").toLowerCase(), c("{file}", this._options.formatFileName(i)), c("{extensions}", d), c("{sizeLimit}", this._formatSize(this._options.validation.sizeLimit)), c("{minSizeLimit}", this._formatSize(this._options.validation.minSizeLimit)), e = f.match(/(\{\w+\})/g), null !== e && qq.each(e, function(a, b){
			c(b, h[a]);
		}), this._options.callbacks.onError(null, i, f, void 0), f;
	},
	_batchError: function(a){
		this._options.callbacks.onError(null, null, a, void 0);
	},
	_isAllowedExtension: function(a){
		var b = this._options.validation.allowedExtensions, c = !1;
		return b.length ? (qq.each(b, function(b, d){
			if ( qq.isString(d) ){
				var e = new RegExp("\\." + d + "$", "i");
				if ( null != a.match(e) )
					return c = !0, !1;
			}
		}), c) : !0;
	},
	_formatSize: function(a){
		var b = -1;
		do
			a /= 1e3, b++;
		while( a > 999 );
		return Math.max(a, .1).toFixed(1) + this._options.text.sizeSymbols[b];
	},
	_wrapCallbacks: function(){
		var a, b;
		a = this, b = function(b, c, d){
			try{
				return c.apply(a, d);
			}catch( e ){
				a.log("Caught exception in '" + b + "' callback - " + e.message, "error");
			}
		};
		for( var c in this._options.callbacks )
			!function(){
				var d, e;
				d = c, e = a._options.callbacks[d], a._options.callbacks[d] = function(){
					return b(d, e, arguments);
				};
			}();
	},
	_parseFileOrBlobDataName: function(a){
		var b;
		return b = qq.isFileOrInput(a) ? a.value ? a.value.replace(/.*(\/|\\)/, "") : null !== a.fileName && void 0 !== a.fileName ? a.fileName : a.name : a.name;
	},
	_parseFileOrBlobDataSize: function(a){
		var b;
		return qq.isFileOrInput(a) ? a.value || (b = null !== a.fileSize && void 0 !== a.fileSize ? a.fileSize : a.size) : b = a.blob.size, b;
	},
	_getValidationDescriptor: function(a){
		var b, c, d;
		return d = {}, b = this._parseFileOrBlobDataName(a), c = this._parseFileOrBlobDataSize(a), d.name = b, void 0 !== c && (d.size = c), d;
	},
	_getValidationDescriptors: function(a){
		var b = this, c = [];
		return qq.each(a, function(a, d){
			c.push(b._getValidationDescriptor(d));
		}), c;
	},
	_createParamsStore: function(a){
		var b = {}, c = this;
		return {
			setParams: function(a, c){
				var d = {};
				qq.extend(d, a), b[c] = d;
			},
			getParams: function(d){
				var e = {};
				return null != d && b[d] ? qq.extend(e, b[d]) : qq.extend(e, c._options[a].params), e;
			},
			remove: function(a){
				return delete b[a];
			},
			reset: function(){
				b = {};
			}
		};
	},
	_createEndpointStore: function(a){
		var b = {}, c = this;
		return {
			setEndpoint: function(a, c){
				b[c] = a;
			},
			getEndpoint: function(d){
				return null != d && b[d] ? b[d] : c._options[a].endpoint;
			},
			remove: function(a){
				return delete b[a];
			},
			reset: function(){
				b = {};
			}
		};
	},
	_handleCameraAccess: function(){
		this._options.camera.ios && qq.ios() && (this._options.multiple = !1, null === this._options.validation.acceptFiles ? this._options.validation.acceptFiles = "image/*;capture=camera" : this._options.validation.acceptFiles += ",image/*;capture=camera");
	}
},
qq.FineUploaderBasic = function(a){
	this._options = {
		debug: !1,
		button: null,
		multiple: !0,
		maxConnections: 3,
		disableCancelForFormUploads: !1,
		autoUpload: !0,
		request: {
			endpoint: "/server/upload",
			params: {},
			paramsInBody: !0,
			customHeaders: {},
			forceMultipart: !0,
			inputName: "qqfile",
			uuidName: "qquuid",
			totalFileSizeName: "qqtotalfilesize",
			filenameParam: "qqfilename"
		},
		validation: {
			allowedExtensions: [],
			sizeLimit: 0,
			minSizeLimit: 0,
			itemLimit: 0,
			stopOnFirstInvalidFile: !0,
			acceptFiles: null
		},
		callbacks: {
			onSubmit: function(){
			},
			onSubmitted: function(){
			},
			onComplete: function(){
			},
			onCancel: function(){
			},
			onUpload: function(){
			},
			onUploadChunk: function(){
			},
			onResume: function(){
			},
			onProgress: function(){
			},
			onError: function(){
			},
			onAutoRetry: function(){
			},
			onManualRetry: function(){
			},
			onValidateBatch: function(){
			},
			onValidate: function(){
			},
			onSubmitDelete: function(){
			},
			onDelete: function(){
			},
			onDeleteComplete: function(){
			},
			onPasteReceived: function(){
			},
			onStatusChange: function(){
			}
		},
		messages: {
			typeError: "{file} has an invalid extension. Valid extension(s): {extensions}.",
			sizeError: "{file} is too large, maximum file size is {sizeLimit}.",
			minSizeError: "{file} is too small, minimum file size is {minSizeLimit}.",
			emptyError: "{file} is empty, please select files again without it.",
			noFilesError: "No files to upload.",
			tooManyItemsError: "Too many items ({netItems}) would be uploaded. Item limit is {itemLimit}.",
			retryFailTooManyItems: "Retry failed - you have reached your file limit.",
			onLeave: "The files are being uploaded, if you leave now the upload will be cancelled."
		},
		retry: {
			enableAuto: !1,
			maxAutoAttempts: 3,
			autoAttemptDelay: 5,
			preventRetryResponseProperty: "preventRetry"
		},
		classes: {
			buttonHover: "qq-upload-button-hover",
			buttonFocus: "qq-upload-button-focus"
		},
		chunking: {
			enabled: !1,
			partSize: 2e6,
			paramNames: {
				partIndex: "qqpartindex",
				partByteOffset: "qqpartbyteoffset",
				chunkSize: "qqchunksize",
				totalFileSize: "qqtotalfilesize",
				totalParts: "qqtotalparts"
			}
		},
		resume: {
			enabled: !1,
			id: null,
			cookiesExpireIn: 7,
			paramNames: {
				resuming: "qqresume"
			}
		},
		formatFileName: function(a){
			return void 0 !== a && a.length > 33 && (a = a.slice(0, 19) + "..." + a.slice(-14)), a;
		},
		text: {
			defaultResponseError: "Upload failure reason unknown",
			sizeSymbols: [
				"kB",
				"MB",
				"GB",
				"TB",
				"PB",
				"EB"
			]
		},
		deleteFile: {
			enabled: !1,
			method: "DELETE",
			endpoint: "/server/upload",
			customHeaders: {},
			params: {}
		},
		cors: {
			expected: !1,
			sendCredentials: !1,
			allowXdr: !1
		},
		blobs: {
			defaultName: "misc_data"
		},
		paste: {
			targetElement: null,
			defaultName: "pasted_image"
		},
		camera: {
			ios: !1
		}
	},
	qq.extend(this._options, a, !0), this._handleCameraAccess(), this._wrapCallbacks(), this._disposeSupport = new qq.DisposeSupport, this._filesInProgress = [
	], this._storedIds = [], this._autoRetries = [], this._retryTimeouts = [
	], this._preventRetries = [
	], this._netUploadedOrQueued = 0, this._netUploaded = 0, this._uploadData = this._createUploadDataTracker(), this._paramsStore = this._createParamsStore("request"), this._deleteFileParamsStore = this._createParamsStore("deleteFile"), this._endpointStore = this._createEndpointStore("request"), this._deleteFileEndpointStore = this._createEndpointStore("deleteFile"), this._handler = this._createUploadHandler(), this._deleteHandler = this._createDeleteHandler(), this._options.button && (this._button = this._createUploadButton(this._options.button)), this._options.paste.targetElement && (this._pasteHandler = this._createPasteHandler()), this._preventLeaveInProgress();
}, qq.FineUploaderBasic.prototype = qq.basePublicApi, qq.extend(qq.FineUploaderBasic.prototype, qq.basePrivateApi), qq.DragAndDrop = function(a){
	"use strict";
	function b(a){
		h.callbacks.dropLog("Grabbed " + a.length + " dropped files."), i.dropDisabled(!1), h.callbacks.processingDroppedFilesComplete(a);
	}
	function c(a){
		var b, d = new qq.Promise;
		return a.isFile ? a.file(function(a){
			j.push(a), d.success();
		}, function(b){
			h.callbacks.dropLog("Problem parsing '" + a.fullPath + "'. FileError code " + b.code + ".", "error"), d.failure();
		}) : a.isDirectory && (b = a.createReader(), b.readEntries(function(a){
			var b = a.length;
			qq.each(a, function(a, e){
				c(e).done(function(){
					b -= 1, 0 === b && d.success();
				});
			}), a.length || d.success();
		}, function(b){
			h.callbacks.dropLog("Problem parsing '" + a.fullPath + "'. FileError code " + b.code + ".", "error"), d.failure();
		})), d;
	}
	function d(a){
		var b = [], d = new qq.Promise;
		return h.callbacks.processingDroppedFiles(), i.dropDisabled(!0), a.files.length > 1 && !h.allowMultipleItems ? (h.callbacks.processingDroppedFilesComplete([
		]), h.callbacks.dropError("tooManyFilesError", ""), i.dropDisabled(!1), d.failure()) : (j = [
		], qq.isFolderDropSupported(a) ? qq.each(a.items, function(a, e){
			var f = e.webkitGetAsEntry();
			f && (f.isFile ? j.push(e.getAsFile()) : b.push(c(f).done(function(){
				b.pop(), 0 === b.length && d.success();
			})));
		}) : j = a.files, 0 === b.length && d.success()), d;
	}
	function e(a){
		i = new qq.UploadDropZone({
			element: a,
			onEnter: function(b){
				qq(a).addClass(h.classes.dropActive), b.stopPropagation();
			},
			onLeaveNotDescendants: function(){
				qq(a).removeClass(h.classes.dropActive);
			},
			onDrop: function(c){
				h.hideDropZonesBeforeEnter && qq(a).hide(), qq(a).removeClass(h.classes.dropActive), d(c.dataTransfer).done(function(){
					b(j);
				});
			}
		}), k.addDisposer(function(){
			i.dispose();
		}), h.hideDropZonesBeforeEnter && qq(a).hide();
	}
	function f(a){
		var b;
		return qq.each(a.dataTransfer.types, function(a, c){
			return "Files" === c ? (b = !0, !1) : void 0;
		}), b;
	}
	function g(){
		var a = h.dropZoneElements;
		qq.each(a, function(a, b){
			e(b);
		}), !a.length || qq.ie() && !qq.ie10() || k.attach(document, "dragenter", function(b){
			!i.dropDisabled() && f(b) && qq.each(a, function(a, b){
				qq(b).css({
					display: "block"
				});
			});
		}), k.attach(document, "dragleave", function(b){
			h.hideDropZonesBeforeEnter && qq.FineUploader.prototype._leaving_document_out(b) && qq.each(a, function(a, b){
				qq(b).hide();
			});
		}), k.attach(document, "drop", function(b){
			h.hideDropZonesBeforeEnter && qq.each(a, function(a, b){
				qq(b).hide();
			}), b.preventDefault();
		});
	}
	var h, i, j = [], k = new qq.DisposeSupport;
	return h = {
		dropZoneElements: [],
		hideDropZonesBeforeEnter: !1,
		allowMultipleItems: !0,
		classes: {
			dropActive: null
		},
		callbacks: new qq.DragAndDrop.callbacks
	},
	qq.extend(h, a, !0), g(), {
		setupExtraDropzone: function(a){
			h.dropZoneElements.push(a), e(a);
		},
		removeDropzone: function(a){
			var b, c = h.dropZoneElements;
			for( b in c )
				if ( c[b] === a )
					return c.splice(b, 1);
		},
		dispose: function(){
			k.dispose(), i.dispose();
		}
	};
}, qq.DragAndDrop.callbacks = function(){
	return {
		processingDroppedFiles: function(){
		},
		processingDroppedFilesComplete: function(){
		},
		dropError: function(a, b){
			qq.log("Drag & drop error code '" + a + " with these specifics: '" + b + "'", "error");
		},
		dropLog: function(a, b){
			qq.log(a, b);
		}
	};
}, qq.UploadDropZone = function(a){
	"use strict";
	function b(){
		return qq.safari() || qq.firefox() && qq.windows();
	}
	function c(){
		j || (b ? k.attach(document, "dragover", function(a){
			a.preventDefault();
		}) : k.attach(document, "dragover", function(a){
			a.dataTransfer && (a.dataTransfer.dropEffect = "none", a.preventDefault());
		}), j = !0);
	}
	function d(a){
		if ( qq.ie() && !qq.ie10() )
			return !1;
		var b, c = a.dataTransfer, d = qq.safari();
		return b = qq.ie10() ? !0 : "none" !== c.effectAllowed, c && b && (c.files || !d && c.types.contains && c.types.contains("Files"));
	}
	function e(a){
		return void 0 !== a && (i = a), i;
	}
	function f(){
		k.attach(h, "dragover", function(a){
			if ( d(a) ){
				var b = qq.ie() ? null : a.dataTransfer.effectAllowed;
				a.dataTransfer.dropEffect = "move" === b || "linkMove" === b ? "move" : "copy", a.stopPropagation(), a.preventDefault();
			}
		}), k.attach(h, "dragenter", function(a){
			if ( !e() ){
				if ( !d(a) )
					return;
				g.onEnter(a);
			}
		}), k.attach(h, "dragleave", function(a){
			if ( d(a) ){
				g.onLeave(a);
				var b = document.elementFromPoint(a.clientX, a.clientY);
				qq(this).contains(b) || g.onLeaveNotDescendants(a);
			}
		}), k.attach(h, "drop", function(a){
			if ( !e() ){
				if ( !d(a) )
					return;
				a.preventDefault(), g.onDrop(a);
			}
		});
	}
	var g, h, i, j, k = new qq.DisposeSupport;
	return g = {
		element: null,
		onEnter: function(){
		},
		onLeave: function(){
		},
		onLeaveNotDescendants: function(){
		},
		onDrop: function(){
		}
	},
	qq.extend(g, a), h = g.element, c(), f(), {
		dropDisabled: function(a){
			return e(a);
		},
		dispose: function(){
			k.dispose();
		}
	};
}, qq.uiPublicApi = {
	clearStoredFiles: function(){
		this._parent.prototype.clearStoredFiles.apply(this, arguments), this._listElement.innerHTML = '';
	},
	addExtraDropzone: function(a){
		this._dnd.setupExtraDropzone(a);
	},
	removeExtraDropzone: function(a){
		return this._dnd.removeDropzone(a);
	},
	getItemByFileId: function(a){
		for( var b = this._listElement.firstChild; b; ){
			if ( b.qqFileId == a )
				return b;
			b = b.nextSibling;
		}
	},
	reset: function(){
		this._parent.prototype.reset.apply(this, arguments), this._element.innerHTML = this._options.template, this._listElement = this._options.listElement || this._find(this._element, "list"), this._options.button || (this._button = this._createUploadButton(this._find(this._element, "button"))), this._dnd.dispose(), this._dnd = this._setupDragAndDrop(), this._totalFilesInBatch = 0, this._filesInBatchAddedToUi = 0;
	}
},
qq.uiPrivateApi = {
	_removeFileItem: function(a){
		var b = this.getItemByFileId(a);
		qq(b).remove();
	},
	_setupDragAndDrop: function(){
		var a, b = this, c = this._find(this._element, "dropProcessing"), d = this._options.dragAndDrop.extraDropzones;
		return a = function(a){
			a.preventDefault();
		}, this._options.dragAndDrop.disableDefaultDropzone || d.push(this._find(this._options.element, "drop")), new qq.DragAndDrop({
			dropZoneElements: d,
			hideDropZonesBeforeEnter: this._options.dragAndDrop.hideDropzones,
			allowMultipleItems: this._options.multiple,
			classes: {
				dropActive: this._options.classes.dropActive
			},
			callbacks: {
				processingDroppedFiles: function(){
					var d = b._button.getInput();
					qq(c).css({
						display: "block"
					}), qq(d).attach("click", a);
				},
				processingDroppedFilesComplete: function(d){
					var e = b._button.getInput();
					qq(c).hide(), qq(e).detach("click", a), d && b.addFiles(d);
				},
				dropError: function(a, c){
					b._itemError(a, c);
				},
				dropLog: function(a, c){
					b.log(a, c);
				}
			}
		});
	},
	_bindDeleteRetryOrCancelClickEvent: function(){
		var a = this;
		return new qq.DeleteRetryOrCancelClickHandler({
			listElement: this._listElement,
			classes: this._classes,
			log: function(b, c){
				a.log(b, c);
			},
			onDeleteFile: function(b){
				a.deleteFile(b);
			},
			onCancel: function(b){
				a.cancel(b);
			},
			onRetry: function(b){
				var c = a.getItemByFileId(b);
				qq(c).removeClass(a._classes.retryable), a.retry(b);
			},
			onGetName: function(b){
				return a.getName(b);
			}
		});
	},
	_isEditFilenameEnabled: function(){
		return this._options.editFilename.enabled && !this._options.autoUpload;
	},
	_filenameEditHandler: function(){
		var a = this;
		return {
			listElement: this._listElement,
			classes: this._classes,
			log: function(b, c){
				a.log(b, c);
			},
			onGetUploadStatus: function(b){
				return a.getUploads({
					id: b
				}).status;
			},
			onGetName: function(b){
				return a.getName(b);
			},
			onSetName: function(b, c){
				var d = a.getItemByFileId(b), e = qq(a._find(d, "file")), f = a._options.formatFileName(c);
				e.setText(f), a.setName(b, c);
			},
			onGetInput: function(b){
				return a._find(b, "editFilenameInput");
			},
			onEditingStatusChange: function(b, c){
				var d = a.getItemByFileId(b), e = qq(a._find(d, "editFilenameInput")), f = qq(a._find(d, "file")), g = qq(a._find(d, "editNameIcon")), h = a._classes.editable;
				c ? (e.addClass("qq-editing"), f.hide(), g.removeClass(h)) : (e.removeClass("qq-editing"), f.css({
					display: ""
				}), g.addClass(h)), qq(d).addClass("qq-temp").removeClass("qq-temp");
			}
		};
	},
	_onUploadStatusChange: function(a, b, c){
		if ( this._isEditFilenameEnabled() ){
			var d, e, f = this.getItemByFileId(a), g = this._classes.editable;
			f && c !== qq.status.SUBMITTED && (d = qq(this._find(f, "file")), e = qq(this._find(f, "editNameIcon")), d.removeClass(g), e.removeClass(g));
		}
	},
	_bindFilenameInputFocusInEvent: function(){
		var a = qq.extend({}, this._filenameEditHandler());
		return new qq.FilenameInputFocusInHandler(a);
	},
	_bindFilenameInputFocusEvent: function(){
		var a = qq.extend({}, this._filenameEditHandler());
		return new qq.FilenameInputFocusHandler(a);
	},
	_bindFilenameClickEvent: function(){
		var a = qq.extend({}, this._filenameEditHandler());
		return new qq.FilenameClickHandler(a);
	},
	_leaving_document_out: function(a){
		return (qq.chrome() || qq.safari() && qq.windows()) && 0 === a.clientX && 0 === a.clientY || qq.firefox() && !a.relatedTarget;
	},
	_storeForLater: function(a){
		this._parent.prototype._storeForLater.apply(this, arguments);
		var b = this.getItemByFileId(a);
		qq(this._find(b, "spinner")).hide();
	},
	_find: function(a, b){
		var c = qq(a).getByClass(this._options.classes[b])[0];
		if ( !c )
			throw new Error("element not found " + b);
		return c;
	},
	_onSubmit: function(a, b){
		this._parent.prototype._onSubmit.apply(this, arguments), this._addToList(a, b);
	},
	_onSubmitted: function(a){
		if ( this._isEditFilenameEnabled() ){
			var b = this.getItemByFileId(a), c = qq(this._find(b, "file")), d = qq(this._find(b, "editNameIcon")), e = this._classes.editable;
			c.addClass(e), d.addClass(e), this._focusinEventSupported || this._filenameInputFocusHandler.addHandler(this._find(b, "editFilenameInput"));
		}
	},
	_onProgress: function(a, b, c, d){
		this._parent.prototype._onProgress.apply(this, arguments);
		var e, f, g, h;
		e = this.getItemByFileId(a), f = this._find(e, "progressBar"), g = Math.round(100 * (c / d)), c === d ? (h = this._find(e, "cancel"), qq(h).hide(), qq(f).hide(), qq(this._find(e, "statusText")).setText(this._options.text.waitingForResponse), this._displayFileSize(a)) : (this._displayFileSize(a, c, d), qq(f).css({
			display: "block"
		})), qq(f).css({
			width: g + "%"
		});
	},
	_onComplete: function(a, b, c){
		function d(b){
			var c = f.getItemByFileId(a);
			qq(f._find(c, "statusText")).clearText(), qq(c).removeClass(f._classes.retrying), qq(f._find(c, "progressBar")).hide(), (!f._options.disableCancelForFormUploads || qq.supportedFeatures.ajaxUploading) && qq(f._find(c, "cancel")).hide(), qq(f._find(c, "spinner")).hide(), b.success ? (f._isDeletePossible() && f._showDeleteLink(a), qq(c).addClass(f._classes.success), f._classes.successIcon && (f._find(c, "finished").style.display = "inline-block", qq(c).addClass(f._classes.successIcon))) : (qq(c).addClass(f._classes.fail), f._classes.failIcon && (f._find(c, "finished").style.display = "inline-block", qq(c).addClass(f._classes.failIcon)), f._options.retry.showButton && !f._preventRetries[a] && qq(c).addClass(f._classes.retryable), f._controlFailureTextDisplay(c, b));
		}
		var e = this._parent.prototype._onComplete.apply(this, arguments), f = this;
		return qq.isPromise(e) ? e.done(function(a){
			d(a);
		}) : d(c), e;
	},
	_onUpload: function(a){
		var b = this._parent.prototype._onUpload.apply(this, arguments);
		return this._showSpinner(a), b;
	},
	_onCancel: function(a){
		this._parent.prototype._onCancel.apply(this, arguments), this._removeFileItem(a);
	},
	_onBeforeAutoRetry: function(a){
		var b, c, d, e, f, g;
		this._parent.prototype._onBeforeAutoRetry.apply(this, arguments), b = this.getItemByFileId(a), c = this._find(b, "progressBar"), this._showCancelLink(b), c.style.width = 0, qq(c).hide(), this._options.retry.showAutoRetryNote && (d = this._find(b, "statusText"), e = this._autoRetries[a] + 1, f = this._options.retry.maxAutoAttempts, g = this._options.retry.autoRetryNote.replace(/\{retryNum\}/g, e), g = g.replace(/\{maxAuto\}/g, f), qq(d).setText(g), 1 === e && qq(b).addClass(this._classes.retrying));
	},
	_onBeforeManualRetry: function(a){
		var b = this.getItemByFileId(a);
		return this._parent.prototype._onBeforeManualRetry.apply(this, arguments) ? (this._find(b, "progressBar").style.width = 0, qq(b).removeClass(this._classes.fail), qq(this._find(b, "statusText")).clearText(), this._showSpinner(a), this._showCancelLink(b), !0) : (qq(b).addClass(this._classes.retryable), !1);
	},
	_onSubmitDelete: function(a){
		var b = qq.bind(this._onSubmitDeleteSuccess, this);
		this._parent.prototype._onSubmitDelete.call(this, a, b);
	},
	_onSubmitDeleteSuccess: function(){
		this._options.deleteFile.forceConfirm ? this._showDeleteConfirm.apply(this, arguments) : this._sendDeleteRequest.apply(this, arguments);
	},
	_onDeleteComplete: function(a, b, c){
		this._parent.prototype._onDeleteComplete.apply(this, arguments);
		var d = this.getItemByFileId(a), e = this._find(d, "spinner"), f = this._find(d, "statusText");
		qq(e).hide(), c ? (qq(f).setText(this._options.deleteFile.deletingFailedText), this._showDeleteLink(a)) : this._removeFileItem(a);
	},
	_sendDeleteRequest: function(a){
		var b = this.getItemByFileId(a), c = this._find(b, "deleteButton"), d = this._find(b, "statusText");
		qq(c).hide(), this._showSpinner(a), qq(d).setText(this._options.deleteFile.deletingStatusText), this._deleteHandler.sendDelete.apply(this, arguments);
	},
	_showDeleteConfirm: function(a){
		var b = this._handler.getName(a), c = this._options.deleteFile.confirmMessage.replace(/\{filename\}/g, b), d = (this.getUuid(a), arguments), e = this;
		this._options.showConfirm(c, function(){
			e._sendDeleteRequest.apply(e, d);
		});
	},
	_addToList: function(a, b){
		var c = qq.toElement(this._options.fileTemplate);
		if ( this._options.disableCancelForFormUploads && !qq.supportedFeatures.ajaxUploading ){
			var d = this._find(c, "cancel");
			qq(d).remove();
		}
		c.qqFileId = a;
		var e = this._find(c, "file");
		qq(e).setText(this._options.formatFileName(b)), qq(this._find(c, "size")).hide(), this._options.multiple || (this._handler.cancelAll(), this._clearList()), this._options.display.prependFiles ? this._prependItem(c) : this._listElement.appendChild(c), this._filesInBatchAddedToUi += 1, this._options.display.fileSizeOnSubmit && qq.supportedFeatures.ajaxUploading && this._displayFileSize(a);
	},
	_prependItem: function(a){
		var b = this._listElement, c = b.firstChild;
		this._totalFilesInBatch > 1 && this._filesInBatchAddedToUi > 0 && (c = qq(b).children()[this._filesInBatchAddedToUi - 1].nextSibling), b.insertBefore(a, c);
	},
	_clearList: function(){
		this._listElement.innerHTML = "", this.clearStoredFiles();
	},
	_displayFileSize: function(a, b, c){
		var d = this.getItemByFileId(a), e = this.getSize(a), f = this._formatSize(e), g = this._find(d, "size");
		void 0 !== b && void 0 !== c && (f = this._formatProgress(b, c)), qq(g).css({
			display: "inline"
		}), qq(g).setText(f);
	},
	_formatProgress: function(a, b){
		function c(a, b){
			d = d.replace(a, b);
		}
		var d = this._options.text.formatProgress;
		return c("{percent}", Math.round(100 * (a / b))), c("{total_size}", this._formatSize(b)), d;
	},
	_controlFailureTextDisplay: function(a, b){
		var c, d, e, f, g;
		c = this._options.failedUploadTextDisplay.mode, d = this._options.failedUploadTextDisplay.maxChars, e = this._options.failedUploadTextDisplay.responseProperty, "custom" === c ? (f = b[e], f ? f.length > d && (g = f.substring(0, d) + "...") : (f = this._options.text.failUpload, this.log("'" + e + "' is not a valid property on the server response.", "warn")), qq(this._find(a, "statusText")).setText(g || f), this._options.failedUploadTextDisplay.enableTooltip && this._showTooltip(a, f)) : "default" === c ? qq(this._find(a, "statusText")).setText(this._options.text.failUpload) : "none" !== c && this.log("failedUploadTextDisplay.mode value of '" + c + "' is not valid", "warn");
	},
	_showTooltip: function(a, b){
		a.title = b;
	},
	_showSpinner: function(a){
		var b = this.getItemByFileId(a), c = this._find(b, "spinner");
		c.style.display = "inline-block";
	},
	_showCancelLink: function(a){
		if ( !this._options.disableCancelForFormUploads || qq.supportedFeatures.ajaxUploading ){
			var b = this._find(a, "cancel");
			qq(b).css({
				display: "inline"
			});
		}
	},
	_showDeleteLink: function(a){
		var b = this.getItemByFileId(a), c = this._find(b, "deleteButton");
		qq(c).css({
			display: "inline"
		});
	},
	_itemError: function(){
		var a = this._parent.prototype._itemError.apply(this, arguments);
		this._options.showMessage(a);
	},
	_batchError: function(a){
		this._parent.prototype._batchError.apply(this, arguments), this._options.showMessage(a);
	},
	_setupPastePrompt: function(){
		var a = this;
		this._options.callbacks.onPasteReceived = function(){
			var b = a._options.paste.namePromptMessage, c = a._options.paste.defaultName;
			return a._options.showPrompt(b, c);
		};
	},
	_fileOrBlobRejected: function(){
		this._totalFilesInBatch -= 1, this._parent.prototype._fileOrBlobRejected.apply(this, arguments);
	},
	_prepareItemsForUpload: function(a){
		this._totalFilesInBatch = a.length, this._filesInBatchAddedToUi = 0, this._parent.prototype._prepareItemsForUpload.apply(this, arguments);
	}
},
qq.FineUploader = function(a, b){
	this._parent = b ? qq[b].FineUploaderBasic : qq.FineUploaderBasic, this._parent.apply(this, arguments), qq.extend(this._options, {
		element: null,
		listElement: null,
		dragAndDrop: {
			extraDropzones: [],
			hideDropzones: !0,
			disableDefaultDropzone: !1
		},
		text: {
			uploadButton: "Upload a file",
			cancelButton: "Cancel",
			retryButton: "Retry",
			deleteButton: "Delete",
			failUpload: "Upload failed",
			dragZone: "Drop files here to upload",
			dropProcessing: "Processing dropped files...",
			formatProgress: "{percent}% of {total_size}",
			waitingForResponse: "Processing..."
		},
		template: '<div class="qq-uploader">' + (this._options.dragAndDrop && this._options.dragAndDrop.disableDefaultDropzone ? "" : '<div class="qq-upload-drop-area"><span>{dragZoneText}</span></div>') + (this._options.button ? "" : '<div class="qq-upload-button"><div>{uploadButtonText}</div></div>') + '<span class="qq-drop-processing"><span>{dropProcessingText}</span><span class="qq-drop-processing-spinner"></span></span>' + (this._options.listElement ? "" : '<ul class="qq-upload-list"></ul>') + "</div>",
		fileTemplate: '<li><div class="qq-progress-bar"></div><span class="qq-upload-spinner"></span><span class="qq-upload-finished"></span>' + (this._options.editFilename && this._options.editFilename.enabled ? '<span class="qq-edit-filename-icon"></span>' : "") + '<span class="qq-upload-file"></span>' + (this._options.editFilename && this._options.editFilename.enabled ? '<input class="qq-edit-filename" tabindex="0" type="text">' : "") + '<span class="qq-upload-size"></span>' + '<a class="qq-upload-cancel" href="#">{cancelButtonText}</a>' + '<a class="qq-upload-retry" href="#">{retryButtonText}</a>' + '<a class="qq-upload-delete" href="#">{deleteButtonText}</a>' + '<span class="qq-upload-status-text">{statusText}</span>' + "</li>",
		classes: {
			button: "qq-upload-button",
			drop: "qq-upload-drop-area",
			dropActive: "qq-upload-drop-area-active",
			list: "qq-upload-list",
			progressBar: "qq-progress-bar",
			file: "qq-upload-file",
			spinner: "qq-upload-spinner",
			finished: "qq-upload-finished",
			retrying: "qq-upload-retrying",
			retryable: "qq-upload-retryable",
			size: "qq-upload-size",
			cancel: "qq-upload-cancel",
			deleteButton: "qq-upload-delete",
			retry: "qq-upload-retry",
			statusText: "qq-upload-status-text",
			editFilenameInput: "qq-edit-filename",
			success: "qq-upload-success",
			fail: "qq-upload-fail",
			successIcon: null,
			failIcon: null,
			editNameIcon: "qq-edit-filename-icon",
			editable: "qq-editable",
			dropProcessing: "qq-drop-processing",
			dropProcessingSpinner: "qq-drop-processing-spinner"
		},
		failedUploadTextDisplay: {
			mode: "default",
			maxChars: 50,
			responseProperty: "error",
			enableTooltip: !0
		},
		messages: {
			tooManyFilesError: "You may only drop one file",
			unsupportedBrowser: "Unrecoverable error - this browser does not permit file uploading of any kind."
		},
		retry: {
			showAutoRetryNote: !0,
			autoRetryNote: "Retrying {retryNum}/{maxAuto}...",
			showButton: !1
		},
		deleteFile: {
			forceConfirm: !1,
			confirmMessage: "Are you sure you want to delete {filename}?",
			deletingStatusText: "Deleting...",
			deletingFailedText: "Delete failed"
		},
		display: {
			fileSizeOnSubmit: !1,
			prependFiles: !1
		},
		paste: {
			promptForName: !1,
			namePromptMessage: "Please name this image"
		},
		editFilename: {
			enabled: !1
		},
		showMessage: function(a){
			setTimeout(function(){
				window.alert(a);
			}, 0);
		},
		showConfirm: function(a, b, c){
			setTimeout(function(){
				var d = window.confirm(a);
				d ? b() : c && c();
			}, 0);
		},
		showPrompt: function(a, b){
			var c = new qq.Promise, d = window.prompt(a, b);
			return null != d && qq.trimStr(d).length > 0 ? c.success(d) : c.failure("Undefined or invalid user-supplied value."), c;
		}
	},
	!0), qq.extend(this._options, a, !0), !qq.supportedFeatures.uploading || this._options.cors.expected && !qq.supportedFeatures.uploadCors ? this._options.element.innerHTML = "<div>" + this._options.messages.unsupportedBrowser + "</div>" : (this._wrapCallbacks(), this._options.template = this._options.template.replace(/\{dragZoneText\}/g, this._options.text.dragZone), this._options.template = this._options.template.replace(/\{uploadButtonText\}/g, this._options.text.uploadButton), this._options.template = this._options.template.replace(/\{dropProcessingText\}/g, this._options.text.dropProcessing), this._options.fileTemplate = this._options.fileTemplate.replace(/\{cancelButtonText\}/g, this._options.text.cancelButton), this._options.fileTemplate = this._options.fileTemplate.replace(/\{retryButtonText\}/g, this._options.text.retryButton), this._options.fileTemplate = this._options.fileTemplate.replace(/\{deleteButtonText\}/g, this._options.text.deleteButton), this._options.fileTemplate = this._options.fileTemplate.replace(/\{statusText\}/g, ""), this._element = this._options.element, this._element.innerHTML = this._options.template, this._listElement = this._options.listElement || this._find(this._element, "list"), this._classes = this._options.classes, this._button || (this._button = this._createUploadButton(this._find(this._element, "button"))), this._deleteRetryOrCancelClickHandler = this._bindDeleteRetryOrCancelClickEvent(), this._focusinEventSupported = !qq.firefox(), this._isEditFilenameEnabled() && (this._filenameClickHandler = this._bindFilenameClickEvent(), this._filenameInputFocusInHandler = this._bindFilenameInputFocusInEvent(), this._filenameInputFocusHandler = this._bindFilenameInputFocusEvent()), this._dnd = this._setupDragAndDrop(), this._options.paste.targetElement && this._options.paste.promptForName && this._setupPastePrompt(), this._totalFilesInBatch = 0, this._filesInBatchAddedToUi = 0);
}, qq.extend(qq.FineUploader.prototype, qq.basePublicApi), qq.extend(qq.FineUploader.prototype, qq.basePrivateApi), qq.extend(qq.FineUploader.prototype, qq.uiPublicApi), qq.extend(qq.FineUploader.prototype, qq.uiPrivateApi), qq.AjaxRequestor = function(a){
	"use strict";
	function b(){
		return w.method.toUpperCase();
	}
	function c(){
		return qq.indexOf([
			"GET",
			"POST",
			"HEAD"
		], b()) >= 0;
	}
	function d(){
		var a = !1;
		return qq.each(a, function(b, c){
			return qq.indexOf([
				"Accept",
				"Accept-Language",
				"Content-Language",
				"Content-Type"
			], c) < 0 ? (a = !0, !1) : void 0;
		}), a;
	}
	function e(a){
		return w.cors.expected && void 0 === a.withCredentials;
	}
	function f(){
		var a;
		return window.XMLHttpRequest && (a = new XMLHttpRequest, void 0 === a.withCredentials && (a = new XDomainRequest)), a;
	}
	function g(a, b){
		var c = v[a].xhr;
		return c || b || (c = w.cors.expected ? f() : new XMLHttpRequest, v[a].xhr = c), c;
	}
	function h(a){
		var b, c = qq.indexOf(u, a), d = w.maxConnections;
		delete v[a], u.splice(c, 1), u.length >= d && d > c && (b = u[d - 1], k(b));
	}
	function i(a, c){
		var d = g(a), f = b(), i = c === !0;
		h(a), i ? s(f + " request for " + a + " has failed", "error") : e(d) || r(d.status) || (i = !0, s(f + " request for " + a + " has failed - response code " + d.status, "error")), w.onComplete(a, d, i);
	}
	function j(a){
		var b, c = v[a].onDemandParams, d = w.mandatedParams;
		return w.paramsStore.getParams && (b = w.paramsStore.getParams(a)), c && qq.each(c, function(a, c){
			b = b || {}, b[a] = c;
		}), d && qq.each(d, function(a, c){
			b = b || {}, b[a] = c;
		}), b;
	}
	function k(a){
		var c, d = g(a), f = b(), h = j(a), i = v[a].body;
		w.onSend(a), c = l(a, h), e(d) ? (d.onload = n(a), d.onerror = o(a)) : d.onreadystatechange = m(a), d.open(f, c, !0), w.cors.expected && w.cors.sendCredentials && !e(d) && (d.withCredentials = !0), p(a), s("Sending " + f + " request for " + a), i ? d.send(i) : t || !h ? d.send() : h && w.contentType.toLowerCase().indexOf("application/x-www-form-urlencoded") >= 0 ? d.send(qq.obj2url(h, "")) : h && w.contentType.toLowerCase().indexOf("application/json") >= 0 ? d.send(JSON.stringify(h)) : d.send(h);
	}
	function l(a, b){
		var c = w.endpointStore.getEndpoint(a), d = v[a].addToPath;
		return void 0 !== d && (c += "/" + d), t && b ? qq.obj2url(b, c) : c;
	}
	function m(a){
		return function(){
			4 === g(a).readyState && i(a);
		};
	}
	function n(a){
		return function(){
			i(a);
		};
	}
	function o(a){
		return function(){
			i(a, !0);
		};
	}
	function p(a){
		var f = g(a), h = w.customHeaders, i = v[a].additionalHeaders || {}, j = b(), k = {};
		e(f) && (w.cors.expected && c() && !d(h) || (f.setRequestHeader("X-Requested-With", "XMLHttpRequest"), f.setRequestHeader("Cache-Control", "no-cache"))), !w.contentType || "POST" !== j && "PUT" !== j || e(f) || f.setRequestHeader("Content-Type", w.contentType), e(f) || (qq.extend(k, h), qq.extend(k, i), qq.each(k, function(a, b){
			f.setRequestHeader(a, b);
		}));
	}
	function q(a){
		var c = g(a, !0), d = b();
		return c ? (e(c) ? (c.onerror = null, c.onload = null) : c.onreadystatechange = null, c.abort(), h(a), s("Cancelled " + d + " for " + a), w.onCancel(a), !0) : !1;
	}
	function r(a){
		return qq.indexOf(w.successfulResponseCodes[b()], a) >= 0;
	}
	var s, t, u = [], v = [], w = {
		validMethods: [
			"POST"
		],
		method: "POST",
		contentType: "application/x-www-form-urlencoded",
		maxConnections: 3,
		customHeaders: {},
		endpointStore: {},
		paramsStore: {},
		mandatedParams: {},
		successfulResponseCodes: {
			DELETE: [
				200,
				202,
				204
			],
			POST: [
				200,
				204
			]
		},
		cors: {
			expected: !1,
			sendCredentials: !1
		},
		log: function(){
		},
		onSend: function(){
		},
		onComplete: function(){
		},
		onCancel: function(){
		}
	};
	if ( qq.extend(w, a), s = w.log, qq.indexOf(w.validMethods, b()) < 0 )
		throw new Error("'" + b() + "' is not a supported method for this type of request!");
	return t = "GET" === b() || "DELETE" === b(), {
		send: function(a, b, c, d, e){
			v[a] = {
				addToPath: b,
				onDemandParams: c,
				additionalHeaders: d,
				body: e
			};
			var f = u.push(a);
			f <= w.maxConnections && k(a);
		},
		cancel: function(a){
			return q(a);
		},
		getMethod: function(){
			return b();
		}
	};
}, qq.DeleteFileAjaxRequestor = function(a){
	"use strict";
	function b(){
		return "POST" === d.method.toUpperCase() ? {
			_method: "DELETE"
		} : {};
	}
	var c, d = {
		method: "DELETE",
		uuidParamName: "qquuid",
		endpointStore: {},
		maxConnections: 3,
		customHeaders: {},
		paramsStore: {},
		demoMode: !1,
		cors: {
			expected: !1,
			sendCredentials: !1
		},
		log: function(){
		},
		onDelete: function(){
		},
		onDeleteComplete: function(){
		}
	};
	return qq.extend(d, a), c = new qq.AjaxRequestor({
		validMethods: [
			"POST",
			"DELETE"
		],
		method: d.method,
		endpointStore: d.endpointStore,
		paramsStore: d.paramsStore,
		mandatedParams: b(),
		maxConnections: d.maxConnections,
		customHeaders: d.customHeaders,
		demoMode: d.demoMode,
		log: d.log,
		onSend: d.onDelete,
		onComplete: d.onDeleteComplete,
		cors: d.cors
	}), {
		sendDelete: function(a, b, e){
			var f = e || {};
			d.log("Submitting delete file request for " + a), "DELETE" === c.getMethod() ? c.send(a, b, f) : (f[d.uuidParamName] = b, c.send(a, null, f));
		}
	};
}, qq.WindowReceiveMessage = function(a){
	var b = {
		log: function(){
		}
	}, c = {};
	return qq.extend(b, a), {
		receiveMessage: function(a, b){
			var d = function(a){
				b(a.data);
			};
			window.postMessage ? c[a] = qq(window).attach("message", d) : log("iframe message passing not supported in this browser!", "error");
		},
		stopReceivingMessages: function(a){
			if ( window.postMessage ){
				var b = c[a];
				b && b();
			}
		}
	};
}, qq.UploadHandler = function(a, b){
	"use strict";
	function c(a){
		var b, c = qq.indexOf(j, a), d = f.maxConnections;
		c >= 0 && (j.splice(c, 1), j.length >= d && d > c && (b = j[d - 1], h.upload(b)));
	}
	function d(a){
		g("Cancelling " + a), f.paramsStore.remove(a), c(a);
	}
	function e(){
		var a = b ? qq[b] : qq, d = qq.supportedFeatures.ajaxUploading ? "Xhr" : "Form";
		h = new a["UploadHandler" + d](f, c, f.onUuidChanged, g);
	}
	var f, g, h, i, j = [];
	return f = {
		debug: !1,
		forceMultipart: !0,
		paramsInBody: !1,
		paramsStore: {},
		endpointStore: {},
		filenameParam: "qqfilename",
		cors: {
			expected: !1,
			sendCredentials: !1
		},
		maxConnections: 3,
		uuidParam: "qquuid",
		totalFileSizeParam: "qqtotalfilesize",
		chunking: {
			enabled: !1,
			partSize: 2e6,
			paramNames: {
				partIndex: "qqpartindex",
				partByteOffset: "qqpartbyteoffset",
				chunkSize: "qqchunksize",
				totalParts: "qqtotalparts",
				filename: "qqfilename"
			}
		},
		resume: {
			enabled: !1,
			id: null,
			cookiesExpireIn: 7,
			paramNames: {
				resuming: "qqresume"
			}
		},
		log: function(){
		},
		onProgress: function(){
		},
		onComplete: function(){
		},
		onCancel: function(){
		},
		onUpload: function(){
		},
		onUploadChunk: function(){
		},
		onAutoRetry: function(){
		},
		onResume: function(){
		},
		onUuidChanged: function(){
		}
	},
	qq.extend(f, a), g = f.log, i = {
		add: function(a){
			return h.add(a);
		},
		upload: function(a){
			var b = j.push(a);
			return b <= f.maxConnections ? (h.upload(a), !0) : !1;
		},
		retry: function(a){
			var b = qq.indexOf(j, a);
			return b >= 0 ? h.upload(a, !0) : this.upload(a);
		},
		cancel: function(a){
			var b = h.cancel(a);
			qq.isPromise(b) ? b.then(function(){
				d(a);
			}) : b !== !1 && d(a);
		},
		cancelAll: function(){
			var a = this, b = [];
			qq.extend(b, j), qq.each(b, function(b, c){
				a.cancel(c);
			}), j = [];
		},
		getName: function(a){
			return h.getName(a);
		},
		setName: function(a, b){
			h.setName(a, b);
		},
		getSize: function(a){
			return h.getSize ? h.getSize(a) : void 0;
		},
		getFile: function(a){
			return h.getFile ? h.getFile(a) : void 0;
		},
		reset: function(){
			g("Resetting upload handler"), i.cancelAll(), j = [], h.reset();
		},
		expunge: function(a){
			return h.expunge(a);
		},
		getUuid: function(a){
			return h.getUuid(a);
		},
		setUuid: function(a, b){
			return h.setUuid(a, b);
		},
		isValid: function(a){
			return h.isValid(a);
		},
		getResumableFilesData: function(){
			return h.getResumableFilesData ? h.getResumableFilesData() : [];
		},
		getThirdPartyFileId: function(a){
			return h.getThirdPartyFileId && i.isValid(a) ? h.getThirdPartyFileId(a) : void 0;
		}
	},
	e(), i;
}, qq.UploadHandlerXhrApi = function(a, b, c, d, e, f, g){
	"use strict";
	function h(a, b, c){
		return a.slice ? a.slice(b, c) : a.mozSlice ? a.mozSlice(b, c) : a.webkitSlice ? a.webkitSlice(b, c) : void 0;
	}
	var i;
	return qq.extend(a, {
		createXhr: function(a){
			var c = new XMLHttpRequest;
			return b[a].xhr = c, c;
		},
		getTotalChunks: function(a){
			if ( c ){
				var b = i.getSize(a), d = c.partSize;
				return Math.ceil(b / d);
			}
		},
		getChunkData: function(b, d){
			var e = c.partSize, f = i.getSize(b), g = i.getFile(b), j = e * d, k = j + e >= f ? f : j + e, l = a.getTotalChunks(b);
			return {
				part: d,
				start: j,
				end: k,
				count: l,
				blob: h(g, j, k),
				size: k - j
			};
		},
		getChunkDataForCallback: function(a){
			return {
				partIndex: a.part,
				startByte: a.start + 1,
				endByte: a.end,
				totalParts: a.count
			};
		}
	}), i = {
		add: function(a){
			var c, d = qq.getUniqueId();
			if ( qq.isFile(a) )
				c = b.push({
					file: a
				}) - 1;
			else{
				if ( !qq.isBlob(a.blob) )
					throw new Error("Passed obj in not a File or BlobData (in qq.UploadHandlerXhr)");
				c = b.push({
					blobData: a
				}) - 1;
			}
			return b[c].uuid = d, c;
		},
		getName: function(a){
			if ( i.isValid(a) ){
				var c = b[a].file, d = b[a].blobData, e = b[a].newName;
				return void 0 !== e ? e : c ? null != c.fileName && void 0 !== c.fileName ? c.fileName : c.name : d.name;
			}
			g(a + " is not a valid item ID.", "error");
		},
		setName: function(a, c){
			b[a].newName = c;
		},
		getSize: function(a){
			var c = b[a].file || b[a].blobData.blob;
			return qq.isFileOrInput(c) ? null != c.fileSize ? c.fileSize : c.size : c.size;
		},
		getFile: function(a){
			return b[a] ? b[a].file || b[a].blobData.blob : void 0;
		},
		isValid: function(a){
			return void 0 !== b[a];
		},
		reset: function(){
			b.length = 0;
		},
		expunge: function(a){
			var c = b[a].xhr;
			c && (c.onreadystatechange = null, c.abort()), delete b[a];
		},
		getUuid: function(a){
			return b[a].uuid;
		},
		upload: function(a, b){
			return d(a, b);
		},
		cancel: function(a){
			var b = e(a, i.getName(a));
			return qq.isPromise(b) ? b.then(function(){
				i.expunge(a);
			}) : b !== !1 ? (i.expunge(a), !0) : !1;
		},
		setUuid: function(a, c){
			g("Server requested UUID change from '" + b[a].uuid + "' to '" + c + "'"), b[a].uuid = c, f(a, c);
		}
	};
}, qq.UploadHandlerFormApi = function(a, b, c, d, e, f, g){
	"use strict";
	function h(d){
		delete p[d], delete b[d], c && (clearTimeout(q[d]), delete q[d], n.stopReceivingMessages(d));
		var e = document.getElementById(a.getIframeName(d));
		e && (e.setAttribute("src", "java" + String.fromCharCode(115) + "cript:false;"), qq(e).remove());
	}
	function i(c, d){
		var e = c.id, f = k(e), h = b[f].uuid;
		o[h] = d, p[f] = qq(c).attach("load", function(){
			b[f].input && (g("Received iframe load event for CORS upload request (iframe name " + e + ")"), q[e] = setTimeout(function(){
				var a = "No valid message received from loaded iframe for iframe name " + e;
				g(a, "error"), d({
					error: a
				});
			}, 1e3));
		}), n.receiveMessage(e, function(b){
			g("Received the following window message: '" + b + "'");
			var c, d = k(e), f = a.parseJsonResponse(d, b), h = f.uuid;
			h && o[h] ? (g("Handling response for iframe name " + e), clearTimeout(q[e]), delete q[e], a.detachLoadEvent(e), c = o[h], delete o[h], n.stopReceivingMessages(e), c(f)) : h || g("'" + b + "' does not contain a UUID - ignoring.");
		});
	}
	function j(a){
		var b = qq.toElement('<iframe src="javascript:false;" name="' + a + '"/>');
		return b.setAttribute('id', a), b.style.display = "none", document.body.appendChild(b), b;
	}
	function k(a){
		return a.split('_')[0];
	}
	var l, m = qq.getUniqueId(), n = new qq.WindowReceiveMessage({
		log: g
	}), o = {}, p = {}, q = {};
	return qq.extend(a, {
		getIframeName: function(a){
			return a + '_' + m;
		},
		createIframe: function(b){
			var c = a.getIframeName(b);
			return j(c);
		},
		parseJsonResponse: function(a, b){
			var c;
			try{
				c = qq.parseJson(b), void 0 !== c.newUuid && l.setUuid(a, c.newUuid);
			}catch( d ){
				g("Error when attempting to parse iframe upload response (" + d.message + ")", "error"), c = {};
			}
			return c;
		},
		initFormForUpload: function(a){
			var b = a.method, c = a.endpoint, d = a.params, e = a.paramsInBody, f = a.targetName, g = qq.toElement('<form method="' + b + '" enctype="multipart/form-data"></form>'), h = c;
			return e ? qq.obj2Inputs(d, g) : h = qq.obj2url(d, c), g.setAttribute("action", h), g.setAttribute("target", f), g.style.display = "none", document.body.appendChild(g), g;
		},
		attachLoadEvent: function(a, b){
			var d;
			c ? i(a, b) : p[a.id] = qq(a).attach("load", function(){
				if ( g("Received response for " + a.id), a.parentNode ){
					try{
						if ( a.contentDocument && a.contentDocument.body && "false" == a.contentDocument.body.innerHTML )
							return;
					}catch( c ){
						g("Error when attempting to access iframe during handling of upload response (" + c.message + ")", "error"), d = {
							success: !1
						};
					}
					b(d);
				}
			});
		},
		detachLoadEvent: function(a){
			void 0 !== p[a] && (p[a](), delete p[a]);
		}
	}), l = {
		add: function(a){
			var c = b.push({
				input: a
			}) - 1;
			return a.setAttribute("name", d), b[c].uuid = qq.getUniqueId(), a.parentNode && qq(a).remove(), c;
		},
		getName: function(a){
			return void 0 !== b[a].newName ? b[a].newName : l.isValid(a) ? b[a].input.value.replace(/.*(\/|\\)/, "") : (g(a + " is not a valid item ID.", "error"), void 0);
		},
		setName: function(a, c){
			b[a].newName = c;
		},
		isValid: function(a){
			return void 0 !== b[a] && void 0 !== b[a].input;
		},
		reset: function(){
			b.length = 0;
		},
		expunge: function(a){
			return h(a);
		},
		getUuid: function(a){
			return b[a].uuid;
		},
		cancel: function(a){
			var b = e(a, l.getName(a));
			return qq.isPromise(b) ? b.then(function(){
				l.expunge(a);
			}) : b !== !1 ? (l.expunge(a), !0) : !1;
		},
		upload: function(){
		},
		setUuid: function(a, c){
			g("Server requested UUID change from '" + b[a].uuid + "' to '" + c + "'"), b[a].uuid = c, f(a, c);
		}
	};
}, qq.UiEventHandler = function(a, b){
	"use strict";
	function c(a){
		d.attach(a, e.eventType, function(a){
			a = a || window.event;
			var b = a.target || a.srcElement;
			e.onHandled(b, a);
		});
	}
	var d = new qq.DisposeSupport, e = {
		eventType: "click",
		attachTo: null,
		onHandled: function(){
		}
	}, f = {
		addHandler: function(a){
			c(a);
		},
		dispose: function(){
			d.dispose();
		}
	};
	return qq.extend(b, {
		getItemFromEventTarget: function(a){
			for( var b = a.parentNode; void 0 === b.qqFileId; )
				b = b.parentNode;
			return b;
		},
		getFileIdFromItem: function(a){
			return a.qqFileId;
		},
		getDisposeSupport: function(){
			return d;
		}
	}), qq.extend(e, a), e.attachTo && c(e.attachTo), f;
}, qq.DeleteRetryOrCancelClickHandler = function(a){
	"use strict";
	function b(a, b){
		if ( qq(a).hasClass(e.classes.cancel) || qq(a).hasClass(e.classes.retry) || qq(a).hasClass(e.classes.deleteButton) ){
			var f = d.getItemFromEventTarget(a), g = d.getFileIdFromItem(f);
			qq.preventDefault(b), e.log(qq.format("Detected valid cancel, retry, or delete click event on file '{}', ID: {}.", e.onGetName(g), g)), c(a, g);
		}
	}
	function c(a, b){
		qq(a).hasClass(e.classes.deleteButton) ? e.onDeleteFile(b) : qq(a).hasClass(e.classes.cancel) ? e.onCancel(b) : e.onRetry(b);
	}
	var d = {}, e = {
		listElement: document,
		log: function(){
		},
		classes: {
			cancel: "qq-upload-cancel",
			deleteButton: "qq-upload-delete",
			retry: "qq-upload-retry"
		},
		onDeleteFile: function(){
		},
		onCancel: function(){
		},
		onRetry: function(){
		},
		onGetName: function(){
		}
	};
	qq.extend(e, a), e.eventType = "click", e.onHandled = b, e.attachTo = e.listElement, qq.extend(this, new qq.UiEventHandler(e, d));
}, qq.FilenameEditHandler = function(a, b){
	"use strict";
	function c(a){
		var b = i.onGetName(a), c = b.lastIndexOf(".");
		return c > 0 && (b = b.substr(0, c)), b;
	}
	function d(a){
		var b = i.onGetName(a);
		return qq.getExtension(b);
	}
	function e(a, b){
		var c, e = a.value;
		void 0 !== e && qq.trimStr(e).length > 0 && (c = d(b), void 0 !== c && (e = e + "." + c), i.onSetName(b, e)), i.onEditingStatusChange(b, !1);
	}
	function f(a, c){
		b.getDisposeSupport().attach(a, "blur", function(){
			e(a, c);
		});
	}
	function g(a, c){
		b.getDisposeSupport().attach(a, "keyup", function(b){
			var d = b.keyCode || b.which;
			13 === d && e(a, c);
		});
	}
	var h, i = {
		listElement: null,
		log: function(){
		},
		classes: {
			file: "qq-upload-file"
		},
		onGetUploadStatus: function(){
		},
		onGetName: function(){
		},
		onSetName: function(){
		},
		onGetInput: function(){
		},
		onEditingStatusChange: function(){
		}
	};
	return qq.extend(i, a), i.attachTo = i.listElement, h = qq.extend(this, new qq.UiEventHandler(i, b)), qq.extend(b, {
		handleFilenameEdit: function(a, b, d, e){
			var h = i.onGetInput(d);
			i.onEditingStatusChange(a, !0), h.value = c(a), e && h.focus(), f(h, a), g(h, a);
		}
	}), h;
}, qq.FilenameClickHandler = function(a){
	"use strict";
	function b(a, b){
		if ( qq(a).hasClass(d.classes.file) || qq(a).hasClass(d.classes.editNameIcon) ){
			var e = c.getItemFromEventTarget(a), f = c.getFileIdFromItem(e), g = d.onGetUploadStatus(f);
			g === qq.status.SUBMITTED && (d.log(qq.format("Detected valid filename click event on file '{}', ID: {}.", d.onGetName(f), f)), qq.preventDefault(b), c.handleFilenameEdit(f, a, e, !0));
		}
	}
	var c = {}, d = {
		log: function(){
		},
		classes: {
			file: "qq-upload-file",
			editNameIcon: "qq-edit-filename-icon"
		},
		onGetUploadStatus: function(){
		},
		onGetName: function(){
		}
	};
	return qq.extend(d, a), d.eventType = "click", d.onHandled = b, qq.extend(this, new qq.FilenameEditHandler(d, c));
}, qq.FilenameInputFocusInHandler = function(a, b){
	"use strict";
	function c(a){
		if ( qq(a).hasClass(d.classes.editFilenameInput) ){
			var c = b.getItemFromEventTarget(a), e = b.getFileIdFromItem(c), f = d.onGetUploadStatus(e);
			f === qq.status.SUBMITTED && (d.log(qq.format("Detected valid filename input focus event on file '{}', ID: {}.", d.onGetName(e), e)), b.handleFilenameEdit(e, a, c));
		}
	}
	var d = {
		listElement: null,
		classes: {
			editFilenameInput: "qq-edit-filename"
		},
		onGetUploadStatus: function(){
		},
		log: function(){
		}
	};
	return b || (b = {}), d.eventType = "focusin", d.onHandled = c, qq.extend(d, a), qq.extend(this, new qq.FilenameEditHandler(d, b));
}, qq.FilenameInputFocusHandler = function(a){
	"use strict";
	return a.eventType = "focus", a.attachTo = null, qq.extend(this, new qq.FilenameInputFocusInHandler(a, {}));
}, function(a){
	"use strict";
	function b(a){
		if ( a ){
			var b = h(a), d = c(b);
			e(d), g(b, d);
		}
		return k;
	}
	function c(a){
		var b = f("uploaderType"), c = f("endpointType");
		return b ? (b = b.charAt(0).toUpperCase() + b.slice(1).toLowerCase(), c ? new qq[c]["FineUploader" + b](a) : new qq["FineUploader" + b](a)) : c ? new qq[c].FineUploader(a) : new qq.FineUploader(a);
	}
	function d(a, b){
		var c = k.data("fineuploader");
		return b ? (void 0 === c && (c = {}), c[a] = b, k.data("fineuploader", c), void 0) : void 0 === c ? null : c[a];
	}
	function e(a){
		return d("uploader", a);
	}
	function f(a, b){
		return d(a, b);
	}
	function g(b, c){
		var d = b.callbacks = {};
		a.each(c._options.callbacks, function(a){
			var b, c;
			b = /^on(\w+)/.exec(a)[1], b = b.substring(0, 1).toLowerCase() + b.substring(1), c = k, d[a] = function(){
				var a = Array.prototype.slice.call(arguments);
				return c.triggerHandler(b, a);
			};
		}), c._options.callbacks = d;
	}
	function h(b, c){
		var d, e;
		return d = void 0 === c ? "basic" !== b.uploaderType ? {
			element: k[0]
		} : {} : c, a.each(b, function(b, c){
			a.inArray(b, l) >= 0 ? f(b, c) : c instanceof a ? d[b] = c[0] : a.isPlainObject(c) ? (d[b] = {}, h(c, d[b])) : a.isArray(c) ? (e = [
			], a.each(c, function(b, c){
				c instanceof a ? a.merge(e, c) : e.push(c);
			}), d[b] = e) : d[b] = c;
		}), void 0 === c ? d : void 0;
	}
	function i(b){
		return "string" === a.type(b) && !b.match(/^_/) && void 0 !== e()[b];
	}
	function j(b){
		var c, d = [], f = Array.prototype.slice.call(arguments, 1);
		return h(f, d), c = e()[b].apply(e(), d), "object" !== typeof c || 1 !== c.nodeType && 9 !== c.nodeType || !c.cloneNode || (c = a(c)), c;
	}
	var k, l = [
		"uploaderType",
		"endpointType"
	];
	a.fn.fineUploader = function(c){
		var d = this, f = arguments, g = [];
		return this.each(function(h, l){
			if ( k = a(l), e() && i(c) ){
				if ( g.push(j.apply(d, f)), 1 === d.length )
					return !1;
			}else
				"object" !== typeof c && c ? a.error("Method " + c + " does not exist on jQuery.fineUploader") : b.apply(d, f);
		}), 1 === g.length ? g[0] : g.length > 1 ? g : this;
	};
}(jQuery), function(a){
	"use strict";
	function b(a){
		a || (a = {}), a.dropZoneElements = [i];
		var b = f(a);
		return e(b), d(new qq.DragAndDrop(b)), i;
	}
	function c(a, b){
		var c = i.data(j);
		return b ? (void 0 === c && (c = {}), c[a] = b, i.data(j, c), void 0) : void 0 === c ? null : c[a];
	}
	function d(a){
		return c("dndInstance", a);
	}
	function e(b){
		var c = b.callbacks = {};
		new qq.FineUploaderBasic, a.each(new qq.DragAndDrop.callbacks, function(a){
			var b, d = a;
			b = i, c[a] = function(){
				var a = Array.prototype.slice.call(arguments), c = b.triggerHandler(d, a);
				return c;
			};
		});
	}
	function f(b, c){
		var d, e;
		return d = void 0 === c ? {} : c, a.each(b, function(b, c){
			c instanceof a ? d[b] = c[0] : a.isPlainObject(c) ? (d[b] = {}, f(c, d[b])) : a.isArray(c) ? (e = [
			], a.each(c, function(b, c){
				c instanceof a ? a.merge(e, c) : e.push(c);
			}), d[b] = e) : d[b] = c;
		}), void 0 === c ? d : void 0;
	}
	function g(b){
		return "string" === a.type(b) && "dispose" === b && void 0 !== d()[b];
	}
	function h(a){
		var b = [], c = Array.prototype.slice.call(arguments, 1);
		return f(c, b), d()[a].apply(d(), b);
	}
	var i, j = "fineUploaderDnd";
	a.fn.fineUploaderDnd = function(c){
		var e = this, f = arguments, j = [];
		return this.each(function(k, l){
			if ( i = a(l), d() && g(c) )
			{
				if ( j.push(h.apply(e, f)), 1 === e.length )
					return !1;
			}
			else
			{
				"object" !== typeof c && c ? a.error("Method " + c + " does not exist in Fine Uploader's DnD module.") : b.apply(e, f);
			}
		}), 1 === j.length ? j[0] : j.length > 1 ? j : this;
	};
}(jQuery), qq.UploadHandlerForm = function(a, b, c, d){
	"use strict";
	function e(a, b){
		var c;
		try{
			var d = b.contentDocument || b.contentWindow.document, e = d.body.innerHTML;
			j("converting iframe's innerHTML to JSON"), j("innerHTML = " + e), e && e.match(/^<pre/i) && (e = d.body.firstChild.firstChild.nodeValue), c = k.parseJsonResponse(a, e);
		}catch( f ){
			j("Error when attempting to parse form upload response (" + f.message + ")", "error"), c = {
				success: !1
			};
		}
		return c;
	}
	function f(b, c){
		var d = a.paramsStore.getParams(b), e = a.demoMode ? "GET" : "POST", f = a.endpointStore.getEndpoint(b), g = h[b].newName;
		return d[a.uuidParam] = h[b].uuid, g && (d[a.filenameParam] = g), k.initFormForUpload({
			method: e,
			endpoint: f,
			params: d,
			paramsInBody: a.paramsInBody,
			targetName: c.name
		});
	}
	var g, h = [], i = b, j = d, k = {};
	return g = new qq.UploadHandlerFormApi(k, h, a.cors.expected, a.inputName, a.onCancel, c, j), qq.extend(g, {
		upload: function(b){
			var c, d = h[b].input, l = g.getName(b), m = k.createIframe(b);
			if ( !d )
				throw new Error("file with passed id was not added, or already uploaded or cancelled");
			a.onUpload(b, g.getName(b)), c = f(b, m), c.appendChild(d), k.attachLoadEvent(m, function(c){
				j("iframe loaded");
				var d = c ? c : e(b, m);
				k.detachLoadEvent(b), a.cors.expected || qq(m).remove(), (d.success || !a.onAutoRetry(b, l, d)) && (a.onComplete(b, l, d), i(b));
			}), j("Sending upload request for " + b), c.submit(), qq(c).remove();
		}
	});
}, qq.UploadHandlerXhr = function(a, b, c, d){
	"use strict";
	function e(b, c, d){
		var e = F.getSize(b), f = F.getName(b);
		c[a.chunking.paramNames.partIndex] = d.part, c[a.chunking.paramNames.partByteOffset] = d.start, c[a.chunking.paramNames.chunkSize] = d.size, c[a.chunking.paramNames.totalParts] = d.count, c[a.totalFileSizeParam] = e, N && (c[a.filenameParam] = f);
	}
	function f(b){
		b[a.resume.paramNames.resuming] = !0;
	}
	function g(b, c, d, e){
		var f = new FormData, g = a.demoMode ? "GET" : "POST", h = a.endpointStore.getEndpoint(e), i = h, j = F.getName(e), k = F.getSize(e), l = I[e].blobData, m = I[e].newName;
		return b[a.uuidParam] = I[e].uuid, N && (b[a.totalFileSizeParam] = k, l && (b[a.filenameParam] = l.name)), void 0 !== m && (b[a.filenameParam] = m), a.paramsInBody || (N || (b[a.inputName] = m || j), i = qq.obj2url(b, h)), c.open(g, i, !0), a.cors.expected && a.cors.sendCredentials && (c.withCredentials = !0), N ? (a.paramsInBody && qq.obj2FormData(b, f), f.append(a.inputName, d), f) : d;
	}
	function h(b, c){
		var d = a.customHeaders, e = I[b].file || I[b].blobData.blob;
		c.setRequestHeader("X-Requested-With", "XMLHttpRequest"), c.setRequestHeader("Cache-Control", "no-cache"), N || (c.setRequestHeader("Content-Type", "application/octet-stream"), c.setRequestHeader("X-Mime-Type", e.type)), qq.each(d, function(a, b){
			c.setRequestHeader(a, b);
		});
	}
	function i(b, c, d){
		var e = F.getName(b), f = F.getSize(b);
		I[b].attemptingResume = !1, a.onProgress(b, e, f, f), a.onComplete(b, e, c, d), I[b] && delete I[b].xhr, G(b);
	}
	function j(b){
		var c, d, i = I[b].remainingChunkIdxs[0], j = O.getChunkData(b, i), l = O.createXhr(b), m = F.getSize(b), n = F.getName(b);
		void 0 === I[b].loaded && (I[b].loaded = 0), L && I[b].file && u(b, j), l.onreadystatechange = t(b, l), l.upload.onprogress = function(c){
			if ( c.lengthComputable ){
				var d = c.loaded + I[b].loaded, e = k(b, i, c.total);
				a.onProgress(b, n, d, e);
			}
		}, a.onUploadChunk(b, n, O.getChunkDataForCallback(j)), d = a.paramsStore.getParams(b), e(b, d, j), I[b].attemptingResume && f(d), c = g(d, l, j.blob, b), h(b, l), H("Sending chunked upload request for item " + b + ": bytes " + (j.start + 1) + "-" + j.end + " of " + m), l.send(c);
	}
	function k(a, b, c){
		var d = O.getChunkData(a, b), e = d.size, f = c - e, g = F.getSize(a), h = d.count, i = I[a].initialRequestOverhead, j = f - i;
		return I[a].lastRequestOverhead = f, 0 === b ? (I[a].lastChunkIdxProgress = 0, I[a].initialRequestOverhead = f, I[a].estTotalRequestsSize = g + h * f) : I[a].lastChunkIdxProgress !== b && (I[a].lastChunkIdxProgress = b, I[a].estTotalRequestsSize += j), I[a].estTotalRequestsSize;
	}
	function l(a){
		return N ? I[a].lastRequestOverhead : 0;
	}
	function m(a, b, c){
		var d = I[a].remainingChunkIdxs.shift(), e = O.getChunkData(a, d);
		I[a].attemptingResume = !1, I[a].loaded += e.size + l(a), I[a].remainingChunkIdxs.length > 0 ? j(a) : (L && v(a), i(a, b, c));
	}
	function n(a, b){
		return 200 !== a.status || !b.success || b.reset;
	}
	function o(a, b){
		var c;
		try{
			H(qq.format("Received response status {} with body: {}", b.status, b.responseText)), c = qq.parseJson(b.responseText), void 0 !== c.newUuid && F.setUuid(a, c.newUuid);
		}catch( d ){
			H("Error when attempting to parse xhr response text (" + d.message + ")", "error"), c = {};
		}
		return c;
	}
	function p(a){
		H("Server has ordered chunking effort to be restarted on next attempt for item ID " + a, "error"), L && (v(a), I[a].attemptingResume = !1), I[a].remainingChunkIdxs = [
		], delete I[a].loaded, delete I[a].estTotalRequestsSize, delete I[a].initialRequestOverhead;
	}
	function q(a){
		I[a].attemptingResume = !1, H("Server has declared that it cannot handle resume for item ID " + a + " - starting from the first chunk", "error"), p(a), F.upload(a, !0);
	}
	function r(b, c, d){
		var e = F.getName(b);
		a.onAutoRetry(b, e, c, d) || i(b, c, d);
	}
	function s(a, b){
		var c;
		I[a] && (H("xhr - server response received for " + a), H("responseText = " + b.responseText), c = o(a, b), n(b, c) ? (c.reset && p(a), I[a].attemptingResume && c.reset ? q(a) : r(a, c, b)) : K ? m(a, c, b) : i(a, c, b));
	}
	function t(a, b){
		return function(){
			4 === b.readyState && s(a, b);
		};
	}
	function u(b, c){
		var d = F.getUuid(b), e = I[b].loaded, f = I[b].initialRequestOverhead, g = I[b].estTotalRequestsSize, h = x(b), i = d + J + c.part + J + e + J + f + J + g, j = a.resume.cookiesExpireIn;
		qq.setCookie(h, i, j);
	}
	function v(a){
		if ( I[a].file ){
			var b = x(a);
			qq.deleteCookie(b);
		}
	}
	function w(a){
		var b, c, d, e, f, g, h = qq.getCookie(x(a)), i = F.getName(a);
		if ( h ){
			if ( b = h.split(J), 5 === b.length )
				return c = b[0], d = parseInt(b[1], 10), e = parseInt(b[2], 10), f = parseInt(b[3], 10), g = parseInt(b[4], 10), {
					uuid: c,
					part: d,
					lastByteSent: e,
					initialRequestOverhead: f,
					estTotalRequestsSize: g
				};
			H("Ignoring previously stored resume/chunk cookie for " + i + " - old cookie format", "warn");
		}
	}
	function x(b){
		var c, d = F.getName(b), e = F.getSize(b), f = a.chunking.partSize;
		return c = "qqfilechunk" + J + encodeURIComponent(d) + J + e + J + f, void 0 !== M && (c += J + M), c;
	}
	function y(){
		return null === a.resume.id || void 0 === a.resume.id || qq.isFunction(a.resume.id) || qq.isObject(a.resume.id) ? void 0 : a.resume.id;
	}
	function z(a, b){
		var c;
		for( c = O.getTotalChunks(a) - 1; c >= b; c -= 1 )
			I[a].remainingChunkIdxs.unshift(c);
		j(a);
	}
	function A(a, b, c, d){
		c = d.part, I[a].loaded = d.lastByteSent, I[a].estTotalRequestsSize = d.estTotalRequestsSize, I[a].initialRequestOverhead = d.initialRequestOverhead, I[a].attemptingResume = !0, H("Resuming " + b + " at partition index " + c), z(a, c);
	}
	function B(b, c, d){
		var e, f = F.getName(b), g = O.getChunkData(b, c.part);
		e = a.onResume(b, f, O.getChunkDataForCallback(g)), qq.isPromise(e) ? (H("Waiting for onResume promise to be fulfilled for " + b), e.then(function(){
			A(b, f, d, c);
		}, function(){
			H("onResume promise fulfilled - failure indicated. Will not resume."), z(b, d);
		})) : e !== !1 ? A(b, f, d, c) : (H("onResume callback return ed false. Will not resume."), z(b, d));
	}
	function C(a, b){
		var c, d = 0;
		I[a].remainingChunkIdxs && 0 !== I[a].remainingChunkIdxs.length ? j(a) : (I[a].remainingChunkIdxs = [
		], L && !b && I[a].file ? (c = w(a), c ? B(a, c, d) : z(a, d)) : z(a, d));
	}
	function D(b){
		var c, d, e, f = I[b].file || I[b].blobData.blob, i = F.getName(b);
		I[b].loaded = 0, c = O.createXhr(b), c.upload.onprogress = function(c){
			c.lengthComputable && (I[b].loaded = c.loaded, a.onProgress(b, i, c.loaded, c.total));
		}, c.onreadystatechange = t(b, c), d = a.paramsStore.getParams(b), e = g(d, c, f, b), h(b, c), H("Sending upload request for " + b), c.send(e);
	}
	function E(b, c){
		var d = F.getName(b);
		F.isValid(b) && (a.onUpload(b, d), K ? C(b, c) : D(b));
	}
	var F, G = b, H = d, I = [
	], J = "|", K = a.chunking.enabled && qq.supportedFeatures.chunking, L = a.resume.enabled && K && qq.supportedFeatures.resume, M = y(), N = a.forceMultipart || a.paramsInBody, O = {};
	return F = new qq.UploadHandlerXhrApi(O, I, K ? a.chunking : null, E, a.onCancel, c, H), qq.override(F, function(b){
		return {
			add: function(a){
				var c, d = b.add(a);
				return L && (c = w(d), c && (I[d].uuid = c.uuid)), d;
			},
			getResumableFilesData: function(){
				var b = [], c = [];
				return K && L ? (b = void 0 === M ? qq.getCookieNames(new RegExp("^qqfilechunk\\" + J + ".+\\" + J + "\\d+\\" + J + a.chunking.partSize + "=")) : qq.getCookieNames(new RegExp("^qqfilechunk\\" + J + ".+\\" + J + "\\d+\\" + J + a.chunking.partSize + "\\" + J + M + "=")), qq.each(b, function(a, b){
					var d = b.split(J), e = qq.getCookie(b).split(J);
					c.push({
						name: decodeURIComponent(d[1]),
						size: d[2],
						uuid: e[0],
						partIdx: e[1]
					});
				}), c) : [];
			},
			expunge: function(a){
				L && v(a), b.expunge(a);
			}
		};
	}), F;
};