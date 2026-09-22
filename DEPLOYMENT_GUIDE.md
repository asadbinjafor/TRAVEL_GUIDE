# Travel Guide deployment guide

এই guide-টি repository-র actual code ও routes অনুযায়ী লেখা। Project-এর নাম **Travel Guide**; PHP entrypoint ও real homepage হলো `/index.php`। Login `/index.php?route=%2Flogin`, registration `/index.php?route=%2Fregister`, এবং admin dashboard `/index.php?route=%2Fadmin`। Roles হলো `admin`, `scout`, `user`।

## Project architecture

- **GitHub**: এই repository-র source of truth। Render ও Vercel একই production branch (`main`) থেকে deploy হবে।
- **Supabase**: PostgreSQL database এবং permanent profile/post image storage। Render filesystem persistent ধরা যাবে না।
- **Render**: PHP 8.3 + Apache container; সম্পূর্ণ frontend/backend, sessions, authentication, CRUD এবং storage upload চালায়।
- **Vercel**: `vercel-proxy/` থেকে static startup page serve করে। `/` কখনও সরাসরি Render-এ যায় না; অন্য path/query/POST Render-এ external rewrite হয়।

Flow: browser → Vercel root startup page → Vercel-এর same-origin proxy দিয়ে Render health/home probes → stable হলে `/index.php` → Render PHP → Supabase PostgreSQL/Storage।

## Environment variables

Render production-এ required:

| Variable | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_BASE_PATH` | empty |
| `DATABASE_URL` | Supabase direct অথবা session-pooler PostgreSQL URI; এটিই priority পায় |
| `DB_SSLMODE` | `require` |
| `SUPABASE_URL` | `https://<project-ref>.supabase.co` |
| `SUPABASE_STORAGE_BUCKET` | `travel-guide-uploads` |
| `SUPABASE_SERVICE_ROLE_KEY` | Supabase secret service-role key; শুধু Render secret |

`DATABASE_URL` না দিলে `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_SSLMODE` সব দিতে হবে। `DATABASE_URL` এবং split variables একসঙ্গে থাকলে `DATABASE_URL` ব্যবহৃত হয়।

Admin seed command-এর জন্য `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` লাগে। Password অন্তত 12 characters হতে হবে। এগুলো code বা Git-এ লিখবে না। Optional limits: `UPLOAD_MAX_BYTES=2097152`, `MAX_POST_IMAGES=5`, `REMEMBER_DAYS=30`।

## Local run with Docker (recommended)

Prerequisites: Git এবং Docker Desktop। PowerShell commands:

```powershell
git clone https://github.com/asadbinjafor/TRAVEL_GUIDE.git
Set-Location TRAVEL_GUIDE
Copy-Item .env.example .env
notepad .env
docker compose up --build -d
docker compose ps
docker compose exec app php scripts/seed_admin.php
Invoke-RestMethod http://localhost:8080/health.php
Start-Process http://localhost:8080/index.php
```

`.env`-এ local `DB_PASSWORD` বদলাবে এবং একই file-এ `ADMIN_EMAIL`/`ADMIN_PASSWORD` বসাবে। Compose `database.sql` শুধু একেবারে নতুন PostgreSQL volume initialize করার সময় চালায়। Existing volume-এ schema পুনরায় দরকার হলে:

```powershell
Get-Content .\database.sql -Raw | docker compose exec -T db psql -U travel_guide -d travel_guide
docker compose exec app php scripts/seed_admin.php
```

যদি `DB_USER`/`DB_NAME` বদলাও, command-এও সেই values ব্যবহার করো। Stop/restart/logs:

```powershell
docker compose stop
docker compose start
docker compose restart app
docker compose logs -f app
docker compose down
```

`docker compose down -v` database এবং local upload volumes মুছে দেয়; data reset ইচ্ছাকৃত না হলে `-v` ব্যবহার করবে না।

### Docker ছাড়া local run

PHP 8.3+, PostgreSQL 15+, এবং extensions `pdo_pgsql`, `curl`, `fileinfo`, `mbstring`, `json` লাগবে। PostgreSQL-এ `database.sql` চালাও, `.env`-এ `DB_HOST=127.0.0.1`, `DB_PORT=5432`, database/user/password এবং development-এর জন্য `DB_SSLMODE=disable` বসাও। তারপর:

```powershell
php scripts/seed_admin.php
php -S 127.0.0.1:8080
```

Built-in server-এ application links `/index.php?route=...` ব্যবহার করে, তাই router script লাগে না। Open `http://127.0.0.1:8080/index.php`; health `http://127.0.0.1:8080/health.php`।

