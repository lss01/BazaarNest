<template>
  <div class="dashboard-container">
    <!-- Navigation Sidebar -->
    <div class="sidebar">
      <div class="user-info">
        <img :src="userAvatar" alt="User Avatar" class="avatar" />
        <h3>{{ userName }}</h3>
      </div>
      <nav class="nav-menu">
        <div v-for="item in menuItems" :key="item.id" :class="['nav-item', { active: activeSection === item.id }]"
          @click="activeSection = item.id">
          <i :class="item.icon"></i>
          <span>{{ item.name }}</span>
        </div>
        <!-- Back to Home button -->
        <div class="nav-item back-home" @click="goHome">
          <i class="fas fa-home"></i>
          <span>Back to Home</span>
        </div>
      </nav>
      <!-- Logout button at the bottom -->
      <div class="logout-section">
        <button class="logout-btn" @click="logout">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </button>
      </div>
    </div>


    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Orders Section -->
      <div v-if="activeSection === 'orders'" class="section">
        <h2>Order History</h2>
        <div class="orders-grid">
          <div v-for="order in orders" :key="order.id" class="order-card">
            <div class="order-header">
              <span class="order-id">Order #{{ order.id }}</span>
              <span :class="['order-status', order.status.toLowerCase()]">{{ order.status }}</span>
            </div>
            <div class="order-details">
              <img :src="order.productImage" :alt="order.productName" class="product-image" />
              <div class="product-info">
                <h4>{{ order.productName }}</h4>
                <p>Quantity: {{ order.quantity }}</p>
                <p>Total: ${{ order.total }}</p>
              </div>
            </div>
            <div class="order-date">{{ formatDate(order.date) }}</div>
          </div>
        </div>
      </div>

      <!-- Favorites Section -->
      <div v-if="activeSection === 'favorites'" class="section">
        <h2>Favorite Products</h2>
        <div class="favorites-grid">
          <div v-for="product in favorites" :key="product.id" class="favorite-card">
            <img :src="product.image" :alt="product.name" class="product-image" />
            <div class="product-info">
              <h4>{{ product.name }}</h4>
              <p class="price">${{ product.price }}</p>
              <button class="remove-btn" @click="removeFavorite(product.id)">
                Remove from Favorites
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Account Settings Section -->
      <div v-if="activeSection === 'settings'" class="section">
        <h2>Account Settings</h2>
        <div class="settings-form">
          <div class="form-group">
            <label>Email Notifications</label>
            <div class="toggle-switch">
              <input type="checkbox" v-model="settings.emailNotifications" id="email-notifications">
              <label for="email-notifications"></label>
            </div>
          </div>
          <div class="form-group">
            <label>Two-Factor Authentication</label>
            <div class="toggle-switch">
              <input type="checkbox" v-model="settings.twoFactorAuth" id="two-factor">
              <label for="two-factor"></label>
            </div>
          </div>
          <button class="save-btn" @click="saveSettings">Save Settings</button>
        </div>
      </div>

      <!-- Profile Section -->
      <div v-if="activeSection === 'profile'" class="section">
        <h2>Edit Profile</h2>
        <div class="profile-form">
          <div class="form-group">
            <label>Username</label>
            <input type="text" v-model="profile.username" />
          </div>
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" v-model="profile.fullname" />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" v-model="profile.email" />
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input type="tel" v-model="profile.phone" />
          </div>
          <div class="form-group">
            <label>Address</label>
            <textarea v-model="profile.address"></textarea>
          </div>
          <button class="save-btn" @click="saveProfile">Save Profile</button>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
/**
 * BuyerDashboard component
 * Provides a comprehensive dashboard for buyers including order history,
 * favorite products, account settings, and profile management
 */
