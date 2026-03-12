import { CalendarDateTime, parseDateTime } from '@internationalized/date'

export function useDateTime() {
  function toDateTime(value: string | null): CalendarDateTime | null {
    if (!value) return null

    try {
      return parseDateTime(value.replace(' ', 'T')) as CalendarDateTime
    } catch {
      return null
    }
  }

  function fromDateTime(value: CalendarDateTime | null): string | null {
    if (!value) return null

    return value.toString().replace('T', ' ')
  }

  function nowDateTime(): CalendarDateTime {
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

  return { toDateTime, fromDateTime, nowDateTime }
}