## Supabase setup

1. Supabase Dashboard-এ নতুন project তৈরি করো এবং database password password manager-এ রাখো।
2. **SQL Editor → New query** খুলে repository-র `database.sql` সম্পূর্ণ paste করে Run করো। Script destructive নয়: drop/truncate করে না এবং tables/indexes `IF NOT EXISTS` দিয়ে তৈরি করে। Production startup নিজে migration চালায় না।
3. **Table Editor**-এ `users`, `posts`, `post_requests`, `wishlist`, `comments`, `cost_estimates` verify করো। Boolean `users.is_verified`, JSONB fields, identity primary keys, foreign keys, unique/index constraints আছে। Tables-এ RLS enabled এবং কোনো anon policy নেই—Render direct DB connection ব্যবহার করে।
4. **Connect** থেকে connection string নাও। Render-এর জন্য direct connection reachable হলে সেটি, নাহলে IPv4-compatible **Session pooler** URI নাও। Native prepared statements ব্যবহৃত হওয়ায় transaction-pooler URI এড়িয়ে direct/session mode ব্যবহার করো। Password-এ reserved character থাকলে URI percent-encode করো। শেষে `?sslmode=require` রাখো।
5. **Storage → New bucket**: নাম ঠিক `travel-guide-uploads`, bucket public করো। Stored file names random; public read URL views-এ ব্যবহৃত হয়। Upload/write শুধু Render-এর server-side service-role key দিয়ে হয়।
6. **Project Settings/API Keys** থেকে project URL এবং secret service-role key নাও। Service-role key কখনও Vercel, browser JavaScript, Git বা screenshot-এ দেবে না।
7. Render deploy হওয়ার পরে Render Shell-এ নিচের command একবার চালাও (idempotent; একই email থাকলে password/role/verification update করে):

```bash
php scripts/seed_admin.php
```

তার আগে Render secrets হিসেবে `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` set করো। চাইলে seed শেষে এই তিনটি secret remove করতে পারো; application runtime-এ এগুলো লাগে না।

## Render setup

1. Render → **New → Blueprint** (বা Web Service) → GitHub repository `asadbinjafor/TRAVEL_GUIDE` connect করো। Branch `main`।
2. Blueprint হলে root `render.yaml` detect হবে। Manual service হলে Runtime **Docker**, Root Directory empty/repository root, Dockerfile `./Dockerfile`। Build/start command override করবে না।
3. `render.yaml`-এর secret values (`sync: false`) dashboard-এ বসাও: `DATABASE_URL`, `SUPABASE_URL`, `SUPABASE_SERVICE_ROLE_KEY`; bucket নাম verify করো। `APP_DEBUG=false`, `DB_SSLMODE=require` রাখো।
4. Health Check Path `/health.php`। Endpoint session/auth/HTML redirect ছাড়াই DB-তে `SELECT 1` করে success-এ exact JSON `{"status":"ok"}` এবং HTTP 200 দেয়; failure-এ generic JSON 503 দেয়।
5. Deploy করো। Container Apache-কে Render-provided `$PORT`-এ `0.0.0.0` bind করায়। Expected log-এ Apache start থাকবে; `could not bind to address` বা `No open ports detected` থাকা উচিত নয়।
6. URL পাওয়া গেলে verify:

```powershell
$renderUrl = 'https://YOUR-SERVICE.onrender.com'
Invoke-RestMethod "$renderUrl/health.php"
(Invoke-WebRequest "$renderUrl/index.php").StatusCode
```

7. Render Shell-এ admin seed চালাও। Direct Render URL-এ login, logout, registration, admin, scout request, comments, wishlist, profile/post upload পরীক্ষা করো।
8. নতুন commit push হলে auto-deploy হয়। Manual redeploy: service → **Manual Deploy → Deploy latest commit**। Restart: service → **Manual Deploy/Settings → Restart service**। Logs tab-এ build/runtime logs দেখো।

Render Free web service inactivity-তে sleep করতে পারে এবং wake-up-এ কিছু সময় লাগে। Paid instance always-on latency দেয়। Startup page sleep hide/wake করে, Render-কে permanently awake রাখে না।

## Vercel setup

Render URL জানা হওয়ার পরে, local repository-তে exact HTTPS URL (trailing slash ছাড়া) দুই জায়গায় replace করো:

- `vercel-proxy/vercel.json`
- `vercel-proxy/index.html`-এর direct link

PowerShell example:

