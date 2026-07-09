/* Manager-only: click the direct bonus value (row 12) to open the modal
   listing every user of the source with an editable amount. Each row also
   has a percent input (share of the pool) kept in two-way sync with the
   amount: typing a percent fills the amount, typing an amount refills the
   percent. Inputs are clamped while typing: amounts to [0, pool], percents
   to whole numbers in [0, 100]. Submitting sends the whole amount list to the server, which
   validates that the total distributed does not exceed the source's direct
   bonus pool. Amounts are formatted with the user's thousand separator
   while typing. */

const ENTRY_URL = "index.php?entryPoint=entryPointGeneral";

(function () {
  var overlay = document.getElementById('direct_bonus_modal');
  if (!overlay) return;

  // num_grp_sep is SuiteCRM's standard page-level JS var for the current
  // user's number format; the pool is set by the tpl
  var POOL = Number(window.BONUS_DIRECT_POOL || 0);

  var activeTrigger = null;

  function el(selector) {
    return overlay.querySelector(selector);
  }

  function inputs() {
    return overlay.querySelectorAll('.direct-bonus-input');
  }

  function percentInputs() {
    return overlay.querySelectorAll('.direct-bonus-percent-input');
  }

  function formatMoney(digits) {
    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, num_grp_sep);
  }

  function parseAmount(input) {
    return input.value.replace(/[^\d]/g, '');
  }

  // Percents are whole numbers only: keep digits, drop any decimal part
  function parsePercent(input) {
    return input.value.replace(/[^\d]/g, '');
  }

  function amountToPercent(amount) {
    if (POOL <= 0) return '0';
    return String(Math.round((amount / POOL) * 100));
  }

  // Min/max bounds: parse* strips the minus sign so the floor is 0;
  // these cap one row's amount at the pool and its percent at 100
  function clampedAmountDigits(input) {
    var digits = parseAmount(input);
    return Number(digits || 0) > POOL ? String(POOL) : digits;
  }

  function clampedPercentValue(input) {
    var value = parsePercent(input);
    return Number(value || 0) > 100 ? '100' : value;
  }

  // Percent typed: fill the row's amount with its share of the pool
  // (floored so a 100% split never exceeds the pool)
  function syncAmountFromPercent(pctInput) {
    var row = pctInput.closest('.bonus-user-row');
    var amountInput = row.querySelector('.direct-bonus-input');
    var pct = Number(parsePercent(pctInput) || 0);
    amountInput.value = formatMoney(String(Math.floor((POOL * pct) / 100)));
    refreshTotal();
  }

  // Amount typed: refill the row's percent from its share of the pool
  function syncPercentFromAmount(amountInput) {
    var row = amountInput.closest('.bonus-user-row');
    var pctInput = row.querySelector('.direct-bonus-percent-input');
    if (pctInput) pctInput.value = amountToPercent(Number(parseAmount(amountInput) || 0));
  }

  // Recompute the distributed total and color it when it exceeds the pool
  function refreshTotal() {
    var total = 0;
    inputs().forEach(function (input) {
      total += Number(parseAmount(input) || 0);
    });

    var box = el('.bm-total');
    box.textContent = formatMoney(String(total));
    box.style.color = total > POOL ? '#d9534f' : '';

    var pctBox = el('.bm-total-percent');
    if (pctBox) {
      pctBox.textContent = amountToPercent(total);
      pctBox.style.color = total > POOL ? '#d9534f' : '';
    }
    return total;
  }

  function openModal(trigger) {
    activeTrigger = trigger;

    el('.bm-source').textContent = trigger.dataset.sourceName || '';
    el('.bonus-modal-error').textContent = '';
    el('.bm-submit').disabled = false;

    refreshTotal();
    overlay.classList.add('open');

    var first = inputs()[0];
    if (first) first.focus();
  }

  function closeModal() {
    overlay.classList.remove('open');
    activeTrigger = null;
  }

  function showError(message) {
    var box = el('.bonus-modal-error');
    box.classList.remove('bonus-modal-success');
    box.textContent = message;
    el('.bm-submit').disabled = false;
  }

  function showSuccess(message) {
    var box = el('.bonus-modal-error');
    box.classList.add('bonus-modal-success');
    box.textContent = message;
    el('.bm-submit').disabled = false;
  }

  function submit() {
    if (!activeTrigger) return;

    var bonuses = [];
    var total = 0;
    var valid = true;

    inputs().forEach(function (input) {
      var amount = parseAmount(input);
      if (amount === '') {
        valid = false;
        input.focus();
        return;
      }
      total += Number(amount);

      var row = input.closest('.bonus-user-row');
      var descInput = row ? row.querySelector('.direct-bonus-description') : null;

      bonuses.push({
        assigned_user_id: input.dataset.userId,
        direct_bonus: amount,
        description: descInput ? descInput.value.trim() : ''
      });
    });

    if (!valid) {
      showError('Vui lòng nhập số tiền cho tất cả nhân viên (nhập 0 nếu không thưởng)');
      return;
    }
    if (total > POOL) {
      showError('Tổng đã chia (' + formatMoney(String(total)) + ') vượt quá tối đa có thể chia (' + formatMoney(String(POOL)) + ')');
      return;
    }

    el('.bm-submit').disabled = true;
    el('.bonus-modal-error').textContent = '';

    $.ajax({
      url: ENTRY_URL,
      type: 'POST',
      contentType: 'application/json',
      dataType: 'json',
      data: JSON.stringify({
        class: 'entryBonusClass',
        method: 'updateDirectBonusList',
        params: {
          source_id: activeTrigger.dataset.sourceId,
          source_type: activeTrigger.dataset.sourceType,
          bonuses: bonuses
        }
      }),
      success: function (res) {
        if (res && res.status === true) {
          showSuccess((res && res.message) || 'Đã cập nhật thưởng trực tiếp');
        } else {
          showError((res && res.message) || 'Cập nhật thất bại');
        }
      },
      error: function () {
        showError('Cập nhật thất bại, vui lòng thử lại');
      }
    });
  }

  el('.bm-cancel').addEventListener('click', closeModal);
  el('.bm-submit').addEventListener('click', submit);
  overlay.addEventListener('mousedown', function (e) {
    if (e.target === overlay) closeModal();
  });

  inputs().forEach(function (input) {
    // Initial value comes unformatted from the tpl
    input.value = formatMoney(clampedAmountDigits(input));
    syncPercentFromAmount(input);

    input.addEventListener('input', function () {
      this.value = formatMoney(clampedAmountDigits(this));
      syncPercentFromAmount(this);
      refreshTotal();
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') submit();
      if (e.key === 'Escape') closeModal();
    });
  });

  percentInputs().forEach(function (input) {
    input.addEventListener('input', function () {
      this.value = clampedPercentValue(this);
      syncAmountFromPercent(this);
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') submit();
      if (e.key === 'Escape') closeModal();
    });
  });

  overlay.querySelectorAll('.direct-bonus-description').forEach(function (input) {
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') submit();
      if (e.key === 'Escape') closeModal();
    });
  });

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('.direct-bonus-trigger');
    if (trigger) openModal(trigger);
  });
})();
