<script setup lang="ts">
withDefaults(
  defineProps<{
    images: string[]
    tall?: boolean
  }>(),
  {
    tall: false,
  },
)
</script>

<template>
  <div
    v-if="images.length"
    class="pointer-events-none absolute inset-x-0 top-0 -z-10 overflow-hidden mask-[linear-gradient(to_bottom,black,transparent)]"
    :class="tall ? 'h-215 opacity-45' : 'h-105 opacity-40'"
    aria-hidden="true"
  >
    <div
      class="absolute -inset-x-20 -top-20 bottom-0 flex saturate-160"
      :class="tall ? 'blur-[80px]' : 'blur-[72px]'"
    >
      <img
        v-for="image in images"
        :key="image"
        :src="image"
        alt=""
        class="h-full min-w-0 flex-1 object-cover"
        loading="lazy"
        decoding="async"
      />
    </div>
  </div>
</template>
