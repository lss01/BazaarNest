<template>
  <div class="login-container">
    <div class="login-box">
      <h2>Login</h2>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" v-model="username" required placeholder="Enter your username">
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" v-model="password" required placeholder="Enter your password">
        </div>

        <div class="form-group">
          <label for="role">Role</label>
          <select id="role" v-model="role" required>
            <option value="">Select your role</option>
            <option value="seller">Seller</option>
            <option value="buyer">Buyer</option>
          </select>
        </div>

        <button type="submit" class="login-btn">Login</button>
      </form>

      <p class="register-link">
        Don't have an account?
        <router-link to="/register">Register here</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const username = ref('')
const password = ref('')
const role = ref('')

const handleLogin = async () => {
  try {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        username: username.value,
        password: password.value,
        role: role.value
      })
    })

    const result = await response.json()

    if (result.status === 'success') {
      localStorage.setItem('token', result.token)
      localStorage.setItem('userRole', result.role)
      localStorage.setItem('username', username.value)
      localStorage.setItem('userId', result.userId)
      localStorage.setItem('avatar', result.avatar || '')
      alert('Login successful!')

      if (role.value === 'buyer') {
        router.push('/home')
      } else {
        router.push('/seller-dashboard')
      }
    } else {
      alert(result.message)
    }

  } catch (error) {
    console.error('Login failed:', error)
    alert('Login failed. Please try again.')
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

.login-box {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 500px;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

h2 {
  text-align: center;
  color: #333;
  margin-bottom: 2rem;
  font-size: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.75rem;
  color: #333;
  font-size: 1.1rem;
}

input,
select {
  width: 100%;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1.1rem;
}

.login-btn {
  width: 100%;
  padding: 1rem;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1.1rem;
  cursor: pointer;
  margin-top: 1.5rem;
  transition: background-color 0.3s ease;
}

.login-btn:hover {
  background-color: #45a049;
}

.register-link {
  text-align: center;
  margin-top: 1.5rem;
  color: #333;
  font-size: 1.1rem;
}

.register-link a {
  color: #4CAF50;
  text-decoration: none;
  font-weight: 500;
}

.register-link a:hover {
  text-decoration: underline;
}
</style>