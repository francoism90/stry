<script setup lang="ts">
import VideoLayout from '@/layouts/Admin/VideoLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Video } from '@/types'

defineProps<{
  video: Video
}>()

defineOptions({ layout: [DashboardLayout, VideoLayout] })
</script>

<template>
  <UPageCard
    title="Password"
    description="Confirm your current password before setting a new one."
    variant="subtle"
  >
  </UPageCard>

  <UPageCard
    title="Account"
    description="No longer want to use our service? You can delete your account here. This action is not reversible. All information related to this account will be deleted permanently."
    class="bg-linear-to-tl from-error/10 from-5% to-default"
  >
    <template #footer>
      <UButton
        label="Delete account"
        color="error"
      />
    </template>
  </UPageCard>
</template>
