<script setup lang="ts">
import { ref } from 'vue'

const items = ref(['Recommended', 'Watching', 'Favorites'])
const value = ref('Recommended')
</script>

<template>
  <UDashboardToolbar>
    <template #left>
      <USelect
        v-model="value"
        :items="items"
        variant="ghost"
        color="primary"
      />
    </template>

    <template #right>
      <USlideover>
        <UButton
          label="Filters"
          icon="i-lucide-panel-right"
          color="neutral"
          variant="ghost"
        />

        <template #content>
          <Placeholder class="m-4 h-full" />
        </template>
      </USlideover>
    </template>
  </UDashboardToolbar>
</template>
