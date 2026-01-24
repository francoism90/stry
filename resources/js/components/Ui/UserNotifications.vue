<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { onUnmounted } from 'vue'

const toast = useToast()

onUnmounted(
  router.on('flash', (event) => {
    if (event.detail.flash) {
      toast.add({
        title: event.detail.flash.label ?? 'Notice',
        description: event.detail.flash.message ?? 'No message provided.',
        color: event.detail.flash.color ?? 'primary',
        icon: event.detail.flash.icon ?? 'i-lucide-info',
      })
    }
  }),
)
</script>

<template>
  <div>
    <slot />
  </div>
</template>
