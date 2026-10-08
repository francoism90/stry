<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterToolbar from '@/components/Ui/FilterToolbar.vue'
import { provideQuery } from '@/composables/query'
import { useHeaderCollapse } from '@/composables/scroll'
import type { OptionItem, QueryFilter, QueryValue } from '@/types'
import type { ButtonProps } from '@nuxt/ui'

const props = withDefaults(
  defineProps<{
    id?: string
    title?: string
    description?: string
    headline?: ButtonProps
    links?: ButtonProps[]
    chips?: ButtonProps[]
    scopes?: OptionItem[]
    sorters?: OptionItem[]
    filter?: QueryFilter
    sort?: QueryValue
    query?: QueryValue
    fluid?: boolean
  }>(),
  {
    id: 'resource',
    fluid: false,
    sort: null,
    query: null,
  },
)

provideQuery(props)

const { isHeaderCollapsed } = useHeaderCollapse()
</script>

<template>
  <UDashboardPanel :id="id">
    <template #header>
      <div
        class="sticky top-0 z-50 transition-colors duration-300"
        :class="isHeaderCollapsed ? 'bg-default/75 backdrop-blur' : 'bg-transparent'"
      >
        <AppHeader />

        <div
          v-if="title"
          class="grid transition-[grid-template-rows] duration-300 ease-in-out"
          :class="isHeaderCollapsed ? 'grid-rows-[0fr]' : 'grid-rows-[1fr]'"
        >
          <div class="overflow-hidden">
            <UPageHeader
              :title="title"
              :description="description"
              class="mx-auto w-full max-w-(--ui-container) px-4 py-4 sm:px-6"
              :ui="{
                wrapper: 'flex-row flex-wrap items-center justify-between gap-3 lg:items-center',
                title: 'min-w-0 flex-1 basis-60 wrap-anywhere capitalize',
                description: 'mt-1 text-sm text-muted',
              }"
            >
              <template
                v-if="headline"
                #headline
              >
                <UButton
                  v-bind="headline"
                  color="neutral"
                  variant="outline"
                  size="xs"
                  class="rounded-full bg-(--glass) ps-2 pe-2.5 text-default ring-(--glass-border) backdrop-blur-md backdrop-saturate-140 hover:bg-(--glass-strong) hover:text-highlighted"
                />
              </template>

              <template
                v-if="links?.length"
                #links
              >
                <UButton
                  v-for="link in links"
                  :key="link.label ?? link.icon"
                  color="neutral"
                  variant="outline"
                  size="sm"
                  v-bind="link"
                  class="rounded-full bg-(--glass) text-highlighted ring-(--glass-border) backdrop-blur-md backdrop-saturate-140 hover:bg-(--glass-strong)"
                />
              </template>

              <div
                v-if="chips?.length"
                class="mt-3 flex flex-wrap items-center gap-1.5"
              >
                <UButton
                  v-for="chip in chips"
                  :key="chip.label"
                  v-bind="chip"
                  color="neutral"
                  variant="outline"
                  size="xs"
                  class="rounded-full bg-(--glass) ps-2 pe-3 text-default ring-(--glass-border) backdrop-blur-md backdrop-saturate-140 hover:bg-(--glass-strong) hover:text-highlighted"
                />
              </div>
            </UPageHeader>
          </div>
        </div>

        <FilterToolbar
          v-if="scopes || sorters"
          :scopes="scopes"
          :sorters="sorters"
        />
      </div>
    </template>

    <template #body>
      <div
        v-if="!fluid"
        class="mx-auto flex w-full flex-col gap-4 sm:gap-6 lg:max-w-5xl lg:gap-12"
      >
        <slot />
      </div>

      <slot v-else />
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
