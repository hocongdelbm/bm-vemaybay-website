/**
 * Report: Hành trình theo quốc gia (route analysis)
 * Dữ liệu động được nạp qua window.REPORT_ROUTE.
 */
(function ($) {
  $(function () {
    var REPORT = window.REPORT_ROUTE || { charts: {}, labels: {} };

    // Chart.js v4: phải register thủ công thì số mới hiện cố định trên đầu cột
    if (
      typeof Chart !== "undefined" &&
      typeof ChartDataLabels !== "undefined"
    ) {
      Chart.register(ChartDataLabels);
    }

    // ===== Calendar cho ô ngày =====
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
        step: 1,
      });
    }

    // ===== Accordion: click cột kỳ để xổ chi tiết route theo kỳ đó =====
    $(document).on("click", ".main-line td.period-col", function (e) {
      if ($(e.target).closest("a").length) return;

      var periodId = $(this).data("period"); // 'p0'..'p5'
      var periodLabel = $(this).data("label");

      var trMain = $(this).parent();
      var detailRow = trMain.next(".detail-row");
      var icon = trMain.find(".toggle-icon");

      detailRow.find(".period-name-header").text(periodLabel);

      detailRow.find(".route-item").each(function () {
        var $r = $(this);
        var bkOk = $r.attr("data-" + periodId + "-bkok");
        var bkAll = $r.attr("data-" + periodId + "-bkall");
        var conv = $r.attr("data-" + periodId + "-conv");
        var ticket = $r.attr("data-" + periodId + "-ticket");
        var ref = $r.attr("data-" + periodId + "-ref");
        var profit = $r.attr("data-" + periodId + "-profit");
        var avg = $r.attr("data-" + periodId + "-avg");
        var fromF = $r.attr("data-" + periodId + "-f");
        var toT = $r.attr("data-" + periodId + "-t");

        var linkOk = $r.find(".route-bk-ok");
        var linkAll = $r.find(".route-bk-all");
        linkOk.text(bkOk);
        linkAll.text(bkAll);
        $r.find(".show-bk-list")
          .attr("data-period", periodLabel)
          .attr("data-fromdate", fromF)
          .attr("data-todate", toT);

        $r.find(".route-conv").text(conv);
        $r.find(".route-ref").text(ref);
        $r.find(".route-ticket").text(ticket);
        $r.find(".route-profit").text(profit);
        $r.find(".route-avg").text(avg);

        // Ẩn route không có BK nào trong kỳ (tổng BK = 0)
        if ((parseInt(bkAll, 10) || 0) === 0) {
          $r.removeClass("active-route-item").hide();
        } else {
          $r.addClass("active-route-item").show();
        }
      });

      renderRoutes(detailRow);

      if (
        detailRow.is(":visible") &&
        detailRow.data("active-period") === periodId
      ) {
        detailRow.fadeOut(200);
        icon.html("&#9654;");
        trMain
          .removeClass("row-open")
          .find("td.period-col")
          .removeClass("period-active");
        return;
      }
      detailRow.data("active-period", periodId);
      // Đánh dấu ô/kỳ đang xem
      trMain
        .addClass("row-open")
        .find("td.period-col")
        .removeClass("period-active");
      $(this).addClass("period-active");
      if (!detailRow.is(":visible")) {
        detailRow.fadeIn(200);
        icon.html("&#9660;");
      }
    });

    // ===== Date select dropdown =====
    $(document).on("change", "#date_select", function () {
      var $opt = $(this).find("option:selected");
      var from = $opt.attr("fromdate");
      var to = $opt.attr("todate");
      if (from) $("#from_date").val(from);
      if (to) $("#to_date").val(to);
    });

    function syncDateSelectBeforeSubmit() {
      var $sel = $("#date_select");
      var $opt = $sel.find("option:selected");
      var optFrom = $opt.attr("fromdate") || "";
      var optTo = $opt.attr("todate") || "";
      var curFrom = $("#from_date").val() || "";
      var curTo = $("#to_date").val() || "";
      if (optFrom !== curFrom || optTo !== curTo) {
        $sel.val("");
      }
    }

    // ===== Loading button khi bấm "Xem báo cáo" =====
    $("#ec_search_form").on("submit", function () {
      syncDateSelectBeforeSubmit();
      $("#btnSearch")
        .val("⏳ Đang tải...")
        .css("pointer-events", "none")
        .css("opacity", "0.7");
    });

    // ===== Modal AJAX xem danh sách BK =====
    $(document).on("click", ".show-bk-list", function (e) {
      e.preventDefault();
      var country = $(this).attr("data-country");
      var period = $(this).attr("data-period");
      var dep = $(this).attr("data-dep") || "";
      var arr = $(this).attr("data-arr") || "";
      var scope = $(this).attr("data-scope") || "";
      var destCountry = $(this).attr("data-dest-country") || "";
      var grp = $(this).attr("data-group") || "";
      var statusFilter = $(this).attr("data-status") || "";
      var fromDate = $(this).attr("data-fromdate");
      var toDate = $(this).attr("data-todate");

      var prefix =
        statusFilter === "completed"
          ? "BK hoàn tất"
          : statusFilter === "reference"
            ? "BK tham khảo"
            : "Tất cả BK";
      $("#mock-modal-title-text").text(
        prefix + " - " + country + " (" + period + ")",
      );
      $("#mock-modal-body").html(
        '<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Đang tải dữ liệu...</div>',
      );
      $("#mock-modal").css("display", "flex");

      $.ajax({
        url: "index.php?entryPoint=entryPointFlightBookings",
        type: "POST",
        data: {
          departure: dep,
          arrival: arr,
          scope: scope,
          dest_country: destCountry,
          grp: grp,
          status_filter: statusFilter,
          from_date: fromDate,
          to_date: toDate,
          for: "getDetailsAirportStatistics",
        },
        success: function (response) {
          $("#mock-modal-body").html(response);
        },
        error: function () {
          $("#mock-modal-body").html(
            '<div class="text-danger text-center p-4">Có lỗi xảy ra khi tải dữ liệu!</div>',
          );
        },
      });
    });
    $("#mock-modal-close-btn").click(function () {
      $("#mock-modal").hide();
    });

    // ===== Charts (mỗi tab 1 chart, chỉ SL BK hoàn tất) =====
    var builtCharts = {};
    function buildChart(key) {
      if (builtCharts[key]) return;
      var canvas = document.getElementById("chartjs__report_" + key);
      var d = REPORT.charts[key];
      if (!canvas || !d) return;
      var lb = REPORT.labels;
      builtCharts[key] = new Chart(canvas.getContext("2d"), {
        type: "bar",
        data: {
          labels: d.labels,
          datasets: [
            {
              label: lb.p5,
              data: d.p5,
              backgroundColor: "rgba(207,34,46,0.75)",
              borderColor: "rgba(207,34,46,1)",
              borderWidth: 1,
            },
            {
              label: lb.p4,
              data: d.p4,
              backgroundColor: "rgba(191,57,137,0.75)",
              borderColor: "rgba(191,57,137,1)",
              borderWidth: 1,
            },
            {
              label: lb.p3,
              data: d.p3,
              backgroundColor: "rgba(130,80,223,0.75)",
              borderColor: "rgba(130,80,223,1)",
              borderWidth: 1,
            },
            {
              label: lb.p2,
              data: d.p2,
              backgroundColor: "rgba(26,127,55,0.8)",
              borderColor: "rgba(26,127,55,1)",
              borderWidth: 1,
            },
            {
              label: lb.p1,
              data: d.p1,
              backgroundColor: "rgba(227,98,9,0.8)",
              borderColor: "rgba(227,98,9,1)",
              borderWidth: 1,
            },
            {
              label: lb.p0,
              data: d.p0,
              backgroundColor: "rgba(31,111,235,0.85)",
              borderColor: "rgba(31,111,235,1)",
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            datalabels: {
              anchor: "end",
              align: "top",
              formatter: Math.round,
              font: { weight: "bold" },
            },
            legend: { position: "top" },
          },
          scales: {
            y: {
              beginAtZero: true,
              title: { display: true, text: "BK hoàn tất" },
            },
          },
        },
      });
    }

    // ===== Tabs =====
    $(".report-tab-link").on("click", function () {
      var key = $(this).data("tab");
      $(".report-tab-link").removeClass("active");
      $(this).addClass("active");
      $(".report-tab-pane").hide();
      $("#tab-pane-" + key).show();
      buildChart(key);
    });
    var firstKey = $(".report-tab-link.active").data("tab");
    if (firstKey) buildChart(firstKey);

    // ===== Render route detail: lọc theo tìm kiếm hành trình + phân trang =====
    function renderRoutes(detailRow) {
      var q = (detailRow.find(".route-search").val() || "")
        .trim()
        .toLowerCase();
      var eligible = detailRow
        .find(".route-item.active-route-item")
        .filter(function () {
          if (!q) return true;
          return ($(this).attr("data-search") || "").indexOf(q) !== -1;
        });

      detailRow.find(".route-item").hide();
      eligible.each(function (i) {
        $(this)
          .find("td:first")
          .text(i + 1);
      });

      var wrapper = detailRow.find(".route-pagination-wrapper");
      var perPage = 10;
      var total = eligible.length;

      if (total <= perPage) {
        if (wrapper.length) wrapper.html("");
        eligible.show();
        return;
      }

      var totalPages = Math.ceil(total / perPage);
      function showPage(page) {
        eligible.hide();
        eligible.slice((page - 1) * perPage, page * perPage).show();
        renderPagination(page);
      }
      function renderPagination(page) {
        var html =
          '<ul class="pagination pagination-sm justify-content-center mb-0">';
        html +=
          '<li class="page-item ' +
          (page === 1 ? "disabled" : "") +
          '"><a class="page-link" href="#" data-page="' +
          (page - 1) +
          '">&laquo;</a></li>';
        var start = Math.max(1, page - 2);
        var end = Math.min(totalPages, page + 2);
        if (start > 1)
          html +=
            '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li><li class="page-item disabled"><span class="page-link">...</span></li>';
        for (var i = start; i <= end; i++)
          html +=
            '<li class="page-item ' +
            (i === page ? "active" : "") +
            '"><a class="page-link" href="#" data-page="' +
            i +
            '">' +
            i +
            "</a></li>";
        if (end < totalPages)
          html +=
            '<li class="page-item disabled"><span class="page-link">...</span></li><li class="page-item"><a class="page-link" href="#" data-page="' +
            totalPages +
            '">' +
            totalPages +
            "</a></li>";
        html +=
          '<li class="page-item ' +
          (page === totalPages ? "disabled" : "") +
          '"><a class="page-link" href="#" data-page="' +
          (page + 1) +
          '">&raquo;</a></li>';
        html += "</ul>";
        if (wrapper.length) wrapper.html(html);
      }
      if (wrapper.length) {
        wrapper
          .off("click", ".page-link")
          .on("click", ".page-link", function (e) {
            e.preventDefault();
            var p = $(this).data("page");
            if (p && p >= 1 && p <= totalPages) showPage(p);
          });
      }
      showPage(1);
    }

    // Tìm kiếm hành trình trong bảng chi tiết (lọc realtime)
    $(document).on("input", ".route-search", function () {
      renderRoutes($(this).closest(".detail-row"));
    });
  });
})(jQuery);
