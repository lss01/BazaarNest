import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import App from './App.vue'
import router from './router'
import '@fortawesome/fontawesome-free/css/all.css'
import { jwtDecode } from 'jwt-decode'


const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

const app = createApp(App)
app.use(router)
app.use(pinia)
app.mount('#app')

const decoded = jwtDecode(token);
console.log(decoded.exp);