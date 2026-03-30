$(document).ready(function () {
    let period = getUrlParams('period');
    let from_date = getUrlParams('from_date');
    let to_date = getUrlParams('to_date');
    if(period.length > 0) $('select[name="period"]').val(period);
    if(period == 'other') {
        if(from_date.length > 0 || to_date.length > 0) {
            $('input[name="from_date"]').val(from_date);
            $('input[name="to_date"]').val(to_date);
            $('.wrap-choose-date').css('display', 'flex');
        }
    }

    $('select[name="period"]').change( function() {
        if($(this).val() == 'other') {
            $('.wrap-choose-date').css('display', 'flex');
        }
        else {
            $('.wrap-choose-date').css('display', 'none');
            $('#form-search').submit();
        }
    });
});

function getUrlParams(key = '') {
    let params = {};
    let parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,    
    function(m, key, value) {
        params[key] = value;
    });

    if(key.length > 0) return params[key] ? params[key] : '';
    return params;
}