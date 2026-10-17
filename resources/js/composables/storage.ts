import type { OptionItem, StorageContent, StorageDisk, StorageSettings } from '@/types'
import { newStorageDisk } from '@/utils/storage'
import { computed, ref, toValue, type MaybeRefOrGetter } from 'vue'

/**
 * Editing a list of storage disks: adding and removing them, the contents they can hold and the
 * defaults S3 disks inherit.
 */
export function useStorageDisks(disks: MaybeRefOrGetter<StorageDisk[]>) {
  const drivers = ref<OptionItem[]>([])
  const contents = ref<StorageContent[]>([])
  const s3Defaults = ref<StorageSettings['s3_defaults'] | null>(null)

  const configure = (settings: StorageSettings): void => {
    drivers.value = settings.drivers
    contents.value = settings.contents
    s3Defaults.value = settings.s3_defaults
  }

  const addDisk = (): void => {
    toValue(disks).push(newStorageDisk())
  }

  const removeDisk = (index: number): void => {
    toValue(disks).splice(index, 1)
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
