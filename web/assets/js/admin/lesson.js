var Study=function(n){var t={};function e(o){if(t[o])return t[o].exports;var r=t[o]={i:o,l:!1,exports:{}};return n[o].call(r.exports,r,r.exports,e),r.l=!0,r.exports}return e.m=n,e.c=t,e.d=function(n,t,o){e.o(n,t)||Object.defineProperty(n,t,{enumerable:!0,get:o})},e.r=function(n){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(n,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(n,"__esModule",{value:!0})},e.t=function(n,t){if(1&t&&(n=e(n)),8&t)return n;if(4&t&&"object"==typeof n&&n&&n.__esModule)return n;var o=Object.create(null);if(e.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:n}),2&t&&"string"!=typeof n)for(var r in n)e.d(o,r,function(t){return n[t]}.bind(null,r));return o},e.n=function(n){var t=n&&n.__esModule?function(){return n.default}:function(){return n};return e.d(t,"a",t),t},e.o=function(n,t){return Object.prototype.hasOwnProperty.call(n,t)},e.p="",e(e.s=28)}({28:function(n,t){let e;function o(){let n=r(e),t=n.find("ul.proposition-list");console.log(n),console.log(t),i(t)}function r(n){let t=n.data("prototype"),e=n.data("index"),o=t;o=o.replace(/__name__/g,e),n.data("index",e+1);let r=$("<li></li>").append(o);return n.find("> li").last().before(r),r}function i(n){let t=$("<button>+<button>"),e=$("<li></li>").append(t);n.append(e),n.data("index",n.find("li").length),t.on("click",()=>(function(n){r(n)})(n))}$(document).ready(function(n){e=$("ul.question-list"),function(){let n=$("<button>+<button>"),t=$("<li></li>").append(n);e.append(t),e.data("index",e.find("ul.proposition-list").length),n.on("click",o)}(),e.find("ul.proposition-list").each(function(){i($(this))})}),$(document).ready(function(n){$(".remove-question").on("click",function(n){$(this).parent().parent().parent().parent().remove()}),$(".remove-proposition").on("click",function(n){$(this).parent().parent().parent().parent().remove()})})}});