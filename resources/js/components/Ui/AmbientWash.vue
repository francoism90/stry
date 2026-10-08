<script setup lang="ts">
import { useAmbientWash } from '@/composables/ambient'
import { useTemplateRef } from 'vue'

const props = withDefaults(
  defineProps<{
    images: string[]
    tall?: boolean
  }>(),
  {
    tall: false,
  },
)

const layer = useTemplateRef<HTMLDivElement>('layer')

const { layerKey, isLoaded, markLoaded } = useAmbientWash(() => props.images, layer)
</script>

<template>
  <div
    v-if="images.length"
    class="pointer-events-none absolute inset-x-0 top-0 -z-10 overflow-hidden mask-[linear-gradient(to_bottom,black,transparent)]"
    :class="tall ? 'h-215 opacity-45' : 'h-105 opacity-40'"
    aria-hidden="true"
  >
    <Transition
      enter-active-class="transition-opacity duration-1000 ease-out"
      leave-active-class="transition-opacity duration-1000 ease-in"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        :key="layerKey"
        ref="layer"
        class="absolute -inset-x-20 -top-20 bottom-0 flex saturate-160 will-change-transform motion-safe:animate-ambient-drift"
        :class="tall ? 'blur-[80px]' : 'blur-[72px]'"
      >
        <img
          v-for="image in images"
          :key="image"
          :src="image"
          :data-image="image"
          alt=""
          class="h-full min-w-0 flex-1 object-cover transition-opacity duration-700 ease-out"
          :class="isLoaded(image) ? 'opacity-100' : 'opacity-0'"
          decoding="async"
          @load="markLoaded(image)"
        />
      </div>
    </Transition>
  </div>
</template>
