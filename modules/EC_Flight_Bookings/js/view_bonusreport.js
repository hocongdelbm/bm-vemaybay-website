var DETAIL_PAGE_SIZE = 20;

function formatBonusNumber(n) {
	return new Intl.NumberFormat('vi-VN').format(Math.round(n));
}

// Build pager controls: prev/next, windowed page numbers around the
// current page (with ellipsis), and a total counter
function bonusDetailPagerHtml(page, totalPages, totalRows) {
	var html = '<button type="button" class="pager-btn pager-prev"' + (page <= 1 ? ' disabled' : '') + '>&laquo;</button>';

	var pages = [];
	for (var p = 1; p <= totalPages; p++) {
		if (p === 1 || p === totalPages || Math.abs(p - page) <= 2) {
			pages.push(p);
		}
	}

	var prev = 0;
	pages.forEach(function (p) {
		if (p - prev > 1) {
			html += '<span class="pager-ellipsis">&hellip;</span>';
		}
		html += '<button type="button" class="pager-btn pager-num' + (p === page ? ' active' : '') + '" data-page="' + p + '">' + p + '</button>';
		prev = p;
	});

	html += '<button type="button" class="pager-btn pager-next"' + (page >= totalPages ? ' disabled' : '') + '>&raquo;</button>';
	html += '<span class="pager-info">' + totalRows + ' booking</span>';
	return html;
}

// Show one page of booking rows in a per-user detail table. The footer
// (totals) row is not part of .bonus-booking-row so it always stays visible
function paginateBonusDetail($table, page) {
	var $rows = $table.find('.bonus-booking-row');
	var totalRows = $rows.length;
	var totalPages = Math.ceil(totalRows / DETAIL_PAGE_SIZE);

	if (totalPages <= 1) {
		return;
	}

	page = Math.min(Math.max(page, 1), totalPages);
	$table.data('current-page', page);

	var start = (page - 1) * DETAIL_PAGE_SIZE;
	$rows.hide().slice(start, start + DETAIL_PAGE_SIZE).show();

	var $pager = $table.next('.bonus-detail-pager');
	if (!$pager.length) {
		$pager = $('<div class="bonus-detail-pager"></div>').insertAfter($table);
	}
	$pager.html(bonusDetailPagerHtml(page, totalPages, totalRows));
}

