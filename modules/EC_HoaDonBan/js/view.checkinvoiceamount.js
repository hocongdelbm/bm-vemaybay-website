$(document).ready(function () {
    // use global cal_date_format so frontend follows user date format
    Calendar.setup({
        inputField: "from_date",
        daFormat: typeof cal_date_format !== 'undefined' ? cal_date_format : "%d-%m-%Y",
        button: "fdate_trigger",
        singleClick: true,
        dateStr: "",
        position: [244, 202],
        step: 1
    });
    Calendar.setup({
        inputField: "to_date",
        daFormat: typeof cal_date_format !== 'undefined' ? cal_date_format : "%d-%m-%Y",
        button: "tdate_trigger",
        singleClick: true,
        dateStr: "",
        step: 2
    });

    $("#btnClear").click(function () {
        $("#search_form input:not([type=submit], [type=button], [type=hidden]), #search_form select").val("");
    });

    $("#pick_quickly_date").change(function () {
        $("#from_date").val($(this).find("option:selected").attr("fromdate"));
        $("#to_date").val($(this).find("option:selected").attr("todate"));
    });
});