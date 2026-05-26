function en(e,t){return function(){return e.apply(t,arguments)}}const{toString:hr}=Object.prototype,{getPrototypeOf:Qe}=Object,ge=(e=>t=>{const n=hr.call(t);return e[n]||(e[n]=n.slice(8,-1).toLowerCase())})(Object.create(null)),C=e=>(e=e.toLowerCase(),t=>ge(t)===e),be=e=>t=>typeof t===e,{isArray:G}=Array,Z=be("undefined");function pr(e){return e!==null&&!Z(e)&&e.constructor!==null&&!Z(e.constructor)&&R(e.constructor.isBuffer)&&e.constructor.isBuffer(e)}const tn=C("ArrayBuffer");function mr(e){let t;return typeof ArrayBuffer<"u"&&ArrayBuffer.isView?t=ArrayBuffer.isView(e):t=e&&e.buffer&&tn(e.buffer),t}const gr=be("string"),R=be("function"),nn=be("number"),ye=e=>e!==null&&typeof e=="object",br=e=>e===!0||e===!1,ce=e=>{if(ge(e)!=="object")return!1;const t=Qe(e);return(t===null||t===Object.prototype||Object.getPrototypeOf(t)===null)&&!(Symbol.toStringTag in e)&&!(Symbol.iterator in e)},yr=C("Date"),wr=C("File"),Er=C("Blob"),Sr=C("FileList"),Tr=e=>ye(e)&&R(e.pipe),Ir=e=>{let t;return e&&(typeof FormData=="function"&&e instanceof FormData||R(e.append)&&((t=ge(e))==="formdata"||t==="object"&&R(e.toString)&&e.toString()==="[object FormData]"))},Ar=C("URLSearchParams"),[_r,vr,Rr,Or]=["ReadableStream","Request","Response","Headers"].map(C),Cr=e=>e.trim?e.trim():e.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,"");function re(e,t,{allOwnKeys:n=!1}={}){if(e===null||typeof e>"u")return;let r,s;if(typeof e!="object"&&(e=[e]),G(e))for(r=0,s=e.length;r<s;r++)t.call(null,e[r],r,e);else{const o=n?Object.getOwnPropertyNames(e):Object.keys(e),i=o.length;let a;for(r=0;r<i;r++)a=o[r],t.call(null,e[a],a,e)}}function rn(e,t){t=t.toLowerCase();const n=Object.keys(e);let r=n.length,s;for(;r-- >0;)if(s=n[r],t===s.toLowerCase())return s;return null}const sn=typeof globalThis<"u"?globalThis:typeof self<"u"?self:typeof window<"u"?window:global,on=e=>!Z(e)&&e!==sn;function $e(){const{caseless:e}=on(this)&&this||{},t={},n=(r,s)=>{const o=e&&rn(t,s)||s;ce(t[o])&&ce(r)?t[o]=$e(t[o],r):ce(r)?t[o]=$e({},r):G(r)?t[o]=r.slice():t[o]=r};for(let r=0,s=arguments.length;r<s;r++)arguments[r]&&re(arguments[r],n);return t}const Dr=(e,t,n,{allOwnKeys:r}={})=>(re(t,(s,o)=>{n&&R(s)?e[o]=en(s,n):e[o]=s},{allOwnKeys:r}),e),kr=e=>(e.charCodeAt(0)===65279&&(e=e.slice(1)),e),Nr=(e,t,n,r)=>{e.prototype=Object.create(t.prototype,r),e.prototype.constructor=e,Object.defineProperty(e,"super",{value:t.prototype}),n&&Object.assign(e.prototype,n)},Pr=(e,t,n,r)=>{let s,o,i;const a={};if(t=t||{},e==null)return t;do{for(s=Object.getOwnPropertyNames(e),o=s.length;o-- >0;)i=s[o],(!r||r(i,e,t))&&!a[i]&&(t[i]=e[i],a[i]=!0);e=n!==!1&&Qe(e)}while(e&&(!n||n(e,t))&&e!==Object.prototype);return t},Br=(e,t,n)=>{e=String(e),(n===void 0||n>e.length)&&(n=e.length),n-=t.length;const r=e.indexOf(t,n);return r!==-1&&r===n},Mr=e=>{if(!e)return null;if(G(e))return e;let t=e.length;if(!nn(t))return null;const n=new Array(t);for(;t-- >0;)n[t]=e[t];return n},Fr=(e=>t=>e&&t instanceof e)(typeof Uint8Array<"u"&&Qe(Uint8Array)),Lr=(e,t)=>{const r=(e&&e[Symbol.iterator]).call(e);let s;for(;(s=r.next())&&!s.done;){const o=s.value;t.call(e,o[0],o[1])}},xr=(e,t)=>{let n;const r=[];for(;(n=e.exec(t))!==null;)r.push(n);return r},$r=C("HTMLFormElement"),jr=e=>e.toLowerCase().replace(/[-_\s]([a-z\d])(\w*)/g,function(n,r,s){return r.toUpperCase()+s}),mt=(({hasOwnProperty:e})=>(t,n)=>e.call(t,n))(Object.prototype),Ur=C("RegExp"),an=(e,t)=>{const n=Object.getOwnPropertyDescriptors(e),r={};re(n,(s,o)=>{let i;(i=t(s,o,e))!==!1&&(r[o]=i||s)}),Object.defineProperties(e,r)},Hr=e=>{an(e,(t,n)=>{if(R(e)&&["arguments","caller","callee"].indexOf(n)!==-1)return!1;const r=e[n];if(R(r)){if(t.enumerable=!1,"writable"in t){t.writable=!1;return}t.set||(t.set=()=>{throw Error("Can not rewrite read-only method '"+n+"'")})}})},qr=(e,t)=>{const n={},r=s=>{s.forEach(o=>{n[o]=!0})};return G(e)?r(e):r(String(e).split(t)),n},Vr=()=>{},zr=(e,t)=>e!=null&&Number.isFinite(e=+e)?e:t,Ae="abcdefghijklmnopqrstuvwxyz",gt="0123456789",cn={DIGIT:gt,ALPHA:Ae,ALPHA_DIGIT:Ae+Ae.toUpperCase()+gt},Kr=(e=16,t=cn.ALPHA_DIGIT)=>{let n="";const{length:r}=t;for(;e--;)n+=t[Math.random()*r|0];return n};function Wr(e){return!!(e&&R(e.append)&&e[Symbol.toStringTag]==="FormData"&&e[Symbol.iterator])}const Gr=e=>{const t=new Array(10),n=(r,s)=>{if(ye(r)){if(t.indexOf(r)>=0)return;if(!("toJSON"in r)){t[s]=r;const o=G(r)?[]:{};return re(r,(i,a)=>{const l=n(i,s+1);!Z(l)&&(o[a]=l)}),t[s]=void 0,o}}return r};return n(e,0)},Jr=C("AsyncFunction"),Yr=e=>e&&(ye(e)||R(e))&&R(e.then)&&R(e.catch),u={isArray:G,isArrayBuffer:tn,isBuffer:pr,isFormData:Ir,isArrayBufferView:mr,isString:gr,isNumber:nn,isBoolean:br,isObject:ye,isPlainObject:ce,isReadableStream:_r,isRequest:vr,isResponse:Rr,isHeaders:Or,isUndefined:Z,isDate:yr,isFile:wr,isBlob:Er,isRegExp:Ur,isFunction:R,isStream:Tr,isURLSearchParams:Ar,isTypedArray:Fr,isFileList:Sr,forEach:re,merge:$e,extend:Dr,trim:Cr,stripBOM:kr,inherits:Nr,toFlatObject:Pr,kindOf:ge,kindOfTest:C,endsWith:Br,toArray:Mr,forEachEntry:Lr,matchAll:xr,isHTMLForm:$r,hasOwnProperty:mt,hasOwnProp:mt,reduceDescriptors:an,freezeMethods:Hr,toObjectSet:qr,toCamelCase:jr,noop:Vr,toFiniteNumber:zr,findKey:rn,global:sn,isContextDefined:on,ALPHABET:cn,generateString:Kr,isSpecCompliantForm:Wr,toJSONObject:Gr,isAsyncFn:Jr,isThenable:Yr};function p(e,t,n,r,s){Error.call(this),Error.captureStackTrace?Error.captureStackTrace(this,this.constructor):this.stack=new Error().stack,this.message=e,this.name="AxiosError",t&&(this.code=t),n&&(this.config=n),r&&(this.request=r),s&&(this.response=s)}u.inherits(p,Error,{toJSON:function(){return{message:this.message,name:this.name,description:this.description,number:this.number,fileName:this.fileName,lineNumber:this.lineNumber,columnNumber:this.columnNumber,stack:this.stack,config:u.toJSONObject(this.config),code:this.code,status:this.response&&this.response.status?this.response.status:null}}});const ln=p.prototype,un={};["ERR_BAD_OPTION_VALUE","ERR_BAD_OPTION","ECONNABORTED","ETIMEDOUT","ERR_NETWORK","ERR_FR_TOO_MANY_REDIRECTS","ERR_DEPRECATED","ERR_BAD_RESPONSE","ERR_BAD_REQUEST","ERR_CANCELED","ERR_NOT_SUPPORT","ERR_INVALID_URL"].forEach(e=>{un[e]={value:e}});Object.defineProperties(p,un);Object.defineProperty(ln,"isAxiosError",{value:!0});p.from=(e,t,n,r,s,o)=>{const i=Object.create(ln);return u.toFlatObject(e,i,function(l){return l!==Error.prototype},a=>a!=="isAxiosError"),p.call(i,e.message,t,n,r,s),i.cause=e,i.name=e.name,o&&Object.assign(i,o),i};const Xr=null;function je(e){return u.isPlainObject(e)||u.isArray(e)}function dn(e){return u.endsWith(e,"[]")?e.slice(0,-2):e}function bt(e,t,n){return e?e.concat(t).map(function(s,o){return s=dn(s),!n&&o?"["+s+"]":s}).join(n?".":""):t}function Qr(e){return u.isArray(e)&&!e.some(je)}const Zr=u.toFlatObject(u,{},null,function(t){return/^is[A-Z]/.test(t)});function we(e,t,n){if(!u.isObject(e))throw new TypeError("target must be an object");t=t||new FormData,n=u.toFlatObject(n,{metaTokens:!0,dots:!1,indexes:!1},!1,function(g,T){return!u.isUndefined(T[g])});const r=n.metaTokens,s=n.visitor||c,o=n.dots,i=n.indexes,l=(n.Blob||typeof Blob<"u"&&Blob)&&u.isSpecCompliantForm(t);if(!u.isFunction(s))throw new TypeError("visitor must be a function");function d(h){if(h===null)return"";if(u.isDate(h))return h.toISOString();if(!l&&u.isBlob(h))throw new p("Blob is not supported. Use a Buffer instead.");return u.isArrayBuffer(h)||u.isTypedArray(h)?l&&typeof Blob=="function"?new Blob([h]):Buffer.from(h):h}function c(h,g,T){let I=h;if(h&&!T&&typeof h=="object"){if(u.endsWith(g,"{}"))g=r?g:g.slice(0,-2),h=JSON.stringify(h);else if(u.isArray(h)&&Qr(h)||(u.isFileList(h)||u.endsWith(g,"[]"))&&(I=u.toArray(h)))return g=dn(g),I.forEach(function(w,Y){!(u.isUndefined(w)||w===null)&&t.append(i===!0?bt([g],Y,o):i===null?g:g+"[]",d(w))}),!1}return je(h)?!0:(t.append(bt(T,g,o),d(h)),!1)}const f=[],b=Object.assign(Zr,{defaultVisitor:c,convertValue:d,isVisitable:je});function m(h,g){if(!u.isUndefined(h)){if(f.indexOf(h)!==-1)throw Error("Circular reference detected in "+g.join("."));f.push(h),u.forEach(h,function(I,N){(!(u.isUndefined(I)||I===null)&&s.call(t,I,u.isString(N)?N.trim():N,g,b))===!0&&m(I,g?g.concat(N):[N])}),f.pop()}}if(!u.isObject(e))throw new TypeError("data must be an object");return m(e),t}function yt(e){const t={"!":"%21","'":"%27","(":"%28",")":"%29","~":"%7E","%20":"+","%00":"\0"};return encodeURIComponent(e).replace(/[!'()~]|%20|%00/g,function(r){return t[r]})}function Ze(e,t){this._pairs=[],e&&we(e,this,t)}const fn=Ze.prototype;fn.append=function(t,n){this._pairs.push([t,n])};fn.toString=function(t){const n=t?function(r){return t.call(this,r,yt)}:yt;return this._pairs.map(function(s){return n(s[0])+"="+n(s[1])},"").join("&")};function es(e){return encodeURIComponent(e).replace(/%3A/gi,":").replace(/%24/g,"$").replace(/%2C/gi,",").replace(/%20/g,"+").replace(/%5B/gi,"[").replace(/%5D/gi,"]")}function hn(e,t,n){if(!t)return e;const r=n&&n.encode||es,s=n&&n.serialize;let o;if(s?o=s(t,n):o=u.isURLSearchParams(t)?t.toString():new Ze(t,n).toString(r),o){const i=e.indexOf("#");i!==-1&&(e=e.slice(0,i)),e+=(e.indexOf("?")===-1?"?":"&")+o}return e}class wt{constructor(){this.handlers=[]}use(t,n,r){return this.handlers.push({fulfilled:t,rejected:n,synchronous:r?r.synchronous:!1,runWhen:r?r.runWhen:null}),this.handlers.length-1}eject(t){this.handlers[t]&&(this.handlers[t]=null)}clear(){this.handlers&&(this.handlers=[])}forEach(t){u.forEach(this.handlers,function(r){r!==null&&t(r)})}}const pn={silentJSONParsing:!0,forcedJSONParsing:!0,clarifyTimeoutError:!1},ts=typeof URLSearchParams<"u"?URLSearchParams:Ze,ns=typeof FormData<"u"?FormData:null,rs=typeof Blob<"u"?Blob:null,ss={isBrowser:!0,classes:{URLSearchParams:ts,FormData:ns,Blob:rs},protocols:["http","https","file","blob","url","data"]},et=typeof window<"u"&&typeof document<"u",os=(e=>et&&["ReactNative","NativeScript","NS"].indexOf(e)<0)(typeof navigator<"u"&&navigator.product),is=typeof WorkerGlobalScope<"u"&&self instanceof WorkerGlobalScope&&typeof self.importScripts=="function",as=et&&window.location.href||"http://localhost",cs=Object.freeze(Object.defineProperty({__proto__:null,hasBrowserEnv:et,hasStandardBrowserEnv:os,hasStandardBrowserWebWorkerEnv:is,origin:as},Symbol.toStringTag,{value:"Module"})),O={...cs,...ss};function ls(e,t){return we(e,new O.classes.URLSearchParams,Object.assign({visitor:function(n,r,s,o){return O.isNode&&u.isBuffer(n)?(this.append(r,n.toString("base64")),!1):o.defaultVisitor.apply(this,arguments)}},t))}function us(e){return u.matchAll(/\w+|\[(\w*)]/g,e).map(t=>t[0]==="[]"?"":t[1]||t[0])}function ds(e){const t={},n=Object.keys(e);let r;const s=n.length;let o;for(r=0;r<s;r++)o=n[r],t[o]=e[o];return t}function mn(e){function t(n,r,s,o){let i=n[o++];if(i==="__proto__")return!0;const a=Number.isFinite(+i),l=o>=n.length;return i=!i&&u.isArray(s)?s.length:i,l?(u.hasOwnProp(s,i)?s[i]=[s[i],r]:s[i]=r,!a):((!s[i]||!u.isObject(s[i]))&&(s[i]=[]),t(n,r,s[i],o)&&u.isArray(s[i])&&(s[i]=ds(s[i])),!a)}if(u.isFormData(e)&&u.isFunction(e.entries)){const n={};return u.forEachEntry(e,(r,s)=>{t(us(r),s,n,0)}),n}return null}function fs(e,t,n){if(u.isString(e))try{return(t||JSON.parse)(e),u.trim(e)}catch(r){if(r.name!=="SyntaxError")throw r}return(n||JSON.stringify)(e)}const se={transitional:pn,adapter:["xhr","http","fetch"],transformRequest:[function(t,n){const r=n.getContentType()||"",s=r.indexOf("application/json")>-1,o=u.isObject(t);if(o&&u.isHTMLForm(t)&&(t=new FormData(t)),u.isFormData(t))return s?JSON.stringify(mn(t)):t;if(u.isArrayBuffer(t)||u.isBuffer(t)||u.isStream(t)||u.isFile(t)||u.isBlob(t)||u.isReadableStream(t))return t;if(u.isArrayBufferView(t))return t.buffer;if(u.isURLSearchParams(t))return n.setContentType("application/x-www-form-urlencoded;charset=utf-8",!1),t.toString();let a;if(o){if(r.indexOf("application/x-www-form-urlencoded")>-1)return ls(t,this.formSerializer).toString();if((a=u.isFileList(t))||r.indexOf("multipart/form-data")>-1){const l=this.env&&this.env.FormData;return we(a?{"files[]":t}:t,l&&new l,this.formSerializer)}}return o||s?(n.setContentType("application/json",!1),fs(t)):t}],transformResponse:[function(t){const n=this.transitional||se.transitional,r=n&&n.forcedJSONParsing,s=this.responseType==="json";if(u.isResponse(t)||u.isReadableStream(t))return t;if(t&&u.isString(t)&&(r&&!this.responseType||s)){const i=!(n&&n.silentJSONParsing)&&s;try{return JSON.parse(t)}catch(a){if(i)throw a.name==="SyntaxError"?p.from(a,p.ERR_BAD_RESPONSE,this,null,this.response):a}}return t}],timeout:0,xsrfCookieName:"XSRF-TOKEN",xsrfHeaderName:"X-XSRF-TOKEN",maxContentLength:-1,maxBodyLength:-1,env:{FormData:O.classes.FormData,Blob:O.classes.Blob},validateStatus:function(t){return t>=200&&t<300},headers:{common:{Accept:"application/json, text/plain, */*","Content-Type":void 0}}};u.forEach(["delete","get","head","post","put","patch"],e=>{se.headers[e]={}});const hs=u.toObjectSet(["age","authorization","content-length","content-type","etag","expires","from","host","if-modified-since","if-unmodified-since","last-modified","location","max-forwards","proxy-authorization","referer","retry-after","user-agent"]),ps=e=>{const t={};let n,r,s;return e&&e.split(`
`).forEach(function(i){s=i.indexOf(":"),n=i.substring(0,s).trim().toLowerCase(),r=i.substring(s+1).trim(),!(!n||t[n]&&hs[n])&&(n==="set-cookie"?t[n]?t[n].push(r):t[n]=[r]:t[n]=t[n]?t[n]+", "+r:r)}),t},Et=Symbol("internals");function X(e){return e&&String(e).trim().toLowerCase()}function le(e){return e===!1||e==null?e:u.isArray(e)?e.map(le):String(e)}function ms(e){const t=Object.create(null),n=/([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;let r;for(;r=n.exec(e);)t[r[1]]=r[2];return t}const gs=e=>/^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(e.trim());function _e(e,t,n,r,s){if(u.isFunction(r))return r.call(this,t,n);if(s&&(t=n),!!u.isString(t)){if(u.isString(r))return t.indexOf(r)!==-1;if(u.isRegExp(r))return r.test(t)}}function bs(e){return e.trim().toLowerCase().replace(/([a-z\d])(\w*)/g,(t,n,r)=>n.toUpperCase()+r)}function ys(e,t){const n=u.toCamelCase(" "+t);["get","set","has"].forEach(r=>{Object.defineProperty(e,r+n,{value:function(s,o,i){return this[r].call(this,t,s,o,i)},configurable:!0})})}class A{constructor(t){t&&this.set(t)}set(t,n,r){const s=this;function o(a,l,d){const c=X(l);if(!c)throw new Error("header name must be a non-empty string");const f=u.findKey(s,c);(!f||s[f]===void 0||d===!0||d===void 0&&s[f]!==!1)&&(s[f||l]=le(a))}const i=(a,l)=>u.forEach(a,(d,c)=>o(d,c,l));if(u.isPlainObject(t)||t instanceof this.constructor)i(t,n);else if(u.isString(t)&&(t=t.trim())&&!gs(t))i(ps(t),n);else if(u.isHeaders(t))for(const[a,l]of t.entries())o(l,a,r);else t!=null&&o(n,t,r);return this}get(t,n){if(t=X(t),t){const r=u.findKey(this,t);if(r){const s=this[r];if(!n)return s;if(n===!0)return ms(s);if(u.isFunction(n))return n.call(this,s,r);if(u.isRegExp(n))return n.exec(s);throw new TypeError("parser must be boolean|regexp|function")}}}has(t,n){if(t=X(t),t){const r=u.findKey(this,t);return!!(r&&this[r]!==void 0&&(!n||_e(this,this[r],r,n)))}return!1}delete(t,n){const r=this;let s=!1;function o(i){if(i=X(i),i){const a=u.findKey(r,i);a&&(!n||_e(r,r[a],a,n))&&(delete r[a],s=!0)}}return u.isArray(t)?t.forEach(o):o(t),s}clear(t){const n=Object.keys(this);let r=n.length,s=!1;for(;r--;){const o=n[r];(!t||_e(this,this[o],o,t,!0))&&(delete this[o],s=!0)}return s}normalize(t){const n=this,r={};return u.forEach(this,(s,o)=>{const i=u.findKey(r,o);if(i){n[i]=le(s),delete n[o];return}const a=t?bs(o):String(o).trim();a!==o&&delete n[o],n[a]=le(s),r[a]=!0}),this}concat(...t){return this.constructor.concat(this,...t)}toJSON(t){const n=Object.create(null);return u.forEach(this,(r,s)=>{r!=null&&r!==!1&&(n[s]=t&&u.isArray(r)?r.join(", "):r)}),n}[Symbol.iterator](){return Object.entries(this.toJSON())[Symbol.iterator]()}toString(){return Object.entries(this.toJSON()).map(([t,n])=>t+": "+n).join(`
`)}get[Symbol.toStringTag](){return"AxiosHeaders"}static from(t){return t instanceof this?t:new this(t)}static concat(t,...n){const r=new this(t);return n.forEach(s=>r.set(s)),r}static accessor(t){const r=(this[Et]=this[Et]={accessors:{}}).accessors,s=this.prototype;function o(i){const a=X(i);r[a]||(ys(s,i),r[a]=!0)}return u.isArray(t)?t.forEach(o):o(t),this}}A.accessor(["Content-Type","Content-Length","Accept","Accept-Encoding","User-Agent","Authorization"]);u.reduceDescriptors(A.prototype,({value:e},t)=>{let n=t[0].toUpperCase()+t.slice(1);return{get:()=>e,set(r){this[n]=r}}});u.freezeMethods(A);function ve(e,t){const n=this||se,r=t||n,s=A.from(r.headers);let o=r.data;return u.forEach(e,function(a){o=a.call(n,o,s.normalize(),t?t.status:void 0)}),s.normalize(),o}function gn(e){return!!(e&&e.__CANCEL__)}function J(e,t,n){p.call(this,e??"canceled",p.ERR_CANCELED,t,n),this.name="CanceledError"}u.inherits(J,p,{__CANCEL__:!0});function bn(e,t,n){const r=n.config.validateStatus;!n.status||!r||r(n.status)?e(n):t(new p("Request failed with status code "+n.status,[p.ERR_BAD_REQUEST,p.ERR_BAD_RESPONSE][Math.floor(n.status/100)-4],n.config,n.request,n))}function ws(e){const t=/^([-+\w]{1,25})(:?\/\/|:)/.exec(e);return t&&t[1]||""}function Es(e,t){e=e||10;const n=new Array(e),r=new Array(e);let s=0,o=0,i;return t=t!==void 0?t:1e3,function(l){const d=Date.now(),c=r[o];i||(i=d),n[s]=l,r[s]=d;let f=o,b=0;for(;f!==s;)b+=n[f++],f=f%e;if(s=(s+1)%e,s===o&&(o=(o+1)%e),d-i<t)return;const m=c&&d-c;return m?Math.round(b*1e3/m):void 0}}function Ss(e,t){let n=0;const r=1e3/t;let s=null;return function(){const i=this===!0,a=Date.now();if(i||a-n>r)return s&&(clearTimeout(s),s=null),n=a,e.apply(null,arguments);s||(s=setTimeout(()=>(s=null,n=Date.now(),e.apply(null,arguments)),r-(a-n)))}}const ue=(e,t,n=3)=>{let r=0;const s=Es(50,250);return Ss(o=>{const i=o.loaded,a=o.lengthComputable?o.total:void 0,l=i-r,d=s(l),c=i<=a;r=i;const f={loaded:i,total:a,progress:a?i/a:void 0,bytes:l,rate:d||void 0,estimated:d&&a&&c?(a-i)/d:void 0,event:o,lengthComputable:a!=null};f[t?"download":"upload"]=!0,e(f)},n)},Ts=O.hasStandardBrowserEnv?function(){const t=/(msie|trident)/i.test(navigator.userAgent),n=document.createElement("a");let r;function s(o){let i=o;return t&&(n.setAttribute("href",i),i=n.href),n.setAttribute("href",i),{href:n.href,protocol:n.protocol?n.protocol.replace(/:$/,""):"",host:n.host,search:n.search?n.search.replace(/^\?/,""):"",hash:n.hash?n.hash.replace(/^#/,""):"",hostname:n.hostname,port:n.port,pathname:n.pathname.charAt(0)==="/"?n.pathname:"/"+n.pathname}}return r=s(window.location.href),function(i){const a=u.isString(i)?s(i):i;return a.protocol===r.protocol&&a.host===r.host}}():function(){return function(){return!0}}(),Is=O.hasStandardBrowserEnv?{write(e,t,n,r,s,o){const i=[e+"="+encodeURIComponent(t)];u.isNumber(n)&&i.push("expires="+new Date(n).toGMTString()),u.isString(r)&&i.push("path="+r),u.isString(s)&&i.push("domain="+s),o===!0&&i.push("secure"),document.cookie=i.join("; ")},read(e){const t=document.cookie.match(new RegExp("(^|;\\s*)("+e+")=([^;]*)"));return t?decodeURIComponent(t[3]):null},remove(e){this.write(e,"",Date.now()-864e5)}}:{write(){},read(){return null},remove(){}};function As(e){return/^([a-z][a-z\d+\-.]*:)?\/\//i.test(e)}function _s(e,t){return t?e.replace(/\/?\/$/,"")+"/"+t.replace(/^\/+/,""):e}function yn(e,t){return e&&!As(t)?_s(e,t):t}const St=e=>e instanceof A?{...e}:e;function q(e,t){t=t||{};const n={};function r(d,c,f){return u.isPlainObject(d)&&u.isPlainObject(c)?u.merge.call({caseless:f},d,c):u.isPlainObject(c)?u.merge({},c):u.isArray(c)?c.slice():c}function s(d,c,f){if(u.isUndefined(c)){if(!u.isUndefined(d))return r(void 0,d,f)}else return r(d,c,f)}function o(d,c){if(!u.isUndefined(c))return r(void 0,c)}function i(d,c){if(u.isUndefined(c)){if(!u.isUndefined(d))return r(void 0,d)}else return r(void 0,c)}function a(d,c,f){if(f in t)return r(d,c);if(f in e)return r(void 0,d)}const l={url:o,method:o,data:o,baseURL:i,transformRequest:i,transformResponse:i,paramsSerializer:i,timeout:i,timeoutMessage:i,withCredentials:i,withXSRFToken:i,adapter:i,responseType:i,xsrfCookieName:i,xsrfHeaderName:i,onUploadProgress:i,onDownloadProgress:i,decompress:i,maxContentLength:i,maxBodyLength:i,beforeRedirect:i,transport:i,httpAgent:i,httpsAgent:i,cancelToken:i,socketPath:i,responseEncoding:i,validateStatus:a,headers:(d,c)=>s(St(d),St(c),!0)};return u.forEach(Object.keys(Object.assign({},e,t)),function(c){const f=l[c]||s,b=f(e[c],t[c],c);u.isUndefined(b)&&f!==a||(n[c]=b)}),n}const wn=e=>{const t=q({},e);let{data:n,withXSRFToken:r,xsrfHeaderName:s,xsrfCookieName:o,headers:i,auth:a}=t;t.headers=i=A.from(i),t.url=hn(yn(t.baseURL,t.url),e.params,e.paramsSerializer),a&&i.set("Authorization","Basic "+btoa((a.username||"")+":"+(a.password?unescape(encodeURIComponent(a.password)):"")));let l;if(u.isFormData(n)){if(O.hasStandardBrowserEnv||O.hasStandardBrowserWebWorkerEnv)i.setContentType(void 0);else if((l=i.getContentType())!==!1){const[d,...c]=l?l.split(";").map(f=>f.trim()).filter(Boolean):[];i.setContentType([d||"multipart/form-data",...c].join("; "))}}if(O.hasStandardBrowserEnv&&(r&&u.isFunction(r)&&(r=r(t)),r||r!==!1&&Ts(t.url))){const d=s&&o&&Is.read(o);d&&i.set(s,d)}return t},vs=typeof XMLHttpRequest<"u",Rs=vs&&function(e){return new Promise(function(n,r){const s=wn(e);let o=s.data;const i=A.from(s.headers).normalize();let{responseType:a}=s,l;function d(){s.cancelToken&&s.cancelToken.unsubscribe(l),s.signal&&s.signal.removeEventListener("abort",l)}let c=new XMLHttpRequest;c.open(s.method.toUpperCase(),s.url,!0),c.timeout=s.timeout;function f(){if(!c)return;const m=A.from("getAllResponseHeaders"in c&&c.getAllResponseHeaders()),g={data:!a||a==="text"||a==="json"?c.responseText:c.response,status:c.status,statusText:c.statusText,headers:m,config:e,request:c};bn(function(I){n(I),d()},function(I){r(I),d()},g),c=null}"onloadend"in c?c.onloadend=f:c.onreadystatechange=function(){!c||c.readyState!==4||c.status===0&&!(c.responseURL&&c.responseURL.indexOf("file:")===0)||setTimeout(f)},c.onabort=function(){c&&(r(new p("Request aborted",p.ECONNABORTED,s,c)),c=null)},c.onerror=function(){r(new p("Network Error",p.ERR_NETWORK,s,c)),c=null},c.ontimeout=function(){let h=s.timeout?"timeout of "+s.timeout+"ms exceeded":"timeout exceeded";const g=s.transitional||pn;s.timeoutErrorMessage&&(h=s.timeoutErrorMessage),r(new p(h,g.clarifyTimeoutError?p.ETIMEDOUT:p.ECONNABORTED,s,c)),c=null},o===void 0&&i.setContentType(null),"setRequestHeader"in c&&u.forEach(i.toJSON(),function(h,g){c.setRequestHeader(g,h)}),u.isUndefined(s.withCredentials)||(c.withCredentials=!!s.withCredentials),a&&a!=="json"&&(c.responseType=s.responseType),typeof s.onDownloadProgress=="function"&&c.addEventListener("progress",ue(s.onDownloadProgress,!0)),typeof s.onUploadProgress=="function"&&c.upload&&c.upload.addEventListener("progress",ue(s.onUploadProgress)),(s.cancelToken||s.signal)&&(l=m=>{c&&(r(!m||m.type?new J(null,e,c):m),c.abort(),c=null)},s.cancelToken&&s.cancelToken.subscribe(l),s.signal&&(s.signal.aborted?l():s.signal.addEventListener("abort",l)));const b=ws(s.url);if(b&&O.protocols.indexOf(b)===-1){r(new p("Unsupported protocol "+b+":",p.ERR_BAD_REQUEST,e));return}c.send(o||null)})},Os=(e,t)=>{let n=new AbortController,r;const s=function(l){if(!r){r=!0,i();const d=l instanceof Error?l:this.reason;n.abort(d instanceof p?d:new J(d instanceof Error?d.message:d))}};let o=t&&setTimeout(()=>{s(new p(`timeout ${t} of ms exceeded`,p.ETIMEDOUT))},t);const i=()=>{e&&(o&&clearTimeout(o),o=null,e.forEach(l=>{l&&(l.removeEventListener?l.removeEventListener("abort",s):l.unsubscribe(s))}),e=null)};e.forEach(l=>l&&l.addEventListener&&l.addEventListener("abort",s));const{signal:a}=n;return a.unsubscribe=i,[a,()=>{o&&clearTimeout(o),o=null}]},Cs=function*(e,t){let n=e.byteLength;if(!t||n<t){yield e;return}let r=0,s;for(;r<n;)s=r+t,yield e.slice(r,s),r=s},Ds=async function*(e,t,n){for await(const r of e)yield*Cs(ArrayBuffer.isView(r)?r:await n(String(r)),t)},Tt=(e,t,n,r,s)=>{const o=Ds(e,t,s);let i=0;return new ReadableStream({type:"bytes",async pull(a){const{done:l,value:d}=await o.next();if(l){a.close(),r();return}let c=d.byteLength;n&&n(i+=c),a.enqueue(new Uint8Array(d))},cancel(a){return r(a),o.return()}},{highWaterMark:2})},It=(e,t)=>{const n=e!=null;return r=>setTimeout(()=>t({lengthComputable:n,total:e,loaded:r}))},Ee=typeof fetch=="function"&&typeof Request=="function"&&typeof Response=="function",En=Ee&&typeof ReadableStream=="function",Ue=Ee&&(typeof TextEncoder=="function"?(e=>t=>e.encode(t))(new TextEncoder):async e=>new Uint8Array(await new Response(e).arrayBuffer())),ks=En&&(()=>{let e=!1;const t=new Request(O.origin,{body:new ReadableStream,method:"POST",get duplex(){return e=!0,"half"}}).headers.has("Content-Type");return e&&!t})(),At=64*1024,He=En&&!!(()=>{try{return u.isReadableStream(new Response("").body)}catch{}})(),de={stream:He&&(e=>e.body)};Ee&&(e=>{["text","arrayBuffer","blob","formData","stream"].forEach(t=>{!de[t]&&(de[t]=u.isFunction(e[t])?n=>n[t]():(n,r)=>{throw new p(`Response type '${t}' is not supported`,p.ERR_NOT_SUPPORT,r)})})})(new Response);const Ns=async e=>{if(e==null)return 0;if(u.isBlob(e))return e.size;if(u.isSpecCompliantForm(e))return(await new Request(e).arrayBuffer()).byteLength;if(u.isArrayBufferView(e))return e.byteLength;if(u.isURLSearchParams(e)&&(e=e+""),u.isString(e))return(await Ue(e)).byteLength},Ps=async(e,t)=>{const n=u.toFiniteNumber(e.getContentLength());return n??Ns(t)},Bs=Ee&&(async e=>{let{url:t,method:n,data:r,signal:s,cancelToken:o,timeout:i,onDownloadProgress:a,onUploadProgress:l,responseType:d,headers:c,withCredentials:f="same-origin",fetchOptions:b}=wn(e);d=d?(d+"").toLowerCase():"text";let[m,h]=s||o||i?Os([s,o],i):[],g,T;const I=()=>{!g&&setTimeout(()=>{m&&m.unsubscribe()}),g=!0};let N;try{if(l&&ks&&n!=="get"&&n!=="head"&&(N=await Ps(c,r))!==0){let L=new Request(t,{method:"POST",body:r,duplex:"half"}),W;u.isFormData(r)&&(W=L.headers.get("content-type"))&&c.setContentType(W),L.body&&(r=Tt(L.body,At,It(N,ue(l)),null,Ue))}u.isString(f)||(f=f?"cors":"omit"),T=new Request(t,{...b,signal:m,method:n.toUpperCase(),headers:c.normalize().toJSON(),body:r,duplex:"half",withCredentials:f});let w=await fetch(T);const Y=He&&(d==="stream"||d==="response");if(He&&(a||Y)){const L={};["status","statusText","headers"].forEach(pt=>{L[pt]=w[pt]});const W=u.toFiniteNumber(w.headers.get("content-length"));w=new Response(Tt(w.body,At,a&&It(W,ue(a,!0)),Y&&I,Ue),L)}d=d||"text";let fr=await de[u.findKey(de,d)||"text"](w,e);return!Y&&I(),h&&h(),await new Promise((L,W)=>{bn(L,W,{data:fr,headers:A.from(w.headers),status:w.status,statusText:w.statusText,config:e,request:T})})}catch(w){throw I(),w&&w.name==="TypeError"&&/fetch/i.test(w.message)?Object.assign(new p("Network Error",p.ERR_NETWORK,e,T),{cause:w.cause||w}):p.from(w,w&&w.code,e,T)}}),qe={http:Xr,xhr:Rs,fetch:Bs};u.forEach(qe,(e,t)=>{if(e){try{Object.defineProperty(e,"name",{value:t})}catch{}Object.defineProperty(e,"adapterName",{value:t})}});const _t=e=>`- ${e}`,Ms=e=>u.isFunction(e)||e===null||e===!1,Sn={getAdapter:e=>{e=u.isArray(e)?e:[e];const{length:t}=e;let n,r;const s={};for(let o=0;o<t;o++){n=e[o];let i;if(r=n,!Ms(n)&&(r=qe[(i=String(n)).toLowerCase()],r===void 0))throw new p(`Unknown adapter '${i}'`);if(r)break;s[i||"#"+o]=r}if(!r){const o=Object.entries(s).map(([a,l])=>`adapter ${a} `+(l===!1?"is not supported by the environment":"is not available in the build"));let i=t?o.length>1?`since :
`+o.map(_t).join(`
`):" "+_t(o[0]):"as no adapter specified";throw new p("There is no suitable adapter to dispatch the request "+i,"ERR_NOT_SUPPORT")}return r},adapters:qe};function Re(e){if(e.cancelToken&&e.cancelToken.throwIfRequested(),e.signal&&e.signal.aborted)throw new J(null,e)}function vt(e){return Re(e),e.headers=A.from(e.headers),e.data=ve.call(e,e.transformRequest),["post","put","patch"].indexOf(e.method)!==-1&&e.headers.setContentType("application/x-www-form-urlencoded",!1),Sn.getAdapter(e.adapter||se.adapter)(e).then(function(r){return Re(e),r.data=ve.call(e,e.transformResponse,r),r.headers=A.from(r.headers),r},function(r){return gn(r)||(Re(e),r&&r.response&&(r.response.data=ve.call(e,e.transformResponse,r.response),r.response.headers=A.from(r.response.headers))),Promise.reject(r)})}const Tn="1.7.2",tt={};["object","boolean","number","function","string","symbol"].forEach((e,t)=>{tt[e]=function(r){return typeof r===e||"a"+(t<1?"n ":" ")+e}});const Rt={};tt.transitional=function(t,n,r){function s(o,i){return"[Axios v"+Tn+"] Transitional option '"+o+"'"+i+(r?". "+r:"")}return(o,i,a)=>{if(t===!1)throw new p(s(i," has been removed"+(n?" in "+n:"")),p.ERR_DEPRECATED);return n&&!Rt[i]&&(Rt[i]=!0,console.warn(s(i," has been deprecated since v"+n+" and will be removed in the near future"))),t?t(o,i,a):!0}};function Fs(e,t,n){if(typeof e!="object")throw new p("options must be an object",p.ERR_BAD_OPTION_VALUE);const r=Object.keys(e);let s=r.length;for(;s-- >0;){const o=r[s],i=t[o];if(i){const a=e[o],l=a===void 0||i(a,o,e);if(l!==!0)throw new p("option "+o+" must be "+l,p.ERR_BAD_OPTION_VALUE);continue}if(n!==!0)throw new p("Unknown option "+o,p.ERR_BAD_OPTION)}}const Ve={assertOptions:Fs,validators:tt},x=Ve.validators;class H{constructor(t){this.defaults=t,this.interceptors={request:new wt,response:new wt}}async request(t,n){try{return await this._request(t,n)}catch(r){if(r instanceof Error){let s;Error.captureStackTrace?Error.captureStackTrace(s={}):s=new Error;const o=s.stack?s.stack.replace(/^.+\n/,""):"";try{r.stack?o&&!String(r.stack).endsWith(o.replace(/^.+\n.+\n/,""))&&(r.stack+=`
`+o):r.stack=o}catch{}}throw r}}_request(t,n){typeof t=="string"?(n=n||{},n.url=t):n=t||{},n=q(this.defaults,n);const{transitional:r,paramsSerializer:s,headers:o}=n;r!==void 0&&Ve.assertOptions(r,{silentJSONParsing:x.transitional(x.boolean),forcedJSONParsing:x.transitional(x.boolean),clarifyTimeoutError:x.transitional(x.boolean)},!1),s!=null&&(u.isFunction(s)?n.paramsSerializer={serialize:s}:Ve.assertOptions(s,{encode:x.function,serialize:x.function},!0)),n.method=(n.method||this.defaults.method||"get").toLowerCase();let i=o&&u.merge(o.common,o[n.method]);o&&u.forEach(["delete","get","head","post","put","patch","common"],h=>{delete o[h]}),n.headers=A.concat(i,o);const a=[];let l=!0;this.interceptors.request.forEach(function(g){typeof g.runWhen=="function"&&g.runWhen(n)===!1||(l=l&&g.synchronous,a.unshift(g.fulfilled,g.rejected))});const d=[];this.interceptors.response.forEach(function(g){d.push(g.fulfilled,g.rejected)});let c,f=0,b;if(!l){const h=[vt.bind(this),void 0];for(h.unshift.apply(h,a),h.push.apply(h,d),b=h.length,c=Promise.resolve(n);f<b;)c=c.then(h[f++],h[f++]);return c}b=a.length;let m=n;for(f=0;f<b;){const h=a[f++],g=a[f++];try{m=h(m)}catch(T){g.call(this,T);break}}try{c=vt.call(this,m)}catch(h){return Promise.reject(h)}for(f=0,b=d.length;f<b;)c=c.then(d[f++],d[f++]);return c}getUri(t){t=q(this.defaults,t);const n=yn(t.baseURL,t.url);return hn(n,t.params,t.paramsSerializer)}}u.forEach(["delete","get","head","options"],function(t){H.prototype[t]=function(n,r){return this.request(q(r||{},{method:t,url:n,data:(r||{}).data}))}});u.forEach(["post","put","patch"],function(t){function n(r){return function(o,i,a){return this.request(q(a||{},{method:t,headers:r?{"Content-Type":"multipart/form-data"}:{},url:o,data:i}))}}H.prototype[t]=n(),H.prototype[t+"Form"]=n(!0)});class nt{constructor(t){if(typeof t!="function")throw new TypeError("executor must be a function.");let n;this.promise=new Promise(function(o){n=o});const r=this;this.promise.then(s=>{if(!r._listeners)return;let o=r._listeners.length;for(;o-- >0;)r._listeners[o](s);r._listeners=null}),this.promise.then=s=>{let o;const i=new Promise(a=>{r.subscribe(a),o=a}).then(s);return i.cancel=function(){r.unsubscribe(o)},i},t(function(o,i,a){r.reason||(r.reason=new J(o,i,a),n(r.reason))})}throwIfRequested(){if(this.reason)throw this.reason}subscribe(t){if(this.reason){t(this.reason);return}this._listeners?this._listeners.push(t):this._listeners=[t]}unsubscribe(t){if(!this._listeners)return;const n=this._listeners.indexOf(t);n!==-1&&this._listeners.splice(n,1)}static source(){let t;return{token:new nt(function(s){t=s}),cancel:t}}}function Ls(e){return function(n){return e.apply(null,n)}}function xs(e){return u.isObject(e)&&e.isAxiosError===!0}const ze={Continue:100,SwitchingProtocols:101,Processing:102,EarlyHints:103,Ok:200,Created:201,Accepted:202,NonAuthoritativeInformation:203,NoContent:204,ResetContent:205,PartialContent:206,MultiStatus:207,AlreadyReported:208,ImUsed:226,MultipleChoices:300,MovedPermanently:301,Found:302,SeeOther:303,NotModified:304,UseProxy:305,Unused:306,TemporaryRedirect:307,PermanentRedirect:308,BadRequest:400,Unauthorized:401,PaymentRequired:402,Forbidden:403,NotFound:404,MethodNotAllowed:405,NotAcceptable:406,ProxyAuthenticationRequired:407,RequestTimeout:408,Conflict:409,Gone:410,LengthRequired:411,PreconditionFailed:412,PayloadTooLarge:413,UriTooLong:414,UnsupportedMediaType:415,RangeNotSatisfiable:416,ExpectationFailed:417,ImATeapot:418,MisdirectedRequest:421,UnprocessableEntity:422,Locked:423,FailedDependency:424,TooEarly:425,UpgradeRequired:426,PreconditionRequired:428,TooManyRequests:429,RequestHeaderFieldsTooLarge:431,UnavailableForLegalReasons:451,InternalServerError:500,NotImplemented:501,BadGateway:502,ServiceUnavailable:503,GatewayTimeout:504,HttpVersionNotSupported:505,VariantAlsoNegotiates:506,InsufficientStorage:507,LoopDetected:508,NotExtended:510,NetworkAuthenticationRequired:511};Object.entries(ze).forEach(([e,t])=>{ze[t]=e});function In(e){const t=new H(e),n=en(H.prototype.request,t);return u.extend(n,H.prototype,t,{allOwnKeys:!0}),u.extend(n,t,null,{allOwnKeys:!0}),n.create=function(s){return In(q(e,s))},n}const E=In(se);E.Axios=H;E.CanceledError=J;E.CancelToken=nt;E.isCancel=gn;E.VERSION=Tn;E.toFormData=we;E.AxiosError=p;E.Cancel=E.CanceledError;E.all=function(t){return Promise.all(t)};E.spread=Ls;E.isAxiosError=xs;E.mergeConfig=q;E.AxiosHeaders=A;E.formToJSON=e=>mn(u.isHTMLForm(e)?new FormData(e):e);E.getAdapter=Sn.getAdapter;E.HttpStatusCode=ze;E.default=E;window.axios=E;window.axios.defaults.headers.common["X-Requested-With"]="XMLHttpRequest";const $s=()=>{};var Ot={};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const An=function(e){const t=[];let n=0;for(let r=0;r<e.length;r++){let s=e.charCodeAt(r);s<128?t[n++]=s:s<2048?(t[n++]=s>>6|192,t[n++]=s&63|128):(s&64512)===55296&&r+1<e.length&&(e.charCodeAt(r+1)&64512)===56320?(s=65536+((s&1023)<<10)+(e.charCodeAt(++r)&1023),t[n++]=s>>18|240,t[n++]=s>>12&63|128,t[n++]=s>>6&63|128,t[n++]=s&63|128):(t[n++]=s>>12|224,t[n++]=s>>6&63|128,t[n++]=s&63|128)}return t},js=function(e){const t=[];let n=0,r=0;for(;n<e.length;){const s=e[n++];if(s<128)t[r++]=String.fromCharCode(s);else if(s>191&&s<224){const o=e[n++];t[r++]=String.fromCharCode((s&31)<<6|o&63)}else if(s>239&&s<365){const o=e[n++],i=e[n++],a=e[n++],l=((s&7)<<18|(o&63)<<12|(i&63)<<6|a&63)-65536;t[r++]=String.fromCharCode(55296+(l>>10)),t[r++]=String.fromCharCode(56320+(l&1023))}else{const o=e[n++],i=e[n++];t[r++]=String.fromCharCode((s&15)<<12|(o&63)<<6|i&63)}}return t.join("")},_n={byteToCharMap_:null,charToByteMap_:null,byteToCharMapWebSafe_:null,charToByteMapWebSafe_:null,ENCODED_VALS_BASE:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",get ENCODED_VALS(){return this.ENCODED_VALS_BASE+"+/="},get ENCODED_VALS_WEBSAFE(){return this.ENCODED_VALS_BASE+"-_."},HAS_NATIVE_SUPPORT:typeof atob=="function",encodeByteArray(e,t){if(!Array.isArray(e))throw Error("encodeByteArray takes an array as a parameter");this.init_();const n=t?this.byteToCharMapWebSafe_:this.byteToCharMap_,r=[];for(let s=0;s<e.length;s+=3){const o=e[s],i=s+1<e.length,a=i?e[s+1]:0,l=s+2<e.length,d=l?e[s+2]:0,c=o>>2,f=(o&3)<<4|a>>4;let b=(a&15)<<2|d>>6,m=d&63;l||(m=64,i||(b=64)),r.push(n[c],n[f],n[b],n[m])}return r.join("")},encodeString(e,t){return this.HAS_NATIVE_SUPPORT&&!t?btoa(e):this.encodeByteArray(An(e),t)},decodeString(e,t){return this.HAS_NATIVE_SUPPORT&&!t?atob(e):js(this.decodeStringToByteArray(e,t))},decodeStringToByteArray(e,t){this.init_();const n=t?this.charToByteMapWebSafe_:this.charToByteMap_,r=[];for(let s=0;s<e.length;){const o=n[e.charAt(s++)],a=s<e.length?n[e.charAt(s)]:0;++s;const d=s<e.length?n[e.charAt(s)]:64;++s;const f=s<e.length?n[e.charAt(s)]:64;if(++s,o==null||a==null||d==null||f==null)throw new Us;const b=o<<2|a>>4;if(r.push(b),d!==64){const m=a<<4&240|d>>2;if(r.push(m),f!==64){const h=d<<6&192|f;r.push(h)}}}return r},init_(){if(!this.byteToCharMap_){this.byteToCharMap_={},this.charToByteMap_={},this.byteToCharMapWebSafe_={},this.charToByteMapWebSafe_={};for(let e=0;e<this.ENCODED_VALS.length;e++)this.byteToCharMap_[e]=this.ENCODED_VALS.charAt(e),this.charToByteMap_[this.byteToCharMap_[e]]=e,this.byteToCharMapWebSafe_[e]=this.ENCODED_VALS_WEBSAFE.charAt(e),this.charToByteMapWebSafe_[this.byteToCharMapWebSafe_[e]]=e,e>=this.ENCODED_VALS_BASE.length&&(this.charToByteMap_[this.ENCODED_VALS_WEBSAFE.charAt(e)]=e,this.charToByteMapWebSafe_[this.ENCODED_VALS.charAt(e)]=e)}}};class Us extends Error{constructor(){super(...arguments),this.name="DecodeBase64StringError"}}const Hs=function(e){const t=An(e);return _n.encodeByteArray(t,!0)},vn=function(e){return Hs(e).replace(/\./g,"")},qs=function(e){try{return _n.decodeString(e,!0)}catch(t){console.error("base64Decode failed: ",t)}return null};/**
 * @license
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Vs(){if(typeof self<"u")return self;if(typeof window<"u")return window;if(typeof global<"u")return global;throw new Error("Unable to locate global object.")}/**
 * @license
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const zs=()=>Vs().__FIREBASE_DEFAULTS__,Ks=()=>{if(typeof process>"u"||typeof Ot>"u")return;const e=Ot.__FIREBASE_DEFAULTS__;if(e)return JSON.parse(e)},Ws=()=>{if(typeof document>"u")return;let e;try{e=document.cookie.match(/__FIREBASE_DEFAULTS__=([^;]+)/)}catch{return}const t=e&&qs(e[1]);return t&&JSON.parse(t)},Gs=()=>{try{return $s()||zs()||Ks()||Ws()}catch(e){console.info(`Unable to get __FIREBASE_DEFAULTS__ due to: ${e}`);return}},Rn=()=>{var e;return(e=Gs())===null||e===void 0?void 0:e.config};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Js{constructor(){this.reject=()=>{},this.resolve=()=>{},this.promise=new Promise((t,n)=>{this.resolve=t,this.reject=n})}wrapCallback(t){return(n,r)=>{n?this.reject(n):this.resolve(r),typeof t=="function"&&(this.promise.catch(()=>{}),t.length===1?t(n):t(n,r))}}}function Ys(){const e=typeof chrome=="object"?chrome.runtime:typeof browser=="object"?browser.runtime:void 0;return typeof e=="object"&&e.id!==void 0}function rt(){try{return typeof indexedDB=="object"}catch{return!1}}function st(){return new Promise((e,t)=>{try{let n=!0;const r="validate-browser-context-for-indexeddb-analytics-module",s=self.indexedDB.open(r);s.onsuccess=()=>{s.result.close(),n||self.indexedDB.deleteDatabase(r),e(!0)},s.onupgradeneeded=()=>{n=!1},s.onerror=()=>{var o;t(((o=s.error)===null||o===void 0?void 0:o.message)||"")}}catch(n){t(n)}})}function On(){return!(typeof navigator>"u"||!navigator.cookieEnabled)}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Xs="FirebaseError";class K extends Error{constructor(t,n,r){super(n),this.code=t,this.customData=r,this.name=Xs,Object.setPrototypeOf(this,K.prototype),Error.captureStackTrace&&Error.captureStackTrace(this,oe.prototype.create)}}class oe{constructor(t,n,r){this.service=t,this.serviceName=n,this.errors=r}create(t,...n){const r=n[0]||{},s=`${this.service}/${t}`,o=this.errors[t],i=o?Qs(o,r):"Error",a=`${this.serviceName}: ${i} (${s}).`;return new K(s,a,r)}}function Qs(e,t){return e.replace(Zs,(n,r)=>{const s=t[r];return s!=null?String(s):`<${r}?>`})}const Zs=/\{\$([^}]+)}/g;function fe(e,t){if(e===t)return!0;const n=Object.keys(e),r=Object.keys(t);for(const s of n){if(!r.includes(s))return!1;const o=e[s],i=t[s];if(Ct(o)&&Ct(i)){if(!fe(o,i))return!1}else if(o!==i)return!1}for(const s of r)if(!n.includes(s))return!1;return!0}function Ct(e){return e!==null&&typeof e=="object"}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const eo=1e3,to=2,no=4*60*60*1e3,ro=.5;function Dt(e,t=eo,n=to){const r=t*Math.pow(n,e),s=Math.round(ro*r*(Math.random()-.5)*2);return Math.min(no,r+s)}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function ie(e){return e&&e._delegate?e._delegate:e}class k{constructor(t,n,r){this.name=t,this.instanceFactory=n,this.type=r,this.multipleInstances=!1,this.serviceProps={},this.instantiationMode="LAZY",this.onInstanceCreated=null}setInstantiationMode(t){return this.instantiationMode=t,this}setMultipleInstances(t){return this.multipleInstances=t,this}setServiceProps(t){return this.serviceProps=t,this}setInstanceCreatedCallback(t){return this.onInstanceCreated=t,this}}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const j="[DEFAULT]";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class so{constructor(t,n){this.name=t,this.container=n,this.component=null,this.instances=new Map,this.instancesDeferred=new Map,this.instancesOptions=new Map,this.onInitCallbacks=new Map}get(t){const n=this.normalizeInstanceIdentifier(t);if(!this.instancesDeferred.has(n)){const r=new Js;if(this.instancesDeferred.set(n,r),this.isInitialized(n)||this.shouldAutoInitialize())try{const s=this.getOrInitializeService({instanceIdentifier:n});s&&r.resolve(s)}catch{}}return this.instancesDeferred.get(n).promise}getImmediate(t){var n;const r=this.normalizeInstanceIdentifier(t==null?void 0:t.identifier),s=(n=t==null?void 0:t.optional)!==null&&n!==void 0?n:!1;if(this.isInitialized(r)||this.shouldAutoInitialize())try{return this.getOrInitializeService({instanceIdentifier:r})}catch(o){if(s)return null;throw o}else{if(s)return null;throw Error(`Service ${this.name} is not available`)}}getComponent(){return this.component}setComponent(t){if(t.name!==this.name)throw Error(`Mismatching Component ${t.name} for Provider ${this.name}.`);if(this.component)throw Error(`Component for ${this.name} has already been provided`);if(this.component=t,!!this.shouldAutoInitialize()){if(io(t))try{this.getOrInitializeService({instanceIdentifier:j})}catch{}for(const[n,r]of this.instancesDeferred.entries()){const s=this.normalizeInstanceIdentifier(n);try{const o=this.getOrInitializeService({instanceIdentifier:s});r.resolve(o)}catch{}}}}clearInstance(t=j){this.instancesDeferred.delete(t),this.instancesOptions.delete(t),this.instances.delete(t)}async delete(){const t=Array.from(this.instances.values());await Promise.all([...t.filter(n=>"INTERNAL"in n).map(n=>n.INTERNAL.delete()),...t.filter(n=>"_delete"in n).map(n=>n._delete())])}isComponentSet(){return this.component!=null}isInitialized(t=j){return this.instances.has(t)}getOptions(t=j){return this.instancesOptions.get(t)||{}}initialize(t={}){const{options:n={}}=t,r=this.normalizeInstanceIdentifier(t.instanceIdentifier);if(this.isInitialized(r))throw Error(`${this.name}(${r}) has already been initialized`);if(!this.isComponentSet())throw Error(`Component ${this.name} has not been registered yet`);const s=this.getOrInitializeService({instanceIdentifier:r,options:n});for(const[o,i]of this.instancesDeferred.entries()){const a=this.normalizeInstanceIdentifier(o);r===a&&i.resolve(s)}return s}onInit(t,n){var r;const s=this.normalizeInstanceIdentifier(n),o=(r=this.onInitCallbacks.get(s))!==null&&r!==void 0?r:new Set;o.add(t),this.onInitCallbacks.set(s,o);const i=this.instances.get(s);return i&&t(i,s),()=>{o.delete(t)}}invokeOnInitCallbacks(t,n){const r=this.onInitCallbacks.get(n);if(r)for(const s of r)try{s(t,n)}catch{}}getOrInitializeService({instanceIdentifier:t,options:n={}}){let r=this.instances.get(t);if(!r&&this.component&&(r=this.component.instanceFactory(this.container,{instanceIdentifier:oo(t),options:n}),this.instances.set(t,r),this.instancesOptions.set(t,n),this.invokeOnInitCallbacks(r,t),this.component.onInstanceCreated))try{this.component.onInstanceCreated(this.container,t,r)}catch{}return r||null}normalizeInstanceIdentifier(t=j){return this.component?this.component.multipleInstances?t:j:t}shouldAutoInitialize(){return!!this.component&&this.component.instantiationMode!=="EXPLICIT"}}function oo(e){return e===j?void 0:e}function io(e){return e.instantiationMode==="EAGER"}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class ao{constructor(t){this.name=t,this.providers=new Map}addComponent(t){const n=this.getProvider(t.name);if(n.isComponentSet())throw new Error(`Component ${t.name} has already been registered with ${this.name}`);n.setComponent(t)}addOrOverwriteComponent(t){this.getProvider(t.name).isComponentSet()&&this.providers.delete(t.name),this.addComponent(t)}getProvider(t){if(this.providers.has(t))return this.providers.get(t);const n=new so(t,this);return this.providers.set(t,n),n}getProviders(){return Array.from(this.providers.values())}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */var y;(function(e){e[e.DEBUG=0]="DEBUG",e[e.VERBOSE=1]="VERBOSE",e[e.INFO=2]="INFO",e[e.WARN=3]="WARN",e[e.ERROR=4]="ERROR",e[e.SILENT=5]="SILENT"})(y||(y={}));const co={debug:y.DEBUG,verbose:y.VERBOSE,info:y.INFO,warn:y.WARN,error:y.ERROR,silent:y.SILENT},lo=y.INFO,uo={[y.DEBUG]:"log",[y.VERBOSE]:"log",[y.INFO]:"info",[y.WARN]:"warn",[y.ERROR]:"error"},fo=(e,t,...n)=>{if(t<e.logLevel)return;const r=new Date().toISOString(),s=uo[t];if(s)console[s](`[${r}]  ${e.name}:`,...n);else throw new Error(`Attempted to log a message with an invalid logType (value: ${t})`)};class Cn{constructor(t){this.name=t,this._logLevel=lo,this._logHandler=fo,this._userLogHandler=null}get logLevel(){return this._logLevel}set logLevel(t){if(!(t in y))throw new TypeError(`Invalid value "${t}" assigned to \`logLevel\``);this._logLevel=t}setLogLevel(t){this._logLevel=typeof t=="string"?co[t]:t}get logHandler(){return this._logHandler}set logHandler(t){if(typeof t!="function")throw new TypeError("Value assigned to `logHandler` must be a function");this._logHandler=t}get userLogHandler(){return this._userLogHandler}set userLogHandler(t){this._userLogHandler=t}debug(...t){this._userLogHandler&&this._userLogHandler(this,y.DEBUG,...t),this._logHandler(this,y.DEBUG,...t)}log(...t){this._userLogHandler&&this._userLogHandler(this,y.VERBOSE,...t),this._logHandler(this,y.VERBOSE,...t)}info(...t){this._userLogHandler&&this._userLogHandler(this,y.INFO,...t),this._logHandler(this,y.INFO,...t)}warn(...t){this._userLogHandler&&this._userLogHandler(this,y.WARN,...t),this._logHandler(this,y.WARN,...t)}error(...t){this._userLogHandler&&this._userLogHandler(this,y.ERROR,...t),this._logHandler(this,y.ERROR,...t)}}const ho=(e,t)=>t.some(n=>e instanceof n);let kt,Nt;function po(){return kt||(kt=[IDBDatabase,IDBObjectStore,IDBIndex,IDBCursor,IDBTransaction])}function mo(){return Nt||(Nt=[IDBCursor.prototype.advance,IDBCursor.prototype.continue,IDBCursor.prototype.continuePrimaryKey])}const Dn=new WeakMap,Ke=new WeakMap,kn=new WeakMap,Oe=new WeakMap,ot=new WeakMap;function go(e){const t=new Promise((n,r)=>{const s=()=>{e.removeEventListener("success",o),e.removeEventListener("error",i)},o=()=>{n(B(e.result)),s()},i=()=>{r(e.error),s()};e.addEventListener("success",o),e.addEventListener("error",i)});return t.then(n=>{n instanceof IDBCursor&&Dn.set(n,e)}).catch(()=>{}),ot.set(t,e),t}function bo(e){if(Ke.has(e))return;const t=new Promise((n,r)=>{const s=()=>{e.removeEventListener("complete",o),e.removeEventListener("error",i),e.removeEventListener("abort",i)},o=()=>{n(),s()},i=()=>{r(e.error||new DOMException("AbortError","AbortError")),s()};e.addEventListener("complete",o),e.addEventListener("error",i),e.addEventListener("abort",i)});Ke.set(e,t)}let We={get(e,t,n){if(e instanceof IDBTransaction){if(t==="done")return Ke.get(e);if(t==="objectStoreNames")return e.objectStoreNames||kn.get(e);if(t==="store")return n.objectStoreNames[1]?void 0:n.objectStore(n.objectStoreNames[0])}return B(e[t])},set(e,t,n){return e[t]=n,!0},has(e,t){return e instanceof IDBTransaction&&(t==="done"||t==="store")?!0:t in e}};function yo(e){We=e(We)}function wo(e){return e===IDBDatabase.prototype.transaction&&!("objectStoreNames"in IDBTransaction.prototype)?function(t,...n){const r=e.call(Ce(this),t,...n);return kn.set(r,t.sort?t.sort():[t]),B(r)}:mo().includes(e)?function(...t){return e.apply(Ce(this),t),B(Dn.get(this))}:function(...t){return B(e.apply(Ce(this),t))}}function Eo(e){return typeof e=="function"?wo(e):(e instanceof IDBTransaction&&bo(e),ho(e,po())?new Proxy(e,We):e)}function B(e){if(e instanceof IDBRequest)return go(e);if(Oe.has(e))return Oe.get(e);const t=Eo(e);return t!==e&&(Oe.set(e,t),ot.set(t,e)),t}const Ce=e=>ot.get(e);function Se(e,t,{blocked:n,upgrade:r,blocking:s,terminated:o}={}){const i=indexedDB.open(e,t),a=B(i);return r&&i.addEventListener("upgradeneeded",l=>{r(B(i.result),l.oldVersion,l.newVersion,B(i.transaction),l)}),n&&i.addEventListener("blocked",l=>n(l.oldVersion,l.newVersion,l)),a.then(l=>{o&&l.addEventListener("close",()=>o()),s&&l.addEventListener("versionchange",d=>s(d.oldVersion,d.newVersion,d))}).catch(()=>{}),a}function De(e,{blocked:t}={}){const n=indexedDB.deleteDatabase(e);return t&&n.addEventListener("blocked",r=>t(r.oldVersion,r)),B(n).then(()=>{})}const So=["get","getKey","getAll","getAllKeys","count"],To=["put","add","delete","clear"],ke=new Map;function Pt(e,t){if(!(e instanceof IDBDatabase&&!(t in e)&&typeof t=="string"))return;if(ke.get(t))return ke.get(t);const n=t.replace(/FromIndex$/,""),r=t!==n,s=To.includes(n);if(!(n in(r?IDBIndex:IDBObjectStore).prototype)||!(s||So.includes(n)))return;const o=async function(i,...a){const l=this.transaction(i,s?"readwrite":"readonly");let d=l.store;return r&&(d=d.index(a.shift())),(await Promise.all([d[n](...a),s&&l.done]))[0]};return ke.set(t,o),o}yo(e=>({...e,get:(t,n,r)=>Pt(t,n)||e.get(t,n,r),has:(t,n)=>!!Pt(t,n)||e.has(t,n)}));/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Io{constructor(t){this.container=t}getPlatformInfoString(){return this.container.getProviders().map(n=>{if(Ao(n)){const r=n.getImmediate();return`${r.library}/${r.version}`}else return null}).filter(n=>n).join(" ")}}function Ao(e){const t=e.getComponent();return(t==null?void 0:t.type)==="VERSION"}const Ge="@firebase/app",Bt="0.12.1";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const M=new Cn("@firebase/app"),_o="@firebase/app-compat",vo="@firebase/analytics-compat",Ro="@firebase/analytics",Oo="@firebase/app-check-compat",Co="@firebase/app-check",Do="@firebase/auth",ko="@firebase/auth-compat",No="@firebase/database",Po="@firebase/data-connect",Bo="@firebase/database-compat",Mo="@firebase/functions",Fo="@firebase/functions-compat",Lo="@firebase/installations",xo="@firebase/installations-compat",$o="@firebase/messaging",jo="@firebase/messaging-compat",Uo="@firebase/performance",Ho="@firebase/performance-compat",qo="@firebase/remote-config",Vo="@firebase/remote-config-compat",zo="@firebase/storage",Ko="@firebase/storage-compat",Wo="@firebase/firestore",Go="@firebase/vertexai",Jo="@firebase/firestore-compat",Yo="firebase";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Je="[DEFAULT]",Xo={[Ge]:"fire-core",[_o]:"fire-core-compat",[Ro]:"fire-analytics",[vo]:"fire-analytics-compat",[Co]:"fire-app-check",[Oo]:"fire-app-check-compat",[Do]:"fire-auth",[ko]:"fire-auth-compat",[No]:"fire-rtdb",[Po]:"fire-data-connect",[Bo]:"fire-rtdb-compat",[Mo]:"fire-fn",[Fo]:"fire-fn-compat",[Lo]:"fire-iid",[xo]:"fire-iid-compat",[$o]:"fire-fcm",[jo]:"fire-fcm-compat",[Uo]:"fire-perf",[Ho]:"fire-perf-compat",[qo]:"fire-rc",[Vo]:"fire-rc-compat",[zo]:"fire-gcs",[Ko]:"fire-gcs-compat",[Wo]:"fire-fst",[Jo]:"fire-fst-compat",[Go]:"fire-vertex","fire-js":"fire-js",[Yo]:"fire-js-all"};/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const he=new Map,Qo=new Map,Ye=new Map;function Mt(e,t){try{e.container.addComponent(t)}catch(n){M.debug(`Component ${t.name} failed to register with FirebaseApp ${e.name}`,n)}}function F(e){const t=e.name;if(Ye.has(t))return M.debug(`There were multiple attempts to register component ${t}.`),!1;Ye.set(t,e);for(const n of he.values())Mt(n,e);for(const n of Qo.values())Mt(n,e);return!0}function ae(e,t){const n=e.container.getProvider("heartbeat").getImmediate({optional:!0});return n&&n.triggerHeartbeat(),e.container.getProvider(t)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Zo={"no-app":"No Firebase App '{$appName}' has been created - call initializeApp() first","bad-app-name":"Illegal App name: '{$appName}'","duplicate-app":"Firebase App named '{$appName}' already exists with different options or config","app-deleted":"Firebase App named '{$appName}' already deleted","server-app-deleted":"Firebase Server App has been deleted","no-options":"Need to provide options, when not being deployed to hosting via source.","invalid-app-argument":"firebase.{$appName}() takes either no argument or a Firebase App instance.","invalid-log-argument":"First argument to `onLog` must be null or a function.","idb-open":"Error thrown when opening IndexedDB. Original error: {$originalErrorMessage}.","idb-get":"Error thrown when reading from IndexedDB. Original error: {$originalErrorMessage}.","idb-set":"Error thrown when writing to IndexedDB. Original error: {$originalErrorMessage}.","idb-delete":"Error thrown when deleting from IndexedDB. Original error: {$originalErrorMessage}.","finalization-registry-not-supported":"FirebaseServerApp deleteOnDeref field defined but the JS runtime does not support FinalizationRegistry.","invalid-server-app-environment":"FirebaseServerApp is not for use in browser environments."},$=new oe("app","Firebase",Zo);/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class ei{constructor(t,n,r){this._isDeleted=!1,this._options=Object.assign({},t),this._config=Object.assign({},n),this._name=n.name,this._automaticDataCollectionEnabled=n.automaticDataCollectionEnabled,this._container=r,this.container.addComponent(new k("app",()=>this,"PUBLIC"))}get automaticDataCollectionEnabled(){return this.checkDestroyed(),this._automaticDataCollectionEnabled}set automaticDataCollectionEnabled(t){this.checkDestroyed(),this._automaticDataCollectionEnabled=t}get name(){return this.checkDestroyed(),this._name}get options(){return this.checkDestroyed(),this._options}get config(){return this.checkDestroyed(),this._config}get container(){return this._container}get isDeleted(){return this._isDeleted}set isDeleted(t){this._isDeleted=t}checkDestroyed(){if(this.isDeleted)throw $.create("app-deleted",{appName:this._name})}}function Nn(e,t={}){let n=e;typeof t!="object"&&(t={name:t});const r=Object.assign({name:Je,automaticDataCollectionEnabled:!1},t),s=r.name;if(typeof s!="string"||!s)throw $.create("bad-app-name",{appName:String(s)});if(n||(n=Rn()),!n)throw $.create("no-options");const o=he.get(s);if(o){if(fe(n,o.options)&&fe(r,o.config))return o;throw $.create("duplicate-app",{appName:s})}const i=new ao(s);for(const l of Ye.values())i.addComponent(l);const a=new ei(n,r,i);return he.set(s,a),a}function Pn(e=Je){const t=he.get(e);if(!t&&e===Je&&Rn())return Nn();if(!t)throw $.create("no-app",{appName:e});return t}function D(e,t,n){var r;let s=(r=Xo[e])!==null&&r!==void 0?r:e;n&&(s+=`-${n}`);const o=s.match(/\s|\//),i=t.match(/\s|\//);if(o||i){const a=[`Unable to register library "${s}" with version "${t}":`];o&&a.push(`library name "${s}" contains illegal characters (whitespace or "/")`),o&&i&&a.push("and"),i&&a.push(`version name "${t}" contains illegal characters (whitespace or "/")`),M.warn(a.join(" "));return}F(new k(`${s}-version`,()=>({library:s,version:t}),"VERSION"))}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ti="firebase-heartbeat-database",ni=1,ee="firebase-heartbeat-store";let Ne=null;function Bn(){return Ne||(Ne=Se(ti,ni,{upgrade:(e,t)=>{switch(t){case 0:try{e.createObjectStore(ee)}catch(n){console.warn(n)}}}}).catch(e=>{throw $.create("idb-open",{originalErrorMessage:e.message})})),Ne}async function ri(e){try{const n=(await Bn()).transaction(ee),r=await n.objectStore(ee).get(Mn(e));return await n.done,r}catch(t){if(t instanceof K)M.warn(t.message);else{const n=$.create("idb-get",{originalErrorMessage:t==null?void 0:t.message});M.warn(n.message)}}}async function Ft(e,t){try{const r=(await Bn()).transaction(ee,"readwrite");await r.objectStore(ee).put(t,Mn(e)),await r.done}catch(n){if(n instanceof K)M.warn(n.message);else{const r=$.create("idb-set",{originalErrorMessage:n==null?void 0:n.message});M.warn(r.message)}}}function Mn(e){return`${e.name}!${e.options.appId}`}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const si=1024,oi=30;class ii{constructor(t){this.container=t,this._heartbeatsCache=null;const n=this.container.getProvider("app").getImmediate();this._storage=new ci(n),this._heartbeatsCachePromise=this._storage.read().then(r=>(this._heartbeatsCache=r,r))}async triggerHeartbeat(){var t,n;try{const s=this.container.getProvider("platform-logger").getImmediate().getPlatformInfoString(),o=Lt();if(((t=this._heartbeatsCache)===null||t===void 0?void 0:t.heartbeats)==null&&(this._heartbeatsCache=await this._heartbeatsCachePromise,((n=this._heartbeatsCache)===null||n===void 0?void 0:n.heartbeats)==null)||this._heartbeatsCache.lastSentHeartbeatDate===o||this._heartbeatsCache.heartbeats.some(i=>i.date===o))return;if(this._heartbeatsCache.heartbeats.push({date:o,agent:s}),this._heartbeatsCache.heartbeats.length>oi){const i=li(this._heartbeatsCache.heartbeats);this._heartbeatsCache.heartbeats.splice(i,1)}return this._storage.overwrite(this._heartbeatsCache)}catch(r){M.warn(r)}}async getHeartbeatsHeader(){var t;try{if(this._heartbeatsCache===null&&await this._heartbeatsCachePromise,((t=this._heartbeatsCache)===null||t===void 0?void 0:t.heartbeats)==null||this._heartbeatsCache.heartbeats.length===0)return"";const n=Lt(),{heartbeatsToSend:r,unsentEntries:s}=ai(this._heartbeatsCache.heartbeats),o=vn(JSON.stringify({version:2,heartbeats:r}));return this._heartbeatsCache.lastSentHeartbeatDate=n,s.length>0?(this._heartbeatsCache.heartbeats=s,await this._storage.overwrite(this._heartbeatsCache)):(this._heartbeatsCache.heartbeats=[],this._storage.overwrite(this._heartbeatsCache)),o}catch(n){return M.warn(n),""}}}function Lt(){return new Date().toISOString().substring(0,10)}function ai(e,t=si){const n=[];let r=e.slice();for(const s of e){const o=n.find(i=>i.agent===s.agent);if(o){if(o.dates.push(s.date),xt(n)>t){o.dates.pop();break}}else if(n.push({agent:s.agent,dates:[s.date]}),xt(n)>t){n.pop();break}r=r.slice(1)}return{heartbeatsToSend:n,unsentEntries:r}}class ci{constructor(t){this.app=t,this._canUseIndexedDBPromise=this.runIndexedDBEnvironmentCheck()}async runIndexedDBEnvironmentCheck(){return rt()?st().then(()=>!0).catch(()=>!1):!1}async read(){if(await this._canUseIndexedDBPromise){const n=await ri(this.app);return n!=null&&n.heartbeats?n:{heartbeats:[]}}else return{heartbeats:[]}}async overwrite(t){var n;if(await this._canUseIndexedDBPromise){const s=await this.read();return Ft(this.app,{lastSentHeartbeatDate:(n=t.lastSentHeartbeatDate)!==null&&n!==void 0?n:s.lastSentHeartbeatDate,heartbeats:t.heartbeats})}else return}async add(t){var n;if(await this._canUseIndexedDBPromise){const s=await this.read();return Ft(this.app,{lastSentHeartbeatDate:(n=t.lastSentHeartbeatDate)!==null&&n!==void 0?n:s.lastSentHeartbeatDate,heartbeats:[...s.heartbeats,...t.heartbeats]})}else return}}function xt(e){return vn(JSON.stringify({version:2,heartbeats:e})).length}function li(e){if(e.length===0)return-1;let t=0,n=e[0].date;for(let r=1;r<e.length;r++)e[r].date<n&&(n=e[r].date,t=r);return t}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function ui(e){F(new k("platform-logger",t=>new Io(t),"PRIVATE")),F(new k("heartbeat",t=>new ii(t),"PRIVATE")),D(Ge,Bt,e),D(Ge,Bt,"esm2017"),D("fire-js","")}ui("");var di="firebase",fi="11.7.1";/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */D(di,fi,"app");const Fn="@firebase/installations",it="0.6.14";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ln=1e4,xn=`w:${it}`,$n="FIS_v2",hi="https://firebaseinstallations.googleapis.com/v1",pi=60*60*1e3,mi="installations",gi="Installations";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const bi={"missing-app-config-values":'Missing App configuration value: "{$valueName}"',"not-registered":"Firebase Installation is not registered.","installation-not-found":"Firebase Installation not found.","request-failed":'{$requestName} request failed with error "{$serverCode} {$serverStatus}: {$serverMessage}"',"app-offline":"Could not process request. Application offline.","delete-pending-registration":"Can't delete installation while there is a pending registration request."},V=new oe(mi,gi,bi);function jn(e){return e instanceof K&&e.code.includes("request-failed")}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Un({projectId:e}){return`${hi}/projects/${e}/installations`}function Hn(e){return{token:e.token,requestStatus:2,expiresIn:wi(e.expiresIn),creationTime:Date.now()}}async function qn(e,t){const r=(await t.json()).error;return V.create("request-failed",{requestName:e,serverCode:r.code,serverMessage:r.message,serverStatus:r.status})}function Vn({apiKey:e}){return new Headers({"Content-Type":"application/json",Accept:"application/json","x-goog-api-key":e})}function yi(e,{refreshToken:t}){const n=Vn(e);return n.append("Authorization",Ei(t)),n}async function zn(e){const t=await e();return t.status>=500&&t.status<600?e():t}function wi(e){return Number(e.replace("s","000"))}function Ei(e){return`${$n} ${e}`}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Si({appConfig:e,heartbeatServiceProvider:t},{fid:n}){const r=Un(e),s=Vn(e),o=t.getImmediate({optional:!0});if(o){const d=await o.getHeartbeatsHeader();d&&s.append("x-firebase-client",d)}const i={fid:n,authVersion:$n,appId:e.appId,sdkVersion:xn},a={method:"POST",headers:s,body:JSON.stringify(i)},l=await zn(()=>fetch(r,a));if(l.ok){const d=await l.json();return{fid:d.fid||n,registrationStatus:2,refreshToken:d.refreshToken,authToken:Hn(d.authToken)}}else throw await qn("Create Installation",l)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Kn(e){return new Promise(t=>{setTimeout(t,e)})}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Ti(e){return btoa(String.fromCharCode(...e)).replace(/\+/g,"-").replace(/\//g,"_")}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ii=/^[cdef][\w-]{21}$/,Xe="";function Ai(){try{const e=new Uint8Array(17);(self.crypto||self.msCrypto).getRandomValues(e),e[0]=112+e[0]%16;const n=_i(e);return Ii.test(n)?n:Xe}catch{return Xe}}function _i(e){return Ti(e).substr(0,22)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Te(e){return`${e.appName}!${e.appId}`}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Wn=new Map;function Gn(e,t){const n=Te(e);Jn(n,t),vi(n,t)}function Jn(e,t){const n=Wn.get(e);if(n)for(const r of n)r(t)}function vi(e,t){const n=Ri();n&&n.postMessage({key:e,fid:t}),Oi()}let U=null;function Ri(){return!U&&"BroadcastChannel"in self&&(U=new BroadcastChannel("[Firebase] FID Change"),U.onmessage=e=>{Jn(e.data.key,e.data.fid)}),U}function Oi(){Wn.size===0&&U&&(U.close(),U=null)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ci="firebase-installations-database",Di=1,z="firebase-installations-store";let Pe=null;function at(){return Pe||(Pe=Se(Ci,Di,{upgrade:(e,t)=>{switch(t){case 0:e.createObjectStore(z)}}})),Pe}async function pe(e,t){const n=Te(e),s=(await at()).transaction(z,"readwrite"),o=s.objectStore(z),i=await o.get(n);return await o.put(t,n),await s.done,(!i||i.fid!==t.fid)&&Gn(e,t.fid),t}async function Yn(e){const t=Te(e),r=(await at()).transaction(z,"readwrite");await r.objectStore(z).delete(t),await r.done}async function Ie(e,t){const n=Te(e),s=(await at()).transaction(z,"readwrite"),o=s.objectStore(z),i=await o.get(n),a=t(i);return a===void 0?await o.delete(n):await o.put(a,n),await s.done,a&&(!i||i.fid!==a.fid)&&Gn(e,a.fid),a}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function ct(e){let t;const n=await Ie(e.appConfig,r=>{const s=ki(r),o=Ni(e,s);return t=o.registrationPromise,o.installationEntry});return n.fid===Xe?{installationEntry:await t}:{installationEntry:n,registrationPromise:t}}function ki(e){const t=e||{fid:Ai(),registrationStatus:0};return Xn(t)}function Ni(e,t){if(t.registrationStatus===0){if(!navigator.onLine){const s=Promise.reject(V.create("app-offline"));return{installationEntry:t,registrationPromise:s}}const n={fid:t.fid,registrationStatus:1,registrationTime:Date.now()},r=Pi(e,n);return{installationEntry:n,registrationPromise:r}}else return t.registrationStatus===1?{installationEntry:t,registrationPromise:Bi(e)}:{installationEntry:t}}async function Pi(e,t){try{const n=await Si(e,t);return pe(e.appConfig,n)}catch(n){throw jn(n)&&n.customData.serverCode===409?await Yn(e.appConfig):await pe(e.appConfig,{fid:t.fid,registrationStatus:0}),n}}async function Bi(e){let t=await $t(e.appConfig);for(;t.registrationStatus===1;)await Kn(100),t=await $t(e.appConfig);if(t.registrationStatus===0){const{installationEntry:n,registrationPromise:r}=await ct(e);return r||n}return t}function $t(e){return Ie(e,t=>{if(!t)throw V.create("installation-not-found");return Xn(t)})}function Xn(e){return Mi(e)?{fid:e.fid,registrationStatus:0}:e}function Mi(e){return e.registrationStatus===1&&e.registrationTime+Ln<Date.now()}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Fi({appConfig:e,heartbeatServiceProvider:t},n){const r=Li(e,n),s=yi(e,n),o=t.getImmediate({optional:!0});if(o){const d=await o.getHeartbeatsHeader();d&&s.append("x-firebase-client",d)}const i={installation:{sdkVersion:xn,appId:e.appId}},a={method:"POST",headers:s,body:JSON.stringify(i)},l=await zn(()=>fetch(r,a));if(l.ok){const d=await l.json();return Hn(d)}else throw await qn("Generate Auth Token",l)}function Li(e,{fid:t}){return`${Un(e)}/${t}/authTokens:generate`}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function lt(e,t=!1){let n;const r=await Ie(e.appConfig,o=>{if(!Qn(o))throw V.create("not-registered");const i=o.authToken;if(!t&&ji(i))return o;if(i.requestStatus===1)return n=xi(e,t),o;{if(!navigator.onLine)throw V.create("app-offline");const a=Hi(o);return n=$i(e,a),a}});return n?await n:r.authToken}async function xi(e,t){let n=await jt(e.appConfig);for(;n.authToken.requestStatus===1;)await Kn(100),n=await jt(e.appConfig);const r=n.authToken;return r.requestStatus===0?lt(e,t):r}function jt(e){return Ie(e,t=>{if(!Qn(t))throw V.create("not-registered");const n=t.authToken;return qi(n)?Object.assign(Object.assign({},t),{authToken:{requestStatus:0}}):t})}async function $i(e,t){try{const n=await Fi(e,t),r=Object.assign(Object.assign({},t),{authToken:n});return await pe(e.appConfig,r),n}catch(n){if(jn(n)&&(n.customData.serverCode===401||n.customData.serverCode===404))await Yn(e.appConfig);else{const r=Object.assign(Object.assign({},t),{authToken:{requestStatus:0}});await pe(e.appConfig,r)}throw n}}function Qn(e){return e!==void 0&&e.registrationStatus===2}function ji(e){return e.requestStatus===2&&!Ui(e)}function Ui(e){const t=Date.now();return t<e.creationTime||e.creationTime+e.expiresIn<t+pi}function Hi(e){const t={requestStatus:1,requestTime:Date.now()};return Object.assign(Object.assign({},e),{authToken:t})}function qi(e){return e.requestStatus===1&&e.requestTime+Ln<Date.now()}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Vi(e){const t=e,{installationEntry:n,registrationPromise:r}=await ct(t);return r?r.catch(console.error):lt(t).catch(console.error),n.fid}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function zi(e,t=!1){const n=e;return await Ki(n),(await lt(n,t)).token}async function Ki(e){const{registrationPromise:t}=await ct(e);t&&await t}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Wi(e){if(!e||!e.options)throw Be("App Configuration");if(!e.name)throw Be("App Name");const t=["projectId","apiKey","appId"];for(const n of t)if(!e.options[n])throw Be(n);return{appName:e.name,projectId:e.options.projectId,apiKey:e.options.apiKey,appId:e.options.appId}}function Be(e){return V.create("missing-app-config-values",{valueName:e})}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Zn="installations",Gi="installations-internal",Ji=e=>{const t=e.getProvider("app").getImmediate(),n=Wi(t),r=ae(t,"heartbeat");return{app:t,appConfig:n,heartbeatServiceProvider:r,_delete:()=>Promise.resolve()}},Yi=e=>{const t=e.getProvider("app").getImmediate(),n=ae(t,Zn).getImmediate();return{getId:()=>Vi(n),getToken:s=>zi(n,s)}};function Xi(){F(new k(Zn,Ji,"PUBLIC")),F(new k(Gi,Yi,"PRIVATE"))}Xi();D(Fn,it);D(Fn,it,"esm2017");/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const me="analytics",Qi="firebase_id",Zi="origin",ea=60*1e3,ta="https://firebase.googleapis.com/v1alpha/projects/-/apps/{app-id}/webConfig",ut="https://www.googletagmanager.com/gtag/js";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const _=new Cn("@firebase/analytics");/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const na={"already-exists":"A Firebase Analytics instance with the appId {$id}  already exists. Only one Firebase Analytics instance can be created for each appId.","already-initialized":"initializeAnalytics() cannot be called again with different options than those it was initially called with. It can be called again with the same options to return the existing instance, or getAnalytics() can be used to get a reference to the already-initialized instance.","already-initialized-settings":"Firebase Analytics has already been initialized.settings() must be called before initializing any Analytics instanceor it will have no effect.","interop-component-reg-failed":"Firebase Analytics Interop Component failed to instantiate: {$reason}","invalid-analytics-context":"Firebase Analytics is not supported in this environment. Wrap initialization of analytics in analytics.isSupported() to prevent initialization in unsupported environments. Details: {$errorInfo}","indexeddb-unavailable":"IndexedDB unavailable or restricted in this environment. Wrap initialization of analytics in analytics.isSupported() to prevent initialization in unsupported environments. Details: {$errorInfo}","fetch-throttle":"The config fetch request timed out while in an exponential backoff state. Unix timestamp in milliseconds when fetch request throttling ends: {$throttleEndTimeMillis}.","config-fetch-failed":"Dynamic config fetch failed: [{$httpStatus}] {$responseMessage}","no-api-key":'The "apiKey" field is empty in the local Firebase config. Firebase Analytics requires this field tocontain a valid API key.',"no-app-id":'The "appId" field is empty in the local Firebase config. Firebase Analytics requires this field tocontain a valid app ID.',"no-client-id":'The "client_id" field is empty.',"invalid-gtag-resource":"Trusted Types detected an invalid gtag resource: {$gtagURL}."},v=new oe("analytics","Analytics",na);/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function ra(e){if(!e.startsWith(ut)){const t=v.create("invalid-gtag-resource",{gtagURL:e});return _.warn(t.message),""}return e}function er(e){return Promise.all(e.map(t=>t.catch(n=>n)))}function sa(e,t){let n;return window.trustedTypes&&(n=window.trustedTypes.createPolicy(e,t)),n}function oa(e,t){const n=sa("firebase-js-sdk-policy",{createScriptURL:ra}),r=document.createElement("script"),s=`${ut}?l=${e}&id=${t}`;r.src=n?n==null?void 0:n.createScriptURL(s):s,r.async=!0,document.head.appendChild(r)}function ia(e){let t=[];return Array.isArray(window[e])?t=window[e]:window[e]=t,t}async function aa(e,t,n,r,s,o){const i=r[s];try{if(i)await t[i];else{const l=(await er(n)).find(d=>d.measurementId===s);l&&await t[l.appId]}}catch(a){_.error(a)}e("config",s,o)}async function ca(e,t,n,r,s){try{let o=[];if(s&&s.send_to){let i=s.send_to;Array.isArray(i)||(i=[i]);const a=await er(n);for(const l of i){const d=a.find(f=>f.measurementId===l),c=d&&t[d.appId];if(c)o.push(c);else{o=[];break}}}o.length===0&&(o=Object.values(t)),await Promise.all(o),e("event",r,s||{})}catch(o){_.error(o)}}function la(e,t,n,r){async function s(o,...i){try{if(o==="event"){const[a,l]=i;await ca(e,t,n,a,l)}else if(o==="config"){const[a,l]=i;await aa(e,t,n,r,a,l)}else if(o==="consent"){const[a,l]=i;e("consent",a,l)}else if(o==="get"){const[a,l,d]=i;e("get",a,l,d)}else if(o==="set"){const[a]=i;e("set",a)}else e(o,...i)}catch(a){_.error(a)}}return s}function ua(e,t,n,r,s){let o=function(...i){window[r].push(arguments)};return window[s]&&typeof window[s]=="function"&&(o=window[s]),window[s]=la(o,e,t,n),{gtagCore:o,wrappedGtag:window[s]}}function da(e){const t=window.document.getElementsByTagName("script");for(const n of Object.values(t))if(n.src&&n.src.includes(ut)&&n.src.includes(e))return n;return null}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const fa=30,ha=1e3;class pa{constructor(t={},n=ha){this.throttleMetadata=t,this.intervalMillis=n}getThrottleMetadata(t){return this.throttleMetadata[t]}setThrottleMetadata(t,n){this.throttleMetadata[t]=n}deleteThrottleMetadata(t){delete this.throttleMetadata[t]}}const tr=new pa;function ma(e){return new Headers({Accept:"application/json","x-goog-api-key":e})}async function ga(e){var t;const{appId:n,apiKey:r}=e,s={method:"GET",headers:ma(r)},o=ta.replace("{app-id}",n),i=await fetch(o,s);if(i.status!==200&&i.status!==304){let a="";try{const l=await i.json();!((t=l.error)===null||t===void 0)&&t.message&&(a=l.error.message)}catch{}throw v.create("config-fetch-failed",{httpStatus:i.status,responseMessage:a})}return i.json()}async function ba(e,t=tr,n){const{appId:r,apiKey:s,measurementId:o}=e.options;if(!r)throw v.create("no-app-id");if(!s){if(o)return{measurementId:o,appId:r};throw v.create("no-api-key")}const i=t.getThrottleMetadata(r)||{backoffCount:0,throttleEndTimeMillis:Date.now()},a=new Ea;return setTimeout(async()=>{a.abort()},ea),nr({appId:r,apiKey:s,measurementId:o},i,a,t)}async function nr(e,{throttleEndTimeMillis:t,backoffCount:n},r,s=tr){var o;const{appId:i,measurementId:a}=e;try{await ya(r,t)}catch(l){if(a)return _.warn(`Timed out fetching this Firebase app's measurement ID from the server. Falling back to the measurement ID ${a} provided in the "measurementId" field in the local Firebase config. [${l==null?void 0:l.message}]`),{appId:i,measurementId:a};throw l}try{const l=await ga(e);return s.deleteThrottleMetadata(i),l}catch(l){const d=l;if(!wa(d)){if(s.deleteThrottleMetadata(i),a)return _.warn(`Failed to fetch this Firebase app's measurement ID from the server. Falling back to the measurement ID ${a} provided in the "measurementId" field in the local Firebase config. [${d==null?void 0:d.message}]`),{appId:i,measurementId:a};throw l}const c=Number((o=d==null?void 0:d.customData)===null||o===void 0?void 0:o.httpStatus)===503?Dt(n,s.intervalMillis,fa):Dt(n,s.intervalMillis),f={throttleEndTimeMillis:Date.now()+c,backoffCount:n+1};return s.setThrottleMetadata(i,f),_.debug(`Calling attemptFetch again in ${c} millis`),nr(e,f,r,s)}}function ya(e,t){return new Promise((n,r)=>{const s=Math.max(t-Date.now(),0),o=setTimeout(n,s);e.addEventListener(()=>{clearTimeout(o),r(v.create("fetch-throttle",{throttleEndTimeMillis:t}))})})}function wa(e){if(!(e instanceof K)||!e.customData)return!1;const t=Number(e.customData.httpStatus);return t===429||t===500||t===503||t===504}class Ea{constructor(){this.listeners=[]}addEventListener(t){this.listeners.push(t)}abort(){this.listeners.forEach(t=>t())}}async function Sa(e,t,n,r,s){if(s&&s.global){e("event",n,r);return}else{const o=await t,i=Object.assign(Object.assign({},r),{send_to:o});e("event",n,i)}}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Ta(){if(rt())try{await st()}catch(e){return _.warn(v.create("indexeddb-unavailable",{errorInfo:e==null?void 0:e.toString()}).message),!1}else return _.warn(v.create("indexeddb-unavailable",{errorInfo:"IndexedDB is not available in this environment."}).message),!1;return!0}async function Ia(e,t,n,r,s,o,i){var a;const l=ba(e);l.then(m=>{n[m.measurementId]=m.appId,e.options.measurementId&&m.measurementId!==e.options.measurementId&&_.warn(`The measurement ID in the local Firebase config (${e.options.measurementId}) does not match the measurement ID fetched from the server (${m.measurementId}). To ensure analytics events are always sent to the correct Analytics property, update the measurement ID field in the local config or remove it from the local config.`)}).catch(m=>_.error(m)),t.push(l);const d=Ta().then(m=>{if(m)return r.getId()}),[c,f]=await Promise.all([l,d]);da(o)||oa(o,c.measurementId),s("js",new Date);const b=(a=i==null?void 0:i.config)!==null&&a!==void 0?a:{};return b[Zi]="firebase",b.update=!0,f!=null&&(b[Qi]=f),s("config",c.measurementId,b),c.measurementId}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Aa{constructor(t){this.app=t}_delete(){return delete Q[this.app.options.appId],Promise.resolve()}}let Q={},Ut=[];const Ht={};let Me="dataLayer",_a="gtag",qt,rr,Vt=!1;function va(){const e=[];if(Ys()&&e.push("This is a browser extension environment."),On()||e.push("Cookies are not available."),e.length>0){const t=e.map((r,s)=>`(${s+1}) ${r}`).join(" "),n=v.create("invalid-analytics-context",{errorInfo:t});_.warn(n.message)}}function Ra(e,t,n){va();const r=e.options.appId;if(!r)throw v.create("no-app-id");if(!e.options.apiKey)if(e.options.measurementId)_.warn(`The "apiKey" field is empty in the local Firebase config. This is needed to fetch the latest measurement ID for this Firebase app. Falling back to the measurement ID ${e.options.measurementId} provided in the "measurementId" field in the local Firebase config.`);else throw v.create("no-api-key");if(Q[r]!=null)throw v.create("already-exists",{id:r});if(!Vt){ia(Me);const{wrappedGtag:o,gtagCore:i}=ua(Q,Ut,Ht,Me,_a);rr=o,qt=i,Vt=!0}return Q[r]=Ia(e,Ut,Ht,t,qt,Me,n),new Aa(e)}function Oa(e=Pn()){e=ie(e);const t=ae(e,me);return t.isInitialized()?t.getImmediate():Ca(e)}function Ca(e,t={}){const n=ae(e,me);if(n.isInitialized()){const s=n.getImmediate();if(fe(t,n.getOptions()))return s;throw v.create("already-initialized")}return n.initialize({options:t})}function Da(e,t,n,r){e=ie(e),Sa(rr,Q[e.app.options.appId],t,n,r).catch(s=>_.error(s))}const zt="@firebase/analytics",Kt="0.10.13";function ka(){F(new k(me,(t,{options:n})=>{const r=t.getProvider("app").getImmediate(),s=t.getProvider("installations-internal").getImmediate();return Ra(r,s,n)},"PUBLIC")),F(new k("analytics-internal",e,"PRIVATE")),D(zt,Kt),D(zt,Kt,"esm2017");function e(t){try{const n=t.getProvider(me).getImmediate();return{logEvent:(r,s,o)=>Da(n,r,s,o)}}catch(n){throw v.create("interop-component-reg-failed",{reason:n})}}}ka();/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Na="/firebase-messaging-sw.js",Pa="/firebase-cloud-messaging-push-scope",sr="BDOU99-h67HcA6JeFXHbSNMu7e2yNNu3RzoMj8TM4W88jITfq7ZmPvIM1Iv-4_l2LxQcYwhqby2xGpWwzjfAnG4",Ba="https://fcmregistrations.googleapis.com/v1",or="google.c.a.c_id",Ma="google.c.a.c_l",Fa="google.c.a.ts",La="google.c.a.e",Wt=1e4;var Gt;(function(e){e[e.DATA_MESSAGE=1]="DATA_MESSAGE",e[e.DISPLAY_NOTIFICATION=3]="DISPLAY_NOTIFICATION"})(Gt||(Gt={}));/**
 * @license
 * Copyright 2018 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */var te;(function(e){e.PUSH_RECEIVED="push-received",e.NOTIFICATION_CLICKED="notification-clicked"})(te||(te={}));/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function P(e){const t=new Uint8Array(e);return btoa(String.fromCharCode(...t)).replace(/=/g,"").replace(/\+/g,"-").replace(/\//g,"_")}function xa(e){const t="=".repeat((4-e.length%4)%4),n=(e+t).replace(/\-/g,"+").replace(/_/g,"/"),r=atob(n),s=new Uint8Array(r.length);for(let o=0;o<r.length;++o)s[o]=r.charCodeAt(o);return s}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Fe="fcm_token_details_db",$a=5,Jt="fcm_token_object_Store";async function ja(e){if("databases"in indexedDB&&!(await indexedDB.databases()).map(o=>o.name).includes(Fe))return null;let t=null;return(await Se(Fe,$a,{upgrade:async(r,s,o,i)=>{var a;if(s<2||!r.objectStoreNames.contains(Jt))return;const l=i.objectStore(Jt),d=await l.index("fcmSenderId").get(e);if(await l.clear(),!!d){if(s===2){const c=d;if(!c.auth||!c.p256dh||!c.endpoint)return;t={token:c.fcmToken,createTime:(a=c.createTime)!==null&&a!==void 0?a:Date.now(),subscriptionOptions:{auth:c.auth,p256dh:c.p256dh,endpoint:c.endpoint,swScope:c.swScope,vapidKey:typeof c.vapidKey=="string"?c.vapidKey:P(c.vapidKey)}}}else if(s===3){const c=d;t={token:c.fcmToken,createTime:c.createTime,subscriptionOptions:{auth:P(c.auth),p256dh:P(c.p256dh),endpoint:c.endpoint,swScope:c.swScope,vapidKey:P(c.vapidKey)}}}else if(s===4){const c=d;t={token:c.fcmToken,createTime:c.createTime,subscriptionOptions:{auth:P(c.auth),p256dh:P(c.p256dh),endpoint:c.endpoint,swScope:c.swScope,vapidKey:P(c.vapidKey)}}}}}})).close(),await De(Fe),await De("fcm_vapid_details_db"),await De("undefined"),Ua(t)?t:null}function Ua(e){if(!e||!e.subscriptionOptions)return!1;const{subscriptionOptions:t}=e;return typeof e.createTime=="number"&&e.createTime>0&&typeof e.token=="string"&&e.token.length>0&&typeof t.auth=="string"&&t.auth.length>0&&typeof t.p256dh=="string"&&t.p256dh.length>0&&typeof t.endpoint=="string"&&t.endpoint.length>0&&typeof t.swScope=="string"&&t.swScope.length>0&&typeof t.vapidKey=="string"&&t.vapidKey.length>0}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ha="firebase-messaging-database",qa=1,ne="firebase-messaging-store";let Le=null;function ir(){return Le||(Le=Se(Ha,qa,{upgrade:(e,t)=>{switch(t){case 0:e.createObjectStore(ne)}}})),Le}async function Va(e){const t=ar(e),r=await(await ir()).transaction(ne).objectStore(ne).get(t);if(r)return r;{const s=await ja(e.appConfig.senderId);if(s)return await dt(e,s),s}}async function dt(e,t){const n=ar(e),s=(await ir()).transaction(ne,"readwrite");return await s.objectStore(ne).put(t,n),await s.done,t}function ar({appConfig:e}){return e.appId}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const za={"missing-app-config-values":'Missing App configuration value: "{$valueName}"',"only-available-in-window":"This method is available in a Window context.","only-available-in-sw":"This method is available in a service worker context.","permission-default":"The notification permission was not granted and dismissed instead.","permission-blocked":"The notification permission was not granted and blocked instead.","unsupported-browser":"This browser doesn't support the API's required to use the Firebase SDK.","indexed-db-unsupported":"This browser doesn't support indexedDb.open() (ex. Safari iFrame, Firefox Private Browsing, etc)","failed-service-worker-registration":"We are unable to register the default service worker. {$browserErrorMessage}","token-subscribe-failed":"A problem occurred while subscribing the user to FCM: {$errorInfo}","token-subscribe-no-token":"FCM returned no token when subscribing the user to push.","token-unsubscribe-failed":"A problem occurred while unsubscribing the user from FCM: {$errorInfo}","token-update-failed":"A problem occurred while updating the user from FCM: {$errorInfo}","token-update-no-token":"FCM returned no token when updating the user to push.","use-sw-after-get-token":"The useServiceWorker() method may only be called once and must be called before calling getToken() to ensure your service worker is used.","invalid-sw-registration":"The input to useServiceWorker() must be a ServiceWorkerRegistration.","invalid-bg-handler":"The input to setBackgroundMessageHandler() must be a function.","invalid-vapid-key":"The public VAPID key must be a string.","use-vapid-key-after-get-token":"The usePublicVapidKey() method may only be called once and must be called before calling getToken() to ensure your VAPID key is used."},S=new oe("messaging","Messaging",za);/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Ka(e,t){const n=await ht(e),r=cr(t),s={method:"POST",headers:n,body:JSON.stringify(r)};let o;try{o=await(await fetch(ft(e.appConfig),s)).json()}catch(i){throw S.create("token-subscribe-failed",{errorInfo:i==null?void 0:i.toString()})}if(o.error){const i=o.error.message;throw S.create("token-subscribe-failed",{errorInfo:i})}if(!o.token)throw S.create("token-subscribe-no-token");return o.token}async function Wa(e,t){const n=await ht(e),r=cr(t.subscriptionOptions),s={method:"PATCH",headers:n,body:JSON.stringify(r)};let o;try{o=await(await fetch(`${ft(e.appConfig)}/${t.token}`,s)).json()}catch(i){throw S.create("token-update-failed",{errorInfo:i==null?void 0:i.toString()})}if(o.error){const i=o.error.message;throw S.create("token-update-failed",{errorInfo:i})}if(!o.token)throw S.create("token-update-no-token");return o.token}async function Ga(e,t){const r={method:"DELETE",headers:await ht(e)};try{const o=await(await fetch(`${ft(e.appConfig)}/${t}`,r)).json();if(o.error){const i=o.error.message;throw S.create("token-unsubscribe-failed",{errorInfo:i})}}catch(s){throw S.create("token-unsubscribe-failed",{errorInfo:s==null?void 0:s.toString()})}}function ft({projectId:e}){return`${Ba}/projects/${e}/registrations`}async function ht({appConfig:e,installations:t}){const n=await t.getToken();return new Headers({"Content-Type":"application/json",Accept:"application/json","x-goog-api-key":e.apiKey,"x-goog-firebase-installations-auth":`FIS ${n}`})}function cr({p256dh:e,auth:t,endpoint:n,vapidKey:r}){const s={web:{endpoint:n,auth:t,p256dh:e}};return r!==sr&&(s.web.applicationPubKey=r),s}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ja=7*24*60*60*1e3;async function Ya(e){const t=await Qa(e.swRegistration,e.vapidKey),n={vapidKey:e.vapidKey,swScope:e.swRegistration.scope,endpoint:t.endpoint,auth:P(t.getKey("auth")),p256dh:P(t.getKey("p256dh"))},r=await Va(e.firebaseDependencies);if(r){if(Za(r.subscriptionOptions,n))return Date.now()>=r.createTime+Ja?Xa(e,{token:r.token,createTime:Date.now(),subscriptionOptions:n}):r.token;try{await Ga(e.firebaseDependencies,r.token)}catch(s){console.warn(s)}return Yt(e.firebaseDependencies,n)}else return Yt(e.firebaseDependencies,n)}async function Xa(e,t){try{const n=await Wa(e.firebaseDependencies,t),r=Object.assign(Object.assign({},t),{token:n,createTime:Date.now()});return await dt(e.firebaseDependencies,r),n}catch(n){throw n}}async function Yt(e,t){const r={token:await Ka(e,t),createTime:Date.now(),subscriptionOptions:t};return await dt(e,r),r.token}async function Qa(e,t){const n=await e.pushManager.getSubscription();return n||e.pushManager.subscribe({userVisibleOnly:!0,applicationServerKey:xa(t)})}function Za(e,t){const n=t.vapidKey===e.vapidKey,r=t.endpoint===e.endpoint,s=t.auth===e.auth,o=t.p256dh===e.p256dh;return n&&r&&s&&o}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Xt(e){const t={from:e.from,collapseKey:e.collapse_key,messageId:e.fcmMessageId};return ec(t,e),tc(t,e),nc(t,e),t}function ec(e,t){if(!t.notification)return;e.notification={};const n=t.notification.title;n&&(e.notification.title=n);const r=t.notification.body;r&&(e.notification.body=r);const s=t.notification.image;s&&(e.notification.image=s);const o=t.notification.icon;o&&(e.notification.icon=o)}function tc(e,t){t.data&&(e.data=t.data)}function nc(e,t){var n,r,s,o,i;if(!t.fcmOptions&&!(!((n=t.notification)===null||n===void 0)&&n.click_action))return;e.fcmOptions={};const a=(s=(r=t.fcmOptions)===null||r===void 0?void 0:r.link)!==null&&s!==void 0?s:(o=t.notification)===null||o===void 0?void 0:o.click_action;a&&(e.fcmOptions.link=a);const l=(i=t.fcmOptions)===null||i===void 0?void 0:i.analytics_label;l&&(e.fcmOptions.analyticsLabel=l)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function rc(e){return typeof e=="object"&&!!e&&or in e}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function sc(e){if(!e||!e.options)throw xe("App Configuration Object");if(!e.name)throw xe("App Name");const t=["projectId","apiKey","appId","messagingSenderId"],{options:n}=e;for(const r of t)if(!n[r])throw xe(r);return{appName:e.name,projectId:n.projectId,apiKey:n.apiKey,appId:n.appId,senderId:n.messagingSenderId}}function xe(e){return S.create("missing-app-config-values",{valueName:e})}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class oc{constructor(t,n,r){this.deliveryMetricsExportedToBigQueryEnabled=!1,this.onBackgroundMessageHandler=null,this.onMessageHandler=null,this.logEvents=[],this.isLogServiceStarted=!1;const s=sc(t);this.firebaseDependencies={app:t,appConfig:s,installations:n,analyticsProvider:r}}_delete(){return Promise.resolve()}}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function ic(e){try{e.swRegistration=await navigator.serviceWorker.register(Na,{scope:Pa}),e.swRegistration.update().catch(()=>{}),await ac(e.swRegistration)}catch(t){throw S.create("failed-service-worker-registration",{browserErrorMessage:t==null?void 0:t.message})}}async function ac(e){return new Promise((t,n)=>{const r=setTimeout(()=>n(new Error(`Service worker not registered after ${Wt} ms`)),Wt),s=e.installing||e.waiting;e.active?(clearTimeout(r),t()):s?s.onstatechange=o=>{var i;((i=o.target)===null||i===void 0?void 0:i.state)==="activated"&&(s.onstatechange=null,clearTimeout(r),t())}:(clearTimeout(r),n(new Error("No incoming service worker found.")))})}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function cc(e,t){if(!t&&!e.swRegistration&&await ic(e),!(!t&&e.swRegistration)){if(!(t instanceof ServiceWorkerRegistration))throw S.create("invalid-sw-registration");e.swRegistration=t}}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function lc(e,t){t?e.vapidKey=t:e.vapidKey||(e.vapidKey=sr)}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function lr(e,t){if(!navigator)throw S.create("only-available-in-window");if(Notification.permission==="default"&&await Notification.requestPermission(),Notification.permission!=="granted")throw S.create("permission-blocked");return await lc(e,t==null?void 0:t.vapidKey),await cc(e,t==null?void 0:t.serviceWorkerRegistration),Ya(e)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function uc(e,t,n){const r=dc(t);(await e.firebaseDependencies.analyticsProvider.get()).logEvent(r,{message_id:n[or],message_name:n[Ma],message_time:n[Fa],message_device_time:Math.floor(Date.now()/1e3)})}function dc(e){switch(e){case te.NOTIFICATION_CLICKED:return"notification_open";case te.PUSH_RECEIVED:return"notification_foreground";default:throw new Error}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function fc(e,t){const n=t.data;if(!n.isFirebaseMessaging)return;e.onMessageHandler&&n.messageType===te.PUSH_RECEIVED&&(typeof e.onMessageHandler=="function"?e.onMessageHandler(Xt(n)):e.onMessageHandler.next(Xt(n)));const r=n.data;rc(r)&&r[La]==="1"&&await uc(e,n.messageType,r)}const Qt="@firebase/messaging",Zt="0.12.18";/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const hc=e=>{const t=new oc(e.getProvider("app").getImmediate(),e.getProvider("installations-internal").getImmediate(),e.getProvider("analytics-internal"));return navigator.serviceWorker.addEventListener("message",n=>fc(t,n)),t},pc=e=>{const t=e.getProvider("messaging").getImmediate();return{getToken:r=>lr(t,r)}};function mc(){F(new k("messaging",hc,"PUBLIC")),F(new k("messaging-internal",pc,"PRIVATE")),D(Qt,Zt),D(Qt,Zt,"esm2017")}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function gc(){try{await st()}catch{return!1}return typeof window<"u"&&rt()&&On()&&"serviceWorker"in navigator&&"PushManager"in window&&"Notification"in window&&"fetch"in window&&ServiceWorkerRegistration.prototype.hasOwnProperty("showNotification")&&PushSubscription.prototype.hasOwnProperty("getKey")}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function bc(e,t){if(!navigator)throw S.create("only-available-in-window");return e.onMessageHandler=t,()=>{e.onMessageHandler=null}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function yc(e=Pn()){return gc().then(t=>{if(!t)throw S.create("unsupported-browser")},t=>{throw S.create("indexed-db-unsupported")}),ae(ie(e),"messaging").getImmediate()}async function wc(e,t){return e=ie(e),lr(e,t)}function Ec(e,t){return e=ie(e),bc(e,t)}mc();const Sc={apiKey:"AIzaSyCycfq9CMdzLXq1vYmLJMWkc54hLEpyDLA",authDomain:"summary-209f2.firebaseapp.com",projectId:"summary-209f2",storageBucket:"summary-209f2.firebasestorage.app",messagingSenderId:"347726553568",appId:"1:347726553568:web:3b6fe288f71e18666a0b85",measurementId:"G-T4JSQXMWL2"},ur=Nn(Sc);Oa(ur);const dr=yc(ur);Ec(dr,e=>{alert(`Informasi Disposisi :

`+e.notification.title+`

`+e.notification.body)});wc(dr,{vapidKey:"BJJZKhEaHj6Bw2ehmnrC2GKzrStyReRd5AeAwF05uUTVbtJinZRd4c1KmPN8MYQwGPj14466QPVxWTAdpUHGyjY"}).then(e=>{e?Ic(e):Tc()}).catch(e=>{console.log("An error occurred while retrieving token. ",e)});function Tc(){Notification.requestPermission().then(e=>{alert(e==="granted"?"Terimakasih Telah Mengizinkan Notifikasi":"Kasih Aktif Izin Notifikasi Dolo KK")})}function Ic(e){var t=document.querySelector('meta[name="csrf-token"]').getAttribute("content");let n=new FormData;n.append("fcm_token",e),fetch("/webtoken",{headers:{"X-CSRF-TOKEN":t,_method:"_POST"},method:"POST",credentials:"same-origin",body:n})}
