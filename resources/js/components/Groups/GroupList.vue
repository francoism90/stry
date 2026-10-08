<script setup lang="ts">
import GroupCard from '@/components/Groups/GroupCard.vue'
import type { Group } from '@/types'

defineProps<{
  items: Group[] | undefined
}>()
</script>

<template>
  <div class="grid grid-cols-[repeat(auto-fill,minmax(min(12.5rem,100%),1fr))] gap-x-4 gap-y-8">
    <GroupCard
      v-for="item in items"
      :key="item.id"
      :item="item"
    />
  </div>
</template>
