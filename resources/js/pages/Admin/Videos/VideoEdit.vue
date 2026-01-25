<script setup lang="ts">
import { update } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import VideoDeleteModal from '@/components/Videos/VideoDeleteModal.vue'
import { useTags } from '@/composables/tags'
import VideoLayout from '@/layouts/Admin/VideoLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { TagMenuItem, Video } from '@/types'
import { capitalize } from '@/utils/case'
import { router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  video: Video
  progress: number | null
}>()

defineOptions({ layout: [DashboardLayout, VideoLayout] })

const { items, filter } = useTags(props.video.tags || [])

const form = useForm('put', update.url(props.video.id), {
  name: props.video.name,
  episode: props.video.episode || null,
  season: props.video.season || null,
  part: props.video.part || null,
  snapshot: props.video.snapshot || null,
  tags: props.video.tags || [],
  summary: props.video.summary || null,
  expires_at: props.video.expires_at || null,
  published_at: props.video.published_at || null,
  released_at: props.video.released_at || null,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
</script>

<template>
  <UForm
    id="general"
    :state="form"
    class="mx-auto flex w-full flex-col gap-6 sm:gap-9 lg:max-w-4xl lg:py-3"
    loading-auto
    @submit="onSubmit"
  >
    <UPageCard
      title="General Details"
      description="General information about the video"
      variant="naked"
      orientation="horizontal"
    >
      <div class="flex items-center gap-2 lg:ms-auto">
        <UButton
          form="general"
          label="Save changes"
          type="submit"
          color="primary"
          variant="soft"
          loading-auto
        />
      </div>
    </UPageCard>

    <UPageCard variant="subtle">
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
          <UInput
            v-model="form.published_at"
            :model-modifiers="{ nullable: true, string: true, trim: true }"
            placeholder="YYYY-MM-DD HH:mm:ss"
          />
        </UFormField>

        <UFormField
          label="Released"
          :error="form.errors.released_at"
        >
          <UInput
            v-model="form.released_at"
            :model-modifiers="{ nullable: true, string: true, trim: true }"
            placeholder="YYYY-MM-DD HH:mm:ss"
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
    </UPageCard>

    <UPageCard
      title="Delete Video"
      description="This will delete the video and all associated data. There is no going back. Please be certain."
      class="from-error/10 to-default bg-linear-to-tl from-5%"
    >
      <template #footer>
        <VideoDeleteModal :item="video" />
      </template>
    </UPageCard>
  </UForm>
</template>
