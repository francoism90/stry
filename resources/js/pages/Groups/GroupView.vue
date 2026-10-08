<script setup lang="ts">
import { index } from '@/actions/Modules/Web/Groups/Controllers/GroupController'
import GroupToggleController from '@/actions/Modules/Web/Groups/Controllers/GroupToggleController'
import GroupCover from '@/components/Groups/GroupCover.vue'
import GroupEditModal from '@/components/Groups/GroupEditModal.vue'
import AmbientWash from '@/components/Ui/AmbientWash.vue'
import FilterToolbar from '@/components/Ui/FilterToolbar.vue'
import VideoRow from '@/components/Videos/VideoRow.vue'
import ResourceLayout from '@/layouts/App/ResourceLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Group, OptionItem, QueryFilter, QueryValue, Video, VideoCollection } from '@/types'
import { Head, InfiniteScroll, router, setLayoutProps } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { DropdownMenuItem } from '@nuxt/ui'
import { computed, ref, watchEffect } from 'vue'

const props = defineProps<{
  group: Group
  items: VideoCollection
  scopes?: OptionItem[]
  sorters?: OptionItem[]
  filter?: QueryFilter
  sort?: QueryValue
  query?: QueryValue
}>()

defineOptions({
  layout: [AppLayout, ResourceLayout],
})

const isEditModalOpen = ref(false)
const itemBody = ref()

const isCustom = computed(() => props.group.type === 'custom')

const videoCount = computed(() => props.group.videos ?? 0)

const videoCountLabel = computed(
  () => `${Intl.NumberFormat().format(videoCount.value)} ${videoCount.value === 1 ? 'video' : 'videos'}`,
)

const removeFromGroup = (video: Video): void => {
  router.post(
    GroupToggleController.url({ group: props.group.id, video: video.id }),
    {},
    {
      preserveScroll: true,
      only: ['group', 'items'],
      reset: ['items'],
    },
  )
}

const rowActions = (video: Video): DropdownMenuItem[] =>
  isCustom.value
    ? [
        {
          label: 'Remove from collection',
          icon: 'i-lucide-list-minus',
          onSelect: () => removeFromGroup(video),
        },
      ]
    : []

// The header and filters live in the page. Layouts also receive page props, so clear the
// scopes and sorters to keep the layout from rendering a second filter bar.
watchEffect(() => {
  setLayoutProps({
    id: 'collections.show',
    scopes: undefined,
    sorters: undefined,
    filter: props.filter,
    sort: props.sort,
    query: props.query,
    fluid: true,
  })
})

useEcho(`groups.${props.group.id}`, '.group.updated', () => router.reload({ only: ['group'] }))
useEcho(`groups.${props.group.id}`, '.group.trashed', () => router.visit(index.url()))
</script>

<template>
  <Head :title="group.title" />

  <AmbientWash :images="group.thumb ? [group.thumb] : []" />

  <UPage>
    <GroupEditModal
      v-if="isCustom"
      v-model:open="isEditModalOpen"
      :group="group"
    />

    <header class="flex flex-wrap items-end gap-6 pt-4">
      <GroupCover
        :item="group"
        size="lg"
        class="max-w-70 min-w-0 flex-[0_1_17.5rem]"
      />

      <div class="flex min-w-0 flex-[1_1_20rem] flex-col items-start gap-3">
        <ULink
          :to="index.url()"
          class="text-xs font-medium text-muted hover:text-highlighted"
        >
          Collection
        </ULink>

        <div class="flex w-full flex-col gap-1">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h1
              class="min-w-0 flex-[1_1_15rem] text-3xl font-bold wrap-anywhere text-highlighted capitalize sm:text-4xl"
            >
              {{ group.title }}
            </h1>

            <UButton
              v-if="isCustom"
              label="Edit"
              icon="i-lucide-pencil"
              color="neutral"
              variant="outline"
              size="sm"
              class="rounded-full bg-(--glass) text-highlighted ring-(--glass-border) backdrop-blur-md backdrop-saturate-140 hover:bg-(--glass-strong)"
              @click="isEditModalOpen = true"
            />
          </div>

          <p class="text-sm text-muted">{{ videoCountLabel }}</p>

          <p
            v-if="group.content"
            class="mt-1 text-sm text-default"
          >
            {{ group.content }}
          </p>
        </div>
      </div>
    </header>

    <FilterToolbar
      :scopes="scopes"
      :sorters="sorters"
      class="px-0 sm:px-0"
    />

    <InfiniteScroll
      data="items"
      :items-element="() => itemBody"
      :buffer="200"
    >
      <ul
        ref="itemBody"
        :aria-label="`Videos in ${group.title}`"
        class="flex flex-col gap-1"
      >
        <VideoRow
          v-for="item in items?.data ?? []"
          :key="item.id"
          :item="item"
          :actions="rowActions(item)"
        />
      </ul>

      <template #loading>
        <div
          class="mt-1 flex flex-col gap-1"
          aria-hidden="true"
        >
          <div
            v-for="i in 3"
            :key="i"
            class="flex items-center gap-3 p-2"
          >
            <USkeleton class="aspect-video w-24 shrink-0 rounded-lg bg-(--glass) sm:w-32" />
            <div class="flex flex-1 flex-col gap-2">
              <USkeleton class="h-3.5 w-1/2 rounded-sm bg-(--glass)" />
              <USkeleton class="h-3 w-1/4 rounded-sm bg-(--glass)" />
            </div>
          </div>
        </div>
      </template>
    </InfiniteScroll>
  </UPage>
</template>
