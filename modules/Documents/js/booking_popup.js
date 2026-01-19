var from_popup_return = false;
function booking_set_return(popup_reply_data) {
  from_popup_return = true;
  //1. Get data from the popup form
  var form_name = popup_reply_data.form_name;
  var name_to_value_array = popup_reply_data.name_to_value_array;
  var booking_id = "EMPTY";
  for (var the_key in name_to_value_array) {
    if (the_key != "toJSON") {
      var displayValue = name_to_value_array[the_key];

      displayValue = displayValue.replace("&#039;", "'");
      displayValue = displayValue.replace("&amp;", "&");
      displayValue = displayValue.replace("&gt;", ">");
      displayValue = displayValue.replace("&lt;", "<");
      displayValue = displayValue.replace("&quot; ", '"');

      if (the_key == "booking_id") {
        booking_id = displayValue;
      }
      if (window.document.forms[form_name].elements[the_key]) {
        window.document.forms[form_name].elements[the_key].value = displayValue;
      }
        console.log('DEBUG: ID: ' + displayValue);
    }
  }
  booking_id = YAHOO.lang.JSON.stringify(booking_id);
  var conditions = new Array();
  conditions[conditions.length] = {
    name: "booking_id",
    op: "starts_with",
    value: booking_id,
  };

  var query = {
    module: "EC_Flight_Bookings",
    field_list: ["id", "name", "date_entered"], //['id', 'the field that you wanna display', 'field to sort by'],
    conditions: conditions,
    order: { by: "date_entered", desc: true },
  };
  result = global_rpcCliend.call_method("query", query, true);
  rhandle.display(result);


}
