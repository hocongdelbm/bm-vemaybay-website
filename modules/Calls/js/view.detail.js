$(document).ready(function () {
     $(".btn-change-status").on('click', function(){
          $('.container-waiting').show();
     })

     let status = $("#status").val();
     let direction = $("#direction").val();
     let status_dom = '#pagecontent[data-module="Calls"] div[field="status"]';
     let direction_dom = '#pagecontent[data-module="Calls"] div[field="direction"]';

     if(status == 'done'){
          // Đã hoàn thành - Không cho phép đổi trạng thái nữa
          $('select#status').remove();
          $('#btnchangeStatus').remove();
     } 
     
     // Fill màu cho status, direction
     if(status == 'done'){
          $(status_dom).css({"color": "rgba(13, 110, 253, 1)", "font-weight": "600"});
     } else if (status == 'processing'){
          $(status_dom).css({"color": "rgba(255, 193, 7, 1)", "font-weight": "600"});
     } 

     if(direction == 'inbound'){
          $(direction_dom).css({"color": "#00ac47", "font-weight": "600"});
     } else if(direction == 'missed'){
          $(direction_dom).css({"color": "rgba(220, 53, 69, 1)", "font-weight": "600"});
     } else if(direction == 'outbound'){
          $(direction_dom).css({"color": "#0d6efd", "font-weight": "600"});
     } else if(direction == 'spam'){
          $(direction_dom).css({"color": "rgba(13, 202, 240, 1)", "font-weight": "600"});
     } else if(direction == 'internal'){
          $(direction_dom).css({"color": "rgba(33, 37, 41, 1)", "font-weight": "600"});
     } else{
          $(direction_dom).css({"color": "rgba(255, 193, 7, 1)", "font-weight": "600"});
     }

});