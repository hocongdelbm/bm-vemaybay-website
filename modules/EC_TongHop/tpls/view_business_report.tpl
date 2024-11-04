{literal}
<script>
	$(document).ready(function() {	
		// Lấy các tham số từ URL
		const urlParams = new URLSearchParams(window.location.search);
		const savedValue = urlParams.get('date_select') || 'this_week';

		$(`input[name="date_select"][value="${savedValue}"]`).prop('checked', true);
		
		$(document).on("change", "input[name='date_select']", function() {
			$(".container-waiting").show();

			const selectedValue = $(this).val();
			const urlParams = new URLSearchParams(window.location.search); 
			urlParams.set('date_select', selectedValue);
			window.location.href = '?' + urlParams.toString();
		});

		$('.btn-add-qc-cost').on('click', function() {
			let from_date  	= $(this).attr('data-from-date');
			let to_date  	= $(this).attr('data-to-date');
			let user_id  	= $(this).attr('data-user-id');
			let site_name  	= $(this).attr('data-site');
			let site_time  	= $(this).attr('data-time');

			$("#site-name").html(site_name + ' ' + site_time);
			$("#from_date").val(from_date);
			$("#to_date").val(to_date);
			$("#user_id").val(user_id);
		});

		$('#real-time').on('click', function() {
			$(".container-waiting").show();
		});

		$('#frmEditCostQc').on('submit', function() {
			if($("#from_date").val() == '' || $("#to_date").val() == '' || $("#user_id").val() == ''){
				showModalNotify('error', 'Không xác định được ID site vé. Liên hệ admin để được hỗ trợ!')
				return false;
			}

			if($("#cost-qc").val() == ''){
				showToastWarning('Vui lòng nhập chi phí quảng cáo!')
				return false;
			}
		});

		document.getElementById('cost-qc').addEventListener('input', function (e) {
			let input = e.target.value.replace(/\D/g, '');
			input = input.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
			e.target.value = input;
		});
    })
</script>
<style>
	.box-report__weekly{
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		grid-gap: 15px;
	}

	table.table-report__weekly thead tr th {
        background: #fff2cc;
    }

	#date_select_container .form-group{
		display: flex;
		align-items: center;
		gap: 8px;
	}	
</style>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
	<h1 class="title">BÁO CÁO KINH DOANH TUẦN</h1>
	<svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
		<path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
	</svg>
</div>

<div class="box-section overflow-auto position-relative mt-0">
	<form id="ec_search_form" name="search_form" method="POST" action="index.php?module=EC_TongHop&action=businessreport&date_select={$CURRENT_OPTION}">
		<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
			<path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
		</svg>
		<div class="action--wrap d-flex gap-4 align-items-center justify-content-between w-100">
			<div id="date_select_container" class="d-flex align-items-center gap-4">
				<div class="form-group">
					<input type="radio" name="date_select" class="form-check-input m-0" value="this_week" id="this_week" checked>
					<label for="this_week">Tuần này</label>
				</div>
				<div class="form-group">
					<input type="radio" name="date_select" class="form-check-input m-0" value="previous_week" id="previous_week">
					<label for="previous_week">Tuần trước</label>
				</div>
				<div class="form-group">
					<input type="radio" name="date_select" class="form-check-input m-0" value="this_month" id="this_month">
					<label for="this_month">Tháng này</label>
				</div>
				<div class="form-group">
					<input type="radio" name="date_select" class="form-check-input m-0" value="previous_month" id="previous_month">
					<label for="previous_month">Tháng trước</label>
				</div>
			</div>
			<button class="btn btn-primary" id="real-time" name="real-time">
				Cập nhật
			</button>
		</div>
	</form>

	<div class="box-report__wrap box-report__weekly mt-3">
        {$DATA_REPORT}
	</div>

	<form method="post" action="index.php?module=EC_TongHop&action=businessreport&date_select={$CURRENT_OPTION}" name="frmEditCostQc" id="frmEditCostQc">
		<input type="hidden" name="module" id="module" value="EC_TongHop">
		<input type="hidden" name="action" id="action" value="businessreport">
		<input type="hidden" name="from_date" id="from_date" value="{$CURRENT_WEEK_FROMDATE}">
		<input type="hidden" name="to_date" id="to_date" value="{$CURRENT_WEEK_TODATE}">
		<input type="hidden" name="user_id" id="user_id" value="">

		<div class="modal_edit-extensions modal fade" id="modalEditCostQc" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalEditCostQcLabel" aria-modal="true" role="dialog">
			 <div class="modal-dialog modal-dialog-centered">
				  <div class="modal-content">
					   <div class="modal-header">
							<h2 class="modal-title fs-5" id="modalEditCostQcLabel">Chi phí QC <span id="site-name"></span></h2>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					   </div>
					   <div class="modal-body">
							<div class="form-control border-0 p-0">
								<label for="cost-qc" class="label">Nhập chi phí QC</label>
								<input type="text" class="box-input allow-number-only" id="cost-qc" name="cost-qc">
							</div>
					   </div>
					   <div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
							<input type="submit" class="btn btn-primary" data-bs-dismiss="modal" name="btnSaveCostQc" id="btnSaveCostQc" value="Lưu" title="Lưu">
					   </div>
				  </div>
			 </div>
		</div>
   </form>
</div>
