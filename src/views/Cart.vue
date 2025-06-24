<template>
  <div class="cart-container">
    <h1 class="cart-title">Shopping Cart</h1>
    
    <div v-if="cartStore.items.length === 0" class="empty-cart">
      <i class="fas fa-shopping-cart"></i>
      <p>Your cart is empty</p>
      <button class="continue-shopping" @click="goToHome">Continue Shopping</button>
    </div>

    <div v-else class="cart-content">
      <!-- Cart Items List -->
      <div class="cart-items">
        <div v-for="item in cartStore.items" :key="item.id" class="cart-item">
          <img :src="item.image" :alt="item.name" class="item-image">
          <div class="item-details">
            <h3 class="item-name">{{ item.name }}</h3>
            <p class="item-seller">{{ item.sellerName }}</p>
            <div class="quantity-control">
              <button @click="decreaseQuantity(item)" :disabled="item.quantity <= 1">
                <i class="fas fa-minus"></i>
              </button>
              <span class="quantity">{{ item.quantity }}</span>
              <button @click="increaseQuantity(item)">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
          <div class="item-price">
            <p class="price">${{ (item.price * item.quantity).toFixed(2) }}</p>
            <button class="remove-item" @click="removeItem(item)">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="order-summary">
        <h2>Order Summary</h2>
        <div class="summary-item">
          <span>Subtotal</span>
          <span>${{ cartStore.subtotal.toFixed(2) }}</span>
        </div>
        <div class="summary-item">
          <span>Shipping</span>
          <span>${{ cartStore.shipping.toFixed(2) }}</span>
        </div>
        <div class="summary-item total">
          <span>Total</span>
          <span>${{ cartStore.total.toFixed(2) }}</span>
        </div>
        <div class="cart-actions">
          <button class="continue-shopping" @click="goToHome">
            <i class="fas fa-arrow-left"></i> Continue Shopping
          </button>
          <button class="checkout" @click="checkout">
            Proceed to Checkout <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useCartStore } from '../stores/cart'
import { onMounted } from 'vue'

export default {
  name: 'Cart',
  setup() {
    const cartStore = useCartStore()

    onMounted(() => {
      // Inject dummy data only if items are empty
      if (cartStore.items.length === 0) {
        // Directly set dummy items (local preview only)
        cartStore.$patch({
          items: [
            {
              id: 1,
              name: 'Wireless Headphones',
              sellerName: 'TechStore',
              image: 'https://via.placeholder.com/100',
              quantity: 2,
              price: 49.99
            },
            {
              id: 2,
              name: 'Portable Speaker',
              sellerName: 'AudioHub',
              image: 'https://via.placeholder.com/100',
              quantity: 1,
              price: 29.99
            }
          ]
        })
      }
    })

    return { cartStore }
  },

  methods: {
    increaseQuantity(item) {
      this.cartStore.updateQuantity(item.id, item.quantity + 1)
    },
    decreaseQuantity(item) {
      if (item.quantity > 1) {
        this.cartStore.updateQuantity(item.id, item.quantity - 1)
      }
    },
    removeItem(item) {
      this.cartStore.removeItem(item.id)
    },
    goToHome() {
      this.$router.push('/')
    },
    checkout() {
      console.log('Proceeding to checkout...')
    }
  }
}
</script>

<style scoped>
.cart-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.cart-title {
  font-size: 2em;
  margin-bottom: 30px;
  color: #333;
}

.empty-cart {
  text-align: center;
  padding: 50px;
  color: #666;
}

.empty-cart i {
  font-size: 48px;
  margin-bottom: 20px;
  color: #ddd;
}

.cart-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
}

.cart-items {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.cart-item {
  display: grid;
  grid-template-columns: 100px 1fr auto;
  gap: 20px;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.cart-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 4px;
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.item-name {
  margin: 0;
  font-size: 1.1em;
}

.item-seller {
  color: #666;
  margin: 0;
}

.quantity-control {
  display: flex;
  align-items: center;
  gap: 10px;
}

.quantity-control button {
  width: 30px;
  height: 30px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
}

.quantity-control button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity {
  min-width: 30px;
  text-align: center;
}

.item-price {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;
}

.price {
  font-weight: bold;
  color: #4CAF50;
  margin: 0;
}

.remove-item {
  background: none;
  border: none;
  color: #ff4444;
  cursor: pointer;
  padding: 5px;
}

.order-summary {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  height: fit-content;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
  color: #666;
}

.summary-item.total {
  font-size: 1.2em;
  font-weight: bold;
  color: #333;
  border-top: 1px solid #eee;
  padding-top: 15px;
  margin-top: 15px;
}

.cart-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 20px;
}

.continue-shopping, .checkout {
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

.checkout {
  background: #4CAF50;
  border: none;
  color: white;
}

.continue-shopping:hover {
  background: #f5f5f5;
}

.checkout:hover {
  background: #45a049;
}

@media (max-width: 768px) {
  .cart-content {
    grid-template-columns: 1fr;
  }
  
  .cart-item {
    grid-template-columns: 80px 1fr;
  }
  
  .item-price {
    grid-column: 1 / -1;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}
</style>