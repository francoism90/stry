import { bootEcho } from '@/plugins/echo'
import type { EchoConfig } from '@/types'
import { configureEcho } from '@laravel/echo-vue'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

vi.mock('@laravel/echo-vue', () => ({
  configureEcho: vi.fn(),
}))

const config: EchoConfig = { key: 'app-key', host: 'reverb.test', port: 443, scheme: 'https' }

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
      expect.objectContaining({ broadcaster: 'reverb', key: 'app-key', wsHost: 'reverb.test', forceTLS: true }),
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
