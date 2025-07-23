<template>
  <div class="customer-product-container">
    <!-- Show Product Detail View -->
    <ProductDetail
      v-if="showProductDetail"
      :productId="selectedProductId"
      :productData="selectedProduct"
      @back="closeProductDetail"
    />

    <!-- Show Product List View -->
    <div v-else>
      <!-- Hero Header -->
      <div class="hero-header">
        <div class="hero-content">
          <h1 class="hero-title">Our Products</h1>
          <p class="hero-subtitle">Discover amazing products crafted just for you</p>
          <button @click="fetchProducts" class="refresh-btn" :disabled="loading">
            <svg v-if="loading" class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 12a9 9 0 11-6.219-8.56"/>
            </svg>
            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
              <path d="M21 3v5h-5"/>
              <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
              <path d="M3 21v-5h5"/>
            </svg>
            <span>{{ loading ? 'Refreshing...' : 'Refresh' }}</span>
          </button>
        </div>
        <div class="hero-decoration">
          <div class="decoration-circle"></div>
          <div class="decoration-circle"></div>
          <div class="decoration-circle"></div>
        </div>
      </div>

      <!-- Search and Filter Bar -->
      <div class="search-filter-bar">
        <div class="search-box">
          <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
          </svg>
          <input
            type="text"
            placeholder="Search products..."
            v-model="searchQuery"
            class="search-input"
          />
        </div>
        <div class="filter-buttons">
          <button
            v-for="filter in ['All', 'Featured', 'New', 'Popular']"
            :key="filter"
            @click="activeFilter = filter"
            :class="['filter-btn', { active: activeFilter === filter }]"
          >
            {{ filter }}
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && products.length === 0" class="loading-state">
        <div class="loading-spinner"></div>
        <p>Loading amazing products...</p>
      </div>

      <!-- Error State -->
      <div v-if="error" class="error-state">
        <div class="error-icon">⚠️</div>
        <h3>Oops! Something went wrong</h3>
        <p>{{ error }}</p>
        <button @click="fetchProducts" class="retry-btn">Try Again</button>
      </div>

      <!-- Products Grid -->
      <div v-if="!loading && filteredProducts.length > 0" class="products-grid">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          class="product-card"
          @click="viewProduct(product)"
        >
          <div class="product-image">
            <div class="product-placeholder">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="9" cy="9" r="2"/>
                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
              </svg>
            </div>
            <div class="product-badge">New</div>
          </div>

          <div class="product-info">
            <h3 class="product-title">{{ product.title }}</h3>
            <p class="product-name">{{ product.name }}</p>

            <div class="product-meta">
              <div class="creator-info">
                <div class="creator-avatar">
                  {{ getInitials(product.user?.name || 'Unknown') }}
                </div>
                <span class="creator-name">{{ product.user?.name || 'Unknown Creator' }}</span>
              </div>
              <span class="product-date">{{ formatDate(product.created_at) }}</span>
            </div>

            <div class="product-actions">
              <button @click.stop="addToWishlist(product)" class="wishlist-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
              </button>
              <button @click.stop="addToCart(product)" class="cart-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="8" cy="21" r="1"/>
                  <circle cx="19" cy="21" r="1"/>
                  <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57L20.05 7H5.12"/>
                </svg>
                Add to Cart
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && filteredProducts.length === 0 && !error" class="empty-state">
        <div class="empty-icon">📦</div>
        <h3>No products found</h3>
        <p v-if="searchQuery">Try adjusting your search terms</p>
        <p v-else>Check back soon for new products!</p>
        <button v-if="searchQuery" @click="clearSearch" class="clear-search-btn">Clear Search</button>
      </div>

      <!-- Floating Action Button -->
      <button class="fab" @click="scrollToTop" v-show="showFab">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 15l-6-6-6 6"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import ProductDetail from './ProductDetail.vue'; // Import the detail component

// Reactive data
const products = ref([])
const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const activeFilter = ref('All')
const showFab = ref(false)

// Product detail view state
const showProductDetail = ref(false)
const selectedProductId = ref(null)
const selectedProduct = ref(null)

// API base URL
const API_BASE_URL = 'http://192.168.1.48:8000/api'

// Computed properties
const filteredProducts = computed(() => {
  let filtered = products.value

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(query) ||
      product.title.toLowerCase().includes(query) ||
      (product.user?.name || '').toLowerCase().includes(query)
    )
  }

  // Category filter (placeholder logic)
  if (activeFilter.value !== 'All') {
    // Add your filter logic here based on your data structure
    // For now, just return all products
  }

  return filtered
})

// Fetch products from API
const fetchProducts = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await axios.get(`${API_BASE_URL}/view/product`)

    if (response.data.error === false && response.data.status === 'OK') {
      products.value = response.data.data
    } else {
      error.value = response.data.message || 'Failed to fetch products'
    }
  } catch (err) {
    error.value = 'Unable to load products. Please check your connection.'
    console.error('API Error:', err)
  } finally {
    loading.value = false
  }
}

