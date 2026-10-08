import { formatTimeLeft, watchedFraction } from '@/utils/duration'
import { describe, expect, it } from 'vite-plus/test'

describe('watchedFraction', () => {
  it('returns the watched share of the duration', () => {
    expect(watchedFraction(30, 120)).toBe(0.25)
  })

  it('returns 0 for unstarted videos and unknown durations', () => {
    expect(watchedFraction(0, 120)).toBe(0)
    expect(watchedFraction(null, 120)).toBe(0)
    expect(watchedFraction(30, null)).toBe(0)
    expect(watchedFraction(30, 0)).toBe(0)
  })

  it('never exceeds the whole video', () => {
    expect(watchedFraction(150, 120)).toBe(1)
  })
})

describe('formatTimeLeft', () => {
  it('rounds the remaining time up to whole minutes', () => {
    expect(formatTimeLeft(60, 420)).toBe('6 min left')
    expect(formatTimeLeft(60, 421)).toBe('7 min left')
  })

  it('shows at least one minute', () => {
    expect(formatTimeLeft(595, 596)).toBe('1 min left')
  })
})
