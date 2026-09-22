# Travel Guide Vercel proxy

This directory is the Vercel project root. `/` serves the static startup page; every other path is reverse-proxied to Render.

Before deploying, replace `https://your-render-service.onrender.com` in both `vercel.json` and `index.html` with the exact HTTPS URL assigned by Render (no trailing slash). Do not rewrite `/` directly to Render.

In Vercel, use **Other** as Framework Preset, set **Root Directory** to `vercel-proxy`, leave build/install commands empty, and deploy.
