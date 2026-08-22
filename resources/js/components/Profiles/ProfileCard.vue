<script setup lang="ts">
import ProfileDeleteModal from '@/components/Profiles/ProfileDeleteModal.vue'
import ProfileEditModal from '@/components/Profiles/ProfileEditModal.vue'
import type { Profile } from '@/types'

defineProps<{
  item: Profile
  current?: Profile | null
}>()

const emit = defineEmits<{
  switchProfile: [item: Profile]
}>()
</script>

<template>
  <UPageCard
    variant="naked"
    class="py-3 first:pt-0 last:pb-0"
  >
    <div class="flex items-center justify-between">
      <UUser
        :name="item.name"
        :avatar="{
          src: item.avatar ?? undefined,
          alt: item.name,
          size: 'lg',
        }"
        :ui="{ wrapper: 'flex flex-col gap-1.5' }"
      >
        <template #description>
          <div class="flex items-center gap-1">
            <UBadge
              color="neutral"
              variant="soft"
              size="sm"
            >
              {{ item.state.label }}
            </UBadge>

            <UBadge
              v-if="item.is_primary"
              color="primary"
              variant="soft"
              size="sm"
            >
              Primary
            </UBadge>

            <UBadge
              v-if="item.is_kids"
              color="warning"
              variant="soft"
              size="sm"
            >
              Kids
            </UBadge>
          </div>
        </template>
      </UUser>

      <div class="z-10 flex items-center gap-2">
        <ProfileEditModal :item="item" />
        <ProfileDeleteModal :item="item" />

        <UButton
          :label="item.id === current?.id ? 'Current' : 'Switch'"
          color="neutral"
          variant="soft"
          size="sm"
          :disabled="item.id === current?.id"
          @click="emit('switchProfile', item)"
        />
      </div>
    </div>
  </UPageCard>
</template>
