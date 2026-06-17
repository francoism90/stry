import '@/plugins/iconify'
import { createInertiaApp } from '@inertiajs/vue3'
import ui from '@nuxt/ui/vue-plugin'

import DefaultLayout from '@/layouts/DefaultLayout.vue'
import '../css/app.css'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  layout: () => DefaultLayout,
  progress: false,
  withApp(app) {
    app.use(ui)
  },
})
