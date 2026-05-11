<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/authStore'
import './styles/global.css'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout() {
  authStore.logout()
  await router.push('/login')
  window.location.reload()
}
</script>

<template>
  <div class="container">
    <header class="header">
      <h1>Gestión de Precios</h1>
      <nav v-if="authStore.isAuthenticated" class="nav">
        <RouterLink to="/productos">Productos</RouterLink>
        <RouterLink to="/profile">Perfil</RouterLink>
        <a href="#" @click.prevent="handleLogout">Logout</a>
      </nav>
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
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: #fff;
  border-bottom: 1px solid #ddd;
}

.header h1 {
  margin: 0;
  font-size: 1.5rem;
  color: #333;
}

.nav {
  display: flex;
  gap: 1rem;
}

.nav a {
  color: #007bff;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.nav a:hover {
  background: #f0f0f0;
}

.nav a.router-link-active {
  background: #007bff;
  color: white;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>