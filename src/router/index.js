import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Home from '../views/Home.vue'
import ProductDetail from '../views/ProductDetail.vue'
import Cart from '../views/Cart.vue'
import BuyerDashboard from '../views/BuyerDashboard.vue'
import StorePage from '../views/StorePage.vue'
import SellerDashboard from '../views/SellerDashboard.vue'
import { jwtDecode } from 'jwt-decode'
/**
 * Router configuration for the application
 * Defines routes for login, registration, home pages, and product details
 */
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login'
    },
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    {
      path: '/register',
      name: 'register',
      component: Register
    },
    {
      path: '/home',
      name: 'home',
      component: Home,
      meta: { requiresAuth: true }
    },
    {
      path: '/product/:id',
      name: 'product-detail',
      component: ProductDetail,
      meta: { requiresAuth: true }
    },
    {
      path: '/cart',
      name: 'cart',
      component: Cart,
      meta: { requiresAuth: true }
    },
    {
      path: '/buyer-dashboard',
      name: 'buyer-dashboard',
      component: BuyerDashboard,
      meta: { requiresAuth: true }
    },
    {
      path: '/seller-dashboard',
      name: 'seller-dashboard',
      component: SellerDashboard,
      meta: { requiresAuth: true }
    },
    {
      path: '/store/:id',
      name: 'StorePage',
      component: StorePage
    }
  ]
})

/**
 * Navigation guard to check authentication
 * Redirects to login page if route requires authentication and user is not logged in
 */
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const userRole = localStorage.getItem('userRole')

  if (token) {
    try {
      const decoded = jwtDecode(token)
      const isExpired = decoded.exp * 1000 < Date.now()

      if (isExpired) {
        // Token expired, clear storage and redirect
        localStorage.clear()
        alert('Session expired. Please log in again.')
        return next('/login')
      }
    } catch (err) {
      // Invalid token format
      localStorage.clear()
      alert('Invalid session. Please log in again.')
      return next('/login')
    }
  }

  const isAuthenticated = !!token

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (to.path === '/login' && isAuthenticated) {
    if (userRole === 'buyer') {
      next('/home')
    } else {
      next('/seller-dashboard')
    }
  } else if (
    ['home', 'cart', 'product-detail', 'buyer-dashboard'].includes(to.name) &&
    userRole !== 'buyer'
  ) {
    next('/seller-dashboard')
  } else if (to.name === 'seller-dashboard' && userRole !== 'seller') {
    next('/home')
  } else {
    next()
  }
})

export default router 
