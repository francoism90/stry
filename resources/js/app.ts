import { initializeEcho, type LaravelEchoConfig } from '@/plugins/echo'
import '@/plugins/iconify'
import { createInertiaApp } from '@inertiajs/vue3'
import ui from '@nuxt/ui/vue-plugin'
import type { DefineComponent } from 'vue' // Type-only import
import { createSSRApp, h } from 'vue'

import DefaultLayout from '@/layouts/DefaultLayout.vue'
import '../css/app.css'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  layout: () => DefaultLayout,
  progress: false,
  resolve: (name) => {
    const pages = import.meta.glob('./pages/**/*.vue')
    return pages[`./pages/${name}.vue`]() as Promise<DefineComponent>
  },
  setup({ el, App, props, plugin }) {
    const echoConfig = props.initialPage.props.echo as LaravelEchoConfig | undefined

    // Initialize websockets
    initializeEcho(echoConfig)

    // Instantiate your Vue instance using the capital 'App' signature
    const vueApp = createSSRApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ui)

    // Safe gate-check for element existence to satisfy strict null compiler rules
    if (el) {
      vueApp.mount(el)
    }

    return vueApp
  },
})
