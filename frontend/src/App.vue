<script setup lang="ts">
import { RouterView } from 'vue-router';
import './styles/global.css';
</script>

<template>
  <div class="container">
    <header class="header">
      <h1>Gestión de Precios</h1>
    </header>
    <main class="main-content">
      <RouterView v-slot="{ Component }">
        <Transition name="fade" mode="out-in">
          <component :is="Component" />
        </Transition>
      </RouterView>
    </main>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>