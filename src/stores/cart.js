import { defineStore } from 'pinia'
import { cartApi } from '../services/api'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    shipping: 5.99,
    loading: false,
    error: null
  }),

  getters: {
    itemCount: (state) => state.items.reduce((total, item) => total + item.quantity, 0),
    subtotal: (state) => state.items.reduce((total, item) => total + (item.price * item.quantity), 0),
    total: (state) => state.subtotal + state.shipping
  },

  actions: {
    async fetchCart() {
      this.loading = true
      this.error = null
      try {
        const username = localStorage.getItem('username')
        if (!username) throw new Error('User not logged in')
        const response = await cartApi.getCart(username)
        this.items = response.data
      } catch (error) {
        this.error = error.message
        console.error('Error fetching cart:', error)
      } finally {
        this.loading = false
      }
    },

    async addItem(product) {
      this.loading = true
      this.error = null
      try {
        const username = localStorage.getItem('username')
        const response = await cartApi.addToCart(username, product)
        this.items = response.data
      } catch (error) {
        this.error = error.message
        console.error('Error adding item to cart:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async removeItem(productId) {
      this.loading = true
      this.error = null
      try {
        const response = await cartApi.removeFromCart(productId)
        this.items = response.data
      } catch (error) {
        this.error = error.message
        console.error('Error removing item from cart:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateQuantity(productId, quantity) {
      this.loading = true
      this.error = null
      try {
        const response = await cartApi.updateQuantity(productId, quantity)
        this.items = response.data
      } catch (error) {
        this.error = error.message
        console.error('Error updating cart item quantity:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async clearCart() {
      this.loading = true
      this.error = null
      try {
        const username = localStorage.getItem('username')
        await cartApi.clearCart(username)
        this.items = []
      } catch (error) {
        this.error = error.message
        console.error('Error clearing cart:', error)
        throw error
      } finally {
        this.loading = false
      }
    }
  },

  persist: {
    enabled: true,
    strategies: [
      {
        key: 'cart',
        storage: localStorage,
        paths: ['items', 'shipping']
      }
    ]
  }
})