function selectRole(id, el) {
    document.querySelectorAll('.drm-role-item').forEach(function (item) {
        item.classList.remove('drm-role-item--selected');
    });
    el.classList.add('drm-role-item--selected');
    document.getElementById('drm-selected-record').value = id;
    document.getElementById('drm-next-btn').disabled = false;
}

function drmConfirmSubmit(form) {
    var roleName = form.getAttribute('data-role') || '';
    if (!confirm('Are you sure?\n\nThis will disable ALL permissions for role "' + roleName + '".\n\nThis cannot be undone automatically.')) {
        return false;
    }
    var btn = document.getElementById('drm-confirm-btn');
    var txt = document.getElementById('drm-confirm-text');
    if (txt) txt.textContent = 'Processing…';
    setTimeout(function () { if (btn) btn.disabled = true; }, 0);
    return true;
}
