<script setup lang="ts">
import ProductDetail from '../components/productos/ProductDetail/index.vue'

defineProps<{
  id: string
}>()
</script>

<template>
  <ProductDetail :id="id" />
</template>