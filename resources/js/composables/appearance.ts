import { titleCase } from 'title-case'

export function useAppearance() {
  const title = (value: string | null) => titleCase(value?.replace(/\s+/g, ' ') || '')

  return {
    title,
  }
}
