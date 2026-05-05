<script setup lang="ts">
import { update } from '@/actions/App/Web/Videos/Controllers/VideoController'
import VideoDeleteModal from '@/components/Videos/VideoDeleteModal.vue'
import { useDateTime } from '@/composables/datetime'
import { useTags } from '@/composables/tags'
import VideoLayout from '@/layouts/App/VideoLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { TagMenuItem, Video } from '@/types'
import { capitalize } from '@/utils/case'
import { Head, useForm } from '@inertiajs/vue3'
import type { CalendarDateTime } from '@internationalized/date'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
  progress: number | null
}>()

defineOptions({ layout: [DefaultLayout, VideoLayout] })

const { toDateTime, fromDateTime, nowDateTime } = useDateTime()
const { items, filter } = useTags(props.video.tags || [])

const form = useForm(update(props.video.id), {
  name: props.video.name,
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

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })
</script>

<template>
  <Head :title="video.title" />

  <UPageBody>
    <UForm
      :state="form"
      class="flex flex-col py-3"
      loading-auto
      @submit="onSubmit"
    >
      <UPageCard
        variant="subtle"
        orientation="vertical"
        :ui="{
          body: 'flex w-full flex-col gap-3',
        }"
      >
        <template #body>
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
                  @click.prevent="form.name = capitalize(form.name)"
                />
              </template>
            </UInput>
          </UFormField>

          <USeparator />

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
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

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
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
                    @click.prevent="form.snapshot = progress || null"
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
                    @click.prevent="publishedAt = nowDateTime()"
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
                    @click.prevent="releasedAt = nowDateTime()"
                  />
                </template>
              </UInputDate>
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

          <div class="flex flex-col gap-4">
            <UFormField
              label="Summary"
              :error="form.errors.summary"
            >
              <UTextarea
                v-model="form.summary"
                :model-modifiers="{ nullable: true, string: true, trim: true }"
                :rows="5"
                autoresize
                placeholder="Enter markdown"
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
        </template>

        <template #footer>
          <UButton
            label="Save changes"
            type="submit"
            color="primary"
            variant="soft"
            loading-auto
          />
        </template>
      </UPageCard>
    </UForm>

    <UPageCard
      variant="subtle"
      orientation="vertical"
      :ui="{
        root: 'bg-linear-to-r from-error/5 to-transparent ring-error/25',
        body: 'flex flex-col gap-3',
      }"
    >
      <template #body>
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
      </template>
    </UPageCard>
  </UPageBody>
</template>
