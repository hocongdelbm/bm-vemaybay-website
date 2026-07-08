/* Manager-only: click the direct bonus value (row 12) to open the modal
   listing every user of the source with an editable amount. Submitting
   sends the whole list to the server, which validates that the total
   distributed does not exceed the source's direct bonus pool.
   Amounts are formatted with the user's thousand separator while typing. */
(function () {
  var overlay = document.getElementById('direct_bonus_modal');
  if (!overlay) return;

  // num_grp_sep is SuiteCRM's standard page-level JS var for the current
  // user's number format; the pool is set by the tpl
  var GRP_SEP = window.num_grp_sep || ',';
  var POOL = Number(window.BONUS_DIRECT_POOL || 0);

  var activeTrigger = null;

  function el(selector) {
    return overlay.querySelector(selector);
  }

  function inputs() {
    return overlay.querySelectorAll('.direct-bonus-input');
  }

  function formatMoney(digits) {
    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, GRP_SEP);
  }

  function parseAmount(input) {
    return input.value.replace(/[^\d]/g, '');
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
      bonuses.push({
        assigned_user_id: input.dataset.userId,
        direct_bonus: amount
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
      url: 'index.php?entryPoint=entryGeneral',
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
        if (res && res.error === 0) {
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
    input.value = formatMoney(parseAmount(input));

    input.addEventListener('input', function () {
      this.value = formatMoney(parseAmount(this));
      refreshTotal();
    });
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
