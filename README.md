# PolySports — Wholesale Back-office (Front-end screen)

PolySports is a small **B2B wholesale back-office** for a football-boot brand.
You're acting as an **account manager placing a bulk order for a business
customer**:

1. **Pick the customer** you're ordering for (their pricing tier sets the discount).
2. **Browse the catalogue (PLP)** and enter quantities across EU sizes — wholesale
   buyers order many sizes at once.
3. **Review the mini-cart** at the top of the screen.
4. **Checkout** submits the order and shows a confirmation.

The stack mirrors our day-to-day work: **Vue 2 (Vue 2.7) + Vuex + Laravel (PHP)
+ MySQL**, built with **Vite** and running in **Docker**. **This is a front-end
screen** — the **back end is solid and you can trust it**. Everything worth your
attention is in the Vue app, its store, and its tooling.

> **Vue 2, mid-migration.** Like the real product, this app is **Vue 2 on its way
> to Vue 3**: most components are **Options API**, a few already use the
> **Composition API** (`<script setup>`, via Vue 2.7), and shared state lives in
> **Vuex**. You'll want to be fluent reading and fixing both styles.

## Running it

You need Docker Desktop (or Docker Engine) with Compose. Then, from the repo root:

```bash
./up.sh
```

> **On Windows, clone and run this from the WSL2 filesystem** — e.g. `~/projects`
> inside your Ubuntu/WSL distro, with Docker Desktop's **WSL2 backend** enabled.
> Don't use a Windows path such as `C:\…` or `/mnt/c/…`: from there the app loads
> very slowly and your code changes may not be picked up at all (not even after a
> refresh).

The first run pulls images, builds the container, installs PHP + Node
dependencies and seeds the database — that takes a few minutes. When it's done:

- App: <http://localhost:8000>
- Vite dev server (HMR): <http://localhost:5173>

Handy commands:

```bash
docker compose logs -f app        # tail application + Vite logs
docker compose exec app sh        # shell into the app container
docker compose exec app npm run dev   # Vite dev server (already running on boot)
```

## Front-end tests

There's a **Vitest** suite:

```bash
docker compose exec app npm test          # one-shot run
docker compose exec app npm run test:watch
```

**Some tests fail on purpose** — they describe behaviour the app *should* have
but currently doesn't. Read each failing test to understand the intended
behaviour; getting them green is part of the exercise (but not the only thing
worth fixing — plenty of the bugs have no failing test pointing at them).

## Code map

- **Vue app entry** — `resources/js/app.js` → `resources/js/components/App.vue`
  *(Options API; owns the current view)*
- **Store (Vuex)** — `resources/js/store/` (`modules/cart.js`, `modules/session.js`)
- **Views/components** — `resources/js/components/`
  - `CustomerSelect.vue` *(Options API)* — first screen
  - `ProductList.vue` *(Composition API / `<script setup>`)* — the catalogue / PLP
  - `ProductDetail.vue` *(Options API)* — the PDP
  - `OrderGrid.vue` *(Options API)* — per-size quantity entry
  - `MiniCart.vue` *(Options API)* — sticky cart summary
  - `Checkout.vue` *(Options API)* — review + submit
  - `base/BaseQtyInput.vue` *(Composition API)* — a shared design-system input
- **API client** — `resources/js/api.js`
- **Money helpers** — `resources/js/money.js`
- **Runtime config** — `resources/js/config.js`
- **Front-end tests** — `resources/js/__tests__/`
- **Build/tooling** — `vite.config.js`, `vitest.config.js`, `package.json`, `.env`

The back end (`app/`, `routes/`, `database/`) is there so the app is real, but
it is **not** where the exercise lives.

## What we're looking for

This is a **debugging exercise**. We care about how you work, not a perfect score:

- **Reproduce before fixing** — confirm the problem first (browser devtools, the
  Vue Devtools, the network tab, the test output).
- **Explain the root cause** — out loud, as if to a teammate or a non-technical
  stakeholder.
- **Fix it, and say how you'd prevent it recurring.**
- **Mind performance and accessibility** — they matter here as much as correctness.
- **Prioritise** — you almost certainly won't get to everything, and that's fine.
- **Think out loud.** Using docs and web search is completely fine.

### Optional stretch

If you finish the obvious issues and have time, we'd love to see how you'd
**migrate an Options API component to the Composition API** (`<script setup>` or
a composable) — `ProductDetail.vue` is a good candidate. Talk us through what you'd
watch out for in a real Vue 2 → 3 migration.

Good luck, and enjoy poking around.
