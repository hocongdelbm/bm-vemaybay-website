
function set_focus() {
	if (document.DetailView.user_name.value != '') {
		document.DetailView.username_password.focus();
		document.DetailView.username_password.select();
	}
	else document.DetailView.user_name.focus();
}

function switchLanguage(lang) {
	var loc = window.location + "";
	loc = loc.replace(/\&login_language=[^&]*/i, "");
	loc += "&login_language=" + lang;
	window.location = loc;

}

function toggleDisplay(id){

	if(this.document.getElementById(id).style.display=='none'){
		this.document.getElementById(id).style.display='inline'
		if(this.document.getElementById(id+"link") != undefined){
			this.document.getElementById(id+"link").style.display='none';
		}
        document.getElementById(id+"_options").src = 'index.php?entryPoint=getImage&themeName='+SUGAR.themes.theme_name+'&imageName=basic_search.gif';
        document.getElementById(id+"_options").alt = LBL_HIDEOPTIONS;/*for 508 compliance fix - label defined in login.tpl*/
	}else{
		this.document.getElementById(id).style.display='none'
		if(this.document.getElementById(id+"link") != undefined){
			this.document.getElementById(id+"link").style.display='inline';
		}
	document.getElementById(id+"_options").src = 'index.php?entryPoint=getImage&themeName='+SUGAR.themes.theme_name+'&imageName=advanced_search.gif';	
    document.getElementById(id+"_options").alt = LBL_SHOWOPTIONS;/*for 508 compliance fix - label defined in login.tpl*/
	}
}


function generatepwd(){
	document.getElementById('generate_pwd_button').value='Please Wait';
	document.getElementById('generate_pwd_button').disabled =1;
	document.getElementById('wait_pwd_generation').innerHTML = '<img src="themes/default/images/img_loading.gif" >';
var callback;
       callback = {
			success: function(o){
			document.getElementById('generate_pwd_button').value=LBL_LOGIN_SUBMIT;
        	document.getElementById('generate_pwd_button').disabled =0;
        	document.getElementById('wait_pwd_generation').innerHTML = '';
			checkok=o.responseText;
			if (checkok.charAt(0) != '1')
				document.getElementById('generate_success').innerHTML =checkok;
			if (checkok.charAt((checkok.length)-1) == '1')
				document.getElementById('generate_success').innerHTML =LBL_REQUEST_SUBMIT;
			},
            failure: function(o){
            document.getElementById('generate_pwd_button').value= LBL_LOGIN_SUBMIT;
        	document.getElementById('generate_pwd_button').disabled =0;
			document.getElementById('wait_pwd_generation').innerHTML = '';
			alert(SUGAR.language.get('app_strings','LBL_AJAX_FAILURE'));
            }
        }   
    postData = '&to_pdf=1&module=Home&action=index&entryPoint=GeneratePassword&user_name='+document.getElementById("fp_user_name").value+'&Users0emailAddress0='+document.getElementById("fp_user_mail").value+'&link=1';
    YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, postData);   
}

//onReady, check the users browser
$(function(){
    if (SUGAR.isIECompatibilityMode()){
        $("#ie_compatibility_mode_warning").show();
    }
    else if (!SUGAR.isSupportedBrowser()){
        $("#browser_warning").show();
    }
});

// CUSTOM
$(document).ready(function () {
    const icon_hide = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 19c.946 0 1.81-.103 2.598-.281l-1.757-1.757c-.273.021-.55.038-.841.038-5.351 0-7.424-3.846-7.926-5a8.642 8.642 0 0 1 1.508-2.297L4.184 8.305c-1.538 1.667-2.121 3.346-2.132 3.379a.994.994 0 0 0 0 .633C2.073 12.383 4.367 19 12 19zm0-14c-1.837 0-3.346.396-4.604.981L3.707 2.293 2.293 3.707l18 18 1.414-1.414-3.319-3.319c2.614-1.951 3.547-4.615 3.561-4.657a.994.994 0 0 0 0-.633C21.927 11.617 19.633 5 12 5zm4.972 10.558-2.28-2.28c.19-.39.308-.819.308-1.278 0-1.641-1.359-3-3-3-.459 0-.888.118-1.277.309L8.915 7.501A9.26 9.26 0 0 1 12 7c5.351 0 7.424 3.846 7.926 5-.302.692-1.166 2.342-2.954 3.558z"></path></svg>';
    const icon_show = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 9a3.02 3.02 0 0 0-3 3c0 1.642 1.358 3 3 3 1.641 0 3-1.358 3-3 0-1.641-1.359-3-3-3z"></path><path d="M12 5c-7.633 0-9.927 6.617-9.948 6.684L1.946 12l.105.316C2.073 12.383 4.367 19 12 19s9.927-6.617 9.948-6.684l.106-.316-.105-.316C21.927 11.617 19.633 5 12 5zm0 12c-5.351 0-7.424-3.846-7.926-5C4.578 10.842 6.652 7 12 7c5.351 0 7.424 3.846 7.926 5-.504 1.158-2.578 5-7.926 5z"></path></svg>';
    $("#view-password").on('click', function(){
         if($(this).hasClass('view-password')){
              $("#username_password").attr('type', 'password');
              $(this).removeClass('view-password');
              $(this).html(icon_hide);
         } else {
              $("#username_password").attr('type', 'text');
              $(this).addClass('view-password');
              $(this).html(icon_show);
         }
    });
});
