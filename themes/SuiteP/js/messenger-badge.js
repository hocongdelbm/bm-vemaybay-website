(function () {
    function updateMessengerBadge(count) {
        var button = document.getElementById('messenger-chat-widget');
        var badge = button && button.querySelector('.messenger-chat-unread-badge');
        if (!badge) return;

        count = Number(count) || 0;
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = count > 0 ? 'flex' : 'none';
        badge.setAttribute('aria-label', count + ' tin nhắn chưa đọc');
    }

    window.addEventListener('messenger:unread', function (event) {
        updateMessengerBadge(event.detail && event.detail.count);
    });
}());
