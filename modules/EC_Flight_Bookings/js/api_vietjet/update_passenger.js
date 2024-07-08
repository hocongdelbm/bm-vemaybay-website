/*---------- UPDATE PASSENGER ----------*/
$(document).ready(function() {
     const id_modal_update_passenger = '#modal-update-passenger';
     const id_modal_confirm_update_passenger = '#modal-confirm-update-passenger';
     const url = "index.php?entryPoint=entryPointAPIVietjet"; 

     // Get information
     $(document).on('click', '.btn_edit_passenger', function(e) {
          let re_key          = $(this).attr('re_key');
          let pass_key        = $(this).attr('pass_key');
          let pass_type       = $(this).attr('pass_type');
          let itinerary_id    = $(this).attr('itinerary_id');
          let td_direction    = $(this).parent().parent().find('.td_direction').html();

          if (re_key.length == 0 || pass_key.length == 0) {
               alert("Không thể thực hiện thao tác");
               return;
          }

          $.ajax({
               type: 'POST',
               url: url,
               data: {
                    action         : 1,
                    re_key         : re_key,
                    pass_key       : pass_key,
                    pass_type      : pass_type,
                    itinerary_id   : itinerary_id
               },
               beforeSend: function () {
                    $('.container-waiting').show();
               },
               success: function(res) {
                    $('.container-waiting').hide();
                    data = JSON.parse(res);

                    if (data['error'] === true) {
                         showModalNotify('error',data['message'], data['code']);
                         return;
                    }

                    // Get value
                    let info = data['data'];
                    $(id_modal_update_passenger + ' .direction').html(td_direction);
                    $(id_modal_update_passenger + ' #passenger_type').html(info['type']);
                    $(id_modal_update_passenger + ' select[name="gender"]').val(info['gender']);
                    $(id_modal_update_passenger + ' input[name="fullname"]').val(info['name']);
                    $(id_modal_update_passenger + ' input[name="reservation_key"]').val(re_key);
                    $(id_modal_update_passenger + ' input[name="passenger_key"]').val(pass_key);
                    if(info['birthdate']) {
                         let birthdate = info['birthdate'].split('-');
                         $(id_modal_update_passenger + ' input[name="bd-day"]').val(birthdate[2]);
                         $(id_modal_update_passenger + ' input[name="bd-month"]').val(birthdate[1]);
                         $(id_modal_update_passenger + ' input[name="bd-year"]').val(birthdate[0]);
                    }
                    else {
                         $(id_modal_update_passenger + ' input[name="bd-day"]').val('');
                         $(id_modal_update_passenger + ' input[name="bd-month"]').val('');
                         $(id_modal_update_passenger + ' input[name="bd-year"]').val('');
                    }

                    showModal(id_modal_update_passenger);
               }
          });
     });

     // Quote for updating
     $('#btn-update-passenger').click(function() {
          let pass_type   = $('#passenger_type').html();
          let gender      = $(id_modal_update_passenger + ' select[name="gender"]').val();
          let fullname    = $(id_modal_update_passenger + ' input[name="fullname"]').val();
          let bd_day      = $(id_modal_update_passenger + ' input[name="bd-day"]').val();
          let bd_month    = $(id_modal_update_passenger + ' input[name="bd-month"]').val();
          let bd_year     = $(id_modal_update_passenger + ' input[name="bd-year"]').val();
          let re_key      = $(id_modal_update_passenger + ' input[name="reservation_key"]').val();
          let pass_key    = $(id_modal_update_passenger + ' input[name="passenger_key"]').val();
          let direction   = $(id_modal_update_passenger + ' .direction i').html();

          // Validate
          if(checkBirthdate(bd_day, bd_month, bd_year) === false || checkName() === false) {
               console.error("Error");
               return;
          }

          $.ajax({
               type: 'POST',
               url: url,
               data: {
                    action      : 2,
                    re_key      : re_key,
                    pass_key    : pass_key,
                    pass_type   : pass_type,
                    gender      : gender,
                    fullname    : fullname,
                    bd_day      : bd_day,
                    bd_month    : bd_month,
                    bd_year     : bd_year
               },
               beforeSend: function () {
                    $('.container-waiting').show();
                },
               success: function(res) {
                    $('.container-waiting').hide();

                    let data = JSON.parse(res);
                    if (data['error'] === true) {
                         showModalError(data['code'], data['message']);
                         return;
                    }

                    // Map value
                    data = data['data'];
                    let currentTotal = 0;
                    if(direction == 'Lượt đi') currentTotal = parseInt($('#dep-amount').html().slice(0, -3).replace(/\&nbsp;/g, '').replace(/,/g, ''));
                    else currentTotal = parseInt($('#ret-amount').html().slice(0, -3).replace(/\&nbsp;/g, '').replace(/,/g, ''));
                    let fee = data['data']['total'] - currentTotal;

                    if(fee < 0) {
                         hideModal(id_modal_update_passenger);
                         showModalNotify('warning','Booking đã quá hạn giữ chỗ');
                         return;
                    }
                  
                    // Show modal
                    $(id_modal_confirm_update_passenger + ' #edit-fee').html(fee.toLocaleString('it-IT', {style: 'currency', currency: 'VND'}).replaceAll('.', ','));
                    $(id_modal_confirm_update_passenger + ' button#confirm-update-passenger').attr('body_request', JSON.stringify(data['data']['body_request']));
                    $(id_modal_confirm_update_passenger + ' button#confirm-update-passenger').attr('re_key', re_key);
                    $(id_modal_confirm_update_passenger + ' button#confirm-update-passenger').attr('pass_key', pass_key);
                    showModal(id_modal_confirm_update_passenger);
                    hideModal(id_modal_update_passenger);
               }    
          });

     });

     // Update
     $('#confirm-update-passenger').click(function(){
          let re_key          = $(this).attr('re_key');
          let pass_key        = $(this).attr('pass_key');
          let body_request    = $(this).attr('body_request');
          let booking_name    = $('#booking_name_display').html();

          if (body_request.length == 0) {
               alert("Không thể thực hiện thao tác");
               return;
          }

          $.ajax({
               type: 'POST',
               url: url,
               data: {
                    action         : 3,
                    re_key         : re_key,
                    pass_key       : pass_key,
                    body_request   : body_request,
                    booking_name   : booking_name
               },
               beforeSend: function () {
                    $('.container-waiting').show();
                },
               success: function(res) {
                    $('.container-waiting').hide();
                    data = JSON.parse(res);
                    if (data['error'] === true) {
                         showModalNotify('error',data['message'], data['code']);
                         return;
                    }
                    
                    showModalNotify('success',data['message']);
               }
          });
     });
});



