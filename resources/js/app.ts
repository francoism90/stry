import { createInertiaApp } from '@inertiajs/vue3'
import ui from '@nuxt/ui/vue-plugin'

import AppLayout from '@/layouts/AppLayout.vue'
import ResourceLayout from '@/layouts/Resources/ResourceLayout.vue'
import AuthLayout from './layouts/AuthLayout.vue'

import '@/plugins/iconify'
import '@/plugins/pusher'

import '../css/app.css'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  layout: (name) => {
    console.log('layout', name)

    switch (true) {
      case name.startsWith('Auth/'):
        return AuthLayout
      case name.startsWith('Resources/'):
        return [AppLayout, ResourceLayout]
      default:
        return AppLayout
    }
  },
  progress: false,
  withApp(app) {
    app.use(ui)
  },
})
