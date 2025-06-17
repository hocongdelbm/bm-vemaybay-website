$(document).ready(function() {
	$("#line_items_label").hide();
	$("#line_items_label").next().attr("colspan", "4");

	// $(".employee-select").chosen({no_results_text: "Không tìm thấy kết quả phù hợp", search_contains: true});
	$(".employee-select").select2();


	$("#btnAddEmployee").click(function() {
		var ln = $("#employee_row_count").val();
		var emp_ln = insertEmployeeRow(ln);
		$("#employee_row_count").val(parseInt(ln) + 1);
		$("#employee_area").append(emp_ln);
		markOrder();

		$(".employee-select").select2();
		// $(".employee-select").chosen({no_results_text: "Không tìm thấy kết quả phù hợp", search_contains: true});
	});

	$(document).on("click", ".btnAddRow", function() {
		var overtime_ln = $("#overtime_row_count"+$(this).attr("ln")).val();
		var row = insertOverTimeRow($(this).attr("ln"), overtime_ln);

		$("#tbl_overtimedetail"+$(this).attr("ln")+">tbody").append(row);
		Calendar.setup ({ 
			inputField : "date_chosen"+$(this).attr("ln")+"_"+overtime_ln,
			daFormat : "%d-%m-%Y %H:%M", 
			button : "date_chosen_btn"+$(this).attr("ln")+"_"+overtime_ln+"trigger", 
			singleClick : true, 
			dateStr : "", 
			step : 1, 
			weekNumbers:false 
		}); 
		markLineOrder($(this).attr("ln"));
		$("#overtime_row_count"+$(this).attr("ln")).val(parseInt(overtime_ln) + 1);
	});
});

function insertEmployeeRow(ln) {
	html = `<div class="employee-row" id="employee_row${ln}">
				<span class="employee-title" id="employee_title${ln}">
					<span class="fw-semibold" id="employee_lbl${ln}">Nhân viên thứ ${ln}</span>
					<button class="remove_employees" title="Xóa nhân viên" type="button" onclick="markEmployeeRowDeleted(${ln})"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ec2029" class="bi bi-dash-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/></svg></button> 
				</span>
				<div class="employee-name"> 
					<label class="text-label">Tên nhân viên: </label><select class="employee-select" name="employee_id[]">${$("#employee_list").val()}</select> 
				</div> 
				<table id="tbl_overtimedetail${ln}" class="tbl_overtimedetail table-details__booking" border="0" cellpadding="0" cellspacing="0"> 
					<thead> 
						<tr id="first-row"> 
							<th width="5%" align="center">Dòng</th> 
							<th width="17%" align="center">Ngày</th> 
							<th width="10%" align="center">Từ giờ</th> 
							<th width="10%" align="center">Đến giờ</th> 
							<th width="50%" align="left">Ghi chú</th> 
						</tr> 
					</thead> 
					<tbody></tbody>
					<tfoot> 
						<tr class="footer-tr"> 
							<td colspan="5" class="text-start"><input type="button" class="btn btn-primary btnAddRow" ln="${ln}" value="Thêm dòng" title="Thêm dòng" /></td> 
							<input type="hidden" id="overtime_row_count${ln}" value="1"> 
						</tr> 
					</tfoot> 
				</table> 
				<input type="hidden" name="employee_deleted[]" id="employee_deleted${ln}" value="0"> 
			</div>`;
	return html;
}

function markEmployeeRowDeleted(ln) {
	$("#employee_deleted"+ln).val(1);
	$("#employee_row"+ln).hide();
	markOrder();
}

function markOrder() {
	// đánh lại stt nhân viên
	var k = 0;
	var emp_deleted = $("input[name=\'employee_deleted[]\']");
	for(i = 0; i < emp_deleted.length; i++) { 
		if(emp_deleted[i].value == 0) {
			$("#employee_lbl"+(i+1)).text("Nhân viên thứ "+(k+1));
			k++;
		}
	}
}

function insertOverTimeRow(group, ln) {
	html = `<tr id="overtime_line${group}_${ln}">
				<td id="row${group}_${ln}" class="row-no"><input class="center border-0 fw-semibold" id="cell_no${group}_${ln}" type="text" value="${ln}" readonly></td>
				<td>
					<span class="dateTime d-flex align-items-center position-relative">
						<input autocomplete="off" type="text" name="date_chosen${group}[]" id="date_chosen${group}_${ln}" class="date_input date_chosen" title="Ngày" size="11" maxlength="10">
						<button type="button" id="date_chosen_btn${group}_${ln}trigger" class="icon_dateTime" onclick="return false;">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
								<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
								<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
							</svg>
						</button>
					</span>
				</td> 
				<td> 
					<div class="d-flex gap-1 align-items-center">
						<input type="text" class="time-input" name="from_hour${group}[]">
						<span>:</span>
						<input type="text" name="from_minute${group}[]" class="time-input"> 
					</div>
				</td> 
				<td> 
					<div class="d-flex gap-1 align-items-center">
						<input type="text" class="time-input" name="to_hour${group}[]">
						<span>:</span>
						<input type="text" class="time-input" name="to_minute${group}[]"> 
					</div>
				</td> 
				<td> 
					<div class="d-flex gap-2 align-items-center">
						<textarea rows="1" cols="100" class="middle" name="description${group}[]"></textarea> 
						<button class="remove_employees" title="Xóa dòng làm ngoài giờ" type="button" onclick="markOverTimeRowDeleted('${group}_${ln}', '${group}')">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ec2029" class="bi bi-dash-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"></path></svg>
						</button> 
						<input type="hidden" name="overtime_deleted${group}[]" id="overtime_deleted${group}_${ln}" value="0"> 
					</div>
				</td> 
			</tr>`;

	return html;
}

function markOverTimeRowDeleted(ln, group) {
	$("#overtime_deleted"+ln).val(1);
	$("#overtime_line"+ln).hide();
	markLineOrder(group);
}

function markLineOrder(group, ln) {
	// đánh lại stt các dòng ngoài giờ
	var k = 0;
	var ln_deleted = $("input[name=\'overtime_deleted"+group+"[]\']");
	for(i = 0; i < ln_deleted.length; i++) { 
		if(ln_deleted[i].value == 0) {
			$("#cell_no"+group+"_"+(i+1)).val(k+1);
			k++;
		}
	}
}