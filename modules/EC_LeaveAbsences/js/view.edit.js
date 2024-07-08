$(document).ready(function() {

     const today = new Date();
     const day = today.getDate().toString().padStart(2, '0');
     const month = (today.getMonth() + 1).toString().padStart(2, '0');
     const year = today.getFullYear();

     const d_m_Y = `${day}-${month}-${year}`;

     var partofday = $(".partofday:checked").val();

     if(partofday == 1) {
          $("#date_chosen_m").parent().show();
     } else if(partofday == 2) {
          $("#date_chosen_a").parent().show();
     }

     $(".partofday").click(function() {
          $("#date_chosen_m").parent().hide();
          $("#date_chosen_a").parent().hide();
          $("#part_date_chosen").val("");

          if($(this).val() == 1) {
               $("#date_chosen_m").parent().show();
               $("#date_chosen_m").val(d_m_Y);
               $("#part_date_chosen").val(d_m_Y);
          }

          if($(this).val() == 2) {
               $("#date_chosen_a").parent().show();
               $("#date_chosen_a").val(d_m_Y);
               $("#part_date_chosen").val(d_m_Y);
          }
     });

     Calendar.setup ({
          inputField : "date_chosen_m",
          daFormat : "%d-%m-%Y %H:%M",
          button : "date_chosen_m_trigger",
          singleClick : true,
          dateStr : "",
          step : 1,
          weekNumbers:false
     });

     Calendar.setup ({
          inputField : "date_chosen_a",
          daFormat : "%d-%m-%Y %H:%M",
          button : "date_chosen_a_trigger",
          singleClick : true,
          dateStr : "",
          step : 1,
          weekNumbers: false
     });

     // $(document).on("submit", "#Editview", function(e) {
     $('#EditView').submit(function(e){
          var partofday = $(".partofday:checked").val();
          if(partofday == 1) {
               $("#part_date_chosen").val($("#date_chosen_m").val());
          } else if(partofday == 2) {
               $("#part_date_chosen").val($("#date_chosen_a").val());
          }
          
          var form = this;
          
          $.ajax({
               url: "index.php?entryPoint=entryPointAbsence",
               type: "POST",
               data: {
                    from_date: $("#from_date").val(),
                    to_date: $("#to_date").val(),
                    absence_days: $("#absence_days").val(),
                    partofday: $("input[name='partofday']:checked").val(),
                    part_date: $("#part_date_chosen").val(),
                    assigned_user_id: $("#assigned_user_id").val(),
                    for: "checkAbsenceCondition",
               },
               success: function(response) {
                    if(response == 1) {
                         form.submit();
                    } else {	
                         alert(response);
                         return false;
                    }
               }
          });
          return false;
     });
});