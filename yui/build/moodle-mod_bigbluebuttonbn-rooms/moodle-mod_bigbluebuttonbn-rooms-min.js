function setUsernameCustom() { document.getElementById('join_button_input').setAttribute('onClick', document.getElementById('join_button_input').getAttribute('onClick').replace(/action=join&(usernamecustom=[^&]*&)?/, 'action=join&usernamecustom=' + document.getElementById('nameinputfield').value + '&')) }
YUI.add("moodle-mod_bigbluebuttonbn-rooms",function(e,t){M.mod_bigbluebuttonbn=M.mod_bigbluebuttonbn||{},M.mod_bigbluebuttonbn.rooms={datasource:null,bigbluebuttonbn:{},panel:null,pinginterval:null,init:function(t){this.datasource=new e.DataSource.Get({source:M.cfg.wwwroot+"/mod/bigbluebuttonbn/bbb_ajax.php?sesskey="+M.cfg.sesskey+"&"}),this.bigbluebuttonbn=t,this.pinginterval=t.ping_interval,this.pinginterval===0&&(this.pinginterval=1e4),(this.bigbluebuttonbn.profile_features.indexOf("all")!=-1||this.bigbluebuttonbn.profile_features.indexOf("showroom")!=-1)&&this.initRoom(),this.initCompletionValidate()},initRoom:function(){if(this.bigbluebuttonbn.activity!=="open"){var t=[M.util.get_string("view_message_conference_has_ended","bigbluebuttonbn")];this.bigbluebuttonbn.activity!=="ended"&&(t=[M.util.get_string("view_message_conference_not_started","bigbluebuttonbn"),this.bigbluebuttonbn.opening,this.bigbluebuttonbn.closing]),e.DOM.addHTML(e.one("#status_bar"),this.initStatusBar(t));return}this.updateRoom()},updateRoom:function(t){var n="false";typeof t!="undefined"&&t&&(n="true");var r=this.bigbluebuttonbn.meetingid,i=this.bigbluebuttonbn.bigbluebuttonbnid;this.datasource.sendRequest({request:"action=meeting_info&id="+r+"&bigbluebuttonbn="+i+"&updatecache="+n,callback:{success:function(t){e.DOM.addHTML(e.one("#status_bar"),M.mod_bigbluebuttonbn.rooms.initStatusBar(t.data.status.message)),e.DOM.addHTML(e.one("#control_panel"),M.mod_bigbluebuttonbn.rooms.initControlPanel(t.data)),typeof t.data.status.can_join!="undefined"&&e.DOM.addHTML(e.one("#join_button"),M.mod_bigbluebuttonbn.rooms.initJoinButton(t.data.status)),typeof t.data.status.can_end!="undefined"&&t.data.status.can_end&&e.DOM.addHTML(e.one("#end_button"),M.mod_bigbluebuttonbn.rooms.initEndButton(t.data.status)),t.data.status.can_join||M.mod_bigbluebuttonbn.rooms.waitModerator({id:r,bnid:i})
setUsernameCustom();
}}})},initStatusBar:function(t){var n=e.DOM.create('<span id="status_bar_span">');if(t.constructor!==Array)return e.DOM.setText(n,t),n;for(var r in t){if(!t.hasOwnProperty(r))continue;var i=e.DOM.create('<span id="status_bar_span_span">');e.DOM.setText(i,t[r]),e.DOM.addHTML(n,i),e.DOM.addHTML(n,e.DOM.create("<br>"))}return n},initControlPanel:function(t){var n=e.DOM.create("<div>");e.DOM.setAttribute(n,"id","control_panel_div");var r="";return t.running&&(r+=this.msgStartedAt(t.info.startTime)+" ",r+=this.msgAttendeesIn(t.info.moderatorCount,t.info.participantCount)),e.DOM.addHTML(n,r),n},msgStartedAt:function(e){var t=parseInt(e,10)-parseInt(e,10)%1e3,n=new Date(t),r=n.getHours(),i=n.getMinutes(),s=M.util.get_string("view_message_session_started_at","bigbluebuttonbn");return s+" <b>"+r+":"+(i<10?"0":"")+i+"</b>."},msgModeratorsIn:function(e){var t=M.util.get_string("view_message_moderators","bigbluebuttonbn");return e==1&&(t=M.util.get_string("view_message_moderator","bigbluebuttonbn")),t},msgViewersIn:function(e){var t=M.util.get_string("view_message_viewers","bigbluebuttonbn");return e==1&&(t=M.util.get_string("view_message_viewer","bigbluebuttonbn")),t},msgAttendeesIn:function(e,t){var n,r,i,s;return this.hasParticipants(t)?(n=this.msgModeratorsIn(e),r=t-e,i=this.msgViewersIn(r),s=M.util.get_string("view_message_session_has_users","bigbluebuttonbn"),t>1?s+" <b>"+e+"</b> "+n+" "+M.util.get_string("view_message_and","bigbluebuttonbn")+" <b>"+r+"</b> "+i+".":(s=M.util.get_string("view_message_session_has_user","bigbluebuttonbn"),e>0?s+" <b>1</b> "+n+".":s+" <b>1</b> "+i+".")):M.util.get_string("view_message_session_no_users","bigbluebuttonbn")+"."},hasParticipants:function(e){return typeof e!="undefined"&&e>0},initJoinButton:function(t){var n=e.DOM.create("<input>");e.DOM.setAttribute(n,"id","join_button_input"),e.DOM.setAttribute(n,"type","button"),e.DOM.setAttribute(n,"value",t.join_button_text),e.DOM.setAttribute(n,"class","btn btn-primary");var r="M.mod_bigbluebuttonbn.rooms.join('"+t.join_url+"');";e.DOM.setAttribute(n,"onclick",r);if(!t.can_join){e.DOM.setAttribute(n,"disabled",!0);var i=e.one("#status_bar_span"),s=e.DOM.create("<img>");e.DOM.setAttribute(s,"id","spinning_wheel"),e.DOM.setAttribute(s,"src","pix/i/processing16.gif"),e.DOM.addHTML(i,"&nbsp;"),e.DOM.addHTML(i,s)}return n},initEndButton:function(t){var n=e.DOM.create("<input>");return e.DOM.setAttribute(n,"id","end_button_input"),e.DOM.setAttribute(n,"type","button"),e.DOM.setAttribute(n,"value",t.end_button_text),e.DOM.setAttribute(n,"class","btn btn-secondary"),t.can_end&&e.DOM.setAttribute(n,"onclick","M.mod_bigbluebuttonbn.broker.endMeeting();"),n},endMeeting:function(){e.one("#control_panel_div").remove(),e.one("#join_button").hide(),e.one("#end_button").hide()},remoteUpdate:function(e){setTimeout(function(){M.mod_bigbluebuttonbn.rooms.cleanRoom(),M.mod_bigbluebuttonbn.rooms.updateRoom(!0)},e)},cleanRoom:function(){e.one("#status_bar_span").remove(),e.one("#control_panel_div").remove(),e.one("#join_button").setContent(""),e.one("#end_button").setContent("")},windowClose:function(){window.onunload=function(){opener.M.mod_bigbluebuttonbn.rooms.remoteUpdate(5e3)},window.close()},waitModerator:function(e){var t=setInterval(function(){M.mod_bigbluebuttonbn.rooms.datasource.sendRequest({request:"action=meeting_info&id="+e.id+"&bigbluebuttonbn="+e.bnid,callback:{success:function(e){if(e.data.running){M.mod_bigbluebuttonbn.rooms.cleanRoom(),M.mod_bigbluebuttonbn.rooms.updateRoom(),clearInterval(t);return}},failure:function(t){e.message=t.error.message}}})},this.pinginterval)},join:function(e){M.mod_bigbluebuttonbn.broker.joinRedirect(e),setTimeout(function(){M.mod_bigbluebuttonbn.rooms.cleanRoom(),M.mod_bigbluebuttonbn.rooms.updateRoom(!0)},15e3)},initCompletionValidate:function(){var t=e.one("a[href*=completion_validate]");if(!t)return;var n=t.get("hash").substr(1);t.on("click",function(){M.mod_bigbluebuttonbn.broker.completionValidate(n)})}}},"@VERSION@",{requires:["base","node","datasource-get","datasource-jsonschema","datasource-polling","moodle-core-notification"]});
