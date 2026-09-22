(function () {
    window.routeUrl = function (path, query) {
        const base = document.querySelector('meta[name="app-base"]')?.content || '/index.php';
        const route = path.startsWith('/') ? path : '/' + path;
        let u;
        if (route === '/') {
            u = base.split('?')[0];
        } else {
            const sep = base.includes('?') ? '&' : '?';
            const root = base.split('?')[0];
            u = root + sep + 'route=' + encodeURIComponent(route);
        }
        if (query && typeof query === 'object') {
            const qs = new URLSearchParams(query).toString();
            if (qs) {
                u += (u.includes('?') ? '&' : '?') + qs;
            }
        }
        return u;
    };

    window.csrfHeaders = function () {
        return { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' };
    };
})();
