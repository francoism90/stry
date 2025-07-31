export const isEmpty = (obj: object) => {
  for (const x in obj) {
    return false
  }

  return true
}
