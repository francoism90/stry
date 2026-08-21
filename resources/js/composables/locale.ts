import { usePage } from '@inertiajs/vue3'
import { CalendarDateTime, parseDateTime } from '@internationalized/date'
import { computed } from 'vue'

export function useLocale() {
  const locale = computed(() => usePage().props.locale)

  const toDateTime = (value: string | null): CalendarDateTime | null => {
    if (!value) return null

    try {
      return parseDateTime(value.replace(' ', 'T')) as CalendarDateTime
    } catch {
      return null
    }
  }

  const fromDateTime = (value: CalendarDateTime | null): string | null => {
    if (!value) return null

    return value.toString().replace('T', ' ')
  }

  const nowDateTime = (): CalendarDateTime => {
    const d = new Date()

    return new CalendarDateTime(
      d.getFullYear(),
      d.getMonth() + 1,
      d.getDate(),
      d.getHours(),
      d.getMinutes(),
      d.getSeconds(),
    )
  }

  return {
    locale,
    toDateTime,
    fromDateTime,
    nowDateTime,
  }
}
