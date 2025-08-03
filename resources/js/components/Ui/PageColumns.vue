<template>
  <section class="flex flex-wrap items-start justify-between gap-2">
    <div class="items-start">
      <slot name="left" />
    </div>

    <div class="items-end">
      <slot name="right" />
    </div>
  </section>
</template>
