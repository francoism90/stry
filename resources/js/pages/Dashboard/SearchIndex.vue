<script setup lang="ts">
import SearchController from '@/actions/App/Web/Dashboard/Controllers/SearchController'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import { Head, router } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { ref } from 'vue'

interface Props {
  search?: string | null
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

const props = defineProps<Props>()

const input = ref(props.search || '')

watchDebounced(input, () => router.get(SearchController.url(), { search: input.value }, { preserveState: true, preserveScroll: true }), {
  debounce: 350,
  maxWait: 1000,
})
</script>

<template>
  <Head title="Search" />

  <PageSection>
    <PageFeature title="Search" />

    <UFormField class="py-2">
      <UInput
        v-model.trim="input"
        placeholder="Title, description, tags..."
        class="w-full"
      />
    </UFormField>
  </PageSection>
</template>
