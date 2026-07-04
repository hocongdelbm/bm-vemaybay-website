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

    // ===== Bấm 1 NCC để lọc bảng chi tiết chứng từ =====
    $("#supplier_summary_list").on("click", ".supplier-row", function () {
      var selectedSupplier = $(this).attr("data-supplier");

      $("#supplier_summary_list .supplier-row").removeClass("bg-warning");
      $(this).addClass("bg-warning");

      var totalVe = 0;
      var totalDt = 0;
      var totalGm = 0;
      var totalDs = 0;
      var visibleIndex = 1;

      $("#supplier_detail_list .supplier-detail-row").each(function () {
        var rowSupplier = $(this).attr("data-supplier");
        if (selectedSupplier === "ALL" || rowSupplier === selectedSupplier) {
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
        var label = $(this).find("td").eq(1).text().trim();
        $("#supplier_detail_title").text("Chi tiết chứng từ: " + label);
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