export default {
  name: 'BuyerDashboard',
  data() {
    return {
      activeSection: 'orders',
      userName: 'John Doe',
      userAvatar: 'src/assets/picture/profile.jpg',
      menuItems: [
        { id: 'orders', name: 'Orders', icon: 'fas fa-shopping-bag' },
        { id: 'favorites', name: 'Favorites', icon: 'fas fa-heart' },
        { id: 'settings', name: 'Settings', icon: 'fas fa-cog' },
        { id: 'profile', name: 'Profile', icon: 'fas fa-user' }
      ],
      orders: [
        {
          id: '12345',
          status: 'Delivered',
          productName: 'Handmade Ceramic Mug',
          productImage: 'src/assets/picture/Handmade_Ceramic_Mug.jpg',
          quantity: 2,
          total: 49.98,
          date: '2024-03-15'
        },
        // Add more mock orders as needed
      ],
      favorites: [
        {
          id: 1,
          name: 'Organic Cotton T-shirt',
          price: 29.99,
          image: 'src/assets/picture/Organic_Cotton_T_shirt.jpg'
        },
        // Add more mock favorites as needed
      ],
      settings: {
        emailNotifications: true,
        twoFactorAuth: false
      },
      profile: {
        username: '',
        fullName: '',
        email: '',
        phone: '',
        address: ''
      }
    }
  },
  methods: {
    /**
     * Formats date to a readable string
     * @param {string} date - Date string to format
     * @returns {string} Formatted date string
     */
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },

    /**
     * Removes a product from favorites
     * @param {number} productId - ID of the product to remove
     */
    removeFavorite(productId) {
      this.favorites = this.favorites.filter(product => product.id !== productId)
    },

    /**
     * Saves user settings
     */
    saveSettings() {
      // TODO: Implement API call to save settings
      console.log('Saving settings:', this.settings)
    },

    goHome() {
      this.$router.push({ name: 'home' });
    },

    logout() {
      // Clear authentication data
      localStorage.removeItem('username')
      localStorage.removeItem('token') // only if you're storing JWTs

      // Redirect to login route
      this.$router.push({ name: 'login' }) // make sure this route exists
    },

    /**
     * Saves user profile information
     */
    async fetchProfile() {
      try {
        const username = localStorage.getItem('username')
        // Check if username exists in localStorage
        if (!username) {
          throw new Error('No username found in localStorage')
        }
        const response = await fetch(`/api/profile/${encodeURIComponent(username)}`);
        if (!response.ok) {
          throw new Error('Failed to fetch profile');
        }

        const data = await response.json();

        if (data.status === 'success') {
          this.profile.username = data.data.username
          this.profile.fullname = data.data.fullname
          this.profile.email = data.data.email
          this.profile.phone = data.data.phone
          this.profile.address = data.data.address
        } else {

          throw new Error(data.message || 'Profile not found')
        }
      } catch (error) {
        console.error('Error fetching profile:', error);
      }
    },
    async saveProfile() {
      try {
        const response = await fetch('/api/profile/update', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.profile)
        });

        const data = await response.json();

        if (response.ok && data.status === 'success') {
          alert('Profile updated successfully!');
        } else {
          throw new Error(data.message || 'Failed to update profile');
        }
      } catch (error) {
        console.error('Error updating profile:', error);
        alert('There was a problem saving your profile.');
      }
    },

  },
  mounted() {
    // Fetch user profile data when the component is mounted
    this.fetchProfile()
  },
}
</script>

<style scoped>
.dashboard-container {
  display: flex;
  min-height: 100vh;
  background-color: #f5f5f5;
}

.sidebar {
  width: 250px;
  background-color: white;
  padding: 20px;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

.user-info {
  text-align: center;
  margin-bottom: 30px;
}

.avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  margin-bottom: 10px;
}

.nav-menu {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  cursor: pointer;
  border-radius: 8px;
  transition: background-color 0.3s;
}

.nav-item:hover {
  background-color: #f0f0f0;
}

.nav-item.active {
  background-color: #4CAF50;
  color: white;
}

.main-content {
  flex: 1;
  padding: 30px;
}

.section {
  background-color: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.orders-grid,
.favorites-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.order-card,
.favorite-card {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 15px;
}

.order-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.order-status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
}

.order-status.delivered {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.order-details {
  display: flex;
  gap: 15px;
}

.product-image {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 4px;
}

.settings-form,
.profile-form {
  max-width: 500px;
  margin-top: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-switch label {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}

.toggle-switch label:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

.toggle-switch input:checked+label {
  background-color: #4CAF50;
}

.toggle-switch input:checked+label:before {
  transform: translateX(26px);
}

.save-btn {
  background-color: #4CAF50;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1em;
}

.save-btn:hover {
  background-color: #45a049;
}

.remove-btn {
  background-color: #ff5252;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9em;
}

.remove-btn:hover {
  background-color: #ff1744;
}

.nav-item.back-home {
  margin-top: 20px;
  border-top: 1px solid #eee;
  padding-top: 16px;
}

.logout-section {
  margin-top: auto;
  padding-top: 350px;
}

.logout-btn {
  width: 100%;
  /* background-color: #ff5252; */
  color: grey;
  padding: 12px 0;
  border: none;
  border-radius: 6px;
  font-size: 1.1em;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: background-color 0.2s;
}

.logout-btn:hover {
  background-color: #ff1744;
  color: #eee;
}
</style>