import { createRouter, createWebHistory } from "vue-router";

import invoiceIndex from '../components/invoices/index.vue';
import createInvoice from '../components/invoices/createInvoice.vue'
import showInvoice from '../components/invoices/showInvoice.vue';
import editInvoice from '../components/invoices/editInvoice.vue';
import notFound from '../components/NotFound.vue'

const routes = [
    {
        path: '/',
        component: invoiceIndex
    },
    {
        path: '/invoice/create',
        component: createInvoice
    },
    {
        path: '/invoice/show/:id',
        component: showInvoice,
        props: true
    },
    {
        path: '/invoice/edit/:id',
        component: editInvoice,
        props: true
    },
    {
        path: '/:pathMatch(.*)*',
        component: notFound
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router