```powershell
$renderUrl = 'https://YOUR-SERVICE.onrender.com'
(Get-Content .\vercel-proxy\vercel.json -Raw).Replace('https://your-render-service.onrender.com', $renderUrl) | Set-Content .\vercel-proxy\vercel.json -Encoding utf8
(Get-Content .\vercel-proxy\index.html -Raw).Replace('https://your-render-service.onrender.com', $renderUrl) | Set-Content .\vercel-proxy\index.html -Encoding utf8
git add vercel-proxy/vercel.json vercel-proxy/index.html
git commit -m "Configure production Render proxy URL"
git push origin main
```

তারপর Vercel:

1. **Add New → Project → Import Git Repository**।
2. Framework Preset **Other**।
3. Root Directory **`vercel-proxy`**।
4. Build Command, Output Directory, Install Command override করো না।
5. Deploy এবং generated production domain open করো। Logs/Deployment details-এ routing errors দেখো। Git push-এর পরে Redeploy বা automatic production deploy ব্যবহার করো।

`vercel.json` exact `/`-কে local `index.html` দেয়; `/:path*` external Render origin-এ proxy করে। Vercel source query parameters defaultভাবে destination-এ forward করে। PHP-generated redirects relative হওয়ায় browser Vercel domain-এই থাকে; form POST, cookies, login/logout এবং uploads একই proxy path দিয়ে যায়। Dynamic responses cache না করতে `no-store` ও rewrite caching disable করা আছে।

## Startup/502 protection কীভাবে কাজ করে

1. Root `/` Render-এ rewrite না হয়ে static HTML সঙ্গে সঙ্গে আসে; sleeping Render-এর root 502 user দেখে না।
2. Page same-origin `/health.php?_startup=<unique timestamp>` fetch করে; Vercel এটি Render-এ proxy করে এবং service wake করে।
3. Health JSON 200 হওয়ার পরে `/index.php?_startup=<timestamp>` fetch করে real homepage HTML/status probe করে।
4. Health এবং homepage—দুটির সম্পূর্ণ cycle পরপর **দুইবার** সফল না হলে redirect হয় না। Timeout, 502, invalid JSON, non-HTML, বা অন্য failure-এ consecutive counter zero হয়।
5. প্রতি 5 seconds retry, প্রতি request 10-second timeout, maximum 4 minutes। Success-এ Vercel-proxied `/index.php` খুলে। Retry button counter/time restart করে; direct Render link fallback। `<noscript>` content থাকায় JavaScript fail হলেও blank page হয় না।

## Correct deployment order

1. Supabase project ও Storage bucket তৈরি।
2. Supabase SQL Editor-এ `database.sql` চালিয়ে tables verify।
3. Render service/Blueprint তৈরি।
4. Render environment variables/secrets বসানো।
5. Render deploy।
6. `/health.php` এবং `/index.php` direct Render URL-এ verify; admin seed চালানো।
7. Render URL `vercel-proxy/vercel.json` ও `index.html`-এ বসানো।
8. Replacement commit GitHub `main`-এ push।
9. Vercel-এ `vercel-proxy` root দিয়ে deploy।
10. Vercel domain-এ cold-start, assets, auth/roles, CRUD এবং upload full live test।

## Application verification checklist

- Guest: homepage, Explore, login, registration; registration-এ role selector নেই এবং server সবসময় unverified `user` দেয়।
- Auth: login-এর পরে session ID regenerate; logout POST+CSRF এবং session destroy; duplicate email friendly error।
- Admin: only verified `admin`; user verify/unverify, add scout/admin/user, approve/reject requests, edit/delete posts, delete comments/users।
- Scout: only verified `scout`; create/edit/delete request, upload maximum 5 validated JPEG/PNG/WebP images, approved post/change request।
- User: only verified `user`; wishlist, comments (max 1000), browse/search/filter/cost calculator।
- Assets: `/public/css/*`, `/public/js/*`, Supabase Storage image URLs।
- Security: JSON mutations require CSRF header; SQL uses prepared statements; output is escaped; `.env`, configs, source directories and SQL are blocked by Apache.

## Troubleshooting

### 502 Bad Gateway / Render cold start

Direct Render 502 থাকলে Render logs দেখো। Free service wake হতে সময় নেয়; Vercel root page 5-second interval-এ wake/probe করবে। চার মিনিট পরেও না হলে direct link ও Render events/logs দেখো। Startup page root-level 502 hide করে, always-on guarantee দেয় না; paid Render instance প্রয়োজন।

