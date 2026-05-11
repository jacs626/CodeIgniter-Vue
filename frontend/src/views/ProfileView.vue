<script setup lang="ts">
import { useAuthStore } from '../stores/authStore'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

function handleLogout() {
  authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="profile-container">
    <div class="profile-card">
      <h1>Mi Perfil</h1>
      
      <div v-if="authStore.user" class="user-info">
        <div class="info-row">
          <span class="label">Nombre:</span>
          <span class="value">{{ authStore.user.nombre }}</span>
        </div>
        
        <div class="info-row">
          <span class="label">Email:</span>
          <span class="value">{{ authStore.user.email }}</span>
        </div>

        <div v-if="authStore.user.created_at" class="info-row">
          <span class="label">Miembro desde:</span>
          <span class="value">{{ new Date(authStore.user.created_at).toLocaleDateString() }}</span>
        </div>
      </div>

      <button @click="handleLogout" class="btn-logout">
        Cerrar Sesión
      </button>
    </div>
  </div>
</template>

<style scoped>
.profile-container {
  display: flex;
  justify-content: center;
  padding: 40px 20px;
}

.profile-card {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 500px;
}

h1 {
  margin: 0 0 1.5rem;
  color: #333;
}

.user-info {
  margin-bottom: 2rem;
}

.info-row {
  display: flex;
  padding: 0.75rem 0;
  border-bottom: 1px solid #eee;
}

.info-row:last-child {
  border-bottom: none;
}

.label {
  font-weight: 600;
  color: #555;
  width: 120px;
}

.value {
  color: #333;
}

.btn-logout {
  width: 100%;
  padding: 0.75rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
}

.btn-logout:hover {
  background: #c82333;
}
</style>