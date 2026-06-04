(function () {
    async function api(path, method, body) {
        const opts = { method, headers: { 'Content-Type': 'application/json' } };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(window.routeUrl(path), opts);
        return res.json();
    }

    document.querySelectorAll('.wishlist-add').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const postId = btn.dataset.postId;
            const data = await api('/api/wishlist/add', 'POST', { post_id: postId });
            if (data.success) {
                btn.textContent = 'Saved';
                btn.disabled = true;
                btn.classList.remove('wishlist-add');
            } else {
                alert(data.error || 'Could not add.');
            }
        });
    });

    document.querySelectorAll('.wishlist-remove').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (!confirm('Remove from wishlist?')) return;
            const postId = btn.dataset.postId;
            const data = await api('/api/wishlist/remove', 'DELETE', { post_id: postId });
            if (data.success) {
                const row = document.getElementById('wish-row-' + postId);
                if (row) row.remove();
            } else {
                alert(data.error || 'Could not remove.');
            }
        });
    });
})();
