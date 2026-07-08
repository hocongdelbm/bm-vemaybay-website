/* Date pickers for the calculate bonus form.
   Loaded after the form markup, so the inputs already exist. */
Calendar.setup({
  inputField : "from_date",
  daFormat : "%d-%m-%Y",
  button : "fdate_trigger",
  singleClick : true,
  dateStr : "",
  step : 1
});

Calendar.setup({
  inputField : "to_date",
  daFormat : "%d-%m-%Y",
  button : "tdate_trigger",
  singleClick : true,
  dateStr : "",
  step : 2
});
