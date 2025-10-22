import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { configureEcho } from '@laravel/echo-vue'
import { createSSRApp, h, type DefineComponent } from 'vue'
import { renderToString } from 'vue/server-renderer'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

configureEcho({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT,
  wssPort: import.meta.env.VITE_REVERB_PORT,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
})

createServer((page) =>
  createInertiaApp({
    page,
    render: renderToString,
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
      const pages = import.meta.glob('./pages/**/*.vue', { eager: true })
      return pages[`./pages/${name}.vue`] as DefineComponent
    },
    setup({ App, props, plugin }) {
      return createSSRApp({
        render: () => h(App, props),
      }).use(plugin)
    },
  }),
)
