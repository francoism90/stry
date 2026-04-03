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
    variant="subtle"
    :ui="{ body: 'flex items-center gap-3', footer: 'flex items-center justify-between' }"
  >
    <template #body>
      <UAvatar
        :src="item.avatar ?? undefined"
        :alt="item.name"
        size="lg"
      />

      <div class="flex min-w-0 flex-col gap-1">
        <p class="truncate font-semibold">{{ item.name }}</p>

        <div class="flex items-center gap-2">
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
      </div>
    </template>

    <template #footer>
      <div class="flex items-center gap-2">
        <ProfileEditModal :item="item" />
        <ProfileDeleteModal :item="item" />
      </div>

      <UButton
        :label="item.id === current?.id ? 'Current' : 'Switch'"
        color="neutral"
        variant="soft"
        size="sm"
        :disabled="item.id === current?.id"
        @click="emit('switchProfile', item)"
      />
    </template>
  </UPageCard>
</template>
