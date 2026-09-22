import type { EchoConfig } from '@/types'
import { createInertiaApp } from '@inertiajs/vue3'
import ui from '@nuxt/ui/vue-plugin'

import { bootEcho } from '@/plugins/echo'
import '@/plugins/iconify'
import '@/plugins/pusher'
import '@/plugins/vueuse'

import AppLayout from '@/layouts/AppLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// CSP nonce for Inertia's injected elements; undefined during SSR (no `document`).
const cspNonce =
  typeof document === 'undefined'
    ? undefined
    : (document.querySelector('meta[name="csp-nonce"]')?.getAttribute('content') ?? undefined)

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
  nonce: cspNonce,
  withApp(app, { page }) {
    app.use(ui)
    bootEcho(page.props.echo as EchoConfig | undefined)
  },
})
