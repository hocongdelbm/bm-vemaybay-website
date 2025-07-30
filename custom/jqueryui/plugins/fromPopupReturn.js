
function getHTTPObject_ajax() {
	var xmlhttp;
	/*@cc_on
	@if (@_jscript_version >= 5)
	try {
	xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
	try {
	xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	} catch (E) {
	xmlhttp = false;
	}
	}
	@else
	xmlhttp = false;
	@end @*/
	if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
		try {
			xmlhttp = new XMLHttpRequest();
		} catch (e) {
			xmlhttp = false;
		}
	}
	return xmlhttp;
}


// Creating http AJAX Object
var http = getHTTPObject_ajax(); // We create the HTTP Object	
function handle_display_popup(){
	if (http.readyState == 4) {
		var txt = http.responseText;
		if(txt!=''){
			alert(txt);
			document.getElementById('maincodeid' + lineno ).innerHTML = txt;
			//document.getElementById('div_message_popup').style.display = "block";
		}
	}
}
	

/**
 * The reply data must be a JSON array structured with the following information:
 *  1) form name to populate
 *  2) associative array of input names to values for populating the form
 */
var fromPopupReturn  = false;
function setObjectReturn(popupReplyData)
{
	fromPopupReturn = true;
	var formName = popupReplyData.form_name;
	var nameToValueArray = popupReplyData.name_to_value_array;
	
	for (var theKey in nameToValueArray)
	{
		if(theKey == 'toJSON')
		{
			/* just ignore */
		}
		else
		{
			var displayValue = nameToValueArray[theKey].replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');;
			/** depreciated
			 window.document.forms[form_name].elements[the_key].value = displayValue;
			 */
			//alert(theKey + " => " + displayValue);
			document.getElementById(theKey).value = displayValue;
			/** uncomment to copy value on select
			 if (theKey.search('product_list_price') != -1) {
			 	var ln = theKey.slice(18);
				document.getElementById('product_unit_price' + ln).value = displayValue;
			 }
			 */
		}
	}
	/** uncomment to copy value on select

	 calculateProductLine(ln);
	 */
//	 subcode1(document.getElementById('product_id' + lineno).value);
}