<script setup lang="ts">
import { update } from '@/actions/App/Web/Videos/Controllers/VideoController'
import { useDateTime } from '@/composables/datetime'
import { useTags } from '@/composables/tags'
import type { TagMenuItem, Video } from '@/types'
import { capitalize } from '@/utils/case'
import type { CalendarDateTime } from '@internationalized/date'
import { useForm } from 'laravel-precognition-vue-inertia'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
}>()

const { toDateTime, fromDateTime, nowDateTime } = useDateTime()
const { items, filter } = useTags(props.video.tags || [])

const form = useForm('put', update.url(props.video.id), {
  name: props.video.name,
  episode: props.video.episode || null,
  season: props.video.season || null,
  part: props.video.part || null,
  summary: props.video.summary || null,
  tags: props.video.tags || [],
  expires_at: props.video.expires_at || null,
  published_at: props.video.published_at || null,
  released_at: props.video.released_at || null,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
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

const expiresAt = computed({
  get: () => toDateTime(form.expires_at),
  set: (value: CalendarDateTime | null) => {
    form.expires_at = fromDateTime(value)
  },
})
</script>

<template>
  <UForm
    :state="form"
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
        </div>

        <USeparator />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
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

          <UFormField
            label="Expires"
            :error="form.errors.expires_at"
          >
            <UInputDate
              v-model="expiresAt"
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
                  @click.prevent="expiresAt = nowDateTime()"
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
            @update:search-term="(value: string) => filter({ query: { search: value } })"
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
</template>
