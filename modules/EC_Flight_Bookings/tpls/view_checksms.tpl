<script src="custom/jqueryui/plugins/jquery.table_filter.js"></script>

{literal}
	<script type="text/javascript">
    $(document).ready(function(){

		$('#txtSearch').table_filter({
			'table':'#tbl-sms-data',
			'no_result': 'Không tìm thấy dữ liệu',
			'no_result_selector': '#no-result'
		});
		
		$('#CurrentPortSms, input:radio[name="msg_type"], #msg_limit, #txtRecieveNumber').change(function(){
			var CurrentPort = $('#CurrentPortSms :selected').val();
			var msg_limit = $('#msg_limit :selected').val();
			var msg_type = $('input:radio[name="msg_type"]:checked').val();
			var recieveNumber = $.trim($('#txtRecieveNumber').val());

			if(msg_type == 1 && CurrentPort == ''){
				let warning = 'Vui lòng chọn port!';
				showToastWarning(warning);

				$('#CurrentPortSms').focus();
				return false;
			}
			
			$.ajax({	
			  cache: false,
			  type: 'POST',
			  timeout: 30000,
			  data: 'CurrentPort=' + CurrentPort + '&PageCur=1' + '&MsgType='+ msg_type +'&MsgLimit=' + msg_limit + '&RecieveNumber=' + recieveNumber,
			  url:"index.php?entryPoint=entryPointGetListSMS",
			  success: function(output){
				var json = $.parseJSON(output);
				if(json.code == 1){
					$('#tbl-sms-data tbody').html(displaySMSData(json.msg));
				} else {
					let warning = json.msg;
					showToastWarning(warning);
					return false;
				}
			  },
			  error: function(){
				let warning = 'Lỗi kết nối tới máy chủ. Vui lòng thử lại sau';
				showToastWarning(warning);
				  return false;
			  }
			});
			
		});
    });
	
	function displaySMSData(data){
		var html = '';
		var stt = 1;
		$.each(data, function(i,item){
			html += '<tr>\
					<td class="text-center fw-semibold">'+ stt +'</td>\
					<td class="text-center">'+ data[i].caller +'</td>\
					<td class="text-center">'+ data[i].datetime +'</td>\
					<td>'+ data[i].content +'</td>\
				</tr>';
			stt++;
		});
		return html;
	}
</script>                
{/literal}

<h1 class="title">KIỂM TRA SMS</h1>

<div class="box-section">
	<table cellpadding="0" cellspacing="0" class="table-config table-details__booking table-smsport search-form w-50">
		<tr>
			<td width="30%"><strong>Chọn port</strong></td>
		<td width="70%"><select name="CurrentPort" id="CurrentPortSms">{$PORT_LIST}</select></td>
		</tr>
	<tr>
			<td><strong>Loại</strong></td>
			<td>
				<div class="d-flex align-items-center gap-2">
					<label class="form-check-label" for="inbound">
						<input checked="checked" type="radio" class="form-check-input" id="inbound" name="msg_type" value="1" /> Tin đã nhận</label>
		
					<label class="form-check-label" for="outbound">
						<input type="radio" class="form-check-input" id="outbound" name="msg_type" value="0" /> Tin đã gửi</label>
				</div>
			</td>
	</tr>
	<tr>
			<td><strong>Hiển thị</strong></td>
			<td>
				<select id="msg_limit" name="msg_limit">
					{php}
						for($i = 200; $i <= 2000; $i += 200){
							echo '<option value="'.$i.'">'.$i.'</option>';
						}
					{/php}
				</select>
			</td>
		</tr>
		<tr>
			<td><strong>Số điện thoại nhận tin</strong></td>
			<td><input type="text" class="w-100" name="txtRecieveNumber" id="txtRecieveNumber" value="" placeholder="Nhập số điện thoại nhận tin cần tìm"></td>
		</tr>
	<tr>
			<td><strong>Tìm kiếm</strong></td>
			<td>
				<input type="text" class="w-100" name="txtSearch" id="txtSearch" value="" placeholder="Nhập nội dung tin nhắn cần tìm" />
			</td>
	</tr>
	</table>
				
	<table id="tbl-sms-data" class="table-details__booking table-sms-data mt-3">
	<thead>
		<tr>
			<th width="5%">STT</th>
			<th width="15%">Số điện thoại</th>
			<th width="12%">Thời gian</th>
			<th>Nội dung</th>
		</tr>
	</thead>
	<tbody></tbody>
	</table>
</div>	
