<script setup lang="ts">
import { useServiceWorker } from '@/composables/pwa'
import { computed } from 'vue'

const { register } = useServiceWorker()
const { needRefresh, offlineReady, updateServiceWorker } = register()

const title = computed(() => (offlineReady.value ? 'App ready to work offline' : 'New content available'))
</script>

<template>
  <UContainer>
    <UAlert
      v-if="offlineReady || needRefresh"
      :title="title"
      color="neutral"
      variant="soft"
      class="rounded-none"
    >
      <template #description>
        <UButton
          v-if="needRefresh"
          label="Reload"
          size="xs"
          @click="updateServiceWorker()"
        />
      </template>
    </UAlert>
  </UContainer>
</template>
