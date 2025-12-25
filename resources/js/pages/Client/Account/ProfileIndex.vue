<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Profile" />

  <UDashboardPanel id="profile">
    <template #header>
      <UDashboardNavbar
        title="Profile"
        :ui="{ root: 'h-24 gap-3 border-0' }"
        :toggle="{ variant: 'link', class: 'ps-0' }"
      >
        <template #right>
          <UButton
            label="Back"
            to="/"
            variant="outline"
            size="xs"
            color="neutral"
            class="p-3"
            icon="i-lucide-arrow-left"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UPage>
        <UPageHeader
          title="Account Profile"
          description="Manage your account profile and settings (WIP)."
          :ui="{
            root: 'border-0 py-4',
            title: 'font-serif text-xl sm:text-2xl',
            description: 'text-base',
          }"
        />

        <UButton
          label="Logout"
          class="my-2"
          variant="soft"
          @click="() => router.post('/logout')"
        />
      </UPage>
    </template>
  </UDashboardPanel>
</template>
