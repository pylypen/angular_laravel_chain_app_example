import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue'
import VueRouter from 'vue-router';
import axios from 'axios';
import VueAxios from 'vue-axios';
import LaravelPagination from 'laravel-vue-pagination';
import App from './App.vue';
import Dashboard from './components/Dashboard.vue';
import Home from './components/Home.vue';
import Login from './components/Login.vue';
import User from './components/User.vue';
import Organisation from './components/Organisation.vue';
import CMSAdmins from './components/CMSAdmins.vue';
import OrganisationDetails from './components/OrganisationDetails.vue';
import VueMaterial from 'vue-material'
import 'vue-material/dist/vue-material.min.css'
import 'vue-material/dist/theme/default.css'

const prodEnv = require('./config/env.js');



Vue.use(VueRouter);
Vue.use(VueAxios, axios);
Vue.use(BootstrapVue);
Vue.use(VueMaterial);

import '../static/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import 'bootstrap/dist/js/bootstrap.js';

Vue.component('pagination', LaravelPagination);
Vue.component('v-select', 'vue-select');

axios.defaults.baseURL = prodEnv.LINK;
const router = new VueRouter({
    routes: [{
        path: '/',
        name: 'home',
        component: Home,
        meta: {
            auth: false
        }
    }, {
        path: '/login',
        name: 'login',
        component: Login,
        meta: {
            auth: false
        }
    }, {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: {
            auth: true
        }
    }, {
            path: '/user',
            name: 'user',
            component: User,
            meta: {
                auth: true
            }
    }, {
        path: '/organisation',
        name: 'organisation',
        component: Organisation,
        meta: {
            auth: true
        }
    }, {
        path: '/cms_admins',
        name: 'cms_admins',
        component: CMSAdmins,
        meta: {
            auth: true
        }
    },{
        path: '/organisation_details',
        name: 'organisation_details',
        component: OrganisationDetails,
        meta: {
            auth: true
        }
    }]
});

router.beforeEach((to, from, next) => {
    var token = localStorage.getItem('default_auth_token');

    if (token != undefined && token != null) {
        jQuery.ajax({
            headers: {
                'Authorization': 'Bearer ' + token
            },
            url: "/auth/check",
            method: 'GET'
        }).done(function() {
            next();
        }).fail(function() {
            localStorage.clear();
            location.href = '/';
        });
    } else {
        next();
    }
});

Vue.router = router;
Vue.use(require('@websanova/vue-auth'), {
    auth: require('@websanova/vue-auth/drivers/auth/bearer.js'),
    http: require('@websanova/vue-auth/drivers/http/axios.1.x.js'),
    router: require('@websanova/vue-auth/drivers/router/vue-router.2.x.js')
});

App.router = Vue.router;
new Vue(App).$mount('#app');