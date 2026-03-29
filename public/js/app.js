// public/js/app.js

document.addEventListener('DOMContentLoaded', function () {

    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // Confirm delete forms
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const msg = form.dataset.confirm || 'Tem a certeza?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // Mobile sidebar toggle with overlay
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle && sidebar) {
        // Create overlay element
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }

    // Format money input on blur
    document.querySelectorAll('input[data-type="money"]').forEach(function (input) {
        input.addEventListener('blur', function () {
            const val = parseFloat(this.value.replace(',', '.'));
            if (!isNaN(val) && val >= 0) this.value = val.toFixed(2);
        });
    });

    // Dynamic debt calculation on aluno form
    const precoInput = document.getElementById('preco_total');
    const pagoInput  = document.getElementById('pago_total');
    const dividaEl   = document.getElementById('divida_calculada');

    function updateDivida() {
        if (!precoInput || !pagoInput || !dividaEl) return;
        const preco = parseFloat(precoInput.value.replace(',', '.')) || 0;
        const pago  = parseFloat(pagoInput.value.replace(',', '.')) || 0;
        const divida = preco - pago;
        dividaEl.textContent = divida.toFixed(2).replace('.', ',') + ' €';
        dividaEl.className = divida > 0 ? 'fw-bold text-danger' : 'fw-bold text-success';
    }

    if (precoInput) precoInput.addEventListener('input', updateDivida);
    if (pagoInput)  pagoInput.addEventListener('input', updateDivida);
    updateDivida();

    // Highlight active nav link on sidebar (fallback for JS routing)
    const currentPage = new URLSearchParams(window.location.search).get('page');
    if (currentPage) {
        document.querySelectorAll('.sidebar-nav .nav-link').forEach(function(link) {
            const href = link.getAttribute('href') || '';
            if (href.includes('page=' + currentPage)) {
                link.classList.add('active');
            }
        });
    }
});