function checkName() {
     $("#name_passenger").each(function (index) {
          if ($(this).val().length == '') {
               $(this).focus();
               $(this).css({ "border": "1px solid #F00" });
               $(".name__error").html("Vui lòng nhập đầy đủ họ và tên.");
               $(".name__error").show();
               return false;
          }
          else {
               $(".name__error").hide();
               $(this).css({ "border": "unset" });
               return true
          }
     });
}

function checkBirthdate(day, month, year) {
     let now = new Date();
     let currentDay      = now.getDate(); // day
     let currentMonth    = now.getMonth() + 1; // month
     let currentYear     = now.getFullYear(); // year
     let selectorDay     = $("#cc_day");
     let selectorMonth   = $("#cc_month");
     let selectorYear    = $("#cc_year");
     let pass_type       = $('#passenger_type').html();
     let icon            = '<i class="fa fa-exclamation-circle me-2" aria-hidden="true"></i>';

     if ((day == '' || month == '' || year == '') && pass_type == 'Người lớn') return true;

     // Year - Month - Day (Null)
     if (day == '' || month == '' || year == '') {
          if (day.length == '') {
               //Day = null
               selectorDay.focus();
               selectorDay.css({ "border": "1px solid #F00" });
               $(".birthday__error").html(icon + "Vui lòng nhập ngày sinh");
               $(".birthday__error").show();
               return false;
          } else {
               // Day # Null 
               selectorDay.css({ "border": "unset", "border-bottom": "2px solid #ddd" });
               $(".birthday__error").hide();

               //Month = null
               if (month.length == '') {
                    selectorMonth.focus();
                    selectorMonth.css({ "border": "1px solid #F00" });
                    $(".birthday__error").html(icon + "Vui lòng nhập tháng sinh");
                    $(".birthday__error").show();
                    return false;
               } else {
                    // Month # null
                    $(".birthday__error").hide();
                    selectorMonth.css({ "border": "unset", "border-bottom": "2px solid #ddd" });

                    // Year = null
                    if (selectorYear.val().length == '') {
                         selectorYear.focus();
                         selectorYear.css({ "border": "1px solid #F00" });
                         $(".birthday__error").html(icon + "Vui lòng nhập năm sinh");
                         $(".birthday__error").show();
                         return false;
                    } else {
                         $(".birthday__error").hide();
                         selectorYear.css({ "border": "unset", "border-bottom": "2px solid #ddd" });
                    }
               }
          }
     } else {
          // Year - Month - Day Valid => Check age
          var ListofDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

          if (month == 2) {
               var leapYear = false;
               if ((!(year % 4) && year % 100) || !(year % 400)) {
                    leapYear = true;
               }

               if ((leapYear == false) && (day >= 29)) {
                    $(".birthday__error").html(icon + "Ngày tháng năm sinh không hợp lệ");
                    $(".birthday__error").show();
                    return false;
               }
               else {
                    $(".birthday__error").hide();
               }

               if ((leapYear == true) && (day > 29)) {
                    $(".birthday__error").html(icon + "Ngày tháng năm sinh không hợp lệ");
                    $(".birthday__error").show();
                    return false;
               }
               else {
                    $(".birthday__error").hide();
               }
          } else {
               if (day > ListofDays[month - 1]) {
                    $(".birthday__error").html(icon + "Ngày tháng năm sinh không hợp lệ");
                    $(".birthday__error").show();
                    return false;
               }
               else {
                    $(".birthday__error").hide();
               }
          }

          // strtotime in js
          let strtotime = Date.parse('"' + year + '-' + month + '-' + day + '"');
          let strtotime_current = Date.parse('"' + currentYear + '-' + currentMonth + '-' + currentDay + '"');

          if (strtotime - strtotime_current <= 0) {
               let age = currentYear - year;
               if (age >= 12) {
                    //console.log('Người lớn');
               } else if (age >= 2 && age <= 11) {
                    //console.log('Trẻ em');
               } else {
                    //console.log('Em bé');
               }
          } else {
               $(".birthday__error").html(icon + "Ngày tháng năm sinh không hợp lệ");
               $(".birthday__error").show();
               return false;
          }
     }

     return true;
}