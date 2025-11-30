export const capitalize = (value: string): string =>
  value
    .replace(/[._-]/g, ' ')
    .replace(/(^\w|\s\w)/g, (m) => m.toUpperCase())
    .replace(/\s\s+/g, ' ')
    .trim()
