$(document).ready(function () {
    $(".detail_domestic").click(function () {
        let from_date = $(this).data('from-date');
        let to_date = $(this).data('to-date');

        $.ajax({
            url: "index.php?entryPoint=entryPointFlightBookings",
            type: "POST",
            data: {
                fdate: from_date,
                tdate: to_date,
                for: "getInfoBookingDomestic",
            },
            beforeSend: function () {
                $(".container-waiting").show();
            },
            success: function (response) {
                $(".container-waiting").hide();
                $("#infor_booking_inter__title").html('<h1 class="title">Thông tin chi tiết vé nội địa</h1>');
                $("#infor_booking_inter__content").html(response);
            }
        });
    });

    $(".show_detail_call").click(function () {
        let direction = $(this).data("direction");
        let from_date = $(this).data('from-date');
        let to_date = $(this).data('to-date');
        let is_booking = $(this).data('is-booking');

        $.ajax({
            url: "index.php?entryPoint=entryPointFlightBookings",
            type: "POST",
            data: {
                fdate: from_date,
                tdate: to_date,
                direction: direction,
                is_booking: is_booking,
                for: "getDetailCallBookingQtyReport",
            },
            beforeSend: function () {
                $(".container-waiting").show();
            },
            success: function (response) {
                $(".container-waiting").hide();
                $("#infor_booking_inter__title").html('<h1 class="title">Danh sách chi tiết cuộc gọi ' + direction + ' từ ngày ' + from_date + ' đến ngày ' + to_date + '</h1>');
                $("#infor_booking_inter__content").html(response);
            }
        });
    });

    $(document).on("change", "#date_select", function (e) {
        $("#from_date").val($(this).find("option:selected").attr("fromdate"));
        $("#to_date").val($(this).find("option:selected").attr("todate"));
    });

    // Chi phí quảng cáo — chỉ có tác dụng với admin (các element chỉ render khi CAN_EDIT_AD_COST).
    // Khi không phải admin, các selector không khớp element nào nên handler vô hại.
    function formatVnIntegerInput(el) {
        var v = String($(el).val() || "").replace(/\D/g, "");
        if (v === "") { $(el).val(""); return; }
        $(el).val(v.replace(/\B(?=(\d{3})+(?!\d))/g, "."));
    }

    $(document).on("click", ".btn-edit-daily-ad-cost", function () {
        var d = $(this).data("ad-date");
        var raw = $(this).data("ad-amount");
        var isAdd = $(this).text().trim() === "Thêm";

        $("#modal_daily_ad_cost_date").val(d);
        $("#modal_daily_ad_cost_date_label").text(d);
        $("#modalDailyAdCostLabel").html((isAdd ? "Thêm" : "Chi phí") + " chi phí quảng cáo — ngày <span id=\"modal_daily_ad_cost_date_label\">" + d + "</span>");
        var n = Math.round(Number(raw) || 0);
        $("#modal_daily_ad_cost_amount").val(n ? String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ".") : "");
        var modal = document.getElementById("modalDailyAdCost");
        if (modal && typeof bootstrap !== "undefined") {
            new bootstrap.Modal(modal).show();
        } else if (typeof $ !== "undefined" && $("#modalDailyAdCost").modal) {
            $("#modalDailyAdCost").modal("show");
        }
    });

    $("#modal_daily_ad_cost_amount").on("input", function () { formatVnIntegerInput(this); });
    $("#frmDailyAdCost").on("submit", function (e) {
        e.preventDefault();
        var costDate = $("#modal_daily_ad_cost_date").val();
        var amount = String($("#modal_daily_ad_cost_amount").val() || "").replace(/\D/g, "");
        var $btn = $(this).find('button[type="submit"]');
        $.ajax({
            url: "index.php?entryPoint=entryPointFlightBookings",
            type: "POST",
            dataType: "json",
            data: { for: "saveDailyAdCost", cost_date: costDate, amount: amount },
            beforeSend: function () {
                $(".container-waiting").show();
                $btn.prop("disabled", true);
            },
            complete: function () {
                $(".container-waiting").hide();
                $btn.prop("disabled", false);
            },
            success: function (res) {
                if (res && res.ok) {
                    var $rowBtn = $('.btn-edit-daily-ad-cost[data-ad-date="' + costDate + '"]');
                    $rowBtn.closest("td").find(".ad-cost-display").text(res.formatted);
                    if (res.can_edit) {
                        $rowBtn.text("Sửa").removeClass("btn-outline-success").addClass("btn-outline-primary").attr("data-ad-amount", res.amount);
                    } else {
                        $rowBtn.remove();
                    }

                    var modalEl = document.getElementById("modalDailyAdCost");
                    if (modalEl && typeof bootstrap !== "undefined") {
                        bootstrap.Modal.getInstance(modalEl).hide();
                    } else {
                        $("#modalDailyAdCost").modal("hide");
                    }
                } else {
                    var msg = (res && res.message) ? res.message : "Không lưu được.";
                    if (typeof showModalNotify === "function") {
                        showModalNotify("error", msg);
                    } else {
                        alert(msg);
                    }
                }
            },
            error: function (xhr) {
                var msg = "Lỗi lưu.";
                try {
                    var j = JSON.parse(xhr.responseText);
                    if (j && j.message) { msg = j.message; }
                } catch (err) {}
                if (typeof showModalNotify === "function") {
                    showModalNotify("error", msg);
                } else {
                    alert(msg);
                }
            }
        });
    });
});
