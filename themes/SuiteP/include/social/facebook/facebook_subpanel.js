$(function() {
    username = $("#facebook_user_c").text();
    if(username.length > 0){
        $("#subpanel_title_activities").before('<table cellspacing="0" cellpadding="0" border="0" width="100%" class="formHeader h3Row"><tbody><tr><td style="width:100px;"><h3 style="width:100px;"><span><a name="facebookfeeds"></a><span style="display: none" id="show_facebookfeeds"><a id="show_facebookfeeds" class="utilsLink" href="#"><img border="0" align="absmiddle" alt="Show" src="themes/SuiteP/images/advanced_search.gif"></a></span><span style="display: inline" id="hide_facebookfeeds"><a  class="utilsLink" href="#" id="facebookfeeds_show"><img border="0" align="absmiddle" alt="Hide" src="themes/SuiteP/images/basic_search.gif"></a></span>&nbsp;Facebook</span></h3></td><td width="100%"><img width="1" height="1" alt="" src="themes/SuiteP/images/blank.gif"></td></tr></tbody></table><div id="FacebookDiv" class="doNotPrint" style="width:100%"><table class="list view"><tr></tr><td width="100%"><span id="facebook_feed"></span></td></td><tr></tr></table></div>');

            $("#show_facebookfeeds").click(function(  ) {
            $("#FacebookDiv").show();
            $("#show_facebookfeeds").hide();
            $("#facebookfeeds_show").show();
            $("#show").hide();

        return false;
        });
        $("#facebookfeeds_show").click(function() {

            $("#FacebookDiv").hide();
            $("#facebookfeeds_show").hide();
            $("#show_facebookfeeds").show();
            return false;
        });
    }
});