import { defineStore } from 'pinia'
import { cartApi } from '../services/api'

/**
 * Cart store for managing shopping cart state
 * Handles cart operations like adding, removing, and updating items
 */
export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    shipping: 5.99,
    loading: false,
    error: null
  }),

  getters: {
    /**
     * Calculate the total number of items in the cart
     * @returns {number} Total number of items
     */
    itemCount: (state) => {
      return state.items.reduce((total, item) => total + item.quantity, 0)
    },

    /**
     * Calculate the subtotal of all items in the cart
     * @returns {number} The subtotal amount
     */
    subtotal: (state) => {
      return state.items.reduce((total, item) => {
        return total + (item.price * item.quantity)
      }, 0)
    },

    /**
     * Calculate the total including shipping
     * @returns {number} The total amount
     */
    total: (state) => {
      return state.subtotal + state.shipping
    }
  },

  actions: {
    /**
     * Fetch cart items from the server
     */
    async fetchCart() {
      this.loading = true
      this.error = null
      try {
        const response = await cartApi.getCart()
        this.items = response.data
      } catch (error) {
        this.error = error.message
        console.error('Error fetching cart:', error)
      } finally {
        this.loading = false
      }
    },

    /**
     * Add an item to the cart
     * @param {Object} product - The product to add
     */
    async addItem(product) {
      this.loading = true
      this.error = null
      try {
        const response = await cartApi.addToCart(product)
        this.items = response.data
      } catch (error) {
        this.error = error.message
        console.error('Error adding item to cart:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Remove an item from the cart
     * @param {number} productId - The ID of the product to remove
     */
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

    /**
     * Update the quantity of an item
     * @param {number} productId - The ID of the product to update
     * @param {number} quantity - The new quantity
     */
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

    /**
     * Clear all items from the cart
     */
    async clearCart() {
      this.loading = true
      this.error = null
      try {
        await cartApi.clearCart()
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