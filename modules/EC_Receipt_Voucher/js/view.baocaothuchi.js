$(document).ready(function () {

    /* ---- Build pill options from hidden selects ---- */
    $('#bct_chips_row .bct-chip-wrap').not('#chip_date').each(function () {
        var $wrap  = $(this);
        var $btn   = $wrap.find('> .bct-chip');
        var selId  = $btn.attr('data-selid');
        if (!selId) return;

        var $sel    = $('#' + selId);
        var isMulti = !!$sel.attr('multiple');
        var rawVal  = $sel.val();
        var selVals = rawVal ? [].concat(rawVal).map(String) : [];
        var $optDiv = $wrap.find('.bct-dd-opts');

        $sel.find('option').each(function () {
            var v = String($(this).val());
            var t = $(this).text();
            $('<span class="bct-opt">')
                .attr('data-val', v)
                .attr('data-selid', selId)
                .attr('data-wrapid', $wrap.attr('id'))
                .attr('data-multi', isMulti ? '1' : '0')
                .toggleClass('sel', selVals.indexOf(v) !== -1)
                .text(t)
                .appendTo($optDiv);
        });

        syncChipLabel($wrap, $btn, $sel);
    });

    /* ---- Toggle chip dropdown ---- */
    $(document).on('click', '.bct-chip', function (e) {
        e.stopPropagation();
        var $wrap   = $(this).closest('.bct-chip-wrap');
        var wasOpen = $wrap.hasClass('open');
        $('.bct-chip-wrap.open').removeClass('open').find('.bct-dropdown').removeClass('open');
        if (!wasOpen) {
            $wrap.addClass('open');
            $wrap.find('> .bct-dropdown').addClass('open');
        }
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.bct-chip-wrap').length) {
            $('.bct-chip-wrap.open').removeClass('open');
            $('.bct-dropdown.open').removeClass('open');
        }
    });
    $(document).on('click', '.bct-dropdown', function (e) { e.stopPropagation(); });

    /* ---- Close / Apply ---- */
    $(document).on('click', '.bct-btn-cancel', function () {
        $(this).closest('.bct-chip-wrap').removeClass('open');
        $(this).closest('.bct-dropdown').removeClass('open');
    });

    $(document).on('click', '.bct-btn-xem', function () {
        $(this).closest('.bct-chip-wrap').removeClass('open');
        $(this).closest('.bct-dropdown').removeClass('open');
        submitForm(false);
    });

    /* ---- Pill option click ---- */
    $(document).on('click', '.bct-opt', function () {
        var $opt    = $(this);
        var selId   = $opt.attr('data-selid');
        var wrapId  = $opt.attr('data-wrapid');
        var val     = $opt.attr('data-val');
        var isMulti = $opt.attr('data-multi') === '1';
        var $sel    = $('#' + selId);
        var $wrap   = $('#' + wrapId);
        var $btn    = $wrap.find('> .bct-chip');
        var $allOpts = $opt.closest('.bct-dd-opts').find('.bct-opt');

        if (!isMulti) {
            $allOpts.removeClass('sel');
            $opt.addClass('sel');
            $sel.val(val);
        } else if (val === '') {
            $allOpts.removeClass('sel');
            $opt.addClass('sel');
            $sel.val(['']);
        } else {
            $opt.toggleClass('sel');
            $allOpts.filter('[data-val=""]').removeClass('sel');

            var vals = [];
            $allOpts.filter('.sel').each(function () { vals.push($(this).attr('data-val')); });

            if (vals.length === 0) {
                $allOpts.filter('[data-val=""]').addClass('sel');
                $sel.val(['']);
            } else {
                $sel.val(vals);
            }
        }

        syncChipLabel($wrap, $btn, $sel);
        refreshActiveTags();
    });

    /* ---- Sync chip label from select state ---- */
    function syncChipLabel($wrap, $btn, $sel) {
        var isMulti  = !!$sel.attr('multiple');
        var defLabel = $btn.attr('data-deflabel');

        if (!isMulti) {
            var v = $sel.val();
            if (v === '' || v === null) {
                $btn.find('.lbl').text(defLabel);
                $btn.removeClass('active');
            } else {
                $btn.find('.lbl').text($sel.find('option:selected').text());
                $btn.addClass('active');
            }
            return;
        }

        var rawVal   = $sel.val() || [];
        var actives  = [].concat(rawVal).map(String).filter(function (v) { return v !== ''; });

        if (actives.length === 0) {
            $btn.find('.lbl').text(defLabel);
            $btn.removeClass('active');
        } else if (actives.length === 1) {
            var txt = $sel.find('option[value="' + actives[0] + '"]').text();
            $btn.find('.lbl').text(txt);
            $btn.addClass('active');
        } else {
            $btn.find('.lbl').text(defLabel + ' (' + actives.length + ')');
            $btn.addClass('active');
        }
    }

    /* ---- Date chip ---- */
    $('#bct_report_term').on('change', function () {
        var $opt = $(this).find(':selected');
        var fd = $opt.data('fromdate');
        var td = $opt.data('todate');
        if (fd) $('#from_date').val(fd);
        if (td) $('#to_date').val(td);
        updateDateChip();
    });

    $(document).on('change keyup blur', '#from_date, #to_date', function () {
        updateDateChip();
    });

    function updateDateChip() {
        var fd   = $('#from_date').val();
        var td   = $('#to_date').val();
        var $btn = $('#chip_date > .bct-chip');

        if (fd || td) {
            $('#chip_date_lbl').text((fd || '?') + ' - ' + (td || '?'));
            $btn.addClass('active');
        } else {
            $('#chip_date_lbl').text('Kỳ / Ngày');
            $btn.removeClass('active');
        }
        refreshActiveTags();
    }

    /* ---- Active tags ---- */
    function refreshActiveTags() {
        var $bar  = $('#bct_active_bar');
        var items = [];

        var fd = $('#from_date').val();
        var td = $('#to_date').val();
        if (fd || td) {
            items.push({ text: 'Kỳ: ' + (fd || '?') + ' - ' + (td || '?'), type: 'date' });
        }

        $('#bct_chips_row .bct-chip-wrap').not('#chip_date').each(function () {
            var $wrap = $(this);
            var $btn  = $wrap.find('> .bct-chip');
            if (!$btn.hasClass('active')) return;
            if ($btn.attr('data-notag') === '1') return;

            var $sel    = $('#' + $btn.attr('data-selid'));
            var rawVal  = $sel.val() || [];
            var actives = [].concat(rawVal).map(String).filter(function (v) { return v !== ''; });
            var label   = $btn.attr('data-filterlabel');
            var tagText;

            if (actives.length === 1) {
                tagText = label + ': ' + $btn.find('.lbl').text();
            } else {
                var names = actives.map(function (v) {
                    return $sel.find('option[value="' + v + '"]').text();
                });
                tagText = label + ': ' + names.join(', ');
            }

            items.push({
                text    : tagText,
                type    : 'chip',
                wrapId  : $wrap.attr('id'),
                selId   : $btn.attr('data-selid'),
                defLabel: $btn.attr('data-deflabel')
            });
        });

        $bar.empty();
        if (!items.length) return;
        $bar.append('<span class="bct-active-lbl fw-semibold">Đang lọc theo: </span>')
            .append('<span class="bct-clear-all" id="bct_clear_all" title="Xóa tất cả bộ lọc">Xóa bộ lọc</span>');
        const $icon_clear = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                            </svg>`; 
        $.each(items, function (i, item) {
            let $tag = $('<span class="bct-tag">').text(item.text);
            let $rm  = $(`<span class="bct-tag-rm" title="Xóa bộ lọc">${$icon_clear}</span>`).data('item', item);
      
            $tag.append($rm);
            $bar.append($tag);
        });
    }

    $(document).on('click', '.bct-tag-rm', function () {
        let item = $(this).data('item');
        if (item.type === 'date') {
            $('#from_date').val('');
            $('#to_date').val('');
        } else {
            let $sel = $('#' + item.selId);
            if ($sel.attr('multiple')) {
                $sel.val(['']);
            } else {
                $sel.val('');
            }
        }
        submitForm(false);
    });

    $(document).on('click', '#bct_clear_all', function () {
        $('#from_date').val('');
        $('#to_date').val('');
        $('#bct_chips_row .bct-chip-wrap').not('#chip_date').each(function () {
            var $sel = $('#' + $(this).find('> .bct-chip').attr('data-selid'));
            if (!$sel.length) return;
            $sel.attr('multiple') ? $sel.val(['']) : $sel.val('');
        });
        submitForm(false);
    });

    $('#bct_btn_view').on('click', function () { submitForm(false); });
    $('#bct_btn_excel').on('click', function () { submitForm(true); });

    function submitForm(excel) {
        $('.container-waiting').show();
        var $form = $('#frmSearchBaoCaoThu');
        $form.find('.bct-sf').remove();
        $form.append('<input type="hidden" class="bct-sf" name="btnViewDetail" value="1">');
        if (excel) $form.append('<input type="hidden" class="bct-sf" name="exportexcel" value="1">');
        var locTxt = $('#sel_location_id option:selected').first().text();
        $('#location_name').val(locTxt && locTxt.indexOf('--') === -1 ? locTxt : '');
        $form.submit();
    }

    updateDateChip();
    refreshActiveTags();
});
