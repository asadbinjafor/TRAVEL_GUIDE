const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const html = fs.readFileSync('vercel-proxy/index.html', 'utf8');
const scripts = [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)];
assert.equal(scripts.length, 1, 'startup page must contain one inline script');
const source = scripts[0][1];

function response(ok, status, kind, body) {
  return {
    ok,
    status,
    headers: { get: (name) => name === 'content-type' ? kind : null },
    json: async () => JSON.parse(body),
    text: async () => body,
  };
}

const okHealth = () => response(true, 200, 'application/json', '{"status":"ok"}');
const okHomepage = () => response(true, 200, 'text/html; charset=utf-8', '<!doctype html><html><body>' + 'ready'.repeat(30) + '</body></html>');
const badGateway = () => response(false, 502, 'text/html', 'bad gateway');

async function flush() {
  for (let i = 0; i < 4; i += 1) {
    await Promise.resolve();
    await new Promise((resolve) => setImmediate(resolve));
  }
}

async function scenario(queue) {
  const elements = {
    status: { textContent: '' },
    retry: { addEventListener: () => {} },
  };
  const timers = new Map();
  let nextTimer = 1;
  const redirects = [];
  const context = {
    AbortController,
    Date,
    console,
    document: { getElementById: (id) => elements[id] },
    fetch: async () => {
      const next = queue.shift();
      if (!next) throw new Error('Unexpected fetch');
      return next();
    },
    setTimeout: (callback, delay) => {
      const id = nextTimer++;
      timers.set(id, { callback, delay, active: true });
      return id;
    },
    clearTimeout: (id) => {
      if (timers.has(id)) timers.get(id).active = false;
    },
    window: {
      addEventListener: () => {},
      location: { replace: (url) => redirects.push(url) },
    },
  };
  vm.runInNewContext(source, context);
  await flush();

  async function runNextRetry() {
    const item = [...timers.values()].find((timer) => timer.active && timer.delay === 5000);
    assert.ok(item, 'a five-second retry must be scheduled');
    item.active = false;
    item.callback();
    await flush();
  }

  return { redirects, elements, runNextRetry };
}

(async () => {
  const stable = await scenario([okHealth, okHomepage, okHealth, okHomepage]);
  assert.equal(stable.redirects.length, 0, 'one successful cycle must not redirect');
  await stable.runNextRetry();
  assert.equal(stable.redirects.length, 1, 'two consecutive successful cycles must redirect');
  assert.match(stable.redirects[0], /^\/index\.php\?_ready=\d+$/);

  const reset = await scenario([
    okHealth, okHomepage,
    badGateway,
    okHealth, okHomepage,
    okHealth, okHomepage,
  ]);
  await reset.runNextRetry();
  assert.equal(reset.redirects.length, 0, '502 must reset the success counter');
  await reset.runNextRetry();
  assert.equal(reset.redirects.length, 0, 'first success after failure must not redirect');
  await reset.runNextRetry();
  assert.equal(reset.redirects.length, 1, 'two new consecutive successes must redirect');

  console.log('PASS: startup readiness requires two consecutive health+homepage cycles and resets on 502');
})().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
