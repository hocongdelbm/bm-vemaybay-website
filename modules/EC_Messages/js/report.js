$(document).ready(function () {
    let period = getUrlParams('period');
    if(period.length > 0) $('select[name="period"]').val(period);

    $('select[name="period"]').change( function() {
        $('#form-search').submit();
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