import './bootstrap';
import { createApp } from 'vue';
import CustomerList from './components/CustomerList.vue';

//criar app vue e montar dentro <div id="app">
createApp(CustomerList).mount('#app');