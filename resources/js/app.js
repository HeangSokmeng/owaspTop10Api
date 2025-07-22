import { createApp } from 'vue';
import ProductsComponent from './components/ProductList.vue';

const app = createApp({});
app.component('products-component', ProductsComponent);
app.mount('#app');
