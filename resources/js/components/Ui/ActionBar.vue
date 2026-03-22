<template>
  <UDashboardToolbar :ui="{ root: 'min-h-6 overflow-x-auto border-0 px-0 sm:px-0' }">
    <template
      v-if="$slots.left"
      #left
    >
      <slot name="left" />
    </template>

    <template
      v-if="$slots.default"
      #default
    >
      <slot />
    </template>

    <template
      v-if="$slots.right"
      #right
    >
      <slot name="right" />
    </template>
  </UDashboardToolbar>
</template>
