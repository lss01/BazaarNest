<template>
  <div class="product-detail-container">
    <!-- Product Image Carousel -->
    <div class="product-images">
      <div class="main-image">
        <img :src="product.image_url" :alt="product.name" />
      </div>

    </div>

    <!-- Product Information -->
    <div class="product-info">
      <h1 class="product-title">{{ product.name }}</h1>
      <p class="product-price">RM {{ product.price }}</p>

      <!-- Add to Cart Section -->
      <div class="add-to-cart-section">
        <div class="quantity-control">
          <button @click="decreaseQuantity" :disabled="quantity <= 1">
            <i class="fa fa-minus"></i>
          </button>
          <span class="quantity">{{ quantity }}</span>
          <button @click="increaseQuantity">
            <i class="fa fa-plus"></i>
          </button>
        </div>
        <button class="add-to-cart-btn" @click="addToCart">
          <i class="fas fa-shopping-cart"></i>
          Add to Cart
        </button>
      </div>

      <div class="product-description">
        <h2>Description</h2>
        <p>{{ product.description }}</p>
      </div>

      <!-- Seller Card -->
      <div class="seller-card" @click="navigateToStore" style="cursor: pointer;">
        <img :src="product.sellerAvatar" :alt="product.shopName" class="seller-avatar" />
        <div class="seller-info">
          <h3>{{ product.shopName }}</h3>
          <p>{{ product.shopDescription }}</p>
        </div>
      </div>
      <button class="continue-shopping" @click="goToHome">
        <i class="fas fa-arrow-left"></i> Continue Shopping
      </button>

    </div>
  </div>
</template>

<script>
import { useCartStore } from '../stores/cart'
import { useRouter } from 'vue-router'

export default {
  name: 'ProductDetail',
  setup() {
    const cartStore = useCartStore()
    const router = useRouter()
    return { cartStore, router }
  },
  data() {
    return {
      product: {
        id: 1,
        name: 'Handmade Ceramic Mug',
        description: 'A beautiful handcrafted ceramic mug perfect for your morning coffee or tea.',
        price: 24.99,
        images: [
          'src/assets/picture/Handmade_Ceramic_Mug.jpg',
          'src/assets/picture/Handmade_Ceramic_Mug.jpg',
          'src/assets/picture/Handmade_Ceramic_Mug.jpg'
        ],
        shopName: 'Artisan Pottery',
        shopDescription: 'Creating unique ceramic pieces since 2010',
        sellerAvatar: 'https://via.placeholder.com/100',
        sellerId: 101
      },
      currentImageIndex: 0,
      quantity: 1,
      userAvatar: '',
    }
  },
  computed: {
    currentImage() {
      return this.product.images[this.currentImageIndex]
    }
  },

  mounted() {
    this.currentImageIndex = 0;
    this.quantity = 1;
    localStorage.getItem('avatar') && (this.userAvatar = "../backend/src/uploads/avatars/" + localStorage.getItem('avatar'))
    this.fetchProducts()
  },
  methods: {
    goToHome() {
      this.$router.push('/')
    },
    async fetchProducts() {
      this.loading = true;
      const id = this.$route.params.id;
      const url = `/api/product-detail/${encodeURIComponent(id)}`;
      console.log(url)
      try {
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch products');

        const data = await response.json();
        if (data.status === 'success') {
          this.product = data.product;
          console.log(data.product)

          this.product.sellerAvatar = "../backend/src/uploads/avatars/" + this.product.sellerAvatar;
        } else {
          console.warn('No products found for the selected criteria');
          throw new Error(data.message || 'Profile not found');
        }
      } catch (error) {
        console.error('Error fetching products:', error);
      }
    },
    increaseQuantity() {
      this.quantity++
    },
    decreaseQuantity() {
      if (this.quantity > 1) this.quantity--
    },
    async addToCart() {
      const userId = localStorage.getItem('userId');
      const quantity = this.quantity;
      const id = this.$route.params.id;
      console.log('check id:' + id)
      const url = `/api/add-cart/${encodeURIComponent(userId)}/${encodeURIComponent(id)}/${encodeURIComponent(quantity)}`
      console.log(url)
      try {
        const response = await fetch(url);

        if (!response.ok) throw new Error('Failed to fetch products');

        const data = await response.json();
        if (data.status === 'success') {
          alert('Product added to cart successfully!');
          this.quantity = 1;
        } else {
          alert(data.message || 'Failed to add product to cart');
          throw new Error(data.message || 'Profile not found');
        }
      } catch (error) {
        console.error('Error adding product to cart:', error);
      }
    },
    navigateToStore() {
      if (this.product.seller.id) {
        this.router.push(`/store/${this.product.seller.id}`)
      } else {
        alert('Seller information not available.')
      }
    }
  }
}
</script>


<style scoped>
.product-detail-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
}

.product-images {
  position: sticky;
  top: 20px;
}

.main-image {
  width: 100%;
  margin-bottom: 20px;
  border-radius: 8px;
  overflow: hidden;
}

.main-image img {
  width: 100%;
  height: auto;
  object-fit: cover;
}

.thumbnail-list {
  display: flex;
  gap: 10px;
}

.thumbnail {
  width: 80px;
  height: 80px;
  border-radius: 4px;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
}

.thumbnail.active {
  border-color: #4CAF50;
}

.thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.product-title {
  font-size: 2em;
  margin: 0;
}

.product-price {
  font-size: 1.5em;
  color: #4CAF50;
  font-weight: bold;
}

.add-to-cart-section {
  margin: 20px 0;
  display: flex;
  gap: 20px;
  align-items: center;
}

.quantity-control {
  display: flex;
  align-items: center;
  gap: 10px;
}

.quantity-control button {
  width: 36px;
  height: 36px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.quantity-control button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity {
  min-width: 40px;
  text-align: center;
  font-size: 1.1em;
}

.add-to-cart-btn {
  flex: 1;
  padding: 12px 24px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1.1em;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: background-color 0.2s;
}

.add-to-cart-btn:hover {
  background-color: #45a049;
}

.add-to-cart-btn i {
  font-size: 1.2em;
}

.product-description {
  margin: 20px 0;
}

.seller-card {
  display: flex;
  gap: 20px;
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 8px;
}

.seller-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
}

.seller-info {
  flex: 1;
}

.seller-stats {
  display: flex;
  gap: 20px;
  margin-top: 10px;
  color: #666;
}

.reviews-section {
  margin-top: 40px;
}

.review-card {
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.review-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 10px;
}

.reviewer-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

.rating {
  color: #ffd700;
}

.star.filled {
  color: #ffd700;
}

.review-date {
  color: #666;
  font-size: 0.9em;
}

.similar-products {
  margin-top: 40px;
}

.similar-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.similar-product-card {
  border: 1px solid #eee;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.2s;
}

.similar-product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.similar-product-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.similar-product-card h3 {
  padding: 10px;
  margin: 0;
  font-size: 1em;
}

.similar-product-card .price {
  padding: 0 10px 10px;
  color: #4CAF50;
  font-weight: bold;
  margin: 0;
}

@media (max-width: 768px) {
  .product-detail-container {
    grid-template-columns: 1fr;
  }

  .product-images {
    position: static;
  }
}

.continue-shopping {
  padding: 12px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.continue-shopping {
  background: white;
  border: 1px solid #4CAF50;
  color: #4CAF50;
}

.continue-shopping:hover {
  background: #f5f5f5;
}
</style>