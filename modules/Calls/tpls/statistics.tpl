{literal}
<style>
     .call-statistics__wrap{
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin-top: 30px;
     }

     .call-statistics__direction,
     .call-statistics__sources,
     .call-statistics__duration--average{
          width: 70%;
          height: 400px;
     }


     .call-statistics__direction canvas, 
     .call-statistics__sources canvas,
     .call-statistics__duration--average canvas{
          width: 70% !important;
     }

     .call-statistics{
          display: flex;
          align-items: center;
          justify-content: center;
          flex-direction: column;
          gap: 15px;
     }

     .total_call{
          font-size: 15px;
          font-weight: 600;
          line-height: 1.5;
          color: #333;
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 8px;
     }

     @keyframes spinner-modal-loader {
          100% {
               transform: rotate(1turn);
          }
     }

     @media screen and (max-width: 575px),
     (orientation: landscape) and (max-width: 950px) {
          .call-statistics__wrap {
               flex-direction: column;
               gap: 20px;
               margin-top: 0px !important;
          }
          
          .call-statistics__direction,
          .call-statistics__sources,
          .call-statistics__duration--average {
               width: 100%;
          }

          .call-statistics__direction canvas, 
          .call-statistics__sources canvas,
          .call-statistics__duration--average canvas{
               width: 100% !important;
          }
     }
