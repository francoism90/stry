<script setup lang="ts">
import GroupToggleController from '@/actions/App/Api/Groups/Controllers/GroupToggleController'
import GroupCreateModal from '@/components/Groups/GroupCreateModal.vue'
import type { Group, Video } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  video: Video
  groups: Group[] | undefined
}>()

const open = defineModel<boolean>('open')

const toggle = (group: Group) => {
  router.post(
    GroupToggleController.url({ group: group.id, video: props.video.id }),
    {},
    {
      preserveState: true,
      preserveScroll: true,
      only: ['groups'],
    },
  )
}
</script>

<template>
  <UModal
    v-model:open="open"
    title="Add to Collection"
  >
    <template #body>
      <div
        v-if="groups === undefined"
        class="flex flex-col gap-2"
      >
        <USkeleton
          v-for="i in 3"
          :key="i"
          class="h-10 w-full rounded-md"
        />
      </div>

      <div
        v-else-if="groups.length === 0"
        class="text-muted flex flex-col items-center gap-2 py-4 text-sm"
      >
        <p>No collections yet.</p>
      </div>

      <ul
        v-else
        class="divide-default -mx-4 flex flex-col divide-y"
      >
        <li
          v-for="group in groups"
          :key="group.id"
          class="flex items-center justify-between px-4 py-2.5"
        >
          <span class="text-sm font-medium">{{ group.name }}</span>

          <UButton
            :icon="group.has ? 'i-lucide-check' : 'i-lucide-plus'"
            :color="group.has ? 'primary' : 'neutral'"
            variant="ghost"
            size="sm"
            @click="toggle(group)"
          />
        </li>
      </ul>
    </template>

    <template #footer>
      <GroupCreateModal />
    </template>
  </UModal>
</template>
