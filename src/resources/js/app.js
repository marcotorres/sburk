
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');


// Dependencies --------------------------------------

import Toasted from 'vue-toasted';
import VueClip from 'vue-clip'
import Multiselect from 'vue-multiselect'
import swal from 'sweetalert';
import VueContentPlaceholders from 'vue-content-placeholders'
import Chartkick from 'vue-chartkick'
import Chart from 'chart.js'
import Datepicker from 'vuejs-datepicker';


Vue.use(Chartkick.use(Chart))

Vue.use(require('vue-moment'));
Vue.use(Toasted)
Vue.toasted.register('error', message => message, {
    position : 'bottom-center',
    duration : 1000
})
Vue.use(VueClip)
Vue.component('multiselect', Multiselect)
Vue.use(VueContentPlaceholders)


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

 // Layout
 Vue.component('sidebar', require('./components/layout/Sidebar.vue'));

// Dashboard
Vue.component('dashboard', require('./components/dashboard/Dashboard.vue'));
Vue.component('sadmin_dashboard', require('./components/dashboard/SAdminDashboard.vue'));


// Profile
Vue.component('profile', require('./components/profile/Profile.vue'));
Vue.component('profile-password', require('./components/profile/Password.vue'));
Vue.component('plan', require('./components/profile/Plan.vue'));
Vue.component('pay', require('./components/profile/Pay.vue'));

// Schools
Vue.component('schools-index', require('./components/schools/Index.vue'));
Vue.component('schools-create', require('./components/schools/Create.vue'));


// School
Vue.component('school', require('./components/school/School.vue'));

// Settings
Vue.component('settings', require('./components/settings/Settings.vue'));

// Plans
Vue.component('plans-index', require('./components/plans/Index.vue'));
Vue.component('plans-create', require('./components/plans/Create.vue'));
Vue.component('plans-edit', require('./components/plans/Edit.vue'));

// Parent
Vue.component('parents-index', require('./components/parents/Index.vue'));
Vue.component('parents-create', require('./components/parents/Create.vue'));
Vue.component('parents-edit', require('./components/parents/Edit.vue'));
Vue.component('parents-map', require('./components/parents/Map.vue'));

// Driver
Vue.component('drivers-index', require('./components/drivers/Index.vue'));
Vue.component('drivers-create', require('./components/drivers/Create.vue'));
Vue.component('drivers-edit', require('./components/drivers/Edit.vue'));
Vue.component('drivers-map', require('./components/drivers/Map.vue'));
Vue.component('drivers-history', require('./components/drivers/History.vue'));

//Bus
Vue.component('buses-index', require('./components/buses/Index.vue'));


const app = new Vue({
    el: '#app'
});