</style>
<script type="text/javascript">
	$(document).ready(function() {	
          $(document).on("change", "input[type=radio][name=optionRadio]", function(e) {
               $('#from_date').val($('input[name=optionRadio]:checked').attr('fromdate'));
               $('#to_date').val($('input[name=optionRadio]:checked').attr('todate'));

               sessionStorage.setItem('optionRadio_calls', $(this).val());
               sessionStorage.removeItem('date_select_calls');
          });

          $(document).on("change", "#date_select", function(e) {
          $("#from_date").val($(this).find("option:selected").attr("fromdate"));
          $("#to_date").val($(this).find("option:selected").attr("todate"));

               sessionStorage.setItem('date_select_calls', $(this).val());
               sessionStorage.removeItem('optionRadio_calls'); 
          });

          // Check sessionStorage - js
          const selectOption 		= document.getElementById('date_select');
          const radioOptions 		= document.getElementsByName('optionRadio');
          const savedSelectOption 	= sessionStorage.getItem('date_select_calls');
          if (savedSelectOption) {
               selectOption.value = savedSelectOption;
          } else {
               const savedRadioOption = sessionStorage.getItem('optionRadio_calls');
               if (savedRadioOption) {
                    radioOptions.forEach(radio => {
                         if (radio.value === savedRadioOption) {
                              radio.checked = true;
                         }
                    });
               }
          }

          $(".view-detail-calls").click(function() {
               if($("#detail-calls__wrap").hasClass('hidden')){
                    $("#detail-calls__wrap").addClass('show');
                    $("#detail-calls__wrap").removeClass('hidden');
               }

               let detail_user_call     = $("#detail-calls__wrap");
               let tr_table             = $("#detail-calls__wrap tbody tr");

               let user_id         = $(this).attr("data-user_id");
               let user_name       = $(this).attr("data-username");
               let call_direciton  = $(this).attr("data-direction").trim();
               let myDirection = {
                    outbound: '"cuộc gọi đi"',
                    ob_answer: '"cuộc gọi đi trả lời"',
                    ob_noanswer: '"cuộc gọi đi không trả lời"',
                    inbound: '"cuộc gọi đến"',
                    missed: '"cuộc gọi nhỡ"',
                    suddenly: '"cuộc gọi nhá máy"',
                    spam: '"cuộc gọi số rác"',
                    internal: '"cuộc gọi nội bộ"'
               };
               let val_direction = myDirection[call_direciton];
               tr_table.each(function( index ) {
                    if($(this).hasClass('show') || $(this).hasClass('hide')){
                         $(this).removeClass('show');
                         $(this).removeClass('hide');
                    } 

                    if ($(this).attr("data-user_id") == user_id && $(this).attr("data-direction").indexOf(call_direciton) != -1) {
                         $(this).addClass('show');
                    } else {
                         $(this).addClass('hide');
                    }
               });

               $("#type-direction").html(val_direction);
               $("#name-employees").html(user_name);

          });

          // function getDetailCallUser(user_id, username, direction) {
          //      $.ajax({
          //           url: "index.php?entryPoint=entryPointStatisticsCall",
          //           type: "POST",
          //           data: {
          //                fdate: $('#from_date').val(),
          //                tdate: $('#to_date').val(),
          //                user_id: user_id,
          //                username: username,
          //                direction: direction,
          //                for: "getDetailCallUser",
          //           },
          //           beforeSend: function() {
          //                $(".container-waiting").show();
		// 		     $(".detail_calls--user").remove();
          //           },
          //           success: function(response) {
          //                $(".container-waiting").hide();
          //                $("#detail-calls__wrap").html('<div class="d-flex justify-content-center align-items-center gap-2 my-3"><h3 class="sub-title mb-0">Danh sách "' + direction + '" của nhân viên ' + username + '</h3><input type="button" class="ms-2 hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Hide"></div>');
		// 		     $(".detail_calls--user").remove();
          //                $("#detail-calls__wrap").after(response);
          //           }
          //      });
          // }

          $(document).on("click", "#hide_detail_btn", function() {
			$("#detail-calls__wrap").addClass('hidden');
		});

          $(document).on("click", ".btnSendTeleBriefEmp", function() {
               let emp_id = $(this).attr("data-id");
               let modal_id = $(this).attr("data-modal-id");
               let emp_name = $("#reviewEmp_name_" + emp_id + "").val() || '';
               let from_date = $("#reviewEmp_fromdate_" + emp_id + "").val() || '';
               let to_date = $("#reviewEmp_todate_" + emp_id + "").val() || '';
               let emp_outbound = $("#reviewEmp_outbound_" + emp_id + "").val() || '';
               let emp_outbound_answer = $("#reviewEmp_outbound_answer_" + emp_id + "").val() || '';
               let emp_question_ticket = $("#reviewEmp_question_ticket_" + emp_id + "").val() || 0;
               let emp_noanswer_up_15 = $("#reviewEmp_outbound_noanswer_up_15_" + emp_id + "").val() || 0;
               let emp_noanswer_under_15 = $("#reviewEmp_outbound_noanswer_under_15_" + emp_id + "").val() || 0;
               let emp_noanswer_unconnected = $("#reviewEmp_outbound_noanswer_unconnected_" + emp_id + "").val() || 0;
               let emp_noanswer_nonote = $("#reviewEmp_outbound_noanswer_nonote_" + emp_id + "").val() || 0;
               let total_talk_outbound = $("#reviewEmp_outbound_answer_total_talk_" + emp_id + "").val() || 0;

               if (parseInt(to_date.length) === 0) {
                    to_date = from_date;
               }

               $.ajax({
                url: "index.php?entryPoint=entryPointStatisticsCall",
                type: "POST",
                cache: false,
                data: {
                    employee_id: emp_id,
                    from_date: from_date,
                    to_date: to_date,
                    emp_outbound: emp_outbound,
                    emp_outbound_answer: emp_outbound_answer,
                    emp_question_ticket: emp_question_ticket,
                    emp_noanswer_up_15: emp_noanswer_up_15,
                    emp_noanswer_under_15: emp_noanswer_under_15,
                    emp_noanswer_unconnected: emp_noanswer_unconnected,
                    emp_noanswer_nonote: emp_noanswer_nonote,
                    total_talk_outbound: total_talk_outbound,
                    for: 'sendTeleConfirmCallSales',
                },
                beforeSend: function () {},
                success: function (response) {
                    $("#" + modal_id).modal("hide");
                    try {
                         let result = JSON.parse(response);
                         console.warn(result);
                         if (result.ok === true) { 
                              showModalNotify('success', 'Gửi thông tin callsales qua Tele thành công!');
                         } else {
                              $mess_error = result.description || '';
                              showModalNotify('error', 'Gửi thông tin callsales thất bại. Liên hệ IT để được hỗ trợ!', $mess_error);
                         }
                    } catch (error) {
                         console.error("Lỗi xử lý JSON:", error);
                    }
                },
               error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
               }
            });
		});
     });
 </script>
{/literal}


<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
     <h1 class="title">Thống kê cuộc gọi</h1>
	<svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block hide-landscape" viewBox="0 0 16 16">
		<path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
	</svg>
