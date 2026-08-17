---
paths:
  - 'resources/js/pages/**'
---

# Pages

## Use setLayoutProps() for page-specific persistent layout data, never static tuple props
AppLayout/ContentLayout are persistent Inertia layouts reused across page navigations (e.g. VideoIndex <-> TagIndex). Static tuple props (`[ContentLayout, { id: 'videos.index' }]`) are "set once when the layout is defined" and do NOT update on navigation while the layout instance persists — they silently freeze at whatever the first-mounted page set (caused the UDashboardPanel `id` to stay stale and go-to filters not refreshing across pages).

Instead declare the layout without static props (`layout: [AppLayout, ContentLayout]`) and call `setLayoutProps({ id: ..., title: ... })` in each page's `<script setup>`. Dynamic layout props auto-reset on every navigation, so each page gets fresh values even though the layout DOM/instance is reused.
