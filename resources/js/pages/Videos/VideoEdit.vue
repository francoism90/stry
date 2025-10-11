<script setup lang="ts">
import { update } from '@/actions/App/Web/Videos/Controllers/VideoController'
import FlashAlert from '@/components/Ui/FlashAlert.vue'
import VideoDeleteModal from '@/components/Video/VideoDeleteModal.vue'
import { useAppearance } from '@/composables/appearance'
import { useTagInput } from '@/composables/taginput'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoResource from '@/layouts/Video/VideoResource.vue'
import type { TagMenuItem, Video } from '@/types'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  video: Video
  progress: number | null
}

defineOptions({ layout: [DefaultLayout, VideoResource] })

const props = defineProps<Props>()

const { data, query } = useTagInput(props.video.tags || [])
const { title } = useAppearance()

const form = useForm('put', update.url({ video: props.video.id }), props.video)

const onSubmit = async () => {
  form.submit({
    preserveState: true,
    replace: true,
  })
}
</script>

<template>
  <UForm
    :state="form"
    @submit="onSubmit"
    class="flex flex-col gap-4"
  >
    <FlashAlert />

    <UFormField
      label="Name"
      name="name"
      required
      :error="form.errors.name"
    >
      <UInput
        v-model="form.name"
        :model-modifiers="{ string: true, trim: true }"
        autofocus
        autocapitalize="words"
        class="w-full"
        :ui="{ trailing: 'pe-1' }"
      >
        <template #trailing>
          <UButton
            color="neutral"
            variant="link"
            size="sm"
            icon="i-lucide-wand-sparkles"
            aria-label="Format name"
            @click.prevent="form.name = title(form.name)"
          />
        </template>
      </UInput>
    </UFormField>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
      <UFormField
        label="Episode"
        name="episode"
        :error="form.errors.episode"
      >
        <UInput
          v-model="form.episode"
          :model-modifiers="{ nullable: true, string: true, trim: true }"
          placeholder="1"
          autocapitalize="characters"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Season"
        name="season"
        :error="form.errors.season"
      >
        <UInput
          v-model="form.season"
          :model-modifiers="{ nullable: true, string: true, trim: true }"
          placeholder="1"
          autocapitalize="characters"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Part"
        name="part"
        :error="form.errors.part"
      >
        <UInput
          v-model="form.part"
          :model-modifiers="{ nullable: true, string: true, trim: true }"
          placeholder="1"
          autocapitalize="characters"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Released"
        name="released_at"
        :error="form.errors.released_at"
      >
        <UInput
          v-model="form.released_at"
          :model-modifiers="{ nullable: true, string: true, trim: true }"
          placeholder="YYYY-MM-DD HH:mm:ss"
          class="w-full"
        />
      </UFormField>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <UFormField
        label="Snapshot"
        name="snapshot"
        :error="form.errors.snapshot"
      >
        <UInput
          v-model="form.snapshot"
          :model-modifiers="{ nullable: true, number: true, trim: true }"
          type="number"
          placeholder="3.00"
          step="0.01"
          min="0"
          :max="video.duration || null"
          class="w-full"
        >
          <template #trailing>
            <UButton
              color="neutral"
              variant="link"
              size="sm"
              icon="i-lucide-wand-sparkles"
              aria-label="Format name"
              @click.prevent="form.snapshot = progress ?? null"
            />
          </template>
        </UInput>
      </UFormField>

      <UFormField
        label="Published"
        name="published_at"
        :error="form.errors.published_at"
      >
        <UInput
          v-model="form.published_at"
          :model-modifiers="{ nullable: true, string: true, trim: true }"
          placeholder="YYYY-MM-DD HH:mm:ss"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Expires"
        name="expires_at"
        :error="form.errors.expires_at"
      >
        <UInput
          v-model="form.expires_at"
          :model-modifiers="{ nullable: true, string: true, trim: true }"
          placeholder="YYYY-MM-DD HH:mm:ss"
          class="w-full"
        />
      </UFormField>
    </div>

    <UFormField
      label="Tags"
      name="tags"
      :error="form.errors.tags"
    >
      <USelectMenu
        v-model="form.tags as TagMenuItem[]"
        :model-modifiers="{ nullable: true }"
        :items="data as TagMenuItem[]"
        :ignore-filter="true"
        label-key="name"
        multiple
        class="w-full"
        placeholder="Add tags"
        @update:search-term="(value: string) => query({ search: value })"
      >
        <template #item-label="{ item }">
          {{ item.name }}

          <span class="text-muted">
            {{ item.category }}
          </span>
        </template>
      </USelectMenu>
    </UFormField>

    <UFormField
      label="Summary"
      name="summary"
      :error="form.errors.summary"
    >
      <UTextarea
        v-model="form.summary"
        :model-modifiers="{ nullable: true, string: true, trim: true }"
        :ui="{
          root: 'w-full',
          base: 'h-32',
        }"
      />
    </UFormField>

    <div class="flex gap-2 self-end">
      <VideoDeleteModal :item="video" />

      <UButton
        label="Save changes"
        type="submit"
        variant="soft"
        loading-auto
      />
    </div>
  </UForm>
</template>
