// This app has no light palette, so `@nuxt/ui`'s useDark() (from `@vueuse/core`,
// triggered by `app.use(ui)` in app.ts) must never flip the `dark` class off
// based on the OS preference. Seeding its storage key before that plugin
// initializes keeps the decision fixed and flash-free — the
// `<html class="dark">` set server-side in app.blade.php already covers the
// pre-hydration paint.
if (typeof window !== 'undefined') {
  try {
    localStorage.setItem('vueuse-color-scheme', 'dark')
  } catch {
    // Ignore storage errors (e.g. private browsing).
  }
}
