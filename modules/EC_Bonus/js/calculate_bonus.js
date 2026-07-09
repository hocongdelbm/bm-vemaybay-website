/* Date pickers for the calculate bonus form.
   Loaded after the form markup, so the inputs already exist. */
Calendar.setup({
  inputField : "from_date",
  daFormat : cal_date_format,
  button : "fdate_trigger",
  singleClick : true,
  dateStr : "",
  step : 1
});

Calendar.setup({
  inputField : "to_date",
  daFormat : cal_date_format,
  button : "tdate_trigger",
  singleClick : true,
  dateStr : "",
  step : 2
});

/* Quick date-range select (only present on the bonus report form):
   fills from_date / to_date; the user still submits with the view button. */
(function () {
  var select = document.getElementById('quick_range');
  if (!select) return;

  // Same user date format as the Calendar pickers, e.g. "%d-%m-%Y"
  function fmt(d) {
    var format = window.cal_date_format || '%d-%m-%Y';
    return format
      .replace('%d', String(d.getDate()).padStart(2, '0'))
      .replace('%m', String(d.getMonth() + 1).padStart(2, '0'))
      .replace('%Y', String(d.getFullYear()));
  }

  select.addEventListener('change', function () {
    if (!this.value) return;

    var now = new Date();
    var from = new Date(now);
    var to = new Date(now);

    switch (this.value) {
      case 'today':
        break;
      case 'yesterday':
        from.setDate(from.getDate() - 1);
        to = new Date(from);
        break;
      case 'last7':
        from.setDate(from.getDate() - 6);
        break;
      case 'this_week':
        // Week starts on Monday
        from.setDate(now.getDate() - ((now.getDay() + 6) % 7));
        break;
      case 'this_month':
        from = new Date(now.getFullYear(), now.getMonth(), 1);
        break;
      case 'last_month':
        from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        to = new Date(now.getFullYear(), now.getMonth(), 0);
        break;
      default:
        return;
    }

    document.getElementById('from_date').value = fmt(from);
    document.getElementById('to_date').value = fmt(to);
  });
})();
