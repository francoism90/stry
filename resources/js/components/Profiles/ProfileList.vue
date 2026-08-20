<script setup lang="ts">
import ProfileCard from '@/components/Profiles/ProfileCard.vue'
import type { Profile } from '@/types'

defineOptions({ inheritAttrs: false })

const props = defineProps<{
  items: Profile[] | undefined
  current?: Profile | null
}>()

const emit = defineEmits<{
  switchProfile: [item: Profile]
}>()
</script>

<template>
  <div
    v-if="!props.items?.length"
    class="flex flex-col items-center justify-center gap-3 py-24 text-center"
  >
    <UIcon
      name="i-lucide-users"
      class="size-10 text-muted"
    />
    <p class="font-semibold">No profiles yet</p>
    <p class="text-sm text-muted">Create a profile to personalize watch history and recommendations.</p>
  </div>

  <div
    v-else
    v-bind="$attrs"
    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
  >
    <ProfileCard
      v-for="item in props.items"
      :key="item.id"
      :item="item"
      :current="props.current"
      @switch-profile="emit('switchProfile', item)"
    />
  </div>
</template>
