(function () {
    document.querySelectorAll('.scout-delete').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete this request?')) return;
            const res = await fetch(window.routeUrl('/api/scout/request/delete'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', ...window.csrfHeaders() },
                body: JSON.stringify({ id: btn.dataset.id }),
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('req-row-' + btn.dataset.id)?.remove();
            } else {
                alert(data.error || 'Delete failed.');
            }
        });
    });

    const form = document.getElementById('scout-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const required = ['title', 'country', 'short_history', 'travel_medium_info'];
            let ok = true;
            required.forEach((id) => {
                const el = form.querySelector('#' + id);
                if (el && !el.value.trim()) ok = false;
            });
            if (!ok) {
                e.preventDefault();
                alert('Please fill all required fields.');
            }
        });
    }
})();
