<script setup lang="ts">
import ApplicationRestartController from '@/actions/Modules/Web/Settings/Controllers/ApplicationRestartController'
import { router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()

const notices = computed(() => page.props.notices)

const isRestarting = ref(false)

function restart() {
  router.post(
    ApplicationRestartController.url(),
    {},
    {
      preserveScroll: true,
      onStart: () => (isRestarting.value = true),
      onFinish: () => (isRestarting.value = false),
    },
  )
}
</script>

<template>
  <div v-if="notices?.migrations || notices?.restart">
    <UBanner
      v-if="notices.migrations"
      color="error"
      icon="i-lucide-database"
      title="Database migrations are pending. Run “php artisan app:update” to finish the upgrade."
    />

    <UBanner
      v-if="notices.restart"
      color="warning"
      icon="i-lucide-rotate-cw"
      title="The storage settings changed. Restart the workers to use them."
      :actions="[
        {
          label: 'Restart now',
          color: 'neutral',
          variant: 'outline',
          loading: isRestarting,
          onClick: restart,
        },
      ]"
    />
  </div>
</template>
