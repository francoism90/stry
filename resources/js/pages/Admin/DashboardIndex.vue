<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  videos: number
  tags: number
  users: number
  media: string
}>()

defineOptions({ layout: DashboardLayout })

interface Stat {
  title: string
  icon: string
  value: number | string
  variation: number
}

interface Activity {
  id: number
  title: string
  description: string
  time: string
  icon: string
  color: 'primary' | 'success' | 'error' | 'warning'
}

const stats = ref<Stat[]>([
  {
    title: 'Total Videos',
    icon: 'i-lucide-videotape',
    value: props.videos,
    variation: 0,
  },
  {
    title: 'Total Tags',
    icon: 'i-lucide-tags',
    value: props.tags,
    variation: 0,
  },
  {
    title: 'Storage Used',
    icon: 'i-lucide-hard-drive',
    value: props.media,
    variation: 0,
  },
  {
    title: 'Active Users',
    icon: 'i-lucide-users',
    value: props.users,
    variation: 0,
  },
])

// Sample recent activity data
const recentActivity = ref<Activity[]>([
  // {
  //   id: 1,
  //   title: 'Video uploaded',
  //   description: 'New video "Example.mp4" uploaded',
  //   time: '2 hours ago',
  //   icon: 'i-lucide-upload',
  //   color: 'primary',
  // },
])
</script>

<template>
  <Head title="Dashboard" />

  <UDashboardPanel id="dashboard">
    <template #header>
      <UDashboardNavbar title="Dashboard">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UPage>
        <div class="space-y-6">
          <!-- Stats Grid -->
          <UPageGrid class="gap-4 sm:gap-6 lg:grid-cols-4 lg:gap-px">
            <UPageCard
              v-for="(stat, index) in stats"
              :key="index"
              :icon="stat.icon"
              :title="stat.title"
              variant="subtle"
              :ui="{
                container: 'gap-y-1.5',
                wrapper: 'items-start',
                leading: 'bg-primary/10 ring-primary/25 rounded-full p-2.5 ring ring-inset',
                title: 'text-muted text-xs font-normal uppercase',
              }"
              class="first:rounded-l-lg last:rounded-r-lg hover:z-1 lg:rounded-none"
            >
              <div class="flex items-center gap-2">
                <span class="text-highlighted text-2xl font-semibold">
                  {{ stat.value }}
                </span>

                <UBadge
                  v-if="stat.variation !== 0"
                  :color="stat.variation > 0 ? 'success' : 'error'"
                  variant="subtle"
                  class="text-xs"
                >
                  {{ stat.variation > 0 ? '+' : '' }}{{ stat.variation }}%
                </UBadge>
              </div>
            </UPageCard>
          </UPageGrid>

          <!-- Recent Activity -->
          <UCard>
            <template #header>
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-muted mb-1.5 text-xs uppercase">Recent Activity</p>
                  <p class="text-highlighted text-lg font-semibold">Latest Updates</p>
                </div>
              </div>
            </template>

            <div
              v-if="recentActivity.length > 0"
              class="divide-default divide-y"
            >
              <div
                v-for="activity in recentActivity"
                :key="activity.id"
                class="flex items-start gap-3 py-3 first:pt-0 last:pb-0"
              >
                <div
                  class="shrink-0 rounded-full p-2"
                  :class="`bg-${activity.color}/10 ring ring-inset ring-${activity.color}/25`"
                >
                  <UIcon
                    :name="activity.icon"
                    class="size-4"
                  />
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-highlighted text-sm font-medium">
                    {{ activity.title }}
                  </p>
                  <p class="text-muted text-sm">
                    {{ activity.description }}
                  </p>
                </div>
                <div class="text-muted shrink-0 text-xs">
                  {{ activity.time }}
                </div>
              </div>
            </div>

            <div
              v-else
              class="text-muted flex flex-col items-center justify-center py-12"
            >
              <UIcon
                name="i-lucide-inbox"
                class="mb-3 size-16"
              />
              <p class="text-sm">No recent activity</p>
            </div>
          </UCard>
        </div>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
