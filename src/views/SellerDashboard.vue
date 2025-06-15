<template>
  <div class="dashboard-container">
    <!-- Navigation Sidebar -->
    <div class="sidebar">
      <div class="user-info">
        <img :src="userAvatar" alt="User Avatar" class="avatar" />
        <h3>{{ userName }}</h3>
      </div>
      <nav class="nav-menu">
        <div 
          v-for="item in menuItems" 
          :key="item.id"
          :class="['nav-item', { active: activeSection === item.id }]"
          @click="activeSection = item.id"
        >
          <i :class="item.icon"></i>
          <span>{{ item.name }}</span>
        </div>
      </nav>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Products Management Section -->
      <div v-if="activeSection === 'products'" class="section">
        <div class="section-header">
          <h2>Product Management</h2>
          <button class="add-btn" @click="showAddProductModal = true">
            <i class="fas fa-plus"></i> Add New Product
          </button>
        </div>
        
        <div class="products-grid">
          <div v-for="product in products" :key="product.id" class="product-card">
            <img :src="product.image" :alt="product.name" class="product-image" />
            <div class="product-info">
              <h4>{{ product.name }}</h4>
              <p class="price">${{ product.price }}</p>
              <p class="stock">Stock: {{ product.stock }}</p>
              <div class="product-actions">
                <button class="edit-btn" @click="editProduct(product)">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button class="delete-btn" @click="deleteProduct(product.id)">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Orders Tracking Section -->
      <div v-if="activeSection === 'orders'" class="section">
        <h2>Order Tracking</h2>
        <div class="orders-grid">
          <div v-for="order in orders" :key="order.id" class="order-card">
            <div class="order-header">
              <span class="order-id">Order #{{ order.id }}</span>
              <span :class="['order-status', order.status.toLowerCase()]">{{ order.status }}</span>
            </div>
            <div class="order-details">
              <div class="customer-info">
                <h4>Customer: {{ order.customerName }}</h4>
                <p>Email: {{ order.customerEmail }}</p>
              </div>
              <div class="order-items">
                <div v-for="item in order.items" :key="item.id" class="order-item">
                  <img :src="item.image" :alt="item.name" class="item-image" />
                  <div class="item-info">
                    <p>{{ item.name }}</p>
                    <p>Quantity: {{ item.quantity }}</p>
                    <p>Price: ${{ item.price }}</p>
                  </div>
                </div>
              </div>
              <div class="order-total">
                <p>Total: ${{ order.total }}</p>
                <button class="update-status-btn" @click="updateOrderStatus(order)">
                  Update Status
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Store Customization Section -->
      <div v-if="activeSection === 'customization'" class="section">
        <h2>Store Customization</h2>
        <div class="customization-container">
          <div class="components-panel">
            <h3>Available Components</h3>
            <div 
              v-for="component in availableComponents" 
              :key="component.id"
              class="component-item"
              draggable="true"
              @dragstart="dragStart($event, component)"
            >
              <i :class="component.icon"></i>
              <span>{{ component.name }}</span>
            </div>
          </div>
          
          <div 
            class="store-preview"
            @dragover.prevent
            @drop="dropComponent"
          >
            <h3>Store Layout</h3>
            <div 
              v-for="(component, index) in storeLayout" 
              :key="index"
              class="layout-component"
              draggable="true"
              @dragstart="dragStart($event, component, index)"
              @dragover.prevent
              @drop="reorderComponent($event, index)"
            >
              <i :class="component.icon"></i>
              <span>{{ component.name }}</span>
              <button class="remove-component" @click="removeComponent(index)">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div v-if="showAddProductModal" class="modal">
      <div class="modal-content">
        <h3>{{ editingProduct ? 'Edit Product' : 'Add New Product' }}</h3>
        <form @submit.prevent="saveProduct">
          <div class="form-group">
            <label>Product Name</label>
            <input type="text" v-model="productForm.name" required />
          </div>
          <div class="form-group">
            <label>Price</label>
            <input type="number" v-model="productForm.price" step="0.01" required />
          </div>
          <div class="form-group">
            <label>Stock</label>
            <input type="number" v-model="productForm.stock" required />
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="productForm.description" required></textarea>
          </div>
          <div class="form-group">
            <label>Image URL</label>
            <input type="url" v-model="productForm.image" required />
          </div>
          <div class="modal-actions">
            <button type="button" class="cancel-btn" @click="showAddProductModal = false">Cancel</button>
            <button type="submit" class="save-btn">Save Product</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * SellerDashboard component
 * Provides a comprehensive dashboard for sellers including product management,
 * order tracking, and store customization features
 */
