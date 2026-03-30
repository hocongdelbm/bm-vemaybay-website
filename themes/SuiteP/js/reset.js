$(document).ready(function(){
    // Fix display full width
    const arr_box = [
        "line_itineraries", // Hành trình
        "line_details", // Chi tiết vé
        "line_passengers", // Hành khách
        "line_relate_voucher", // Phiếu thu
        "line_items", // ec_hoanve
    ];
    arr_box.forEach(element => {
        // Detail view
        if(element) {
            $(`.detail-view-row-item[data-field="${element}"] .label`).remove();
            $(`.detail-view-row-item[data-field="${element}"] .detail-view-field`).removeClass("col-8 col-10 col-sm-10");
            $(`.detail-view-row-item[data-field="${element}"] .detail-view-field`).addClass("col-12 col-sm-12");
        }

        // Edit view
        $(`.edit-view-row-item[data-field="${element}"] .label`).remove();
        $(`.edit-view-row-item[data-field="${element}"] .edit-view-field`).removeClass("col-8 col-sm-8");
        $(`.edit-view-row-item[data-field="${element}"] .edit-view-field`).addClass("col-12 col-sm-12");
    });
});