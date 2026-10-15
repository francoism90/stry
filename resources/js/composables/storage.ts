import type { OptionItem, StorageContent, StorageDisk, StorageRoot, StorageSettings } from '@/types'
import { computed, ref, toValue, type MaybeRefOrGetter } from 'vue'

export const newStorageDisk = (path = ''): StorageDisk => ({
  name: '',
  label: '',
  driver: 'local',
  path,
  visibility: 'private',
  serve: false,
  contents: [],
  default_for: [],
  bucket: null,
  region: null,
  endpoint: null,
  url: null,
  temporary_url: null,
  use_path_style_endpoint: null,
  cache_control: null,
  has_secret: false,
  key: null,
  secret: null,
})

/**
 * Splits a local disk path into the root it's in and the subfolder below it, using the longest
 * matching root. A path outside every root keeps its full value as the subfolder.
 */
export const splitDiskPath = (path: string, roots: string[]): { root: string | undefined; subfolder: string } => {
  const root = [...roots]
    .sort((a, b) => b.length - a.length)
    .find((candidate) => path === candidate || path.startsWith(`${candidate}/`))

  if (!root) {
    return { root: undefined, subfolder: path }
  }

  return { root, subfolder: path.slice(root.length).replace(/^\/+/, '') }
}

export const joinDiskPath = (root: string | undefined, subfolder: string): string => {
  const trimmed = subfolder.replace(/^\/+|\/+$/g, '')

  if (!root) {
    return trimmed
  }

  return trimmed ? `${root}/${trimmed}` : root
}

/**
 * Editing a list of storage disks: adding and removing them, the contents they can hold and the
 * defaults S3 disks inherit.
 */
export function useStorageDisks(disks: MaybeRefOrGetter<StorageDisk[]>) {
  const drivers = ref<OptionItem[]>([])
  const contents = ref<StorageContent[]>([])
  const roots = ref<StorageRoot[]>([])
  const s3Defaults = ref<StorageSettings['s3_defaults'] | null>(null)

  const configure = (settings: StorageSettings): void => {
    drivers.value = settings.drivers
    contents.value = settings.contents
    roots.value = settings.roots
    s3Defaults.value = settings.s3_defaults
  }

  const addDisk = (): void => {
    toValue(disks).push(newStorageDisk(roots.value.find((root) => root.mounted)?.value ?? ''))
  }

  const removeDisk = (index: number): void => {
    toValue(disks).splice(index, 1)
  }

  const rootItems = computed<OptionItem[]>(() =>
    [...roots.value]
      .sort((a, b) => Number(b.mounted) - Number(a.mounted))
      .map((root) => ({
        label: root.mounted ? root.label : `${root.label} (not mounted)`,
        value: root.value,
        disabled: !root.mounted,
      })),
  )

  const rootOf = (disk: StorageDisk): string | undefined =>
    splitDiskPath(
      disk.path,
      roots.value.map((root) => root.value),
    ).root

  const subfolderOf = (disk: StorageDisk): string =>
    splitDiskPath(
      disk.path,
      roots.value.map((root) => root.value),
    ).subfolder

  const setRoot = (disk: StorageDisk, root: string | undefined): void => {
    disk.path = joinDiskPath(root, subfolderOf(disk))
  }

  const setSubfolder = (disk: StorageDisk, subfolder: string): void => {
    disk.path = joinDiskPath(rootOf(disk), subfolder)
  }

  const contentItems = computed<OptionItem[]>(() =>
    contents.value.map((content) => ({ label: content.label, value: content.value })),
  )

  const defaultItems = (disk: StorageDisk): OptionItem[] =>
    contentItems.value.filter((item) => disk.contents.includes(String(item.value)))

  const warnings = (disk: StorageDisk): string[] =>
    contents.value
      .filter((content) => disk.contents.includes(content.value))
      .flatMap((content) => {
        if (content.requires_temporary_urls && disk.driver === 'local' && !disk.serve) {
          return [`${content.label} are shown in the browser, so a local disk must serve its files.`]
        }

        if (content.prefers_local && disk.driver !== 'local') {
          return [`${content.label} are much faster on a local disk.`]
        }

        return []
      })

  const inherited = (value: string | null | undefined): string => (value ? `Default: ${value}` : 'Not set')

  const pathStyles = computed<OptionItem[]>(() => [
    { label: `Default (${s3Defaults.value?.use_path_style_endpoint ? 'on' : 'off'})`, value: null },
    { label: 'On', value: true },
    { label: 'Off', value: false },
  ])

  return {
    drivers,
    contents,
    roots,
    rootItems,
    rootOf,
    subfolderOf,
    setRoot,
    setSubfolder,
    s3Defaults,
    configure,
    addDisk,
    removeDisk,
    contentItems,
    defaultItems,
    warnings,
    inherited,
    pathStyles,
  }
}
