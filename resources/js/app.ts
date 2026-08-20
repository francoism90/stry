import { createInertiaApp } from '@inertiajs/vue3'
import ui from '@nuxt/ui/vue-plugin'

import '../css/app.css'

import AccountLayout from '@/layouts/AccountLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import AuthLayout from './layouts/AuthLayout.vue'

import '@/plugins/iconify'
import '@/plugins/pusher'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  layout: (name) => {
    if (name.startsWith('Auth/')) {
      return AuthLayout
    }

    if (name.startsWith('Account/')) {
      return AccountLayout
    }

    return AppLayout
  },
  progress: false,
  withApp(app) {
    app.use(ui)
  },
})
