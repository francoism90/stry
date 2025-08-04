<script setup lang="ts">
import { update } from '@/actions/App/Web/Videos/Controllers/VideoController'
import FlashAlert from '@/components/Ui/FlashAlert.vue'
import { useAppearance } from '@/composables/appearance'
import { useTagInput } from '@/composables/taginput'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoResource from '@/layouts/Video/VideoResource.vue'
import type { TagMenuItem, Video } from '@/types'
import { router } from '@inertiajs/vue3'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  video: Video
}

defineOptions({ layout: [DefaultLayout, VideoResource] })

const props = defineProps<Props>()

const { data, query } = useTagInput(props.video.tags || [])
const { title } = useAppearance()

const form = useForm('put', update.url({ video: props.video.id }), props.video)

const submit = async () =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => router.reload({ except: ['flash'] }),
  })
</script>

<template>
  <UForm
    :state="form"
    @submit.prevent="submit"
    class="flex flex-col gap-4 py-4"
  >
    <FlashAlert />

    <UFormField
      label="Name"
      name="name"
      required
      :error="form.errors.name"
    >
      <UInput
        v-model.trim="form.name"
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
            @click="form.name = title(form.name)"
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
          v-model.trim="form.episode"
          placeholder="1"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Season"
        name="season"
        :error="form.errors.season"
      >
        <UInput
          v-model.trim="form.season"
          placeholder="1"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Part"
        name="part"
        :error="form.errors.part"
      >
        <UInput
          v-model.trim="form.part"
          placeholder="1"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Released"
        name="released_at"
        :error="form.errors.released"
      >
        <UInput
          v-model.trim="form.released"
          placeholder="YYYY-MM-DD"
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
          v-model.trim="form.snapshot"
          placeholder="1.00"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Published"
        name="published"
        :error="form.errors.published"
      >
        <UInput
          v-model.trim="form.published"
          placeholder="YYYY-MM-DD"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Expires"
        name="expires"
        :error="form.errors.expires"
      >
        <UInput
          v-model.trim="form.expires"
          placeholder="YYYY-MM-DD"
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
        :items="data as TagMenuItem[]"
        label-key="name"
        multiple
        class="w-full"
        @update:search-term="(value) => query({ search: value })"
      >
        <template #item-label="{ item }">
          {{ item.name }}

          <span class="text-muted">
            {{ item.type }}
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
        class="w-full"
        :ui="{
          base: 'h-32',
        }"
      />
    </UFormField>

    <UButton
      label="Save changes"
      type="submit"
      variant="soft"
      class="self-end"
      loading-auto
    />
  </UForm>
</template>
