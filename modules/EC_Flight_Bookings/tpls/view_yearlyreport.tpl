<!-- <script type="text/javascript" src="https://www.google.com/jsapi"></script> -->
<script src="https://www.gstatic.com/charts/loader.js"></script>

{literal}
  <script type="text/javascript">
    $(document).ready(function() {
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      
      function drawChart() {
        // Set Data
        const data = google.visualization.arrayToDataTable(report_data);

        // Set Options
        const options = {
          chart: {
            title: 'BÁO CÁO TỔNG HỢP',
          }
        };
        
        // Draw
        const chart = new google.visualization.ColumnChart(document.getElementById('report_chart'));
        chart.draw(data, options);
      }

    });
  </script>
{/literal}

<input type="hidden" id="grp_seperator" name="grp_seperator" value="{$GRP_SEPERATOR}" />
<input type="hidden" id="dec_seperator" name="dec_seperator" value="{$DEC_SEPERATOR}" />
<input type="hidden" id="sig_digits" name="sig_digits" value="{$SIG_DIGITS}" />
       
<h1 class="title">Báo cáo tổng hợp</h1>

<div class="box-section">
  <form action="index.php" method="post" name="frmSearch" id="frmSearch">
    <input type="hidden" name="module" value="EC_Flight_Bookings" />
    <input type="hidden" name="action" value="yearlyreport" />  
    <div class="d-flex align-items-center gap-2">
      <span class="text-label">Xem theo từng năm: </span> 
      <select class="box-select" name="selectYear" id="selectYear" >
          {$LIST_OF_YEAR}
      </select>
      <input class="btn btn-primary" type="submit" name="btnSearch" value="Tìm" title="Tìm" />			   
    </div>
  </form>

  <div id="report_chart" class="w-100 mt-3" style="height: 500px;"></div>
</div>