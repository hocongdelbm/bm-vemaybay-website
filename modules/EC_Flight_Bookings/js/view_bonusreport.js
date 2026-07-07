var DETAIL_PAGE_SIZE = 20;

// The bonus policy starts on this date — the report cannot go earlier
// (also enforced server-side in view.bonusreport.php)
var BONUS_MIN_DATE = new Date(2026, 6, 1);
var BONUS_MIN_DATE_STR = '01-07-2026';

// Users type both 01-07-2026 and 01/07/2026 — accept either delimiter
function parseBonusDate(str) {
	var m = /^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/.exec((str || '').trim());
	return m ? new Date(+m[3], m[2] - 1, +m[1]) : null;
}

// Format with the SuiteCRM user-preference separators (same globals the
// server-side format_number() uses) so the modal matches the table data
function formatBonusNumber(n) {
	var rounded = Math.round(n || 0);
	if (typeof num_grp_sep !== 'undefined') {
		return rounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, num_grp_sep);
	}
	return new Intl.NumberFormat('vi-VN').format(rounded);
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
	// The date pickers are YUI calendars built by Calendar.setup, which has
	// no min-date option — set YUI's own mindate so earlier days are greyed
	// out. Page-wide patch, but this page only has the two report pickers
	if (typeof YAHOO !== 'undefined' && YAHOO.widget && YAHOO.widget.Calendar) {
		var origCalendarRender = YAHOO.widget.Calendar.prototype.render;
		YAHOO.widget.Calendar.prototype.render = function () {
			// Pass a copy: YUI's DateMath.clearTime() mutates the date it
			// receives (sets it to noon), which would corrupt the shared
			// constant and make 01-07 itself fail the min-date check
			this.cfg.setProperty('mindate', new Date(BONUS_MIN_DATE.getTime()));
			return origCalendarRender.apply(this, arguments);
		};
	}

	// MIN DATE CHECK on submit — catches hand-typed dates and term presets
	// that fall before the policy start (e.g. "Tháng trước")
	$('#bk-bonus-report form').on('submit', function (e) {
		var clamped = false;
		$('#from_date, #to_date').each(function () {
			var d = parseBonusDate($(this).val());
			if (d && d < BONUS_MIN_DATE) {
				$(this).val(BONUS_MIN_DATE_STR);
				clamped = true;
			}
		});
		if (clamped) {
			e.preventDefault();
			alert('Chỉ xem được báo cáo thưởng từ ngày ' + BONUS_MIN_DATE_STR + '.');
		}
	});

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
		var indirectKpi = parseFloat($(this).data('indirectkpi')) || 0;

		$('#parent-bonus-per-ticket').text(formatBonusNumber(perTicket));
		$('#parent-bonus-indirectkpi').text(formatBonusNumber(indirectKpi));
		$('#parent-bonus-indirect-perkpi').text(formatBonusNumber(indirectKpi > 0 ? indirect / indirectKpi : 0));
		$('#parent-bonus-direct').text(formatBonusNumber(direct));
		$('#parent-bonus-indirect').text(formatBonusNumber(indirect));
		$('#parent-bonus-total').text(formatBonusNumber(total));

		// Number line of each formula note (the static description line
		// below it lives in the template). Operators are bolded, and the two
		// threshold values carry a title so users can tell them apart
		var op = function (o) {
			return ' <b>' + o + '</b> ';
		};
		$('#parent-bonus-avgprofit-note').html(
			'<b>=</b> ' + formatBonusNumber(profit) + op('/') + formatBonusNumber(qty)
		);
		// Mirror the server-side rule: base part when avg profit reaches the
		// min threshold, extra part on the amount above the extra threshold
		var perTicketParts = [];
		if (minThreshold > 0 && avgProfit >= minThreshold) {
			perTicketParts.push(
				bonusPct + '%' + op('x')
				+ '<a title="Ngưỡng tối thiểu">' + formatBonusNumber(minThreshold) + '</a>'
			);
		}
		if (extraThreshold > 0 && avgProfit > extraThreshold) {
			perTicketParts.push(
				extraPct + '%' + op('x')
				+ '(' + formatBonusNumber(avgProfit) + op('-')
				+ '<a title="Ngưỡng tính thưởng thêm">' + formatBonusNumber(extraThreshold) + '</a>)'
			);
		}
		$('#parent-bonus-perticket-note').html(
			perTicketParts.length ? '<b>=</b> ' + perTicketParts.join(op('+')) : 'Chưa đạt ngưỡng tối thiểu'
		);
		$('#parent-bonus-direct-note').html(
			'<b>=</b> 70%' + op('x') + formatBonusNumber(total)
		);
		$('#parent-bonus-indirect-note').html(
			'<b>=</b> 30%' + op('x') + formatBonusNumber(total)
		);
		$('#parent-bonus-total-note').html(
			'<b>=</b> ' + formatBonusNumber(perTicket) + op('x') + formatBonusNumber(qty)
		);
		$('#parent-bonus-indirect-perkpi-note').html(
			indirectKpi > 0
				? '<b>=</b> ' + formatBonusNumber(indirect) + op('/') + formatBonusNumber(indirectKpi)
				: ''
		);

		// Re-open centered, dropping any position from a previous drag
		$('#parent-bonus-modal .bonus-modal').css('transform', '').removeData('drag-pos');
		$('#parent-bonus-modal').css('display', 'flex');
	});

	// DRAG THE MODAL BY ITS HEADER: the modal is flex-centered in the
	// overlay, so dragging just offsets it with a translate() transform
	var bonusModalDrag = null;
	var bonusModalDragMoved = false;

	$('#parent-bonus-modal').on('mousedown', function () {
		bonusModalDragMoved = false;
	});

	$('#parent-bonus-modal').on('mousedown', '.bonus-modal-header', function (e) {
		if ($(e.target).closest('.bonus-modal-close').length) {
			return;
		}
		var $modal = $(this).closest('.bonus-modal');
		var pos = $modal.data('drag-pos') || { x: 0, y: 0 };
		bonusModalDrag = {
			$modal: $modal,
			offsetX: e.pageX - pos.x,
			offsetY: e.pageY - pos.y
		};
		e.preventDefault();
	});

	$(document).on('mousemove', function (e) {
		if (!bonusModalDrag) {
			return;
		}
		bonusModalDragMoved = true;
		var x = e.pageX - bonusModalDrag.offsetX;
		var y = e.pageY - bonusModalDrag.offsetY;
		bonusModalDrag.$modal
			.data('drag-pos', { x: x, y: y })
			.css('transform', 'translate(' + x + 'px, ' + y + 'px)');
	});

	$(document).on('mouseup', function () {
		bonusModalDrag = null;
	});

	// Close modal on X button or backdrop click. A click that ends a drag
	// (mouseup over the backdrop) must not close the modal
	$('#parent-bonus-modal').on('click', function (e) {
		if (bonusModalDragMoved) {
			return;
		}
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
