<script setup lang="ts">
import VideoDispatchTranscodeController from '@/actions/App/Web/Videos/Controllers/VideoDispatchTranscodeController'
import { update } from '@/actions/App/Web/Videos/Controllers/VideoController'
import FormModal from '@/components/Ui/FormModal.vue'
import TranscodeDeleteModal from '@/components/Transcodes/TranscodeDeleteModal.vue'
import TranscodeImportModal from '@/components/Transcodes/TranscodeImportModal.vue'
import VideoDeleteModal from '@/components/Videos/VideoDeleteModal.vue'
import { useLocale } from '@/composables/locale'
import { useTags } from '@/composables/tags'
import { index as transcodesIndex } from '@/routes/videos/transcodes'
import type { TagMenuItem, Transcode, Video } from '@/types'
import { capitalize } from '@/utils/case'
import { router, useForm } from '@inertiajs/vue3'
import type { CalendarDateTime } from '@internationalized/date'
import type { TabsItem } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
  progress?: number | null
  transcodes?: Transcode[] | undefined
}>()

const tabs: TabsItem[] = [
  { label: 'General', icon: 'i-lucide-file-text', slot: 'general' },
  { label: 'Media', icon: 'i-lucide-image', slot: 'media' },
  { label: 'Transcodes', icon: 'i-lucide-cpu', slot: 'transcodes' },
]

const open = defineModel<boolean>('open')

const { toDateTime, fromDateTime, nowDateTime } = useLocale()
const { items, filter } = useTags(props.video.tags || [])

const form = useForm(update(props.video.id), {
  name: props.video.name,
  titles: props.video.titles || null,
  episode: props.video.episode || null,
  season: props.video.season || null,
  part: props.video.part || null,
  summary: props.video.summary || null,
  tags: props.video.tags || [],
  adult: props.video.adult,
  snapshot: props.video.snapshot || null,
  published_at: props.video.published_at || null,
  released_at: props.video.released_at || null,
})

const publishedAt = computed({
  get: () => toDateTime(form.published_at),
  set: (value: CalendarDateTime | null) => {
    form.published_at = fromDateTime(value)
  },
})

const releasedAt = computed({
  get: () => toDateTime(form.released_at),
  set: (value: CalendarDateTime | null) => {
    form.released_at = fromDateTime(value)
  },
})

const capitalizeName = (): void => {
  form.name = capitalize(form.name)
}

const setSnapshotFromProgress = (): void => {
  form.snapshot = props.progress || null
}

const setPublishedAtNow = (): void => {
  publishedAt.value = nowDateTime()
}

const setReleasedAtNow = (): void => {
  releasedAt.value = nowDateTime()
}

const onSubmit = (close: () => void) =>
  form.submit({
    preserveState: true,
    onSuccess: () => close(),
  })

const createTranscode = (): void =>
  void router.post(VideoDispatchTranscodeController.url(props.video.id), {}, { preserveScroll: true })
</script>

