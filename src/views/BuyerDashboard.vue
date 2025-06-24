<template>
  <div class="dashboard-container">
    <!-- Navigation Sidebar -->
    <div class="sidebar">
      <div style="text-align: center; margin-top: 20px;">
        <img :src=" userAvatar|| defaultAvatar" alt="User Avatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;" @error="userAvatar = defaultAvatar"/>
        <h3>{{ userName }}</h3>
      </div>
      <br>
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
        <!-- Logout button at the bottom -->
        <div class="logout-section">
          <button class="logout-btn" @click="logout">
            <i class="fas fa-sign-out-alt"></i>
            Logout
          </button>
        </div>
      </nav>
      
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
          <input type="file" id="avatarUpload" @change="handleAvatarUpload" class="avatar-upload"
            style="display: none;" />
          <label for="avatarUpload" class="upload-label">Upload Profile Picture</label>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
/**
 * BuyerDashboard component
 * Provides a comprehensive dashboard for buyers including order history,
 * favorite products and profile management
 */
 export default {
  name: 'BuyerDashboard',
  data() {
    return {
      activeSection: 'orders',
      userAvatar: '',
      userName: '',
      defaultAvatar: 'src/assets/picture/profile.jpg',
      menuItems: [
        { id: 'orders', name: 'Orders', icon: 'fas fa-shopping-bag' },
        { id: 'favorites', name: 'Favorites', icon: 'fas fa-heart' },
        { id: 'profile', name: 'Profile', icon: 'fas fa-user' }
      ],
      orders: [
        {
          id: '12345',
          status: 'Delivered',
          productName: 'Handmade Ceramic Mug',
          productImage: 'https://via.placeholder.com/100',
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
          image: 'https://via.placeholder.com/100'
        },
        // Add more mock favorites as needed
      ],
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
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },

    removeFavorite(productId) {
      this.favorites = this.favorites.filter(product => product.id !== productId);
    },

    goHome() {
      this.$router.push({ name: 'home' });
    },

    logout() {
      localStorage.removeItem('username');
      localStorage.removeItem('token');
      this.$router.push({ name: 'login' });
    },

    async handleAvatarUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('avatar', file);

      const username = localStorage.getItem('username');
      formData.append('username', username);

      try {
        const response = await fetch('/api/upload-avatar', {
          method: 'POST',
          body: formData
        });

        const responseText = await response.text();
        let result;

        try {
          result = JSON.parse(responseText);
        } catch (e) {
          console.error('Upload failed (non-JSON):', responseText);
          alert('Upload failed: Invalid server response.');
          return;
        }

        if (response.ok && result.status === 'success') {
          const avatarUrl = result.avatarUrl;
          localStorage.setItem('avatar', avatarUrl);
          this.userAvatar = "../backend/src/uploads/avatars/" + avatarUrl;
          alert('Profile picture uploaded successfully!');
        } else {
          console.error('Upload error response:', result);
          alert('Upload failed: ' + (result.message || 'Unknown error'));
        }
      } catch (error) {
        console.error('Avatar upload error:', error);
        alert('There was a problem uploading your profile picture.');
      }
    },

    async fetchProfile() {
      try {
        const username = localStorage.getItem('username');
        if (!username) throw new Error('No username found');

        const response = await fetch(`/api/profile/${encodeURIComponent(username)}`);
        if (!response.ok) throw new Error('Failed to fetch profile');

        const data = await response.json();

        if (data.status === 'success') {
          this.userName = data.data.username;
          this.profile.username = data.data.username;
          this.profile.fullname = data.data.fullname;
          this.profile.email = data.data.email;
          this.profile.phone = data.data.phone;
          this.profile.address = data.data.address;

          if (data.data.avatar) {
            this.userAvatar = "../backend/src/uploads/avatars/" + data.data.avatar;
          }
        } else {
          throw new Error(data.message || 'Profile not found');
        }
      } catch (error) {
        console.error('Error fetching profile:', error);
      }
    },

    async saveProfile() {
      try {
        const response = await fetch('/api/profile/update', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
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
    }
  },
  mounted() {
    this.fetchProfile();

    localStorage.getItem('avatar') && (this.userAvatar = "../backend/src/uploads/avatars/" + localStorage.getItem('avatar'))
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

.upload-label {
  display: inline-block;
  margin-top: 10px;
  margin-left: 10px;
  padding: 8px 16px;
  background-color: #4c90af;
  color: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
}

.upload-label:hover {
  background-color: #4595a0;
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
  padding-top: 420px;
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