</div>

<div class="box-section mb-5 position-relative">
	<div class="overlay-mobile"></div>

     <form action="index.php" method="post" name="search_form" id="ec_search_form">
          <input type="hidden" name="module" value="Calls"/>
          <input type="hidden" name="action" value="statistics"/>

          <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-md-none d-block" viewBox="0 0 16 16">
               <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
          </svg>
 
          <div class="d-flex align-items-center gap-2 action--wrap">
               <select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>
            
               <div class="from-to-date--wrap d-inline-flex gap-3 align-items-center">
                    <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
                         <span class="sublabel">Từ ngày: </span>    
                         <div class="dateTime d-flex gap-2 position-relative">
                              <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
                              <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                   <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                              </svg>
                              </button>
                              {literal}
                              <script type="text/javascript">
                              Calendar.setup({
                                        inputField: "from_date",
                                        daFormat: "%d-%m-%Y",
                                        button: "fdate_trigger",
                                        singleClick: true,
                                        dateStr: "",
                                        step: 1
                                   }
                              );
                              </script>
                              {/literal}
                         </div>
                    </div>
     
                    <svg width="40" height="20" fill="none">
                         <g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M33.5 8.5L36 11M4 11h32"></path>
                         </g>
                         <defs>
                              <clipPath id="icon_arrow_flight_long_svg__clip0">
                              <path fill="#fff" d="M0 0h40v20H0z"></path>
                              </clipPath>
                         </defs>
                    </svg>
     
                    <div class="d-flex gap-2 align-items-center date_trigger--wrap tdate_trigger--wrap">
                         <span class="sublabel">Đến ngày: </span>    
                         <div class="dateTime d-flex gap-2 position-relative">
                         <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE}" id="to_date" name="to_date" autocomplete="off">
                              <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                   <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                              </svg>
                              </button>
                              {literal}
                              <script type="text/javascript">
                              Calendar.setup({
                                        inputField: "to_date",
                                        daFormat: "%d-%m-%Y",
                                        button: "tdate_trigger",
                                        singleClick: true,
                                        dateStr: "",
                                        step: 2
                                   }
                              );
                              </script>
                              {/literal}
                         </div>
                    </div>
               </div>

               <div class="d-flex align-items-center gap-2 optionRadio--wrap">
                    <input type="radio" value="yesterday" id="yesterday" class="rd_time form-check-input" name="optionRadio" fromdate="{$YESTERDAY_FROMDATE}" todate="{$YESTERDAY_TODATE}">
                    <label class="cursor-pointer" for="yesterday">Hôm qua</label> 

                    <input type="radio" value="daybefore" id="daybefore" class="rd_time form-check-input" name="optionRadio" fromdate="{$DAYBEFORE_FROMDATE}" todate="{$DAYBEFORE_TODATE}">
                    <label class="cursor-pointer" for="daybefore">Hôm trước</label> 

                    <input type="radio" value="current_week" id="current_week" class="rd_time form-check-input" name="optionRadio" fromdate="{$CURRENT_WEEK_FROMDATE}" todate="{$CURRENT_WEEK_TODATE}">
                    <label class="cursor-pointer" for="current_week">Tuần này</label>

                    <input type="radio" value="previous_week" id="previous_week" class="rd_time form-check-input" name="optionRadio" fromdate="{$PREVIOUS_WEEK_FROMDATE}" todate="{$PREVIOUS_WEEK_TODATE}"> 
                    <label class="cursor-pointer" for="previous_week">Tuần trước</label> 
               </div>
          </div>
          
		<div class="button-action--wrap">
               <input type="submit" class="btn btn-primary button-action" name="btnSearch" id="btnSearch" value="Xem thống kê" title="Xem thống kê" />
			<input type="button" id="btnSearch_cancel" name="search" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" value="Hủy bỏ" title="Hủy bỏ"/>
          </div>
     </form>

     <!-- THỐNG KẾ DIRECTION - TYPE -->
     <div class="call-statistics__wrap">
          <section class="call-statistics__direction flex-fill">
               <canvas id="chartjs__calls-direction" class="mx-auto"></canvas>
          </section>
     </div>
     <div class="total-calls-quantity my-3">
          <div class="total_call">Tổng số cuộc gọi: <span>{$TOTAL_CALLS_QUANTITY}</span></div>
     </div>
