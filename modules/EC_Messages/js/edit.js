$(document).ready(function () {
    $('div.label[data-label=LBL_CONTENT]').append('<span class="content-length"><span id="content_length">0</span> kí tự</span>');

    $('textarea#content').on('input', function(event) {
        $('#content_length').text($(this).val().length);
    });

    // $('select#type').change(function() {
    //     $(this).find(':selected').each(function() {
    //         if($(this).val() == 'send_sms_list_dynamic') {
    //             $('div.label[data-label=LBL_CONTENT]').append('<button type="button" id="btn-add-param-sms" class="btn btn-primary btn-add-param-sms">Thêm biến</button>');
    //         }
    //         else {
    //             $('.btn-add-param-sms').remove();
    //         }

    //         if($(this).val() == 'send_zalo_broadcast') {
    //             $('textarea#content').hide();
    //             $('.card-content-zalo-broadcast').show();
                
    //             $("select#send_from").val("OA Travelpass");
    //             $("select#message_type").val("advertisement");

    //             $(".edit-view-row-item[data-field=file]").hide();
    //             $(".edit-view-row-item[data-field=send_date]").hide();
    //         }
    //         else {
    //             $('textarea#content').show();
    //             $('.card-content-zalo-broadcast').hide();

    //             $("select#send_from").val("Travelpass");

    //             $(".edit-view-row-item[data-field=file]").show();
    //             $(".edit-view-row-item[data-field=send_date]").show();
    //         }
    //     });
    // });

    // $(document).on('click', '#btn-add-param-sms', function () {
    //     let old_value = $('textarea#content').val();
    //     let count = (old_value.match(/{{/g) || []).length + 1;
    //     let variable = `{{bien${count}}}`;
    //     $('textarea#content').val(old_value + variable);
    // });

    // // Submit event
	// $('#EditView').submit(function(){
	// 	let action 		= $('#EditView input:hidden[name="action"]').val();
	// 	let return_id 	= $('#EditView input:hidden[name="return_id"]').val();
    //     let type_sms    = $("#type").val();
    //     let file_sms    = $("#file_file").val();

	// 	if(action == 'Save'){
    //         if(!type_sms){
	// 		    $('#type').focus();
    //             let text_warning = 'Vui lòng chọn Loại chiến dịch.';
    //             showToastWarning(text_warning);
	// 		    return false;
    //         }

    //         if((type_sms == 'send_sms_list_static' || type_sms == 'send_sms_list_dynamic') && !return_id){
    //             if(!file_sms){
    //                 let text_warning = 'Vui lòng chọn file.';
    //                 showToastWarning(text_warning);
    //                 return false;
    //             }
    //         }

    //         $('#send_from').prop('disabled', false);
    //         return true;
    //     }
    // });
});
