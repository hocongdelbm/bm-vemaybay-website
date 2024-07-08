/*---------- UPDATE PASSENGER ----------*/
$(document).ready(function() {
    const id_modal_update_journey           = '#modal-update-journey';
    const id_modal_confirm_update_journey   = '#modal-confirm-update-journey';
    const url                               = "index.php?entryPoint=entryPointAPIVietjet"; 

    $(document).on('click', '.btn-update_journey', function(e) {
        // RESET FORM
        resetChangeJourney();
        if(!$("#modal-change-journey").hasClass('show')){
            $('#modal-change-journey').removeClass('show_flights');
        }

        let journey_key     = '';
        let direction       = -1;

        if($(this).hasClass("btn-update-journey__dep")) {
            direction = 0;
            journey_key = $("#journey-dep").attr('journey_key');
        }
        else if($(this).hasClass("btn-update-journey__ret")) {
            direction = 1;
            journey_key = $("#journey-ret").attr('journey_key');
        }

        $("#search-journey__change").attr('direction', direction);
        $("#search-journey__change").attr('journey_key', journey_key);
    });

    
    $(document).on('click', '#search-journey__change', function(e) {
        let re_key          = $("#reservation_key").attr('re_key');
        let direction       = $(this).attr('direction');
        let journey_key     = $(this).attr('journey_key');
        let supplier_id     = $('#supplier_id').val();
        let depart          = $("#change__journey--depart").val();
        let arrival         = $("#change__journey--arrival").val();
        let datedepart      = $("#change__journey--datedepart").val();

        if(depart.length == 0){
            $("#change__journey--depart").focus();
            return false;
        } 
        if(arrival.length == 0){
            $("#change__journey--arrival").focus();
            return false;
        }
        if(datedepart.length == 0){
            $("#change__journey--datedepart").focus();
            return false;
        }

        $.ajax({
            type: "POST",
            url: "index.php?entryPoint=entryPointAPIVietjet",
            data: {
                action: "search_change_journey",
                direction: direction,
                re_key: re_key,
                journey_key: journey_key,
                depart: depart,
                arrival: arrival,
                date: datedepart,
                supplier_id: supplier_id,
            },
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function (res) {
                $('.container-waiting').hide();
                $('#modal-change-journey').addClass('show_flights');

                let list_flight = JSON.parse(res);
                let flights = '';
                list_flight.forEach(element => {
                    flights += createFlightInfo(element);
                });
                
                $('#flightlist').html(flights);
            }
        });
    });
});

function createFlightInfo(flight) {
    return `
        <div id="flight-item__vja17190249290" class="flight-item flight-transit-0 VJ" rel="vj" data-class="Eco T1" data-timedepart="05:25">
            <div class="flight-item__content" data-timedepart="0525" data-price="3625320">
                <div class="flight-infor">
                    <div class="flight-infor-item infor-plane-wrap">
                        <div class="infor-plane">
                            <div class="infor-brand bg_VJ"></div>
                            <div class="infor-plane__name">
                                <h6 class="box-heading-h6 brand-fly">
                                    ${flight.airline} - ${flight.flightno}
                                </h6>
                            </div>
                        </div>
                    </div>
                    <div class="flight-infor-item infor-time">
                        <div class="infor-time__depart">
                            <h6 class="box-heading-h6 time__depart">${flight.deptime}</h6>
                            <span class="box-subheading-span location__depart">${flight.dep}</span>
                        </div>
                        <svg width="40" height="20" fill="none" class="jss10423">
                            <g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M33.5 8.5L36 11M4 11h32"></path>
                            </g>
                            <defs>
                                <clipPath id="icon_arrow_flight_long_svg__clip0">
                                    <path fill="#fff" d="M0 0h40v20H0z"></path>
                                </clipPath>
                            </defs>
                        </svg>
                        <div class="infor-time__destination">
                            <h6 class="box-heading-h6 time__destination">${flight.arvtime}</h6>
                            <span class="box-subheading-span location__destination">${flight.arv}</span>
                        </div>
                    </div>
                    <div class="flight-infor-item infor-duration">
                        <h6 class="box-heading-h6 time__duration">${flight.nduration}</h6>
                        <span class="box-subheading-span transit">Bay thẳng</span>
                    </div>
                    <div class="flight-infor-item infor-price">
                        <span id="price_vja17190249290" style="font-size:18px; font-weight:600; font-style:normal; color:var(--redvj-color);" value="3625320">${flight.totalAmount.toLocaleString('vi-VN')} VND</span>
                    </div>
                </div>
                <div class="flight-details">
                    <div class="flight-details-item class">${flight.class}</div>
                    <div class="choose-journey" booking_key="${flight.bookingkey}">
                        <button class="btn btn-danger">Chọn</button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function resetChangeJourney() {
    $('#flightlist').html('');
    $('#change__journey--depart').val('');
    $('#change__journey--arrival').val('');
}
