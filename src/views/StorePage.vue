<template>
  <div class="store-page">
    <!-- Store Header -->
    <div class="store-header" :style="{ backgroundColor: storeTheme.primaryColor }">
      <div class="store-banner">
        <img :src="store.bannerImage" :alt="store.name + ' banner'">
      </div>
      <div class="store-info">
        <div class="store-avatar">
          <img :src="store.avatar" :alt="store.name">
        </div>
        <div class="store-details">
          <h1>{{ store.name }}</h1>
          <div class="store-stats">
            <span><i class="fas fa-star"></i> {{ store.rating }}/5</span>
            <span><i class="fas fa-shopping-bag"></i> {{ store.totalSales }} Sales</span>
            <span><i class="fas fa-map-marker-alt"></i> {{ store.location }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Store Description -->
    <div class="store-description">
      <p>{{ store.description }}</p>
    </div>

    <!-- Filter and Sort Section -->
    <div class="filter-sort-section">
      <div class="filter-options">
        <select v-model="selectedCategory">
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category" :value="category">
            {{ category }}
          </option>
        </select>
        <select v-model="priceRange">
          <option value="">Price Range</option>
          <option value="0-50">$0 - $50</option>
          <option value="51-100">$51 - $100</option>
          <option value="101+">$101+</option>
        </select>
      </div>
      <div class="sort-options">
        <select v-model="sortBy">
          <option value="newest">Newest</option>
          <option value="price-low">Price: Low to High</option>
          <option value="price-high">Price: High to Low</option>
          <option value="rating">Rating</option>
        </select>
      </div>
    </div>

    <!-- Products Grid -->
    <div class="products-grid">
      <div v-for="product in filteredProducts" :key="product.id" class="product-card">
        <img :src="product.image" :alt="product.name">
        <div class="product-info">
          <h3>{{ product.name }}</h3>
          <p class="price">${{ product.price }}</p>
          <div class="rating">
            <span v-for="n in 5" :key="n" class="star" :class="{ filled: n <= product.rating }">★</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * StorePage component displays a merchant's store page with their products and information
 * Supports filtering, sorting, and theme customization
 */
export default {
  name: 'StorePage',
  data() {
    return {
      store: {
        id: 1,
        name: 'Artisan Pottery',
        avatar: 'https://via.placeholder.com/150',
        bannerImage: 'https://via.placeholder.com/1200x300',
        description: 'Creating unique ceramic pieces since 2010. Each piece is handcrafted with love and attention to detail.',
        rating: 4.8,
        totalSales: 1234,
        location: 'San Francisco, CA'
      },
      storeTheme: {
        primaryColor: '#4CAF50',
        secondaryColor: '#45a049',
        textColor: '#333333'
      },
      selectedCategory: '',
      priceRange: '',
      sortBy: 'newest',
      categories: ['Ceramics', 'Pottery', 'Home Decor', 'Kitchenware'],
      products: [
        {
          id: 1,
          name: 'Handmade Ceramic Mug',
          price: 24.99,
          image: 'https://via.placeholder.com/300',
          rating: 5,
          category: 'Ceramics'
        },
        {
          id: 2,
          name: 'Ceramic Tea Cup',
          price: 19.99,
          image: 'https://via.placeholder.com/300',
          rating: 4,
          category: 'Ceramics'
        },
        // Add more products as needed
      ]
    }
  },
  computed: {
    filteredProducts() {
      let filtered = [...this.products]
      
      // Apply category filter
      if (this.selectedCategory) {
        filtered = filtered.filter(p => p.category === this.selectedCategory)
      }
      
      // Apply price range filter
      if (this.priceRange) {
        const [min, max] = this.priceRange.split('-').map(Number)
        filtered = filtered.filter(p => {
          if (max) {
            return p.price >= min && p.price <= max
          } else {
            return p.price >= min
          }
        })
      }
      
      // Apply sorting
      switch (this.sortBy) {
        case 'price-low':
          filtered.sort((a, b) => a.price - b.price)
          break
        case 'price-high':
          filtered.sort((a, b) => b.price - a.price)
          break
        case 'rating':
          filtered.sort((a, b) => b.rating - a.rating)
          break
        case 'newest':
          filtered.sort((a, b) => b.id - a.id)
          break
      }
      
      return filtered
    }
  }
}
</script>

<style scoped>
.store-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.store-header {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 20px;
}

.store-banner img {
  width: 100%;
  height: 300px;
  object-fit: cover;
}

.store-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 20px;
  background: linear-gradient(transparent, rgba(0,0,0,0.7));
  color: white;
  display: flex;
  align-items: center;
  gap: 20px;
}

.store-avatar img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 3px solid white;
}

.store-stats {
  display: flex;
  gap: 20px;
  margin-top: 10px;
}

.store-stats span {
  display: flex;
  align-items: center;
  gap: 5px;
}

.store-description {
  margin: 20px 0;
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 8px;
}

.filter-sort-section {
  display: flex;
  justify-content: space-between;
  margin: 20px 0;
  padding: 15px;
  background-color: #f5f5f5;
  border-radius: 8px;
}

.filter-options, .sort-options {
  display: flex;
  gap: 10px;
}

select {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background-color: white;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.product-card {
  border: 1px solid #eee;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.2s;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.product-info {
  padding: 15px;
}

.product-info h3 {
  margin: 0 0 10px 0;
  font-size: 1.1em;
}

.price {
  color: #4CAF50;
  font-weight: bold;
  margin: 5px 0;
}

.rating {
  color: #ffd700;
}

.star.filled {
  color: #ffd700;
}

@media (max-width: 768px) {
  .store-info {
    flex-direction: column;
    text-align: center;
  }
  
  .filter-sort-section {
    flex-direction: column;
    gap: 10px;
  }
  
  .filter-options, .sort-options {
    flex-direction: column;
  }
}
</style> 