(function () {
    const container = document.getElementById('posts-container');
    const searchInput = document.getElementById('search-q');
    let searchTimer;

    function renderCards(posts) {
        if (!container) return;
        if (!posts.length) {
            container.innerHTML = '<p class="page-sub">No destinations found.</p>';
            return;
        }
        let html = '<div class="post-grid" id="post-grid">';
        posts.forEach((p) => {
            html += `<article class="post-card"><div class="post-card-body">
                <h3>${escapeHtml(p.title)}</h3>
                <div class="meta">${escapeHtml(p.country)} &middot; ${escapeHtml(p.genre)}
                <span class="badge badge-${escapeHtml(p.cost_level)}">${escapeHtml(p.cost_level)}</span></div>
                <p class="snippet">${escapeHtml(p.short_history)}...</p>
                <div class="post-card-actions">
                    <a class="btn btn-primary btn-sm" href="${escapeHtml(p.detail_url)}">Read more</a>
                </div></div></article>`;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(async () => {
                const q = searchInput.value.trim();
                if (q.length < 1) return;
                const res = await fetch(window.routeUrl('/api/posts/search', { q }));
                const data = await res.json();
                if (data.success) renderCards(data.posts);
            }, 300);
        });
    }

    const applyBtn = document.getElementById('apply-filters');
    if (applyBtn) {
        applyBtn.addEventListener('click', async () => {
            const country = document.getElementById('filter-country')?.value || '';
            const cost = document.querySelector('input[name="filter-cost"]:checked')?.value || '';
            const genreChecks = document.querySelectorAll('#filter-genres input:checked');
            const genre = genreChecks.length === 1 ? genreChecks[0].value : (genreChecks.length ? genreChecks[0].value : '');
            const params = {};
            if (country) params.country = country;
            if (genre) params.genre = genre;
            if (cost) params.cost = cost;
            const res = await fetch(window.routeUrl('/api/posts/filter', params));
            const data = await res.json();
            if (data.success) renderCards(data.posts);
        });
    }

    const calcBox = document.getElementById('cost-calculator');
    if (calcBox) {
        const baseCost = parseFloat(calcBox.dataset.base) || 0;
        const currency = calcBox.dataset.currency || 'USD';
        const travelersEl = document.getElementById('calc-travelers');
        const daysEl = document.getElementById('calc-days');
        const totalEl = document.getElementById('calc-total');

        function updateCalc() {
            let t = parseInt(travelersEl.value, 10);
            let d = parseInt(daysEl.value, 10);
            if (t < 1 || t > 10) {
                totalEl.textContent = 'Travelers must be 1–10.';
                return;
            }
            if (d < 1) {
                totalEl.textContent = 'Days must be positive.';
                return;
            }
            const total = baseCost * t * (d / 7);
            totalEl.textContent = 'Estimated total: ' + currency + ' ' + total.toFixed(2);
        }
        travelersEl?.addEventListener('input', updateCalc);
        daysEl?.addEventListener('input', updateCalc);
        updateCalc();
    }

    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const content = document.getElementById('comment_content').value.trim();
            const errEl = document.getElementById('comment-error');
            if (!content) {
                errEl.style.display = 'block';
                errEl.textContent = 'Comment cannot be empty.';
                return;
            }
            if (content.length > 1000) {
                errEl.style.display = 'block';
                errEl.textContent = 'Max 1000 characters.';
                return;
            }
            errEl.style.display = 'none';
            const res = await fetch(window.routeUrl('/api/comments/add'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    post_id: document.getElementById('comment-post-id').value,
                    content,
                    display_name: document.getElementById('display_name').value,
                }),
            });
            const data = await res.json();
            if (data.success) {
                const li = document.createElement('li');
                li.className = 'comment-item';
                li.dataset.id = data.comment.id;
                li.innerHTML = `<div class="author">${escapeHtml(data.comment.user_name)}</div>
                    <div class="date">${escapeHtml(data.comment.created_at)}</div>
                    <div class="text">${escapeHtml(data.comment.content)}</div>
                    <button type="button" class="btn btn-danger btn-sm comment-delete" data-id="${data.comment.id}">Delete</button>`;
                document.getElementById('comment-list').prepend(li);
                document.getElementById('comment_content').value = '';
                bindCommentDelete(li.querySelector('.comment-delete'));
            }
        });
    }

    function bindCommentDelete(btn) {
        if (!btn) return;
        btn.addEventListener('click', async () => {
            const res = await fetch(window.routeUrl('/api/comments/delete'), {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: btn.dataset.id }),
            });
            const data = await res.json();
            if (data.success) btn.closest('.comment-item')?.remove();
        });
    }

    document.querySelectorAll('.comment-delete').forEach(bindCommentDelete);
})();