</div>

<!-- ======= THỐNG KẾ THEO SĐT ======= -->
<!-- ======================================= -->
<h3 class="sub-title text-center">Số lượng cuộc gọi theo SĐT từ ngày {$FROM_DATE} đến ngày {$TO_DATE} </h3>
<div class="box-section">
     <table class="table-detail_sdt table-details__booking" cellpadding="0" cellspacing="0">
          <thead>
               <tr>
                    <th width="10%"></th>
                    <th>SĐT</th>
                    <th width="25%" align="center"><span title="outbound">Cuộc gọi đi</span></th>
                    <th width="25%" align="center"><span title="inbound">Cuộc gọi đến</span></th>
                    <th width="10%" align="center">Status</th>
               </tr>
          </thead>
          <tbody>
               {$COUNT_SDT}
               <tr class="footer-tr">
                    <td align="center" colspan="2"><span title="total"></span>Tổng cộng</td>
                    <td align="center"><span title="total_outbound">{$COUNT_SDT_OUTBOUND}</span></td>
                    <td align="center"><span title="total_inbound">{$COUNT_SDT_INBOUND}</span></td>
                    <td align="center"></td>
               </tr>
          </tbody>
     </table>
</div>

<!-- ======= THỐNG KẾ THEO NHÂN VIÊN ======= -->
<!-- ======================================= -->
<h3 class="sub-title text-center">Danh sách cuộc gọi nhân viên {$USER_NAME} từ ngày {$FROM_DATE} đến ngày {$TO_DATE} </h3>
<div class="box-section">
     <table class="table-detail_user table-details__booking" cellpadding="0" cellspacing="0">
          <thead>
               <tr>
                    <th width="3%" class="hide-mobile">STT</th>
                    <th>Họ tên</th>
                    <th width="8%" align="center"><span title="outbound">Cuộc gọi đi</span></th>
                    <th width="8%" align="center"><span title="outbound (Dưới 20s thoại)">Gọi đi (trả lời)</span></th>
                    <th width="8%" align="center"><span title="outbound (Trên 20s thoại)">Gọi đi (>=20s)</span></th>
                    <th width="8%" align="center"><span title="outbound">0 trả lời</span></th>
                    <th width="8%" align="center"><span title="inbound">Cuộc gọi đến</span></th>
                    <th width="8%" align="center"><span title="missed">Cuộc gọi nhỡ</span></th>
                    <th width="8%" align="center" class="hide-mobile"><span title="spam">Số rác</span></th>
                    <th width="8%" align="center" class="hide-mobile"><span title="suddenly">Nhá máy</span></th>
                    <th width="8%" align="center" class="hide-mobile"><span title="internal">Nội bộ</span></th>
                    <th width="8%" align="center" class="hide-mobile"><span title="internal">Tổng cộng</span></th>
                    <th width="8%" align="center" class="hide-mobile"></th>
               </tr>
          </thead>
          <tbody>
               {$CALLS_DATA}
               <tr class="footer-tr">
                    <td align="center" class="hide-mobile"></td>
                    <td align="center"><span title="total"></span>Tổng cộng</td>
                    <td align="center"><span title="outbound">{$TTL_OUTBOUND}</span></td>
                    <td align="center"><span title="outbound_answer">{$TTL_OUTBOUND_ANSWER}</span></td>
                    <td align="center"><span title="outbound_kpi">{$TTL_OUTBOUND_KPI}</span></td>
                    <td align="center"><span title="outbound_noanswer">{$TTL_OUTBOUND_NOANSWER}</span></td>
                    <td align="center"><span title="inbound">{$TTL_INBOUND}</span></td>
                    <td align="center"><span title="missed">{$TTL_MISSED}</span></td>
                    <td align="center" class="hide-mobile"><span title="spam">{$TTL_SPAM}</span></td>
                    <td align="center" class="hide-mobile"><span title="suddenly">{$TTL_SUDDENLY}</span></td>
                    <td align="center" class="hide-mobile"><span title="internal">{$TTL_INTERNAL}</span></td>
                    <td align="center" class="hide-mobile"><span title="total_emp">{$TTL_EMP}</span></td>
                    <td align="center" class="hide-mobile"></td>
               </tr>
          </tbody>
     </table>
