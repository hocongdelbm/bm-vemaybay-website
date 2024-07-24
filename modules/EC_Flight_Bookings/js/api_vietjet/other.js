$(document).ready(function() {
    // Only number
    $(document).on("input", ".only-numeric", function () {
        this.value = this.value.replace(/\D/g, '');
    });

    // Hide modal
    $('#modal-notification .close-modal').click(function() {
        $('#modal-notification').removeClass("show").hide();
    });
    $('#modal-confirm-payment .close-modal').click(function() {
        $('#modal-confirm-payment').removeClass("show").hide();
    });
    $('#modal-add-baggage .close-modal').click(function() {
        $('#modal-add-baggage').removeClass("show").hide();
    });
    $('#modal-add-luggage .close-modal').click(function() {
        $('#modal-add-luggage').removeClass("show").hide();
    });
    $('#modal-update-passenger .close-modal').click(function() {
        $('#modal-update-passenger').removeClass("show").hide();
    });
    $('#modal-confirm-update-passenger .close-modal').click(function() {
        $('#modal-confirm-update-passenger').removeClass("show").hide();

        if($(this).html() == 'Hủy') showModal('#modal-update-passenger');
    });
    $('#modal-confirm-payment-pnr .close-modal').click(function() {
        $('#modal-confirm-payment-pnr').removeClass("show").hide();
    });
    $('#modal-payment-pnr-success .close-modal').click(function() {
        $('#modal-payment-pnr-success').removeClass("show").hide();
    });
});

/* FUNCTIONS */
function showModalError(code, message) {
    var modal_id = '#modal-notification';
    $(modal_id + ' .modal-dialog').removeClass('modal-sm');
    $(modal_id + ' .modal-title').html(getIconNoti('error'));
    $(modal_id + ' .modal-body .content').html('');
    $(modal_id + ' .modal-body .content').append('<span class="error-code">'+code+'</span><br>');
    $(modal_id + ' .modal-body .content').append('<span>'+message+'</span>');
    $(modal_id + ' .modal-body .content').removeClass("content-success");
    $(modal_id + ' .modal-body .content').removeClass("content-warning");
    $(modal_id + ' .modal-body .content').addClass("content-error");
    showModal(modal_id);
}
function showModalSuccess(message) {
    var modal_id = '#modal-notification';
    if(!$(modal_id + ' .modal-dialog').hasClass('modal-sm'))
        $(modal_id + ' .modal-dialog').addClass('modal-sm');
    $(modal_id + ' .modal-title').html(getIconNoti('success'));
    $(modal_id + ' .modal-body .content').html(message);
    $(modal_id + ' .modal-body .content').removeClass("content-error");
    $(modal_id + ' .modal-body .content').removeClass("content-warning");
    $(modal_id + ' .modal-body .content').addClass("content-success");
    showModal(modal_id);
}
function showModalWarning(message) {
    var modal_id = '#modal-notification';
    $(modal_id + ' .modal-dialog').removeClass('modal-sm');
    $(modal_id + ' .modal-title').html(getIconNoti('warning'));
    $(modal_id + ' .modal-body .content').html(message);
    $(modal_id + ' .modal-body .content').removeClass("content-success");
    $(modal_id + ' .modal-body .content').removeClass("content-error");
    $(modal_id + ' .modal-body .content').addClass("content-warning");
    showModal(modal_id);
}

function showModal(id) {
    $(id).addClass('show');
    $(id).show();
}
function hideModal(id) {
    $(id).removeClass('show');
    $(id).hide();
}

function refresh(message = "") {
    $('#btn-search-pnr').attr('flag', 'refresh');
    $('#btn-search-pnr').attr('message', message);
    $('#btn-search-pnr').trigger('click');
}

function resetHTML(flag = '') {
    $('.error').hide();

    $('#passenger-table tbody').html('');
    $('#option-table tbody').html('');
    if(flag == 'refresh') return;

    $('#dep-journey').html('');
    $('#dep-price').html(-1);
    $('#dep-deptime').html('');
    $('#dep-arvtime').html('');
    $('#dep-class').html('');
    $('#dep-flightno').html('');
    $('#dep-logo').attr("src", '');
    $('.li-hide-dep-reservation').hide();
    $('.li-hide-dep-payment').hide();
    $('.wrap-manage-booking #dep-status').css('color', '#696969');

    $('#ret-journey').html('');
    $('#ret-price').html(-1);
    $('#ret-deptime').html('');
    $('#ret-arvtime').html('');
    $('#ret-class').html('');
    $('#ret-flightno').html('');
    $('#ret-logo').attr("src", '');
    $('.li-hide-ret-reservation').hide();
    $('.li-hide-ret-payment').hide();
    $('.wrap-manage-booking #ret-status').css('color', '#696969');

    $('.wrap-info-pnr').hide();
}

function getLogo(airline) {
    if (airline == 'VJA' || airline == 'VJ') return '/images/airline-icons/vj1.png';
    else if (airline == 'VZ') return '/images/airline-icons/vz.png';
    else if (airline == 'BBA') return '/images/airline-icons/qh3.webp';
    else if (airline == 'VNA') return '/images/airline-icons/vna_new.png';
    else if (airline == 'VNP') return '/images/airline-icons/pacific.png';
    else if (airline == 'VTA') return '/images/airline-icons/vta.png';
}

function getIconNoti($name) {
    if ($name == 'success') return '<i class="fa fa-check-circle-o success" aria-hidden="true"></i>';
    else if ($name == 'warning') return '<i class="fa fa-exclamation-triangle warning" aria-hidden="true"></i>';
    else if ($name == 'error') return '<i class="fa fa-times-circle error" aria-hidden="true"></i>'
}

function resetInfoPNR() {
    $("#journeys").html("");
    $("#tbody-passengers").html("");
    $("#tbody-options").html("");
    $("#collapse-charges-dep .card-body").html("");
    $("#charges-journey-dep").html("");
    $("#collapse-charges-ret .card-body").html("");
    $("#charges-journey-ret").html("");
    $("#btn-payment-pnr").hide()
    $(".wrap-info-pnr").hide();
}

function download(data, filename, type = "text/plain") {
    var file = new Blob([data], {type: type});
    if (window.navigator.msSaveOrOpenBlob) // IE10+
        window.navigator.msSaveOrOpenBlob(file, filename);
    else { // Others
        var a = document.createElement("a"),
                url = URL.createObjectURL(file);
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(function() {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);  
        }, 0); 
    }
}