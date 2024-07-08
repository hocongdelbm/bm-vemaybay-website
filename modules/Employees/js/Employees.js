$(document).ready(function () {
	// // thay thế phím TAB bằng phím ENTER
	// var $inputs = $('input:text');
	// $inputs.on('keypress', function (e) {
	// 	if (e.which === 13) {
	// 		var ind = $inputs.index(this);
	// 		$inputs.eq(ind + 1).focus();
	// 		$inputs.eq(ind + 1).select();
	// 	}
	// });

    
    $('#work_history_label').hide();
    $('#LBL_WORK_HISTORY > table > tbody > tr:nth-child(2) > td:nth-child(2)').attr('colspan','4');
    
	// không cho nhấn phím enter trên form
	$('input:text').bind("keypress", function (e) {
		if (e.keyCode == 13) return false;
	});

	// nhấn nút thêm dòng
	$('#btnAddRow').click(function () {
		var row_count = parseInt($('#row_count').val());
		$('#last-row').before(insertNewRow(row_count));
		row_count++;
		$('#row_count').val(row_count);
		// không cho nhấn phím enter trên form
		$('input:text').bind("keypress", function (e) {
			if (e.keyCode == 13) return false;
		});
		// thay thế phím TAB bằng phím ENTER
		var $inputs = $('input:text');
		$inputs.on('keypress', function (e) {
			if (e.which === 13) {
				var ind = $inputs.index(this);
				$inputs.eq(ind + 1).focus();
				$inputs.eq(ind + 1).select();
			}
		});

		$(".date-jquery").datepicker({
			dateFormat: 'dd/mm/yy',//check change
			changeMonth: true,
			changeYear: true,
			defaultDate: new Date(),
		});
	});

	$(".date-jquery").datepicker({
		dateFormat: 'dd/mm/yy', //check change
		changeMonth: true,
		changeYear: true,
		defaultDate: new Date(),
	});

});


function insertNewRow(ln) {
	var html = '';
        html += '<tr id="ct_line_' + ln + '">';
        html += '<td align="center"><input type="text" name="ct_date_start[]" id="ct_date_start' + ln + '" value="" class="date-jquery" placeholder="dd/mm/yyyy" autocomplete="off"/> </td>';
        html += '<td align="center"><input type="text" name="ct_date_end[]" id="ct_date_end' + ln + '" value="" class="date-jquery" placeholder="dd/mm/yyyy" autocomplete="off"/> </td>';
        html += '<td align="center"><select name="ct_status[]" id="ct_status' + ln + '" ><option value="Active">Đang làm việc</option><option value="Online">Online</option><option value="Absent">Vắng mặt</option><option value="InActive">Nghỉ việc</option></select></td>';
        html += '<td align="center"><input type="checkbox" onclick="checkWithSalary('+ln+');" name="ct_chk_with_salary[]" id="ct_chk_with_salary' + ln + '" value="0" /></td>';
        html += '<td align="center"><input type="text" name="ct_description[]" id="ct_description' + ln + '" value="" /></td>';
        html += `<td align="center">
	   				<button title="Xóa" class="button-remove-in-edit" type="button" onclick="markRowDeleted(${ln})">
					   	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
					</button>
					<input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted${ln}" />
					<input type="hidden" name="ct_detail_id[]" id="ct_detail_id${ln}" value="" />
					<input type="hidden" name="ct_with_salary[]" id="ct_with_salary${ln}" value="0" />
				</td>`;
        html += '</tr>';
	$(document).ready(function () {
		$(function() {
			$(".date-jquery").datepicker({
				dateFormat: 'dd/mm/yy',//check change
				changeMonth: true,
				changeYear: true,
				defaultDate: new Date(),
			});
		});
	});

	return html;
}
 

function checkWithSalary(ln) {
	if(	$('#ct_chk_with_salary' + ln).is(":checked"))
		$('#ct_with_salary' + ln).val(1);
	else
		$('#ct_with_salary' + ln).val(0);
}
 
// đánh dấu dòng bị xóa
function markRowDeleted(ln) {
	$('#ct_line_' + ln).hide();
	$('#ct_deleted' + ln).val(1);
}
