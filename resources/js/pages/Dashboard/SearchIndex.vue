<script setup lang="ts">
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import { Head } from '@inertiajs/vue3'
import type { RadioGroupItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  search: string | undefined
  sort: string | undefined
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

defineProps<Props>()

const filters = ref<RadioGroupItem[]>([
  { label: 'Relevant', value: '' },
  { label: 'Newest', value: 'newest' },
  { label: 'Oldest', value: 'oldest' },
  { label: 'Ordered', value: 'ordered' },
  { label: 'Longest', value: 'longest' },
  { label: 'Shortest', value: 'shortest' },
])
</script>

<template>
  <Head title="Search" />

  <PageSection>
    <PageFeature title="Search" />

    <UForm
      :state="form"
      @submit="onSubmit"
      class="flex flex-col gap-4 pt-2"
    >
      <UFormField
        name="search"
        :error="form.errors.search"
      >
        <UInput
          v-model="form.search"
          :model-modifiers="{ nullable: true, string: true, trim: true }"
          type="search"
          placeholder="Title, description, tags..."
          size="lg"
          class="w-full"
          autofocus
        />
      </UFormField>
    </UForm>

    <PageFilters
      v-if="search?.length"
      :filters
    />
  </PageSection>
</template>
