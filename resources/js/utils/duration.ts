export function formatDuration(seconds: number): string {
  const totalSeconds = Math.max(0, Math.round(seconds))
  const hours = Math.floor(totalSeconds / 3600)
  const minutes = Math.floor((totalSeconds % 3600) / 60)
  const remainingSeconds = totalSeconds % 60

  if (hours > 0) {
    return `${hours}:${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`
  }

  return `${minutes}:${String(remainingSeconds).padStart(2, '0')}`
}

/**
 * Share of a video watched so far, between 0 and 1, or 0 when it was never started or counts as watched.
 */
export function watchedFraction(progress: number | null | undefined, duration: number | null | undefined): number {
  if (!progress || !duration || duration <= 0) {
    return 0
  }

  return Math.min(Math.max(progress / duration, 0), 1)
}

/**
 * Remaining time in whole minutes, rounded up so a few seconds left still reads "1 min left".
 */
export function formatTimeLeft(progress: number, duration: number): string {
  const minutes = Math.max(1, Math.ceil((duration - progress) / 60))

  return `${Intl.NumberFormat().format(minutes)} min left`
}
