(function () {
    const html = document.documentElement;
    const isOpen = localStorage.getItem('searchFormVisible') === 'open';

    html.classList.toggle('search-open', isOpen);

    if (isOpen) {
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('search_form');
            const btn = document.getElementById('filter_report');
            if (form) form.style.display = 'block';
            if (btn) btn.classList.add('text-primary');
        });
    }
})();
