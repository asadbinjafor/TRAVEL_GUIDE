(function () {
    async function postJson(path, body) {
        const res = await fetch(window.routeUrl(path), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        return res.json();
    }

    document.querySelectorAll('.admin-verify').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const verified = btn.dataset.verified === '1' ? 0 : 1;
            const data = await postJson('/api/admin/verify', { user_id: btn.dataset.userId, is_verified: verified });
            if (data.success) {
                btn.dataset.verified = String(verified);
                btn.textContent = verified ? 'Unverify' : 'Verify';
                const statusCell = btn.closest('tr')?.querySelector('td:nth-child(4)');
                if (statusCell) {
                    statusCell.innerHTML = verified
                        ? '<span class="badge badge-approved">Verified</span>'
                        : '<span class="badge badge-pending">Pending</span>';
                }
                if (verified) {
                    window.location.reload();
                }
            } else alert(data.error);
        });
    });

    document.querySelectorAll('.admin-delete-user').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete user and related data?')) return;
            const res = await fetch(window.routeUrl('/api/admin/user/delete'), {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: btn.dataset.userId }),
            });
            const data = await res.json();
            if (data.success) btn.closest('tr')?.remove();
            else alert(data.error);
        });
    });

    document.querySelectorAll('.admin-approve').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const data = await postJson('/api/admin/request/approve', { request_id: btn.dataset.id });
            if (data.success) document.getElementById('pending-' + btn.dataset.id)?.remove();
            else alert(data.error);
        });
    });

    document.querySelectorAll('.admin-reject').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const reason = prompt('Rejection reason (optional):') || 'Rejected';
            const data = await postJson('/api/admin/request/reject', { request_id: btn.dataset.id, reason });
            if (data.success) document.getElementById('pending-' + btn.dataset.id)?.remove();
            else alert(data.error);
        });
    });

    document.querySelectorAll('.admin-delete-post').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete post?')) return;
            const data = await postJson('/api/admin/post/delete', { post_id: btn.dataset.id });
            if (data.success) btn.closest('tr')?.remove();
        });
    });

    document.querySelectorAll('.admin-delete-comment').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const data = await postJson('/api/admin/comment/delete', { comment_id: btn.dataset.id });
            if (data.success) document.getElementById('comment-row-' + btn.dataset.id)?.remove();
        });
    });
})();