$(document).ready(function() {
	// REPORT TERM LIST CHANGE
	$('#report_term_list').on('change', function () {
		var reportTermList = $('#report_term_list :selected');
		$('#from_date').val(reportTermList.data('fromdate'));
		$('#to_date').val(reportTermList.data('todate'));
		$('#report_term').val(reportTermList.data('term'));
		$('#report_year').val(reportTermList.data('year'));
	});

	// INIT PAGINATION for every per-user detail table
	$('.bonus-detail-row .table-details__booking').each(function () {
		paginateBonusDetail($(this), 1);
	});

	// PAGER CLICKS (prev / next / page number)
	$('#bonus-report-tbl').on('click', '.bonus-detail-pager .pager-btn:not([disabled])', function () {
		var $pager = $(this).closest('.bonus-detail-pager');
		var $table = $pager.prev('.table-details__booking');
		var current = $table.data('current-page') || 1;

		var page;
		if ($(this).hasClass('pager-prev')) {
			page = current - 1;
		} else if ($(this).hasClass('pager-next')) {
			page = current + 1;
		} else {
			page = parseInt($(this).data('page'), 10);
		}
		paginateBonusDetail($table, page);
	});

	// TOGGLE PER-USER BONUS DETAIL ROW
	$('#bonus-report-tbl').on('click', '.js-toggle-bonus-detail', function () {
		$('#bonus-detail-' + $(this).data('user')).toggle();
	});

	// PARENT BONUS MODAL: clicking a booking name shows the booking-level
	// total direct/indirect bonus (server-computed, same for every user row)
	$('#bonus-report-tbl').on('click', '.js-parent-bonus', function () {
		var qty      = parseFloat($(this).data('qty')) || 0;
		var revenue  = parseFloat($(this).data('revenue')) || 0;
		var cost     = parseFloat($(this).data('cost')) || 0;
		var profit   = parseFloat($(this).data('profit')) || 0;
		var direct   = parseFloat($(this).data('direct')) || 0;
		var indirect = parseFloat($(this).data('indirect')) || 0;
		var total    = direct + indirect;

		var bonusPct  = parseFloat($(this).data('bonuspercent')) || 0;
		var extraPct  = parseFloat($(this).data('extrapercent')) || 0;
		var perTicket = parseFloat($(this).data('perticket')) || 0;

		var avgProfit      = parseFloat($(this).data('avgprofit')) || 0;
		var minThreshold   = parseFloat($(this).data('minthreshold')) || 0;
		var extraThreshold = parseFloat($(this).data('extrathreshold')) || 0;

		$('#parent-bonus-name').text($(this).data('name'));
		$('#parent-bonus-qty').text(formatBonusNumber(qty));
		$('#parent-bonus-revenue').text(formatBonusNumber(revenue));
		$('#parent-bonus-cost').text(formatBonusNumber(cost));
		$('#parent-bonus-profit').text(formatBonusNumber(profit));
		$('#parent-bonus-avgprofit').text(formatBonusNumber(avgProfit));
		$('#parent-bonus-thresholds').text(formatBonusNumber(minThreshold) + ' / ' + formatBonusNumber(extraThreshold));
		$('#parent-bonus-per-ticket').text(formatBonusNumber(perTicket));
		$('#parent-bonus-indirectkpi').text(formatBonusNumber(parseFloat($(this).data('indirectkpi')) || 0));
		$('#parent-bonus-direct').text(formatBonusNumber(direct));
		$('#parent-bonus-indirect').text(formatBonusNumber(indirect));
		$('#parent-bonus-total').text(formatBonusNumber(total));

		// Number line of each formula note (the static description line
		// below it lives in the template)
		$('#parent-bonus-profit-note').text(
			'= ' + formatBonusNumber(revenue) + ' - ' + formatBonusNumber(cost)
		);
		$('#parent-bonus-avgprofit-note').text(
			'= ' + formatBonusNumber(profit) + ' / ' + formatBonusNumber(qty)
		);
		// Mirror the server-side rule: base part when avg profit reaches the
		// min threshold, extra part on the amount above the extra threshold
		var perTicketParts = [];
		if (minThreshold > 0 && avgProfit >= minThreshold) {
			perTicketParts.push(bonusPct + '% x ' + formatBonusNumber(minThreshold));
		}
		if (extraThreshold > 0 && avgProfit > extraThreshold) {
			perTicketParts.push(extraPct + '% x (' + formatBonusNumber(avgProfit) + ' - ' + formatBonusNumber(extraThreshold) + ')');
		}
		$('#parent-bonus-perticket-note').text(
			perTicketParts.length ? '= ' + perTicketParts.join(' + ') : 'Chưa đạt ngưỡng tối thiểu'
		);
		$('#parent-bonus-direct-note').text(
			'= 70% x ' + formatBonusNumber(total)
		);
		$('#parent-bonus-indirect-note').text(
			'= 30% x ' + formatBonusNumber(total)
		);
		$('#parent-bonus-total-note').text(
			'= ' + formatBonusNumber(direct) + ' + ' + formatBonusNumber(indirect)
		);

		$('#parent-bonus-modal').css('display', 'flex');
	});

	// Close modal on X button or backdrop click
	$('#parent-bonus-modal').on('click', function (e) {
		if (e.target === this || $(e.target).closest('.bonus-modal-close').length) {
			$(this).hide();
		}
	});

	// Close modal on Escape
	$(document).on('keydown', function (e) {
		if (e.key === 'Escape') {
			$('#parent-bonus-modal').hide();
		}
	});

	// The search field lives inside the report form only for layout;
	// don't let Enter submit the form
	$('#booking_search').on('keydown', function (e) {
		if (e.key === 'Enter') {
			e.preventDefault();
		}
	});

	// SEARCH BY BOOKING NAME: highlight matches, expand their owner's
	// detail row and jump each table to the page of its first match
	$('#booking_search').on('input', function () {
		var keyword = $(this).val().toLowerCase().trim();

		$('.bonus-booking-row').removeClass('booking-search-hit');

		if (keyword === '') {
			return;
		}

		var $matches = $('.bonus-booking-row').filter(function () {
			return String($(this).data('booking-name')).indexOf(keyword) !== -1;
		});

		$matches.addClass('booking-search-hit')
			.closest('.bonus-detail-row').show();

		$matches.closest('.table-details__booking').each(function () {
			var $table = $(this);
			var firstIdx = $table.find('.bonus-booking-row')
				.index($table.find('.booking-search-hit').first());
			paginateBonusDetail($table, Math.floor(firstIdx / DETAIL_PAGE_SIZE) + 1);
		});

		var $visibleMatches = $matches.filter(':visible');
		if ($visibleMatches.length) {
			$visibleMatches[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	});
});
