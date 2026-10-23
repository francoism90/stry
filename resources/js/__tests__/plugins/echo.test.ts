import { bootEcho } from '@/plugins/echo'
import type { EchoConfig } from '@/types'
import { configureEcho } from '@laravel/echo-vue'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vite-plus/test'

vi.mock('@laravel/echo-vue', () => ({
  configureEcho: vi.fn<(...args: unknown[]) => void>(),
}))

const config: EchoConfig = { key: 'app-key', host: 'laravel.test', port: 443, scheme: 'https', path: '/ws' }

describe('bootEcho', () => {
  beforeEach(() => {
    vi.stubGlobal('window', {})
  })

  afterEach(() => {
    vi.unstubAllGlobals()
    vi.clearAllMocks()
  })

  it('configures the reverb broadcaster in the browser', () => {
    bootEcho(config)

    expect(configureEcho).toHaveBeenCalledWith(
      expect.objectContaining({
        broadcaster: 'reverb',
        key: 'app-key',
        wsHost: 'laravel.test',
        wsPath: '/ws',
        forceTLS: true,
      }),
    )
  })

  it('falls back to the null broadcaster during SSR so useEcho does not throw', () => {
    vi.stubGlobal('window', undefined)

    bootEcho(config)

    expect(configureEcho).toHaveBeenCalledExactlyOnceWith({ broadcaster: 'null' })
  })

  it('falls back to the null broadcaster when the config is missing', () => {
    bootEcho(undefined)

    expect(configureEcho).toHaveBeenCalledExactlyOnceWith({ broadcaster: 'null' })
  })
})
