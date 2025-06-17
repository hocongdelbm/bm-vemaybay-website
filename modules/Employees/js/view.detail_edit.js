$(document).ready(function () {
    // Fix display full width - EDIT
    $('.edit-view-row-item[data-field="work_history"] .label').remove();
    $('.edit-view-row-item[data-field="work_history"] .edit-view-field').removeClass("col-sm-8");
    $('.edit-view-row-item[data-field="work_history"] .edit-view-field').addClass("col-sm-12 col-md-12");

    // Fix display full width - DETAIL
    $('.detail-view-row-item[data-field="work_history"] .label').remove();
    $('.detail-view-row-item[data-field="work_history"] .detail-view-field').removeClass("col-sm-8");
    $('.detail-view-row-item[data-field="work_history"] .detail-view-field').addClass("col-sm-12 col-md-12");
});