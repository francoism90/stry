<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import { computed, ref } from 'vue'

interface Props {
  status: number
}

const props = defineProps<Props>()

const actions = ref(<ButtonProps[]>[
  {
    icon: 'i-lucide-refresh-cw',
    label: 'Refresh',
    variant: 'subtle',
    size: 'md',
    onClick: () => router.reload(),
  },
  {
    icon: 'i-lucide-house',
    label: 'Home',
    variant: 'subtle',
    size: 'md',
    to: '/',
  },
])

const title = computed(() => {
  return {
    503: 'Maintenance Mode',
    500: 'Server Error',
    404: 'Page Not Found',
    403: 'Access Denied',
  }[props.status]
})

const description = computed(() => {
  return {
    503: 'Sorry, we are doing some maintenance. Please check back soon.',
    500: 'Whoops, something went wrong on our servers.',
    404: 'Sorry, the page you are looking for could not be found.',
    403: 'Sorry, you are forbidden from accessing this page.',
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
