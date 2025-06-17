$(document).ready(function(){
    sendRequest();
    createChart("access_year", "bar");
    createChart("access_month", "bar");
    createChart("access_platform", "doughnut");
    createChart("access_os", "doughnut");
    $(".search_button.btn.btn-primary").on("click", function(){
        sendRequest();
        $(".overlay-mobile").css("display", "none");
        $(".normal_search_form.active").removeClass("active");
    });
    function sendRequest(){
        let endPoint = "index.php?entryPoint=entryPointSummaryOnlineUserCounter";
        let selected_url = $("select[name='url_selected']").val();
        let domain_name = $("select[name='url_selected'] option:selected").text();
        if(selected_url !== null){
            $.ajaxSetup({
                header: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "POST",
                url: endPoint,
                contentType: 'json',
                data: JSON.stringify({
                    "url_selected": selected_url
                }),
                beforeSend: function(){
                    $('.online_user_waiting').show();
                },
                success: function(response){
                    // $('.online_user_waiting').hide();
                    try {
                        if(typeof response === "string"){
                            response = JSON.parse(response);
                        }
                    } catch (error) {
                        console.error(error);
                    }
                    callRequest();
                    if(response.error === 0){
                        // $(".online_user_value").text(" " + response.data.online_visitor);
                        $(".online_data").html(`
                            <div class="mini_title_wrap col-lg-12">
                                <h1 class="domain title">${domain_name}</h1>
                                <span class="online_user">Khách online:
                                    <span class="online_user_value">
                                        ${response.data.online_visitor}
                                    </span>
                                </span>
                            </div>   
                        `);
                        removeOverFlow();
                    }else{
                        showModalNotify(0, response.message);
                        $(".online_user_value").text(0);
                    }
                },
                error: function(xhr) {
                    let response;
                    try {
                        if(typeof xhr.responseText == "string"){
                           response = JSON.parse(xhr.responseText);
                        }
                    } catch (error) {
                        console.error(error)
                    }
                    $('.online_user_waiting').hide();
                    showModalNotify(0, response.message);
                    callRequest();
                    $(".online_user_value").text(response.data);
                }
            });
        }
    }
    function callRequest(){
        clearTimeout;
        timeout = setTimeout(sendRequest, 300000);
    };
    function removeOverFlow(){
        tabLength = $(".mini_title_wrap").length;
            if(tabLength == 1){
                $(".online_data").css("overflow", "unset");
                $(".online_data").css("justify-content", "center");
            }
    }

    function createChart(id, type_chart, data){
        let chart_id = document.getElementById(id);
        var ctx = chart_id.getContext("2d");
        var myChart = new Chart(ctx, {
            type: type_chart,
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [
                    {
                        label: "Người",
                        data:[1000, 920, 880, 898, 1200, 1000, 900, 800, 700, 600, 500, 1500],
                        backgroundColor: 'rgba(54, 162, 235, 1)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: true
                    },
                    {
                        label: "Bot",
                        data:[400, 490, 300, 420, 80, 333, 120, 60, 40, 200, 120, 600],
                        backgroundColor: 'rgba(255, 0, 55, 0.2)',
                        borderColor: 'rgba(255, 0, 55, 1)',
                        borderWidth: 2,
                        fill: true
                    }
                ]
             },
             options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
             }
        });
    }
});