</div>

<div id="detail-calls__wrap" class="hidden">
     <div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách <span id="type-direction"></span> của nhân viên <span id="name-employees"></span></h3><input type="button" class="ms-2 hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Hide"></div>
          <div class="box-section detail_calls--user">
               <table class="table-detail-calls table-details__booking table-details__sticky" cellpadding="0" cellspacing="0">
                    <thead>
                         <tr>
                              <th width="3%">STT</th>
                              <th width="10%" align="center">Mã cuộc gọi</th>
                              <th width="10%" align="center">Trạng thái</th>
                              <th width="8%" align="center">Gọi từ</th>
                              <th width="8%" align="center">Gọi đến</th>
                              <th width="8%" align="center">Nguồn</th>
                              <th width="10%" align="center">Thời gian</th>
                              <th width="8%" align="center">Thời lượng</th>
                              <th width="8%" align="center">Hội thoại</th>
                              <th align="center">Ghi chú</th>
                         </tr>
                    </thead>
                    <tbody>
                         {$ALL_OF_CALLS}
                    </tbody>
               </table>
          </div>
     </div>
</div>


<div class="bar-chart d-flex flex-wrap align-items-center gap-4">
     <div class="box-duration__chart flex-fill">
          <!-- THỐNG KÊ THỜI LƯỢNG TRUNG BÌNH CUỘC GỌI -->
          <!-- ======================================= -->
          <h3 class="sub-title text-center mt-5">Thời lượng bình quân cuộc gọi từ ngày {$FROM_DATE} đến ngày {$TO_DATE} </h3>
          <div class="box-section">
               <div class="call-statistics__wrap">
                    <section class="call-statistics__duration--average flex-fill">
                         <canvas id="chartjs__calls-duration--average" class="mx-auto"></canvas>
                    </section>
               </div>
          </div>
     </div>

     <div class="box-lead__chart flex-fill">
          <!-- THỐNG KÊ SỐ LƯỢNG CUỘC GỌI THEO SITE -->
          <!-- ==================================== -->
          <h3 class="sub-title text-center mt-5">Số lượng cuộc gọi theo website từ ngày {$FROM_DATE} đến ngày {$TO_DATE} </h3>
          <div class="box-section">
               <div class="call-statistics__wrap">
                    <section class="call-statistics__sources flex-fill">
                         <canvas id="chartjs__calls-sources" class="mx-auto"></canvas>
                    </section>
               </div>
          </div>
     </div>
</div>


