<script setup lang="ts">
import SearchController from '@/actions/App/Web/Dashboard/Controllers/SearchController'
import PageFilters from '@/components/Ui/PageFilters.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import type { Videos } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'
import { ref } from 'vue'

interface Props {
  search?: string | null
  sort?: string | null
  items?: Videos
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

const props = defineProps<Props>()

const filters = ref<NavigationMenuItem[]>([
  { label: 'Relevant', to: SearchController.url({ mergeQuery: { sort: '' } }), exact: true },
  { label: 'Ordered', to: SearchController.url({ mergeQuery: { sort: 'ordered' } }) },
  { label: 'Longest', to: SearchController.url({ mergeQuery: { sort: 'longest' } }) },
  { label: 'Shortest', to: SearchController.url({ mergeQuery: { sort: 'shortest' } }) },
])

const form = useForm('get', SearchController.url(), { search: props.search || '', sort: props.sort || '' })

const onSubmit = async () =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => router.reload({ except: ['flash'] }),
  })
</script>

<template>
  <Head title="Search" />

  <PageSection>
    <UPageFeature title="Search" />

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
