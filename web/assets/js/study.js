var Study=function(o){var e={};function r(t){if(e[t])return e[t].exports;var n=e[t]={i:t,l:!1,exports:{}};return o[t].call(n.exports,n,n.exports,r),n.l=!0,n.exports}return r.m=o,r.c=e,r.d=function(o,e,t){r.o(o,e)||Object.defineProperty(o,e,{enumerable:!0,get:t})},r.r=function(o){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(o,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(o,"__esModule",{value:!0})},r.t=function(o,e){if(1&e&&(o=r(o)),8&e)return o;if(4&e&&"object"==typeof o&&o&&o.__esModule)return o;var t=Object.create(null);if(r.r(t),Object.defineProperty(t,"default",{enumerable:!0,value:o}),2&e&&"string"!=typeof o)for(var n in o)r.d(t,n,function(e){return o[e]}.bind(null,n));return t},r.n=function(o){var e=o&&o.__esModule?function(){return o.default}:function(){return o};return r.d(e,"a",e),e},r.o=function(o,e){return Object.prototype.hasOwnProperty.call(o,e)},r.p="",r(r.s=0)}([function(o,e,r){"use strict";function t(o){var e=$("textarea#proposition");window.scrollTo(0,0),playgroundApp=new Vue({el:"div#playground",delimiters:["${","}"],data:{proposition:"",lessonTitle:"...",progress:0,exerciseText:"...",goodRun:!1,remarks:[],remarksFg:"red",remarksBg:"red",correctionStatus:"Oki :)",conclusionHeader:"",conclusionBody:"",conclusionFooter:""}}),function(){var o=$("textarea#proposition");console.log(o),$("#caca").fadeOut(function(){})}(),$.ajax({type:"GET",url:Routing.generate("api_study_start",{lesson:o}),dataType:"json",success:function(o){f(o),e.focus(),playgroundApp.proposition="",e.attr("readonly",!1),l=c.WRITING_PROPOSITION},error:function(o,e,r){error(o,e,r)}}),$("#complaint-button").click(g),$("body").keypress(function(o){if(o.which===a){if(l==c.WRITING_PROPOSITION)return(e=$("textarea#proposition")).attr("readonly",!0),void $.ajax({type:"POST",url:Routing.generate("api_study_proposition_send"),data:{text:e.val()},dataType:"json",success:function(o){f(o),l=c.READING_REMARKS,e.focus(),$("#correction-status").fadeIn(),o.isOk?(playgroundApp.correctionStatus="Oki :)",playgroundApp.remarksBg=u,playgroundApp.remarksFg=p):(playgroundApp.correctionStatus="Tropa :(",playgroundApp.remarksBg=i,playgroundApp.remarksFg=s,setTimeout(function(){$("#complaint-button").fadeIn()},150))},error:function(o,e,r){error(o,e,r)}});if(l==c.READING_REMARKS)return function(){var o=$("textarea#proposition");$("#correction-status").fadeOut(),setTimeout(function(){$("#complaint-button").html(n)},1500),$("#complaint-button").fadeOut(),d=!1,$.ajax({type:"GET",url:Routing.generate("api_study_get_new_exercise"),dataType:"json",success:function(e){if(e.studyOver)return function(o){l=c.READING_STUDY_CONCLUSION,playgroundApp.conclusionHeader="Leçon terminée :)",playgroundApp.conclusionBody="Score de "+o.successPercentage+"%",playgroundApp.conclusionFooter="Maitrise de cette leçon : "+o.mastery,playgroundApp.goodRun=o.goodRun,$("#lesson-conclusion-modal").modal("show")}(e);o.attr("readonly",!1),playgroundApp.remarks=[],playgroundApp.proposition="",f(e),l=c.WRITING_PROPOSITION},error:function(o,e,r){error(o,e,r)}})}();if(l==c.READING_STUDY_CONCLUSION)return l=c.IDDLE,playgroundApp.proposition="",$("#lesson-conclusion-modal").modal("hide"),void window.location.replace(Routing.generate("homepage"))}var e})}r.r(e),r.d(e,"startLesson",function(){return t});const n="Euuuh bah si lol ...",a=13,i="#e22d2d",u="#2de230",s="#440d0d",p="#0d440e",c={IDDLE:"iddle",WRITING_PROPOSITION:"writing proposition",WAITING_CORRECTION:"waiting for correction",READING_REMARKS:"reading remarks",READING_STUDY_CONCLUSION:"reading study conclusion"};var l=c.IDDLE,d=!1;$("textarea#proposition");function f(o){void 0!==o.progress&&(playgroundApp.progress=o.progress),void 0!==o.lessonTitle&&(playgroundApp.lessonTitle=o.lessonTitle),void 0!==o.exerciseText&&(playgroundApp.exerciseText=o.exerciseText),void 0!==o.remarks&&(playgroundApp.remarks=o.remarks),void 0!==o.correctionStatus&&(playgroundApp.correctionStatus=o.correctionStatus)}function g(){d||($("#complaint-button").html('<i class="fa fa-cog fa-spin fa-2x fa-fw"></i>'),d=!0,$.ajax({type:"PUT",url:Routing.generate("api_study_complaint"),dataType:"json",success:function(o){$("#complaint-button").html('<i class="fa fa-check-circle fa-fw"></i>'+o.message)},error:function(o,e,r){$("#complaint-button").html('<i class="fa fa-times-circle fa-fw"></i>'),error(o,e,r)}}))}}]);