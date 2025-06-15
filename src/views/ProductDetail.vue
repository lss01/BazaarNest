<template>
  <div class="product-detail-container">
    <!-- Product Image Carousel -->
    <div class="product-images">
      <div class="main-image">
        <img :src="currentImage" :alt="product.name">
      </div>
      <div class="thumbnail-list">
        <div 
          v-for="(image, index) in product.images" 
          :key="index"
          class="thumbnail"
          :class="{ active: currentImageIndex === index }"
          @click="currentImageIndex = index"
        >
          <img :src="image" :alt="`${product.name} - Image ${index + 1}`">
        </div>
      </div>
    </div>

    <!-- Product Information -->
    <div class="product-info">
      <h1 class="product-title">{{ product.name }}</h1>
      <p class="product-price">${{ product.price }}</p>
      
      <!-- Add to Cart Section -->
      <div class="add-to-cart-section">
        <div class="quantity-control">
          <button @click="decreaseQuantity" :disabled="quantity <= 1">
            <i class="fas fa-minus"></i>
          </button>
          <span class="quantity">{{ quantity }}</span>
          <button @click="increaseQuantity">
            <i class="fas fa-plus"></i>
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
        <img :src="product.seller.avatar" :alt="product.seller.name" class="seller-avatar">
        <div class="seller-info">
          <h3>{{ product.seller.name }}</h3>
          <p>{{ product.seller.description }}</p>
          <div class="seller-stats">
            <span>Rating: {{ product.seller.rating }}/5</span>
            <span>Sales: {{ product.seller.totalSales }}</span>
          </div>
        </div>
      </div>

      <!-- Customer Reviews -->
      <div class="reviews-section">
        <h2>Customer Reviews</h2>
        <div class="review-list">
          <div v-for="review in product.reviews" :key="review.id" class="review-card">
            <div class="review-header">
              <img :src="review.userAvatar" :alt="review.userName" class="reviewer-avatar">
              <div class="reviewer-info">
                <h4>{{ review.userName }}</h4>
                <div class="rating">
                  <span v-for="n in 5" :key="n" class="star" :class="{ filled: n <= review.rating }">★</span>
                </div>
              </div>
            </div>
            <p class="review-content">{{ review.content }}</p>
            <p class="review-date">{{ review.date }}</p>
          </div>
        </div>
      </div>

      <!-- Similar Products -->
      <div class="similar-products">
        <h2>Similar Products</h2>
        <div class="similar-products-grid">
          <div v-for="item in similarProducts" :key="item.id" class="similar-product-card">
            <img :src="item.image" :alt="item.name">
            <h3>{{ item.name }}</h3>
            <p class="price">${{ item.price }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useCartStore } from '../stores/cart'
import { useRouter } from 'vue-router'

/**
 * ProductDetail component displays detailed information about a specific product
 * including image carousel, product details, seller information, reviews, and similar products
 */
export default {
  name: 'ProductDetail',
  setup() {
    const cartStore = useCartStore()
    const router = useRouter()
    return { cartStore, router }
  },
  data() {
    return {
      currentImageIndex: 0,
      quantity: 1,
      product: {
        id: 1,
        name: 'Handmade Ceramic Mug',
        price: 24.99,
        description: 'Beautiful handcrafted ceramic mug made with love and attention to detail. Each piece is unique and perfect for your morning coffee or tea.',
        images: [
          'https://via.placeholder.com/600x400',
          'https://via.placeholder.com/600x400',
          'https://via.placeholder.com/600x400'
        ],
        seller: {
          name: 'Artisan Pottery',
          avatar: 'https://via.placeholder.com/100',
          description: 'Creating unique ceramic pieces since 2010',
          rating: 4.8,
          totalSales: 1234
        },
        reviews: [
          {
            id: 1,
            userName: 'John Doe',
            userAvatar: 'https://via.placeholder.com/50',
            rating: 5,
            content: 'Absolutely love this mug! The craftsmanship is amazing.',
            date: '2024-03-15'
          },
          {
            id: 2,
            userName: 'Jane Smith',
            userAvatar: 'https://via.placeholder.com/50',
            rating: 4,
            content: 'Great quality and beautiful design.',
            date: '2024-03-10'
          }
        ]
      },
      similarProducts: [
        {
          id: 2,
          name: 'Ceramic Tea Cup',
          price: 19.99,
          image: 'https://via.placeholder.com/200'
        },
        {
          id: 3,
          name: 'Stoneware Bowl',
          price: 29.99,
          image: 'https://via.placeholder.com/200'
        },
        {
          id: 4,
          name: 'Ceramic Plate Set',
          price: 49.99,
          image: 'https://via.placeholder.com/200'
        }
      ]
    }
  },
  computed: {
    currentImage() {
      return this.product.images[this.currentImageIndex]
    }
  },
  methods: {
    /**
     * Increase the quantity of the product
     */
    increaseQuantity() {
      this.quantity++
    },
    /**
     * Decrease the quantity of the product
     */
    decreaseQuantity() {
      if (this.quantity > 1) {
        this.quantity--
      }
    },
    /**
     * Add the product to the cart
     */
    addToCart() {
      // Create a cart item object
      const cartItem = {
        id: this.product.id,
        name: this.product.name,
        price: this.product.price,
        image: this.product.images[0],
        sellerName: this.product.seller.name,
        quantity: this.quantity
      }
      
      // Add to cart
      this.cartStore.addItem(cartItem)
      
      // Show success message
      alert('Product added to cart!')
      
      // Reset quantity
      this.quantity = 1
    },
    /**
     * Navigate to the store page
     */
    navigateToStore() {
      this.router.push(`/store/${this.product.seller.id}`)
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
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
</style> 