// Format date for display
const formatDate = (dateString) => {
  if (!dateString) return 'Recently'
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

// Get initials for avatar
const getInitials = (name) => {
  if (!name) return 'U'
  return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().slice(0, 2)
}

// Action handlers
const viewProduct = (product) => {
  selectedProductId.value = product.id
  selectedProduct.value = product
  showProductDetail.value = true
}

const closeProductDetail = () => {
  showProductDetail.value = false
  selectedProductId.value = null
  selectedProduct.value = null
}

const addToWishlist = (product) => {
  alert(`${product.title} added to wishlist!`)
}

const addToCart = (product) => {
  alert(`${product.title} added to cart!`)
}

const clearSearch = () => {
  searchQuery.value = ''
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Handle scroll for FAB
const handleScroll = () => {
  showFab.value = window.scrollY > 300
}

// Lifecycle hooks
onMounted(() => {
  fetchProducts()
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.customer-product-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding-bottom: 60px;
}

/* Hero Header */
.hero-header {
  position: relative;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 60px 20px 40px;
  text-align: center;
  overflow: hidden;
}

.hero-content {
  position: relative;
  z-index: 2;
  max-width: 800px;
  margin: 0 auto;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  margin: 0 0 15px 0;
  background: linear-gradient(45deg, #ffffff, #f0f0f0);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle {
  font-size: 1.25rem;
  margin: 0 0 30px 0;
  opacity: 0.9;
  font-weight: 300;
}

.refresh-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 12px 24px;
  border-radius: 25px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.refresh-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.refresh-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.hero-decoration {
  position: absolute;
  top: 0;
  right: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.decoration-circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
}

.decoration-circle:nth-child(1) {
  width: 200px;
  height: 200px;
  top: -100px;
  right: -100px;
  animation: float 6s ease-in-out infinite;
}

.decoration-circle:nth-child(2) {
  width: 150px;
  height: 150px;
  top: 50%;
  right: 10%;
  animation: float 8s ease-in-out infinite reverse;
}

.decoration-circle:nth-child(3) {
  width: 100px;
  height: 100px;
  bottom: 20%;
  left: 10%;
  animation: float 7s ease-in-out infinite;
}

/* Search and Filter Bar */
.search-filter-bar {
  max-width: 1200px;
  margin: -20px auto 40px;
  padding: 0 20px;
  display: flex;
  gap: 20px;
  align-items: center;
  flex-wrap: wrap;
  position: relative;
  z-index: 3;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 300px;
}

.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
}

.search-input {
  width: 100%;
  padding: 15px 15px 15px 50px;
  border: none;
  border-radius: 25px;
  background: white;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  font-size: 16px;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.filter-buttons {
  display: flex;
  gap: 10px;
}

.filter-btn {
  padding: 12px 20px;
  border: none;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.9);
  color: #4b5563;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.filter-btn:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.filter-btn.active {
  background: #667eea;
  color: white;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 60px 20px;
  color: white;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top: 3px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

/* Error State */
.error-state {
  text-align: center;
  padding: 60px 20px;
  color: white;
}

.error-icon {
  font-size: 48px;
  margin-bottom: 20px;
}

.retry-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 12px 24px;
  border-radius: 20px;
  cursor: pointer;
  margin-top: 20px;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Products Grid */
.products-grid {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 30px;
}

.product-card {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: all 0.4s ease;
  cursor: pointer;
}

.product-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.product-image {
  position: relative;
  height: 200px;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-placeholder {
  color: rgba(255, 255, 255, 0.8);
}

.product-badge {
  position: absolute;
  top: 15px;
  right: 15px;
  background: #10b981;
  color: white;
  padding: 5px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.product-info {
  padding: 25px;
}

.product-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.product-name {
  color: #6b7280;
  font-size: 0.95rem;
  margin: 0 0 20px 0;
}

.product-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.creator-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.creator-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #667eea;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
}

.creator-name {
  font-size: 0.875rem;
  color: #4b5563;
}

.product-date {
  font-size: 0.8rem;
  color: #9ca3af;
}

.product-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.wishlist-btn {
  padding: 10px;
  border: 2px solid #e5e7eb;
  background: white;
  border-radius: 10px;
  cursor: pointer;
  color: #6b7280;
  transition: all 0.3s ease;
}

.wishlist-btn:hover {
  border-color: #ef4444;
  color: #ef4444;
}

.cart-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 20px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.cart-btn:hover {
  background: #5a67d8;
  transform: translateY(-1px);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  color: white;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.clear-search-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 10px 20px;
  border-radius: 20px;
  cursor: pointer;
  margin-top: 20px;
}

/* Floating Action Button */
.fab {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #667eea;
  color: white;
  border: none;
  cursor: pointer;
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
  transition: all 0.3s ease;
  z-index: 1000;
}

.fab:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

/* Animations */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }

  .search-filter-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    min-width: auto;
  }

  .filter-buttons {
    justify-content: center;
    flex-wrap: wrap;
  }

  .products-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .fab {
    bottom: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
  }
}

@media (max-width: 480px) {
  .hero-title {
    font-size: 2rem;
  }

  .product-card {
    margin: 0 10px;
  }
}
</style>
