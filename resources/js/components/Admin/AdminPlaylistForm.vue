<script setup lang="ts">
import { show, update } from '@/actions/App/Web/Settings/Controllers/PlaylistSettingsController'
import type { PlaylistSettings } from '@/types'
import { useForm, useHttp } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'

const loaded = ref(false)
const http = useHttp<object, PlaylistSettings>({})

const form = useForm<PlaylistSettings>(update(), {
  type: 'packager',
  disk_name: '',
  language: 'en',
  text_language: 'en',
  expires_after: 0,
  manifest_cache_lifetime: 0,
  manifest_url_lifetime: 0,
  manifest_refresh_before: 0,
  media_url_lifetime: 0,
  key_url_lifetime: 0,
  encryption: null,
  protection_scheme: null,
  key_rotation: false,
  key_rotation_duration: 0,
})

onMounted(() =>
  http.get(show.url(), {
    onSuccess: (data) => {
      form.defaults(data)
      form.reset()
      loaded.value = true
    },
  }),
)

const onSubmit = () => {
  if (!loaded.value) return

  form.submit({
    preserveScroll: true,
    preserveState: true,
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

const protectionSchemeDisabled = computed(() => form.encryption !== 'clearkey')
const keyRotationDisabled = computed(() => !form.encryption)
const keyRotationDurationDisabled = computed(() => !form.encryption || !form.key_rotation)
</script>

<template>
  <div
    v-if="!loaded"
    class="flex flex-col gap-3"
  >
    <USkeleton
      v-for="i in 10"
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
      title="Playlist"
      description="How videos are packaged and served for playback."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
        <UFormField
          label="Type"
          description="Packager repurposes existing files without re-encoding (fastest). Streamer generates playlists on-the-fly with more options."
          name="type"
          :error="form.errors.type"
          :class="fieldClass"
        >
          <USelect
            v-model="form.type"
            class="w-56"
            :items="[
              { label: 'Packager', value: 'packager' },
              { label: 'Streamer', value: 'streamer' },
            ]"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Disk"
          description="The filesystem disk playlists are stored on."
          name="disk_name"
          :error="form.errors.disk_name"
          :class="fieldClass"
        >
          <UInput
            v-model="form.disk_name"
            class="w-56"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Language"
          description="Default audio language for playlists."
          name="language"
          :error="form.errors.language"
          :class="fieldClass"
        >
          <USelect
            v-model="form.language"
            class="w-56"
            :items="[{ label: 'English', value: 'en' }]"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Text language"
          description="Default subtitle language for playlists."
          name="text_language"
          :error="form.errors.text_language"
          :class="fieldClass"
        >
          <USelect
            v-model="form.text_language"
            class="w-56"
            :items="[{ label: 'English', value: 'en' }]"
          />
        </UFormField>
      </template>
    </UPageCard>

    <USeparator />

    <UPageCard
      title="Lifetimes"
      description="How long playlists and their signed URLs stay valid, in seconds."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
        <UFormField
          label="Expires after"
          description="Playlists expire after this many seconds. 0 means they never expire."
          name="expires_after"
          :error="form.errors.expires_after"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.expires_after"
            class="w-56"
            :min="0"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Manifest cache lifetime"
          description="How long a generated manifest is cached before it's regenerated."
          name="manifest_cache_lifetime"
          :error="form.errors.manifest_cache_lifetime"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.manifest_cache_lifetime"
            class="w-56"
            :min="0"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Manifest URL lifetime"
          description="How long a signed manifest URL stays valid."
          name="manifest_url_lifetime"
          :error="form.errors.manifest_url_lifetime"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.manifest_url_lifetime"
            class="w-56"
            :min="1"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Manifest refresh before"
          description="How many seconds before the manifest URL expires the player refetches it."
          name="manifest_refresh_before"
          :error="form.errors.manifest_refresh_before"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.manifest_refresh_before"
            class="w-56"
            :min="0"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Media URL lifetime"
          description="How long signed segment and init segment URLs stay valid."
          name="media_url_lifetime"
          :error="form.errors.media_url_lifetime"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.media_url_lifetime"
            class="w-56"
            :min="1"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Key URL lifetime"
          description="How long signed encryption key URLs stay valid."
          name="key_url_lifetime"
          :error="form.errors.key_url_lifetime"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.key_url_lifetime"
            class="w-56"
            :min="1"
          />
        </UFormField>
      </template>
    </UPageCard>

    <USeparator />

    <UPageCard
      title="Encryption"
      description="Protect streaming segments with AES encryption."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
        <UFormField
          label="Encryption"
          description="Standard AES-128 envelope encryption, or W3C Clear Key for native browser decryption."
          name="encryption"
          :error="form.errors.encryption"
          :class="fieldClass"
        >
          <USelect
            v-model="form.encryption"
            class="w-56"
            :items="[
              { label: 'None', value: null },
              { label: 'Raw key encryption', value: 'raw_key_encryption' },
              { label: 'Clear key', value: 'clearkey' },
            ]"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Protection scheme"
          description="Only used with Clear Key: CENC for cross-browser DASH, CBCS for Apple FairPlay / unified HLS."
          name="protection_scheme"
          :error="form.errors.protection_scheme"
          :class="fieldClass"
        >
          <USelect
            v-model="form.protection_scheme"
            class="w-56"
            :disabled="protectionSchemeDisabled"
            :items="[
              { label: 'None', value: null },
              { label: 'CENC', value: 'cenc' },
              { label: 'CBCS', value: 'cbcs' },
            ]"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Key rotation"
          description="Periodically rotate the encryption key during playback."
          name="key_rotation"
          :error="form.errors.key_rotation"
          :class="fieldClass"
        >
          <USwitch
            v-model="form.key_rotation"
            :disabled="keyRotationDisabled"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Key rotation duration"
          description="Seconds before rotating to a new encryption key."
          name="key_rotation_duration"
          :error="form.errors.key_rotation_duration"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.key_rotation_duration"
            class="w-56"
            :min="1"
            :disabled="keyRotationDurationDisabled"
          />
        </UFormField>
      </template>
    </UPageCard>
  </UForm>
</template>
