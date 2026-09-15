import type { EchoConfig } from '@/types'
import { createInertiaApp } from '@inertiajs/vue3'
import ui from '@nuxt/ui/vue-plugin'

import { bootEcho } from '@/plugins/echo'
import '@/plugins/iconify'
import '@/plugins/pusher'

import AppLayout from '@/layouts/AppLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  layout: (name) => {
    if (name.startsWith('Auth/')) {
      return AuthLayout
    }

    return AppLayout
  },
  progress: {
    color: '#d8b4fe',
  },
  // Lets Inertia's own dynamically-injected elements (e.g. the dev-mode error
  // dialog's <style>) carry the page's CSP nonce instead of being blocked —
  // see https://inertiajs.com/docs/v3/installation/client-side-setup#content-security-policy.
  // Read from a meta tag (set from the same `Vite::cspNonce()` shared as the
  // `nonce` Inertia prop) since this option is evaluated before any page
  // props exist.
  nonce: document.querySelector('meta[name="csp-nonce"]')?.getAttribute('content') ?? undefined,
  withApp(app, { page }) {
    app.use(ui)
    bootEcho(page.props.echo as EchoConfig | undefined)
  },
})
