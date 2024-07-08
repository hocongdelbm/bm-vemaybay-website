<script src="highcharts/js/highcharts.js"></script>
<script src="highcharts/js/modules/exporting.js"></script>

<input type="hidden" id="grp_seperator" name="grp_seperator" value="{$GRP_SEPERATOR}" />
<input type="hidden" id="dec_seperator" name="dec_seperator" value="{$DEC_SEPERATOR}" />
<input type="hidden" id="sig_digits" name="sig_digits" value="{$SIG_DIGITS}" />
            
<form action="index.php" method="post" name="frmSearch" id="frmSearch">
	<input type="hidden" name="module" value="EC_Flight_Bookings" />
	<input type="hidden" name="action" value="monthlyreport" />  
    <select name="selectMonth" id="selectMonth">
        {$LIST_OF_MONTH}
    </select>
    &nbsp;/&nbsp;
    <select name="selectYear" id="selectYear" >
        {$LIST_OF_YEAR}
    </select>
    <input type="submit" name="btnSearch" value="Tìm" title="Tìm" />			   
</form>


{literal}
<script type="text/javascript">

function isInt(value){
	var er = /^[0-9]+$/;
	return ( er.test(value) ) ? true : false;
}

$(function () {
    var chart;
	var sig_digits = 2;//parseInt($('#sig_digits').val());
	var dec_seperator = $('#dec_seperator').val();
	var grp_seperator = $('#grp_seperator').val();
	
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'chart_div',
                type: 'line'
            },
            title: {
                text: title_src
            },
            subtitle: {
                text: subtitle_src
            },
            xAxis: {
                categories: category_src
            },
            yAxis: {
                title: {
                    text: ycolumn_name
                }
            },
            tooltip: {
                enabled: true,
                formatter: function() {
                    return '<b>'+ this.series.name +'</b><br/>Ngày '+
                        this.x +': '+ (isInt(this.y) ? this.y : Highcharts.numberFormat(this.y, sig_digits, dec_seperator, grp_seperator));
                }
            },
            plotOptions: {
                line: {
                    /*dataLabels: {
                        enabled: true,
						formatter: function() {
							return '<b>'+ Highcharts.numberFormat(this.y, 0, '.', ',') +'</b>';
						}
                    },*/
                    enableMouseTracking: true
                }
            },
            series: data_src
        });
    });
    
});
</script>
{/literal}

<div id="chart_div" style="width: 900px; height: 500px; margin:0px auto"></div>