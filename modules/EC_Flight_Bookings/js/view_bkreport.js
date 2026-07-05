(function () {
  "use strict";

  function nf(n) {
    if (typeof num_grp_sep !== "undefined" && typeof dec_sep !== "undefined") {
      let parts = Number(n || 0)
        .toString()
        .split(".");
      let formatted = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, num_grp_sep);
      return parts[1] ? formatted + dec_sep + parts[1] : formatted;
    }
    return Number(n || 0).toLocaleString("en-US");
  }

  $(document).ready(function () {
    if (typeof Calendar !== "undefined") {
      Calendar.setup({
        inputField: "from_date",
        daFormat: "%d-%m-%Y",
        button: "fdate_trigger",
        singleClick: true,
        dateStr: "",
        step: 1,
      });
      Calendar.setup({
        inputField: "to_date",
        daFormat: "%d-%m-%Y",
        button: "tdate_trigger",
        singleClick: true,
        dateStr: "",
        step: 2,
      });
    }

    // ===== Chọn nhanh khoảng thời gian =====
    $("#date_select").change(function () {
      let fdate_val = $(this).children("option:selected").attr("from_date");
      let tdate_val = $(this).children("option:selected").attr("to_date");

      if (fdate_val) $("#from_date").val(fdate_val);
      if (tdate_val) $("#to_date").val(tdate_val);
    });

    
    $("#report_mode").change(function () {
      $("#frmSearch").submit();
    });

    // ===== Bấm 1 Hãng để lọc bảng chi tiết (chế độ Airline) =====
    $("#supplier_summary_list").on("click", ".airline-row", function () {
      var selectedAirline = $(this).attr("data-airline");

      $("#supplier_summary_list tr").removeClass("bg-warning");
      $(this).addClass("bg-warning");

      var totalVe = 0;
      var totalDt = 0;
      var totalGm = 0;
      var totalDs = 0;
      var visibleIndex = 1;

      $("#supplier_detail_list .booking-row").each(function () {
        var rowAirline = $(this).attr("data-airline");
        
        if (selectedAirline === "ALL" || rowAirline === selectedAirline) {
          $(this).show();
          $(this).find(".stt-cell").text(visibleIndex++);
          totalVe += parseFloat($(this).attr("data-qty") || 0);
          totalDt += parseFloat($(this).attr("data-dt") || 0);
          totalGm += parseFloat($(this).attr("data-gm") || 0);
          totalDs += parseFloat($(this).attr("data-amount") || 0);
        } else {
          $(this).hide();
        }
      });

      $("#total_filtered_ticket_qty").text(nf(totalVe));
      $("#total_filtered_dt").text(nf(totalDt));
      $("#total_filtered_gm").text(nf(totalGm));
      $("#total_filtered_ds").text(nf(totalDs));

      if (selectedAirline === "ALL") {
        $("#supplier_detail_title").text("Chi tiết chứng từ: Tất cả Hãng bay");
      } else {
        var label = $(this).find("td").eq(1).text().trim();
        $("#supplier_detail_title").text("Chi tiết chứng từ: " + label);
      }
    });

    // ===== Bấm 1 NCC để lọc bảng chi tiết chứng từ =====
    $("#supplier_summary_list").on("click", ".supplier-row, .supplier-row-airline", function () {
      var selectedSupplier = $(this).attr("data-supplier");
      var selectedAirline = $(this).attr("data-airline");

      $("#supplier_summary_list tr").removeClass("bg-warning");
      $(this).addClass("bg-warning");

      var totalVe = 0;
      var totalDt = 0;
      var totalGm = 0;
      var totalDs = 0;
      var visibleIndex = 1;

      $("#supplier_detail_list .supplier-detail-row").each(function () {
        var rowSupplier = $(this).attr("data-supplier");
        var rowAirline = $(this).attr("data-airline");
        
        var matchSupplier = (selectedSupplier === "ALL" || rowSupplier === selectedSupplier);
        var matchAirline = (!selectedAirline || rowAirline === selectedAirline);

        if (matchSupplier && matchAirline) {
          $(this).show();
          $(this).find(".detail-stt").text(visibleIndex++);
          totalVe += parseFloat($(this).attr("data-qty") || 0);
          totalDt += parseFloat($(this).attr("data-dt") || 0);
          totalGm += parseFloat($(this).attr("data-gm") || 0);
          totalDs += parseFloat($(this).attr("data-ds") || 0);
        } else {
          $(this).hide();
        }
      });

      $("#total_filtered_ve").text(nf(totalVe));
      $("#total_filtered_dt").text(nf(totalDt));
      $("#total_filtered_gm").text(nf(totalGm));
      $("#total_filtered_ds").text(nf(totalDs));

      if (selectedSupplier === "ALL") {
        $("#supplier_detail_title").text("Chi tiết chứng từ: Tất cả NCC");
      } else {
        var label = $(this).find("td").eq(1).text().replace("↳", "").trim();
        if (selectedAirline) {
           // Lấy tên NCC từ data-supplier
           var supplierName = $('#supplier_summary_list .supplier-row[data-supplier="' + selectedSupplier + '"]').find("td").eq(1).text().trim();
           $("#supplier_detail_title").text("Chi tiết chứng từ: " + supplierName + " - " + label);
        } else {
           $("#supplier_detail_title").text("Chi tiết chứng từ: " + label);
        }
      }
    });

    // ===== Loading khi xem báo cáo =====
    $("#frmSearch").on("submit", function () {
      $("#btnView")
        .val("⏳ Đang tải...")
        .css("pointer-events", "none")
        .css("opacity", "0.7");
    });
  });
})();
