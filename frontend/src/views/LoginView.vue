<script setup lang="ts">
import LoginForm from '../components/auth/LoginForm/index.vue'
</script>

<template>
  <div class="auth-container">
    <LoginForm />
  </div>
</template>

<style scoped>
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
  padding: 20px;
}
</style>