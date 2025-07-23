<template>
  <div class="product-detail-container">
    <!-- Back Button -->
    <button @click="goBack" class="back-btn">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5"/>
        <path d="m12 19-7-7 7-7"/>
      </svg>
      Back to Products
    </button>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading product details...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-state">
      <div class="error-icon">⚠️</div>
      <h3>Unable to load product</h3>
      <p>{{ error }}</p>
      <button @click="fetchProductDetail" class="retry-btn">Try Again</button>
    </div>

    <!-- Product Detail Content -->
    <div v-if="!loading && product && !error" class="product-detail-content">
      <!-- Product Header -->
      <div class="product-header">
        <div class="product-image-section">
          <div class="main-product-image">
            <div class="product-placeholder">
              <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="9" cy="9" r="2"/>
                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
              </svg>
            </div>
            <div class="product-badge">New</div>
          </div>

          <!-- Thumbnail Gallery (placeholder for future images) -->
          <div class="thumbnail-gallery">
            <div v-for="i in 4" :key="i" class="thumbnail" :class="{ active: i === 1 }">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="9" cy="9" r="2"/>
                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="product-info-section">
          <div class="product-meta">
            <h1 class="product-title">{{ product.title }}</h1>
            <p class="product-name">{{ product.name }}</p>

            <div class="creator-section">
              <div class="creator-info">
                <div class="creator-avatar">
                  {{ getInitials(product.user?.name || 'Unknown') }}
                </div>
                <div class="creator-details">
                  <p class="creator-name">{{ product.user?.name || 'Unknown Creator' }}</p>
                  <p class="creator-title">Product Creator</p>
                </div>
              </div>
              <div class="product-dates">
                <p class="created-date">Created {{ formatDate(product.created_at) }}</p>
                <p class="updated-date" v-if="product.updated_at !== product.created_at">
                  Updated {{ formatDate(product.updated_at) }}
                </p>
              </div>
            </div>
          </div>

          <div class="action-buttons">
            <button @click="addToWishlist" class="wishlist-btn" :class="{ active: isInWishlist }">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
              </svg>
              {{ isInWishlist ? 'In Wishlist' : 'Add to Wishlist' }}
            </button>

            <button @click="addToCart" class="add-to-cart-btn">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="8" cy="21" r="1"/>
                <circle cx="19" cy="21" r="1"/>
                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57L20.05 7H5.12"/>
              </svg>
              Add to Cart
            </button>

            <button @click="shareProduct" class="share-btn">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="18" cy="5" r="3"/>
                <circle cx="6" cy="12" r="3"/>
                <circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
              </svg>
              Share
            </button>
          </div>
        </div>
      </div>

      <!-- Product Details Tabs -->
      <div class="product-tabs">
        <div class="tab-headers">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="['tab-header', { active: activeTab === tab.id }]"
          >
            {{ tab.label }}
          </button>
        </div>

        <div class="tab-content">
          <!-- Description Tab -->
          <div v-if="activeTab === 'description'" class="tab-panel">
            <div class="description-content">
              <h3>About this product</h3>
              <p>{{ product.description || 'This is a detailed description of the product. It would include features, benefits, specifications, and other relevant information that helps customers understand what they are purchasing.' }}</p>

              <h4>Key Features</h4>
              <ul class="feature-list">
                <li>High-quality materials and construction</li>
                <li>Modern design and functionality</li>
                <li>Suitable for various use cases</li>
                <li>Backed by our quality guarantee</li>
              </ul>
            </div>
          </div>

          <!-- Specifications Tab -->
          <div v-if="activeTab === 'specifications'" class="tab-panel">
            <div class="specifications-grid">
              <div class="spec-item">
                <span class="spec-label">Product ID</span>
                <span class="spec-value">#{{ product.id }}</span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Name</span>
                <span class="spec-value">{{ product.name }}</span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Created By</span>
                <span class="spec-value">{{ product.user?.name || 'Unknown' }}</span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Created Date</span>
                <span class="spec-value">{{ formatDate(product.created_at) }}</span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Last Updated</span>
                <span class="spec-value">{{ formatDate(product.updated_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Reviews Tab -->
          <div v-if="activeTab === 'reviews'" class="tab-panel">
            <div class="reviews-section">
              <div class="reviews-header">
                <h3>Customer Reviews</h3>
                <div class="rating-summary">
                  <div class="stars">
                    <span v-for="i in 5" :key="i" class="star filled">★</span>
                  </div>
                  <span class="rating-text">4.8 out of 5 (24 reviews)</span>
                </div>
              </div>

              <!-- Sample Reviews -->
              <div class="reviews-list">
                <div v-for="review in sampleReviews" :key="review.id" class="review-item">
                  <div class="review-header">
                    <div class="reviewer-info">
                      <div class="reviewer-avatar">{{ review.name.charAt(0) }}</div>
                      <div>
                        <p class="reviewer-name">{{ review.name }}</p>
                        <div class="review-stars">
                          <span v-for="i in review.rating" :key="i" class="star filled">★</span>
                          <span v-for="i in (5 - review.rating)" :key="i + review.rating" class="star">★</span>
                        </div>
                      </div>
                    </div>
                    <span class="review-date">{{ review.date }}</span>
                  </div>
                  <p class="review-text">{{ review.comment }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'

// Props
const props = defineProps({
  productId: {
    type: [String, Number],
    required: true
  },
  productData: {
    type: Object,
    default: null
  }
})

// Emits
const emit = defineEmits(['back'])

// Reactive data
const product = ref(null)
const loading = ref(false)
const error = ref(null)
const activeTab = ref('description')
const isInWishlist = ref(false)

// API base URL
const API_BASE_URL = 'http://192.168.1.48:8000/api'

// Tab configuration
const tabs = [
  { id: 'description', label: 'Description' },
  { id: 'specifications', label: 'Specifications' },
  { id: 'reviews', label: 'Reviews' }
]

// Sample reviews data
const sampleReviews = [
  {
    id: 1,
    name: 'Sarah Johnson',
    rating: 5,
    date: '2 days ago',
    comment: 'Excellent product! Exactly what I was looking for. The quality is outstanding and delivery was fast.'
  },
  {
    id: 2,
    name: 'Mike Chen',
    rating: 4,
    date: '1 week ago',
    comment: 'Very good product overall. Minor issues with packaging but the product itself is great.'
  },
  {
    id: 3,
    name: 'Emma Davis',
    rating: 5,
    date: '2 weeks ago',
    comment: 'Love this product! Will definitely recommend to friends and family.'
  }
]

// Fetch product detail from API
const fetchProductDetail = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await axios.get(`${API_BASE_URL}/view/product/${props.productId}`)

    if (response.data.error === false && response.data.status === 'OK') {
      product.value = response.data.data
    } else {
      error.value = response.data.message || 'Failed to fetch product details'
    }
  } catch (err) {
    // If the individual product API doesn't exist, we can still show the product
    // if it was passed from the parent component
    if (props.productData) {
      product.value = props.productData
      loading.value = false
      return
    }

    error.value = 'Unable to load product details. Please check your connection.'
    console.error('API Error:', err)
  } finally {
    loading.value = false
  }
}

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return 'Unknown'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getInitials = (name) => {
  return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().slice(0, 2)
}

// Action handlers
const goBack = () => {
  emit('back')
}

const addToWishlist = () => {
  isInWishlist.value = !isInWishlist.value
  const action = isInWishlist.value ? 'added to' : 'removed from'
  alert(`${product.value.title} ${action} wishlist!`)
}

const addToCart = () => {
  alert(`${product.value.title} added to cart!`)
}

const shareProduct = () => {
  if (navigator.share) {
    navigator.share({
      title: product.value.title,
      text: `Check out this product: ${product.value.name}`,
      url: window.location.href
    })
  } else {
    // Fallback for browsers that don't support Web Share API
    navigator.clipboard.writeText(window.location.href)
    alert('Product link copied to clipboard!')
  }
}

// Lifecycle hooks
onMounted(() => {
  // If product data is already provided, use it instead of fetching
  if (props.productData) {
    product.value = props.productData
    loading.value = false
  } else {
    fetchProductDetail()
  }
})
</script>

<style scoped>
.product-detail-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

/* Back Button */
.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 12px 20px;
  border-radius: 25px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  margin-bottom: 30px;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

/* Loading and Error States */
.loading-state, .error-state {
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

/* Product Detail Content */
.product-detail-content {
  max-width: 1200px;
  margin: 0 auto;
}

.product-header {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  background: white;
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

/* Product Image Section */
.product-image-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.main-product-image {
  position: relative;
  height: 400px;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-placeholder {
  color: rgba(255, 255, 255, 0.8);
}

.product-badge {
  position: absolute;
  top: 20px;
  right: 20px;
  background: #10b981;
  color: white;
  padding: 8px 16px;
  border-radius: 15px;
  font-size: 0.875rem;
  font-weight: 600;
}

.thumbnail-gallery {
  display: flex;
  gap: 10px;
  justify-content: center;
}

.thumbnail {
  width: 80px;
  height: 80px;
  background: #f3f4f6;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.thumbnail:hover, .thumbnail.active {
  border-color: #667eea;
  background: #f0f4ff;
}

/* Product Info Section */
.product-info-section {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.product-title {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 10px 0;
}

.product-name {
  font-size: 1.25rem;
  color: #6b7280;
  margin: 0 0 30px 0;
}

.creator-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 40px;
}

.creator-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.creator-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #667eea;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.125rem;
}

.creator-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.creator-title {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.product-dates {
  text-align: right;
}

.created-date, .updated-date {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 0 4px 0;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.wishlist-btn, .add-to-cart-btn, .share-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 15px 25px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.wishlist-btn {
  background: #f3f4f6;
  color: #6b7280;
  border: 2px solid #e5e7eb;
}

.wishlist-btn:hover, .wishlist-btn.active {
  background: #fef2f2;
  color: #ef4444;
  border-color: #ef4444;
}

.add-to-cart-btn {
  background: #667eea;
  color: white;
  flex: 1;
  min-width: 200px;
}

.add-to-cart-btn:hover {
  background: #5a67d8;
  transform: translateY(-2px);
}

.share-btn {
  background: #10b981;
  color: white;
}

.share-btn:hover {
  background: #059669;
  transform: translateY(-2px);
}

/* Product Tabs */
.product-tabs {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.tab-headers {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
}

.tab-header {
  flex: 1;
  padding: 20px;
  background: none;
  border: none;
  font-size: 1rem;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.3s ease;
  border-bottom: 3px solid transparent;
}

.tab-header:hover {
  background: #f9fafb;
  color: #374151;
}

.tab-header.active {
  color: #667eea;
  border-bottom-color: #667eea;
  background: #f0f4ff;
}

.tab-content {
  padding: 40px;
}

/* Description Tab */
.description-content h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 15px 0;
}

.description-content h4 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 30px 0 15px 0;
}

.description-content p {
  color: #6b7280;
  line-height: 1.7;
  margin-bottom: 20px;
}

.feature-list {
  list-style: none;
  padding: 0;
}

.feature-list li {
  padding: 8px 0;
  color: #6b7280;
  position: relative;
  padding-left: 25px;
}

.feature-list li:before {
  content: '✓';
  position: absolute;
  left: 0;
  color: #10b981;
  font-weight: bold;
}

/* Specifications Tab */
.specifications-grid {
  display: grid;
  gap: 20px;
}

.spec-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 0;
  border-bottom: 1px solid #f3f4f6;
}

.spec-label {
  font-weight: 600;
  color: #374151;
}

.spec-value {
  color: #6b7280;
}

/* Reviews Tab */
.reviews-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.reviews-header h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.rating-summary {
  display: flex;
  align-items: center;
  gap: 10px;
}

.stars {
  display: flex;
  gap: 2px;
}

.star {
  color: #d1d5db;
  font-size: 1.25rem;
}

.star.filled {
  color: #fbbf24;
}

.rating-text {
  color: #6b7280;
  font-size: 0.875rem;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.review-item {
  padding: 20px;
  background: #f9fafb;
  border-radius: 12px;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
}

.reviewer-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.reviewer-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #667eea;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.reviewer-name {
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.review-stars {
  display: flex;
  gap: 1px;
}

.review-stars .star {
  font-size: 1rem;
}

.review-date {
  color: #9ca3af;
  font-size: 0.875rem;
}

.review-text {
  color: #6b7280;
  line-height: 1.6;
  margin: 0;
}

/* Animations */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
  .product-header {
    grid-template-columns: 1fr;
    gap: 30px;
    padding: 20px;
  }

  .main-product-image {
    height: 300px;
  }

  .product-title {
    font-size: 2rem;
  }

  .creator-section {
    flex-direction: column;
    gap: 20px;
  }

  .action-buttons {
    flex-direction: column;
  }

  .add-to-cart-btn {
    min-width: auto;
  }

  .tab-headers {
    flex-direction: column;
  }

  .tab-content {
    padding: 20px;
  }

  .reviews-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
}

@media (max-width: 480px) {
  .product-detail-container {
    padding: 10px;
  }

  .thumbnail-gallery {
    flex-wrap: wrap;
  }

  .thumbnail {
    width: 60px;
    height: 60px;
  }
}
</style>
