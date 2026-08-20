---
paths:
  - 'resources/js/layouts/Account/**'
---

# Account

## Two nav levels, one component, driven by props (not a stacked layout)

**Top-level section tabs** (Account/Security/Settings/Notifications) are the static `items` array built into `AccountLayout.vue` — a `UNavigationMenu` with `highlight` passed as `UDashboardNavbar`'s default (center) slot, sharing the row with the title. Always present, identical on every Account page, so it doesn't need per-page input.

**Settings sub-section tabs** (General/Appearance) are an optional `tabs?: NavigationMenuItem[]` prop on `AccountLayout`, rendered as a second `UDashboardToolbar` row right under the navbar when the prop is set (`v-if="tabs"`). `AccountSettings.vue` and `AccountAppearance.vue` (genuinely separate pages/routes: `/settings`, `/settings/appearance`) each build their own two-item `tabs` array and hand it in via `setLayoutProps({ title: 'Settings', tabs: [...] })`. Pages that don't set `tabs` (Account, Security, Notifications) simply don't get the second toolbar row.

**Why not `layout: [AccountLayout, SettingsLayout]`:** a stacked layout only lets the inner layout fill the *outer* layout's default `<slot />` — there's no way for it to reach into a named region of the outer layout's own template (e.g. its `#header`). Since `AccountLayout` owns the whole `UDashboardPanel` including the header/toolbar area, a second layout could only render *inside* the body slot, one level too low to sit next to the navbar. `setLayoutProps()` is the right tool here precisely because it feeds data into slots the *persistent* layout itself controls — same mechanism `ContentLayout` uses for `scopes`/`sorters`, just without a second component in the stack. Only reach for a stacked layout when the inner content needs its own full panel (see `ContentLayout` under `AppLayout`, which owns no panel of its own).
