<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { computed, ref } from 'vue'

defineOptions({ layout: AuthLayout })

const props = defineProps<{
  status: number
}>()

const actions = ref(<ButtonProps[]>[
  {
    icon: 'i-lucide-house',
    label: 'Home',
    variant: 'subtle',
    size: 'md',
    to: '/',
  },
  {
    icon: 'i-lucide-refresh-cw',
    label: 'Refresh',
    variant: 'subtle',
    size: 'md',
    onClick: () => router.reload(),
  },
])

const title = computed(() => {
  return {
    403: 'Access Denied',
    404: 'Page Not Found',
    500: 'Server Error',
    503: 'Maintenance Mode',
  }[props.status]
})

const description = computed(() => {
  return {
    403: 'Sorry, you are forbidden from accessing this page.',
    404: 'Sorry, the page you are looking for could not be found.',
    500: 'Whoops, something went wrong on our servers.',
    503: 'Sorry, we are doing some maintenance. Please check back soon.',
  }[props.status]
})
</script>

<template>
  <Head>
    <title>{{ title }}</title>
  </Head>

  <UEmpty
    :title="title"
    :description="description"
    :actions="actions"
    size="xl"
    icon="i-lucide-circle-alert"
  />
</template>