{literal}
<script>
     // Lấy tham chiếu đến canvas
     const calls_direction           = document.getElementById('chartjs__calls-direction');
     const calls_duration_average    = document.getElementById('chartjs__calls-duration--average');

     // Dữ liệu cho biểu đồ - direction
     let data_direction = {
          labels: [
               'Cuộc gọi đến',
               'Cuộc gọi đi',
               'Cuộc gọi nhỡ',
               'Số rác',
               'Nhá máy',
               'Nội bộ',
          ],
          datasets: [
               {
                    type: 'line',
                    data: js_data_direction,
                    backgroundColor: 'rgb(75, 192, 192)',
                    borderColor: 'rgb(75, 192, 192)',
                    pointBorderWidth: 3,
                    tension: 0.2,
                    yAxisID: 'line-y-axis',
                    datalabels: {
                        align: 'end',
                        anchor: 'end',
                    }
               }, 
               {
                    type: 'bar',
                    data: js_data_direction,
                    backgroundColor: [
                         'rgb(27, 207, 180)',
                         'rgb(0, 169, 255)',
                         'rgb(255, 99, 132)',
                         'rgb(194, 222, 220)',
                         'rgb(255, 206, 86)',
                         'rgb(255,242,204)'
                    ],
                    borderWidth: 1, // Độ rộng viền
                    yAxisID: 'bar-y-axis',
                    datalabels: {
                        align: 'center',
                        anchor: 'center',
                        formatter: function(value, context) {
                              let total = js_data_direction.map(Number).reduce((a, b) => a + b, 0);
                              let percentage  = (value == 0) ? '' : (value / total * 100).toFixed(2) + '%';
                              return `${percentage}`;
                         }
                    }

               }
          ]
     };

     // Dữ liệu cho biểu đồ - duration__average
     let data_duration_average = {
          labels: ['Phone', 'Zalo'],
          datasets: [
               {
                    label: 'Thời lượng',
                    data: data_duration,
                    backgroundColor: 'rgb(0,169,255)',
                    borderWidth: 1,

               },
               {
                    label: 'Đổ chuông',
                    data: data_ringing,
                    borderWidth: 1,
                    backgroundColor: 'rgb(255,99,132)',
               },
               {
                    label: 'Hội thoại',
                    data: data_talk,
                    borderWidth: 1,
                    backgroundColor: 'rgb(27,207,180)',
               }
          ]
     };

     // Tùy chọn cấu hình cho biểu đồ - direction
     let options_direction = {
          responsive: true, // Để biểu đồ thích ứng với kích thước của vùng chứa
          maintainAspectRatio: false,
          plugins: {
               title: {
                    display: true,
                    text: '',
                    padding: {
                        top: 10,
                        bottom: 10
                    },
                    color: '#000',
                    font: {
                        size: 14,
                        weight: 'bold',
                    }
                },
               legend: {
                    display: false
               },
               // Cấu hình plugin datalabels
               datalabels: {
                    anchor: 'center',
                    align: 'center',
                    padding: 0,
                    color: '#000', // Màu sắc của văn bản
                    font: {
                         size: 14,
                         weight: 'bold'
                    },
               },
          },
          scales: {
                'bar-y-axis': {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true
                },
                'line-y-axis': {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true
                }
            }
     };

     // Tùy chọn cấu hình cho biểu đồ - duration__average
     let options_duration_average = {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
               legend: {
                    position: 'top',
               },
               title: {
                    display: true,
                    text: '(Đơn vị: giây)',
                    align:'start',
                    position:'top',
                    font: {
                         style: 'italic'
                    }
               },
               datalabels: {
                    anchor: 'center',
                    align: 'center',
                    padding: 0,
                    color: '#000', // Màu sắc của văn bản
                    font: {
                         size: 14,
                         weight: 'bold'
                    },
               },
          }
     };
     
     // Tạo biểu đồ - direction 
     const directionChart = new Chart(calls_direction, {
          data: data_direction, // Dữ liệu
          plugins: [ChartDataLabels],
          options: options_direction // Tùy chọn cấu hình
     });

     // Tạo biểu đồ - duration__average
     const duration_averageChart = new Chart(calls_duration_average, {
          type: 'bar',
          data: data_duration_average, // Dữ liệu
          plugins: [ChartDataLabels],
          options: options_duration_average // Tùy chọn cấu hình
     });

</script>
{/literal}


{literal}
<script>
     const chartjs__calls_sources = document.getElementById('chartjs__calls-sources');

     // Dữ liệu cho biểu đồ
     let data__calls_sources = {
          labels: label_source,
          datasets: [
               {
                    label: 'Cuộc gọi đến',
                    data: quantity_inbound,
                    backgroundColor: 'rgb(27, 207, 180)',
                    borderWidth: 1,

               },
               {
                    label: 'Cuộc gọi nhỡ',
                    data: quantity_missed,
                    borderWidth: 1,
                    backgroundColor: 'rgb(255,99,132)',
               },
          ]
     };

     // Tùy chọn cấu hình cho biểu đồ - duration__average
     let options_calls_sources = {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
               legend: {
                    position: 'top',
               },
               title: {
                    display: true,
                    // text: '(Đơn vị: giây)',
                    align:'start',
                    position:'top',
                    font: {
                         style: 'italic'
                    }
               },
               datalabels: {
                    anchor: 'center',
                    align: 'center',
                    padding: 0,
                    color: '#000', // Màu sắc của văn bản
                    font: {
                         size: 14,
                         weight: 'bold'
                    },
               },
          }
     };

     
     // Tạo biểu đồ - duration__average
     let calls_sources = new Chart(chartjs__calls_sources, {
          type: 'bar',
          data: data__calls_sources, // Dữ liệu
          plugins: [ChartDataLabels],
          options: options_calls_sources // Tùy chọn cấu hình
     });

</script>
{/literal}