<script setup lang="ts">
import RegisterForm from '../components/auth/RegisterForm/index.vue'
</script>

<template>
  <div class="auth-container">
    <RegisterForm />
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