<template>
  <FormModal
    v-model:open="open"
    :title="`Edit ${video.title}`"
    :processing="form.processing"
    :tabs="tabs"
    :ui="{ content: 'sm:max-w-2xl' }"
    @submit="onSubmit"
  >
    <template #general>
      <div class="flex flex-col gap-4">
        <UForm
          :state="form"
          class="flex flex-col gap-3"
        >
          <UFormField
            label="Name"
            required
            :error="form.errors.name"
          >
            <UInput
              v-model="form.name"
              :model-modifiers="{ string: true, trim: true }"
              :ui="{ trailing: 'pe-1' }"
              autofocus
              autocapitalize="words"
            >
              <template #trailing>
                <UButton
                  color="neutral"
                  variant="link"
                  size="sm"
                  icon="i-lucide-wand-sparkles"
                  aria-label="Capitalize"
                  @click.prevent="capitalizeName"
                />
              </template>
            </UInput>
          </UFormField>

          <USeparator />

          <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <UFormField
              label="Episode"
              :error="form.errors.episode"
            >
              <UInput
                v-model="form.episode"
                :model-modifiers="{ nullable: true, string: true, trim: true }"
                placeholder="1"
                autocapitalize="characters"
              />
            </UFormField>

            <UFormField
              label="Part"
              :error="form.errors.part"
            >
              <UInput
                v-model="form.part"
                :model-modifiers="{ nullable: true, string: true, trim: true }"
                placeholder="1"
                autocapitalize="characters"
              />
            </UFormField>

            <UFormField
              label="Season"
              :error="form.errors.season"
            >
              <UInput
                v-model="form.season"
                :model-modifiers="{ nullable: true, string: true, trim: true }"
                placeholder="1"
                autocapitalize="characters"
              />
            </UFormField>
          </div>

          <USeparator />

          <UFormField
            label="Tags"
            :error="form.errors.tags"
          >
            <USelectMenu
              v-model="form.tags as TagMenuItem[]"
              :items="items as TagMenuItem[]"
              :ignore-filter="true"
              label-key="name"
              multiple
              class="w-full"
              placeholder="Add tags"
              @update:search-term="(value: string) => filter({ query: { query: value } })"
            >
              <template #item-label="{ item }">
                {{ item.name }}

                <span class="text-muted">
                  {{ item.category }}
                </span>
              </template>
            </USelectMenu>
          </UFormField>

          <USeparator />

          <div class="flex flex-col gap-3">
            <UFormField
              label="Summary"
              :error="form.errors.summary"
            >
              <UTextarea
                v-model="form.summary"
                :model-modifiers="{ nullable: true, string: true, trim: true }"
                :rows="4"
                autoresize
                placeholder="Enter markdown"
                class="w-full"
              />
            </UFormField>

            <UFormField
              label="Alternative names"
              :error="form.errors.titles"
            >
              <UTextarea
                v-model="form.titles"
                :model-modifiers="{ nullable: true, string: true, trim: true }"
                :rows="1"
                autocapitalize="words"
                placeholder="Separated by commas"
                class="w-full"
              />
            </UFormField>

            <UFormField :error="form.errors.adult">
              <USwitch
                v-model="form.adult"
                label="Adult content"
              />
            </UFormField>
          </div>
        </UForm>

        <USeparator />

        <div class="flex flex-col gap-2">
          <p class="text-sm font-semibold text-error">Delete video</p>
          <p class="text-sm text-muted">This may permanently remove this video and all associated data.</p>

          <VideoDeleteModal :item="video">
            <UButton
              label="Delete video"
              icon="i-lucide-trash"
              color="error"
              variant="soft"
              size="sm"
              class="w-fit"
            />
          </VideoDeleteModal>
        </div>
      </div>
    </template>

    <template #media>
      <div class="flex flex-col gap-4">
        <div
          v-if="video.thumb"
          class="overflow-hidden rounded-lg"
        >
          <img
            :src="video.thumb"
            :srcset="video.thumb_srcset ?? undefined"
            :alt="video.title"
            class="aspect-video w-full object-cover"
            loading="lazy"
            decoding="async"
          >
        </div>

        <UForm
          :state="form"
          class="flex flex-col gap-3"
        >
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <UFormField
              label="Snapshot"
              :error="form.errors.snapshot"
            >
              <UInput
                v-model="form.snapshot"
                :model-modifiers="{ nullable: true, number: true }"
                :ui="{ trailing: 'pe-1' }"
                type="number"
                placeholder="3.00"
                aria-label="Set by progress"
                step="0.01"
                min="0"
                :max="video.duration || undefined"
              >
                <template #trailing>
                  <UButton
                    color="neutral"
                    variant="link"
                    size="sm"
                    icon="i-lucide-image-down"
                    aria-label="From progress"
                    @click.prevent="setSnapshotFromProgress"
                  />
                </template>
              </UInput>
            </UFormField>

            <UFormField
              label="Published"
              :error="form.errors.published_at"
            >
              <UInputDate
                v-model="publishedAt"
                granularity="second"
                :ui="{ trailing: 'pe-1' }"
              >
                <template #trailing>
                  <UButton
                    color="neutral"
                    variant="link"
                    size="sm"
                    icon="i-lucide-calendar-clock"
                    aria-label="Set to now"
                    @click.prevent="setPublishedAtNow"
                  />
                </template>
              </UInputDate>
            </UFormField>

            <UFormField
              label="Released"
              :error="form.errors.released_at"
            >
              <UInputDate
                v-model="releasedAt"
                granularity="second"
                :ui="{ trailing: 'pe-1' }"
              >
                <template #trailing>
                  <UButton
                    color="neutral"
                    variant="link"
                    size="sm"
                    icon="i-lucide-calendar-clock"
                    aria-label="Set to now"
                    @click.prevent="setReleasedAtNow"
                  />
                </template>
              </UInputDate>
            </UFormField>
          </div>
        </UForm>
      </div>
    </template>

    <template #transcodes>
      <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <TranscodeImportModal
              v-if="transcodes?.length"
              :video="video"
            />

            <UButton
              icon="i-lucide-plus"
              label="Create transcode"
              color="neutral"
              variant="outline"
              size="sm"
              @click="createTranscode"
            />
          </div>

          <UButton
            label="View all"
            trailing-icon="i-lucide-arrow-right"
            color="neutral"
            variant="link"
            size="sm"
            :to="transcodesIndex.url(video.id)"
          />
        </div>

        <div
          v-if="transcodes === undefined"
          class="flex flex-col gap-2"
        >
          <USkeleton
            v-for="i in 3"
            :key="i"
            class="h-14 w-full rounded-md"
          />
        </div>

        <UEmpty
          v-else-if="!transcodes.length"
          icon="i-lucide-cpu"
          title="No transcodes"
          description="Transcode this video to AV1 to possibly reduce file size while maintaining quality."
        />

        <UPageList
          v-else
          divide
        >
          <UPageCard
            v-for="item in transcodes"
            :key="item.id"
            variant="naked"
            class="py-3 first:pt-0 last:pb-0"
          >
            <div class="flex items-center justify-between">
              <UUser
                :name="item.id"
                :description="`${item.state.label} · ${item.file_size}`"
                :avatar="{
                  alt: item.id,
                  loading: 'lazy',
                  decoding: 'async',
                  class: 'rounded-sm size-10 me-1',
                }"
              />

              <div class="z-10 flex items-center gap-2">
                <TranscodeDeleteModal :item="item" />
              </div>
            </div>
          </UPageCard>
        </UPageList>
      </div>
    </template>
  </FormModal>
</template>
