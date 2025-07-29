<template>
  <UAlert
    v-if="$page.props.flash?.message"
    :color="$page.props.flash.level"
    :title="$page.props.flash.message"
  />
</template>
