import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Home from '../views/Home.vue'
import ProductDetail from '../views/ProductDetail.vue'
import Cart from '../views/Cart.vue'
import BuyerDashboard from '../views/BuyerDashboard.vue'
import StorePage from '../views/StorePage.vue'
import SellerDashboard from '../views/SellerDashboard.vue'
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
  const isAuthenticated = localStorage.getItem('token')
  const userRole = localStorage.getItem('userRole')

  if (to.meta.requiresAuth && !isAuthenticated) {
    // Not logged in but trying to visit a protected page
    next('/login')
  } else if (to.path === '/login' && isAuthenticated) {
    // Logged in and visiting /login again
    if (userRole === 'buyer') {
      next('/home')
    } else {
      next('/seller-dashboard')
    }
  } else if (['/home', '/cart', '/product/:id', '/buyer-dashboard'].includes(to.path) && userRole !== 'buyer') {
    // Seller tries to visit buyer pages
    next('/seller-dashboard')
  } else if (to.path === '/seller-dashboard' && userRole !== 'seller') {
    // Buyer tries to visit seller dashboard
    next('/home')
  } else {
    next()
  }
})

export default router 
