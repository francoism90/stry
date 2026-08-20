<template>
  <div class="flex items-center justify-between gap-1.5">
    <div class="flex items-center gap-1.5">
      <slot name="left" />
    </div>

    <div class="flex items-center gap-1.5">
      <slot name="right" />
    </div>
  </div>
</template>