### Health 200 কিন্তু homepage unavailable

Startup logic শুধু health 200-এ redirect করে না—real `/index.php` probe-ও দুই consecutive cycle-এ দরকার। Homepage 5xx হলে Render runtime logs-এ PHP exception, DB query/schema বা permissions দেখো। `Invoke-WebRequest "$renderUrl/index.php"` দিয়ে direct response পরীক্ষা করো।

### Render no-deploy / wrong commit

Deploys tab-এ branch `main`, latest commit hash এবং Auto Deploy verify করো। Manual **Deploy latest commit** চালাও। Vercel-এর Render URL replacement commit push হয়েছে কি না `git log -1` দিয়ে দেখো।

### `$PORT` / no open port

Docker start command override remove করো। `docker/entrypoint.sh` `$PORT` validate করে Apache templates-এ বসায় এবং `Listen 0.0.0.0:$PORT` করে। Dashboard-এ custom Docker Command দিও না।

### PostgreSQL connection / SSL error

`DATABASE_URL` typo, percent-encoded password, correct pooler host/user/port এবং `sslmode=require` যাচাই করো। Direct IPv6 unreachable হলে Supabase **Session pooler** নাও। Transaction pooler prepared-statement সমস্যা করতে পারে। Health 503 secret দেখায় না; Render logs-এ connection class/error দেখো।

### Missing `pdo_pgsql`, `curl`, `mbstring`

Render runtime অবশ্যই repository Dockerfile ব্যবহার করবে। Build logs-এ `docker-php-ext-install ... pdo_pgsql` দেখো। Native runtime বা wrong root directory হলে extensions missing হবে।

### SQL compatibility / table not found

MySQL schema import করবে না। Current `database.sql` PostgreSQL: identity columns, booleans, JSONB, checks এবং `ON CONFLICT` compatible queries। Supabase SQL Editor-এ সম্পূর্ণ file rerun করো এবং correct database/project-এর `DATABASE_URL` বসাও। Production startup schema auto-run করে না।

### Linux filename/path error

Repository-তে case-collision নেই। Linux case-sensitive; নতুন files-এর include/asset casing exact রাখো। App paths `__DIR__`/`ROOT_DIR`-based এবং uploads `/`/`DIRECTORY_SEPARATOR` safely ব্যবহার করে।

### Session/login problem বা redirect loop

Vercel root ছাড়া app URL `/index.php` হওয়া উচিত। `APP_BASE_PATH` production-এ empty রাখো। Browser cookies clear করে আবার login করো। Vercel ও Render URL mix করলে host-only session cookies share হবে না—normal usage Vercel domain-এ করো। `X-Forwarded-Proto` অনুযায়ী Secure cookie set হয়। Unverified account protected route থেকে homepage pending state-এ redirect হওয়া expected।

### Missing CSS/JS/images

Vercel project Root Directory `vercel-proxy` এবং catch-all destination exact Render host কি না দেখো। Browser Network tab-এ `/public/css/...` এবং `/public/js/...` status দেখো। Image URL Supabase Storage হলে bucket public এবং stored object exists verify করো।

### Upload persistence/failure

Production-এ তিন storage variables অবশ্যই set এবং bucket public হতে হবে। Service-role key secret হিসেবে Render-এ থাকবে। MIME `image/jpeg`, `image/png`, `image/webp`; default max 2 MiB/file, max 5 post images। Local Docker volume persistent; Render local fallback ephemeral, তাই production-এ storage variables বাদ দিও না।

### Vercel rewrite error / login POST failure

Placeholder দুই file-এই replaced, URL HTTPS এবং trailing slash ছাড়া কি না যাচাই করো। `/` static page হওয়া উচিত, `/index.php` proxy হওয়া উচিত। Vercel Deployment logs এবং browser Network tab-এ destination/status/Set-Cookie দেখো। Query parameters automatic preserve হয়; form actions `/index.php?route=...` হওয়া expected।

## Operational notes

- `.env` Git ignored; only `.env.example` committed।
- No plaintext/default password or source-controlled credential আছে।
- `scripts/seed_admin.php` explicit manual operation; application/container startup schema বা seed চালায় না।
- Supabase Storage service-role key server-only। Rotate immediately if exposed।
- Official references: [Render web services/port binding](https://render.com/docs/web-services), [Render health checks](https://render.com/docs/health-checks), [Supabase Postgres connections](https://supabase.com/docs/guides/database/connecting-to-postgres), [Vercel external rewrites](https://vercel.com/docs/routing/rewrites)।
