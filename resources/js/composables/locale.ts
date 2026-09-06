import { usePage } from '@inertiajs/vue3'
import { CalendarDate, CalendarDateTime, parseDate, parseDateTime } from '@internationalized/date'
import { computed } from 'vue'

export function useLocale() {
  const locale = computed(() => usePage().props.locale)
  const locales = computed(() => usePage().props.locales)
  const languages = computed(() => usePage().props.languages)

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

  const toDate = (value: string | null): CalendarDate | null => {
    if (!value) return null

    try {
      return parseDate(value.slice(0, 10))
    } catch {
      return null
    }
  }

  const fromDate = (value: CalendarDate | null): string | null => {
    if (!value) return null

    return value.toString()
  }

  const nowDate = (): CalendarDate => {
    const d = new Date()

    return new CalendarDate(d.getFullYear(), d.getMonth() + 1, d.getDate())
  }

  return {
    locale,
    locales,
    languages,
    toDateTime,
    fromDateTime,
    nowDateTime,
    toDate,
    fromDate,
    nowDate,
  }
}
