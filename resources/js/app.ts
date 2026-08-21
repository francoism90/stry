import { createInertiaApp } from '@inertiajs/vue3'
import ui from '@nuxt/ui/vue-plugin'

import AppLayout from '@/import AppLayout from '@/layouts/AppLayout.vue'
import AuthLayout from './layouts/AuthLayout.vue'
yimport '@/plugins/iconify'
import '@/plugins/pusher'
eta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        if (name.startsWith('Auth/')) {
            return AuthLayout
        }

        return AppLayout
    },
    progress: false,
    withApp(app) {
        app.use(ui)
    },
})
