/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import { Form, HasError, AlertError } from 'vform';
import moment from 'moment';
import VueProgressBar from 'vue-progressbar';
import Swal from 'sweetalert2';
import Gate from './Gate';

Vue.prototype.$gate = new Gate(window.user);
window.Swal = Swal;
window.Form = Form;
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);

Vue.use(VueProgressBar, {
    color: 'rgb(44,255,14)',
    failedColor: 'red',
    height: '2px'
});

window.Fire = new Vue();

const toast = Swal.mixin({
    toast: true,
    position: 'bottom-end',
    showConfirmButton: false,
    timer: 3000
});
window.toast = toast;

import VueRouter from 'vue-router';
Vue.use(VueRouter);

let routes = [
    {path: '/dashboard', component: require('./components/Dashboard.vue').default},
    {path: '/developer', component: require('./components/Developer.vue').default},
    {path: '/users', component: require('./components/Users.vue').default},
    {path: '/profile', component: require('./components/Profile.vue').default},
    {path: '*', component: require('./components/NotFound.vue').default},
];

const router = new VueRouter({
    mode: 'history',
    routes
});

Vue.filter('uc_first', function(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
});

Vue.filter('formatted_date', function(date) {
    return moment(date).format('MMMM Do YYYY')
    // return moment(date).format('MMMM Do YYYY HH:mm:ss')
    // return  moment.utc(date).local()
    // const tz = moment_tz.tz.guess();
    // return moment_tz(date).tz(tz).format('MMMM Do YYYY HH:mm:ss');
});

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('pagination', require('laravel-vue-pagination'));

Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue').default
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue').default
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue').default
);

Vue.component(
    'not-found',
    require('./components/NotFound.vue').default
);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    router,
    data: {
        search: ''
    },
    methods: {
        globalSearch: _.debounce(() => {
            Fire.$emit("search");
            }, 500)
     }
});
