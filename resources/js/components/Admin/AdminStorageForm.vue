<script setup lang="ts">
import { show, update } from '@/actions/Modules/Web/Settings/Controllers/StorageSettingsController'
import { useStorageDisks } from '@/composables/storage'
import type { OptionItem, StorageDisk, StorageSettings } from '@/types'
import { useForm, useHttp } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'

const loaded = ref(false)
const http = useHttp<object, StorageSettings>({})

const visibilities: OptionItem[] = [
  { label: 'Private', value: 'private' },
  { label: 'Public', value: 'public' },
]

const form = useForm(update(), {
  disks: [] as StorageDisk[],
})

const {
  drivers,
  allowedRoots,
  s3Defaults,
  configure,
  addDisk,
  removeDisk,
  contentItems,
  defaultItems,
  warnings,
  inherited,
  pathStyles,
} = useStorageDisks(() => form.disks)

const toFormDisk = (disk: StorageDisk): StorageDisk => ({ ...disk, key: null, secret: null })

onMounted(() =>
  http.get(show.url(), {
    onSuccess: (data) => {
      configure(data)

      form.defaults({ disks: data.disks.map(toFormDisk) })
      form.reset()
      loaded.value = true
    },
  }),
)

const errors = computed(() => form.errors as Record<string, string | undefined>)

const error = (index: number, field: string): string | undefined => errors.value[`disks.${index}.${field}`]

const onSubmit = () => {
  if (!loaded.value) return

  form.submit({
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      form.disks.forEach((disk) => {
        if (disk.driver === 's3' && disk.secret) {
          disk.has_secret = true
        }

        disk.key = null
        disk.secret = null
      })
    },
  })
}

defineExpose({
  submit: onSubmit,
  get processing() {
    return form.processing
  },
  get recentlySuccessful() {
    return form.recentlySuccessful
  },
})

const fieldClass = 'flex max-sm:flex-col justify-between items-start gap-4'
</script>

