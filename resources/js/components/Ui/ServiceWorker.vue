<script setup lang="ts">
import { useServiceWorker } from '@/composables/pwa'
import { computed, ref } from 'vue'

const { register } = useServiceWorker()
const { needRefresh, offlineReady, updateServiceWorker } = register()

const actions = ref([
  {
    label: 'Refresh',
    trailingIcon: 'i-lucide-refresh-cw',
    onClick: () => updateServiceWorker(),
  },
])

const title = computed(() => (offlineReady.value ? 'App ready to work offline' : 'New content available'))
</script>

<template>
  <UContainer v-if="needRefresh">
    <UBanner
      :title="title"
      :actions="actions"
      icon="i-lucide-info"
      color="secondary"
      variant="soft"
      class="rounded-none"
    />
  </UContainer>
</template>
