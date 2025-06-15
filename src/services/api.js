import axios from 'axios'

/**
 * API service for handling backend communication
 */
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080/api',
  timeout: 5000,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Request interceptor for adding auth token
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// Response interceptor for handling errors
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Handle unauthorized access
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

/**
 * Product related API calls
 */
export const productApi = {
  /**
   * Get product details by ID
   * @param {number} id - Product ID
   * @returns {Promise} Product details
   */
  getProduct(id) {
    return api.get(`/products/${id}`)
  },

  /**
   * Get similar products
   * @param {number} productId - Current product ID
   * @returns {Promise} Similar products
   */
  getSimilarProducts(productId) {
    return api.get(`/products/${productId}/similar`)
  }
}

/**
 * Cart related API calls
 */
export const cartApi = {
  /**
   * Get cart items
   * @returns {Promise} Cart items
   */
  getCart() {
    return api.get('/cart')
  },

  /**
   * Add item to cart
   * @param {Object} item - Cart item
   * @returns {Promise} Updated cart
   */
  addToCart(item) {
    return api.post('/cart', item)
  },

  /**
   * Update cart item quantity
   * @param {number} itemId - Cart item ID
   * @param {number} quantity - New quantity
   * @returns {Promise} Updated cart
   */
  updateQuantity(itemId, quantity) {
    return api.put(`/cart/${itemId}`, { quantity })
  },

  /**
   * Remove item from cart
   * @param {number} itemId - Cart item ID
   * @returns {Promise} Updated cart
   */
  removeFromCart(itemId) {
    return api.delete(`/cart/${itemId}`)
  }
}

export default api 