export default {
  name: 'SellerDashboard',
  data() {
    return {
      activeSection: 'products',
      userName: 'Jane Smith',
      userAvatar: 'https://via.placeholder.com/100',
      menuItems: [
        { id: 'products', name: 'Products', icon: 'fas fa-box' },
        { id: 'orders', name: 'Orders', icon: 'fas fa-shopping-cart' },
        { id: 'customization', name: 'Store Customization', icon: 'fas fa-paint-brush' }
      ],
      products: [
        {
          id: 1,
          name: 'Handmade Ceramic Mug',
          price: 24.99,
          stock: 50,
          image: 'https://via.placeholder.com/100',
          description: 'Beautiful handmade ceramic mug'
        }
      ],
      orders: [
        {
          id: '12345',
          status: 'Processing',
          customerName: 'John Doe',
          customerEmail: 'john@example.com',
          items: [
            {
              id: 1,
              name: 'Handmade Ceramic Mug',
              quantity: 2,
              price: 24.99,
              image: 'https://via.placeholder.com/100'
            }
          ],
          total: 49.98
        }
      ],
      availableComponents: [
        { id: 1, name: 'Featured Products', icon: 'fas fa-star' },
        { id: 2, name: 'Categories', icon: 'fas fa-tags' },
        { id: 3, name: 'Banner', icon: 'fas fa-image' },
        { id: 4, name: 'Testimonials', icon: 'fas fa-quote-right' }
      ],
      storeLayout: [],
      showAddProductModal: false,
      editingProduct: null,
      productForm: {
        name: '',
        price: '',
        stock: '',
        description: '',
        image: ''
      }
    }
  },
  methods: {
    /**
     * Edit product details
     * @param {Object} product - Product to edit
     */
    editProduct(product) {
      this.editingProduct = product
      this.productForm = { ...product }
      this.showAddProductModal = true
    },

    /**
     * Delete product
     * @param {number} productId - ID of product to delete
     */
    deleteProduct(productId) {
      if (confirm('Are you sure you want to delete this product?')) {
        this.products = this.products.filter(p => p.id !== productId)
      }
    },

    /**
     * Save product (create or update)
     */
    saveProduct() {
      if (this.editingProduct) {
        const index = this.products.findIndex(p => p.id === this.editingProduct.id)
        this.products[index] = { ...this.editingProduct, ...this.productForm }
      } else {
        this.products.push({
          id: Date.now(),
          ...this.productForm
        })
      }
      this.showAddProductModal = false
      this.editingProduct = null
      this.productForm = {
        name: '',
        price: '',
        stock: '',
        description: '',
        image: ''
      }
    },

    /**
     * Update order status
     * @param {Object} order - Order to update
     */
    updateOrderStatus(order) {
      // TODO: Implement order status update logic
      console.log('Updating order status:', order)
    },

    /**
     * Handle drag start event
     * @param {DragEvent} event - Drag event
     * @param {Object} component - Component being dragged
     * @param {number} index - Index of component in layout
     */
    dragStart(event, component, index) {
      event.dataTransfer.setData('text/plain', JSON.stringify({ component, index }))
    },

    /**
     * Handle component drop
     * @param {DragEvent} event - Drop event
     */
    dropComponent(event) {
      const data = JSON.parse(event.dataTransfer.getData('text/plain'))
      if (data.index === undefined) {
        this.storeLayout.push(data.component)
      }
    },

    /**
     * Reorder components in layout
     * @param {DragEvent} event - Drop event
     * @param {number} newIndex - New index for component
     */
    reorderComponent(event, newIndex) {
      const data = JSON.parse(event.dataTransfer.getData('text/plain'))
      if (data.index !== undefined) {
        const [removed] = this.storeLayout.splice(data.index, 1)
        this.storeLayout.splice(newIndex, 0, removed)
      }
    },

    /**
     * Remove component from layout
     * @param {number} index - Index of component to remove
     */
    removeComponent(index) {
      this.storeLayout.splice(index, 1)
    }
  }
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
  box-shadow: 2px 0 5px rgba(0,0,0,0.1);
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
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.add-btn {
  background-color: #4CAF50;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.product-card {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 15px;
}

.product-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 4px;
}

.product-info {
  margin-top: 10px;
}

.product-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.edit-btn, .delete-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
}

.edit-btn {
  background-color: #2196F3;
  color: white;
}

.delete-btn {
  background-color: #f44336;
  color: white;
}

.orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 20px;
}

.order-card {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 15px;
}

.order-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
}

.order-status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
}

.order-status.processing {
  background-color: #fff3e0;
  color: #e65100;
}

.order-status.shipped {
  background-color: #e3f2fd;
  color: #1565c0;
}

.order-status.delivered {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.customization-container {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 20px;
}

.components-panel {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
}

.component-item {
  padding: 10px;
  margin: 5px 0;
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: move;
  display: flex;
  align-items: center;
  gap: 10px;
}

.store-preview {
  min-height: 500px;
  background-color: white;
  border: 2px dashed #ddd;
  border-radius: 8px;
  padding: 20px;
}

.layout-component {
  padding: 15px;
  margin: 10px 0;
  background-color: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 10px;
  position: relative;
}

.remove-component {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  color: #f44336;
  cursor: pointer;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  width: 500px;
  max-width: 90%;
}

.form-group {
  margin-bottom: 15px;
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

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.cancel-btn {
  padding: 8px 16px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: none;
  cursor: pointer;
}

.save-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  background-color: #4CAF50;
  color: white;
  cursor: pointer;
}
</style> 