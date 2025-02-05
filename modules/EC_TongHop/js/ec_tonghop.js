$(document).ready(function(){
    sendRequest();
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
                    $('.online_user_waiting').hide();
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
                                <span class="domain">${domain_name}</span>
                                <span class="online_user">Khách online</span>
                                <span class="online_user_value">${response.data.online_visitor}</span>
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
});