<template>
  <div class="home-container">
    <!-- User and Cart Section -->
    <div class="user-cart-section">
      <div class="cart-icon" @click="goToCart">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count" v-if="cartStore.itemCount > 0">{{ cartStore.itemCount }}</span>
      </div>
      <div class="user-avatar" @click="toggleUserMenu">
        <img :src="userAvatar" alt="User Avatar" />
        <div class="user-menu" v-if="showUserMenu">
          <div class="menu-item" @click="goToProfile">Profile</div>
          <div class="menu-item" @click="goToOrders">Orders</div>
          <div class="menu-item" @click="logout">Logout</div>
        </div>
      </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-filter-section">
      <div class="search-bar">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search products..."
          @input="handleSearch"
        />
        <button @click="handleSearch">Search</button>
      </div>
      
      <div class="filters">
        <div class="filter-group">
          <label>Category:</label>
          <select v-model="selectedCategory">
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </option>
          </select>
        </div>

        <div class="filter-group">
          <label>Price Range:</label>
          <select v-model="priceRange">
            <option value="">Any Price</option>
            <option value="0-50">$0 - $50</option>
            <option value="51-100">$51 - $100</option>
            <option value="101-200">$101 - $200</option>
            <option value="201+">$201+</option>
          </select>
        </div>

        <div class="filter-group">
          <label>
            <input type="checkbox" v-model="filters.local"> Local Sellers
          </label>
        </div>

        <div class="filter-group">
          <label>
            <input type="checkbox" v-model="filters.handmade"> Handmade
          </label>
        </div>

        <div class="filter-group">
          <label>
            <input type="checkbox" v-model="filters.sustainable"> Sustainable
          </label>
        </div>
      </div>
    </div>

    <!-- Products Display Section -->
    <div class="products-grid">
      <div v-if="loading" class="loading">Loading...</div>
      <div v-else-if="products.length === 0" class="no-results">
        No products found matching your criteria
      </div>
      <div v-else class="product-card" v-for="product in products" :key="product.id" @click="goToProductDetail(product.id)">
        <img :src="product.image" :alt="product.name" class="product-image">
        <div class="product-info">
          <h3 class="product-name">{{ product.name }}</h3>
          <p class="seller-name">{{ product.sellerName }}</p>
          <p class="product-price">${{ product.price }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useCartStore } from '../stores/cart'

/**
 * Home page component for the e-commerce platform
 * Displays products with search and filter functionality
 */
export default {
  name: 'Home',
  setup() {
    const cartStore = useCartStore()
    return { cartStore }
  },
  data() {
    return {
      searchQuery: '',
      selectedCategory: '',
      priceRange: '',
      filters: {
        local: false,
        handmade: false,
        sustainable: false
      },
      categories: [
        'Clothing',
        'Home & Garden',
        'Art',
        'Jewelry',
        'Electronics',
        'Beauty',
        'Food & Drink'
      ],
      products: [],
      loading: false,
      userAvatar: 'src/assets/picture/profile.jpg',
      showUserMenu: false
    }
  },
  methods: {
    /**
     * Handles the search functionality
     * Fetches products based on search query and filters
     */
    async handleSearch() {
      this.loading = true
      try {
        // TODO: Implement actual API call
        // This is a mock implementation
        await new Promise(resolve => setTimeout(resolve, 1000))
        this.products = this.getMockProducts()
      } catch (error) {
        console.error('Error fetching products:', error)
      } finally {
        this.loading = false
      }
    },

    /**
     * Generates mock product data for demonstration
     * @returns {Array} Array of mock product objects
     */
    getMockProducts() {
      return [
        {
          id: 1,
          name: 'Handmade Ceramic Mug',
          sellerName: 'Artisan Pottery',
          price: 24.99,
          image: 'src/assets/picture/Handmade_Ceramic_Mug.jpg'
        },
        {
          id: 2,
          name: 'Organic Cotton T-shirt',
          sellerName: 'Eco Fashion',
          price: 29.99,
          image: 'src/assets/picture/Organic_Cotton_T_shirt.jpg'
        },
        // Add more mock products as needed
      ]
    },

    /**
     * Navigates to the product detail page
     * @param {number} productId - The ID of the product to view
     */
    goToProductDetail(productId) {
      this.$router.push(`/product/${productId}`)
    },

    /**
     * Toggles the user menu visibility
     */
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu
    },

    /**
     * Navigates to the buyer dashboard page
     */
    goToProfile() {
      this.$router.push('/buyer-dashboard')
    },

    /**
     * Navigates to the user's orders page
     */
    goToOrders() {
      this.$router.push('/orders')
    },

    /**
     * Navigates to the shopping cart page
     */
    goToCart() {
      this.$router.push('/cart')
    },

    /**
     * Handles user logout
     * Clears authentication token and redirects to login page
     */
    logout() {
      // 清除认证token
      localStorage.removeItem('token')
      // 清除购物车数据
      this.cartStore.clearCart()
      // 跳转到登录页面
      this.$router.push('/login')
    }
  },
  mounted() {
    // Load initial products when component is mounted
    this.handleSearch()
  }
}
</script>

<style scoped>
.home-container {
  max-width: 100%;
  margin: 0;
  padding: 0;
}

.search-filter-section {
  position: sticky;
  top: 0;
  background-color: white;
  z-index: 100;
  padding: 10px 20px;
  border-bottom: 1px solid #eee;
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.search-bar {
  display: flex;
  gap: 10px;
  width: 100%;
  max-width: 600px;
}

.search-bar input {
  flex: 1;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.search-bar button {
  padding: 10px 20px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  align-items: center;
  padding: 5px 0;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 5px;
  white-space: nowrap;
}

.filter-group select {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background-color: white;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.product-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.2s;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.product-info {
  padding: 15px;
}

.product-name {
  margin: 0 0 10px 0;
  font-size: 1.1em;
}

.seller-name {
  color: #666;
  margin: 0 0 5px 0;
  font-size: 0.9em;
}

.product-price {
  font-weight: bold;
  color: #4CAF50;
  margin: 0;
}

.loading, .no-results {
  grid-column: 1 / -1;
  text-align: center;
  padding: 20px;
  color: #666;
}

.user-cart-section {
  position: fixed;
  top: 20px;
  right: 20px;
  display: flex;
  align-items: center;
  gap: 20px;
  z-index: 1000;
}

.cart-icon {
  position: relative;
  cursor: pointer;
  font-size: 24px;
  color: #333;
}

.cart-count {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #4CAF50;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 12px;
}

.user-avatar {
  position: relative;
  cursor: pointer;
}

.user-avatar img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #4CAF50;
}

.user-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background-color: white;
  border-radius: 4px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  margin-top: 10px;
  min-width: 150px;
}

.menu-item {
  padding: 10px 15px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.menu-item:hover {
  background-color: #f5f5f5;
}
</style> 