<template>
  <UContainer class="flex flex-col lg:grid lg:grid-cols-10 lg:gap-10">
    <div
      v-if="$slots.left"
      class="lg:col-span-2"
    >
      <slot name="left" />
    </div>

    <div
      v-if="$slots.default"
      class="col-span-full"
    >
      <slot />
    </div>

    <div
      v-if="$slots.right"
      class="order-first lg:order-last lg:col-span-2"
    >
      <slot name="right" />
    </div>
  </UContainer>
</template>
