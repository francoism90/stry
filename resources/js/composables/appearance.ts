export function useAppearance() {
  const title = (value: string) =>
    value
      .replace(/[._]/g, ' ')
      .replace(/(^\w|\s\w)/g, (m) => m.toUpperCase())
      .replace(/\s\s+/g, ' ')
      .trim()

  return {
    title,
  }
}