<template>
  <div
    v-if="!loaded"
    class="flex flex-col gap-3"
  >
    <USkeleton
      v-for="i in 6"
      :key="i"
      class="h-10 w-full rounded-md"
    />
  </div>

  <UForm
    v-else
    :state="form"
    class="flex flex-col gap-4"
    loading-auto
    @submit="onSubmit"
  >
    <UPageCard
      title="Storage"
      description="The disks files are stored on, and what each of them holds. New files go to the default disk of their kind; existing files stay where they are until they're moved. S3 disks use the AWS_* settings for anything left empty."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-4',
      }"
    >
      <template #body>
        <UAlert
          v-if="errors.disks"
          color="error"
          variant="subtle"
          icon="i-lucide-circle-alert"
          :description="errors.disks"
        />

        <UCard
          v-for="(disk, index) in form.disks"
          :key="index"
          variant="subtle"
          :ui="{ body: 'flex flex-col gap-3' }"
        >
          <div class="flex items-center justify-between gap-2">
            <div class="flex min-w-0 items-center gap-2">
              <UIcon
                :name="disk.driver === 's3' ? 'i-lucide-cloud' : 'i-lucide-hard-drive'"
                class="size-5 shrink-0"
              />
              <span class="truncate font-medium">{{ disk.label || disk.name || 'New disk' }}</span>
            </div>

            <UButton
              icon="i-lucide-trash-2"
              color="error"
              variant="ghost"
              size="sm"
              aria-label="Remove disk"
              @click="removeDisk(index)"
            />
          </div>

          <UAlert
            v-if="errors[`disks.${index}`]"
            color="error"
            variant="subtle"
            :description="errors[`disks.${index}`]"
          />

          <UAlert
            v-for="warning in warnings(disk)"
            :key="warning"
            color="warning"
            variant="subtle"
            icon="i-lucide-triangle-alert"
            :description="warning"
          />

          <UFormField
            label="Label"
            description="Shown when choosing a disk, e.g. Library (backed up)."
            :error="error(index, 'label')"
            :class="fieldClass"
          >
            <UInput
              v-model="disk.label"
              class="w-56"
            />
          </UFormField>

          <UFormField
            label="Name"
            description="Lowercase letters, numbers, dashes and underscores. Media refer to their disk by name."
            :error="error(index, 'name')"
            :class="fieldClass"
          >
            <UInput
              v-model="disk.name"
              class="w-56"
            />
          </UFormField>

          <UFormField
            label="Driver"
            :error="error(index, 'driver')"
            :class="fieldClass"
          >
            <USelect
              v-model="disk.driver"
              :items="drivers"
              class="w-56"
            />
          </UFormField>

          <UFormField
            :label="disk.driver === 'local' ? 'Path' : 'Prefix'"
            :description="
              disk.driver === 'local'
                ? `A directory inside ${allowedRoots.join(', ')}.`
                : 'An optional folder inside the bucket.'
            "
            :error="error(index, 'path')"
            :class="fieldClass"
          >
            <UInput
              v-model="disk.path"
              class="w-56"
            />
          </UFormField>

          <template v-if="disk.driver === 's3'">
            <UFormField
              label="Bucket"
              :error="error(index, 'bucket')"
              :class="fieldClass"
            >
              <UInput
                v-model.nullable="disk.bucket"
                class="w-56"
                :placeholder="inherited(s3Defaults?.bucket)"
              />
            </UFormField>

            <UFormField
              label="Region"
              :error="error(index, 'region')"
              :class="fieldClass"
            >
              <UInput
                v-model.nullable="disk.region"
                class="w-56"
                :placeholder="inherited(s3Defaults?.region)"
              />
            </UFormField>

            <UFormField
              label="Endpoint"
              description="For S3-compatible storage, such as RustFS or MinIO."
              :error="error(index, 'endpoint')"
              :class="fieldClass"
            >
              <UInput
                v-model.nullable="disk.endpoint"
                class="w-56"
                :placeholder="inherited(s3Defaults?.endpoint)"
              />
            </UFormField>

            <UFormField
              label="Public URL"
              description="The URL browsers use for this storage, when it differs from the endpoint."
              :error="error(index, 'url')"
              :class="fieldClass"
            >
              <UInput
                v-model.nullable="disk.url"
                class="w-56"
                :placeholder="inherited(s3Defaults?.url)"
              />
            </UFormField>

            <UFormField
              label="Temporary URL"
              description="The host temporary URLs are signed for."
              :error="error(index, 'temporary_url')"
              :class="fieldClass"
            >
              <UInput
                v-model.nullable="disk.temporary_url"
                class="w-56"
                :placeholder="inherited(s3Defaults?.temporary_url)"
              />
            </UFormField>

            <UFormField
              label="Path-style endpoint"
              :error="error(index, 'use_path_style_endpoint')"
              :class="fieldClass"
            >
              <USelect
                v-model="disk.use_path_style_endpoint"
                :items="pathStyles"
                class="w-56"
              />
            </UFormField>

            <UFormField
              label="Access key"
              :description="
                disk.masked_key
                  ? `Leave empty to keep ${disk.masked_key}.`
                  : s3Defaults?.has_credentials
                    ? 'Leave empty to use the credentials of the default S3 disk.'
                    : undefined
              "
              :error="error(index, 'key')"
              :class="fieldClass"
            >
              <UInput
                v-model.nullable="disk.key"
                class="w-56"
                autocomplete="off"
              />
            </UFormField>

            <UFormField
              label="Secret"
              :description="disk.has_secret ? 'Leave empty to keep the stored secret.' : undefined"
              :error="error(index, 'secret')"
              :class="fieldClass"
            >
              <UInput
                v-model.nullable="disk.secret"
                type="password"
                class="w-56"
                autocomplete="new-password"
              />
            </UFormField>

            <UFormField
              label="Cache control"
              :error="error(index, 'cache_control')"
              :class="fieldClass"
            >
              <UInput
                v-model.nullable="disk.cache_control"
                class="w-56"
                placeholder="public, max-age=604800"
              />
            </UFormField>
          </template>

          <UFormField
            v-else
            label="Serve files"
            description="Let browsers load files through temporary URLs. Required for conversions, thumbnails and reels."
            :error="error(index, 'serve')"
            :class="fieldClass"
          >
            <USwitch v-model="disk.serve" />
          </UFormField>

          <UFormField
            label="Visibility"
            :error="error(index, 'visibility')"
            :class="fieldClass"
          >
            <USelect
              v-model="disk.visibility"
              :items="visibilities"
              class="w-56"
            />
          </UFormField>

          <UFormField
            label="Holds"
            description="What may be stored on this disk."
            :error="error(index, 'contents')"
            :class="fieldClass"
          >
            <USelectMenu
              v-model="disk.contents"
              :items="contentItems"
              value-key="value"
              multiple
              class="w-56"
              @update:model-value="disk.default_for = disk.default_for.filter((value) => disk.contents.includes(value))"
            />
          </UFormField>

          <UFormField
            label="Default for"
            description="New files of these kinds are written here."
            :error="error(index, 'default_for')"
            :class="fieldClass"
          >
            <USelectMenu
              v-model="disk.default_for"
              :items="defaultItems(disk)"
              value-key="value"
              multiple
              class="w-56"
            />
          </UFormField>
        </UCard>

        <UButton
          icon="i-lucide-plus"
          label="Add disk"
          color="neutral"
          variant="outline"
          class="self-start"
          @click="addDisk"
        />
      </template>
    </UPageCard>
  </UForm>
</template>
