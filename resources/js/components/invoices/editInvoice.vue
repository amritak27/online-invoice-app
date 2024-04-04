<template>
    <div class="container">
        <div class="invoices">        
        <div class="card__header">
            <div>
                <h2 class="invoice__title">Edit Invoice</h2>
            </div>
        </div>
        <div class="card__content">
            <div class="card__content--header">
                <div>
                    <p class="my-1">Customer</p>
                    <select name="" id="" class="input" v-model="form.customer_id">
                        <option disabled>Select customer</option>
                        <option 
                        v-for="customer in allCustomersList"
                        :value="customer.id" 
                        :key="customer.id">
                        {{ customer.firstname + " " + customer.lastname }}</option>
                    </select>
                </div>
                <div>
                    <p class="my-1">Date</p> 
                    <input id="date" placeholder="dd-mm-yyyy" type="date" class="input" v-model="form.date"> 
                    <p class="my-1">Due Date</p> 
                    <input id="due_date" type="date" class="input" v-model="form.due_date">
                </div>
                <div>
                    <p class="my-1">Invoice Number</p> 
                    <input type="text" class="input" v-model="form.invoice_number"> 
                    <p class="my-1">Reference(Optional)</p> 
                    <input type="text" class="input" v-model="form.reference">
                </div>
            </div>
            <br><br>
            <div class="table">
                <div class="table--heading2">
                    <p>Item Description</p>
                    <p>Unit Price</p>
                    <p>Qty</p>
                    <p>Total</p>
                    <p></p>
                </div>
    
                <!-- item 1 -->
                <div class="table--items2" 
                    v-for="(itemcart, i) in form.invoice_items"
                    :key="itemcart.id">
                    <!-- <pre>
                        {{ itemcart }}
                    </pre> -->
                <p v-if="itemcart.product">{{ itemcart.product.item_code }} {{ itemcart.product.description }}</p>
                <p v-else>{{ itemcart.item_code }} {{ itemcart.description }}</p>
                <p>
                    <input type="text" class="input" v-model="itemcart.unit_price">
                </p>
                <p>
                    <input type="text" class="input" v-model="itemcart.quantity">
                </p>
                <p v-if="itemcart.quantity">
                    $ {{ (itemcart.unit_price) * (itemcart.quantity) }}
                </p>
                <p v-else></p>
                <p style="color: red; font-size: 24px;cursor: pointer;" @click="deleteInvoiceItem(itemcart.id, i)">
                    &times;
                </p>
                </div>
                <div style="padding: 10px 30px !important;">
                    <button class="btn btn-sm btn__open--modal" @click="openModal()">Add New Line</button>
                </div>
            </div>

            <div class="table__footer">
                <div class="document-footer" >
                    <p>Terms and Conditions</p>
                    <textarea cols="50" rows="7" class="textarea" v-model="form.terms_and_conditions"></textarea>
                </div>
                <div>
                    <div class="table__footer--subtotal">
                        <p>Sub Total</p>
                        <span>$ {{ subTotal() }}</span>
                    </div>
                    <div class="table__footer--discount">
                        <p>Discount</p>
                        <input type="text" class="input" v-model="form.discount">
                    </div>
                    <div class="table__footer--total">
                        <p>Grand Total</p>
                        <span>$ {{ grandTotal() }}</span>
                    </div>
                </div>
            </div>

           
        </div>
        <div class="card__header" style="margin-top: 40px;">
            <div>
                <a class="btn btn-secondary" @click="onEdit(form.id)">
                    Update
                </a>
            </div>
        </div>       
        <!--==================== add modal items ====================-->
    <div class="modal main__modal" :class="{show: showModal}">
        <div class="modal__content">
            <span class="modal__close btn__close--modal" @click="showModal = false">×</span> 
            <h3 class="modal__title">Add Item</h3>
            <hr><br>
            <div class="modal__items">
                <ul style="list-style: none;">
                    <li
                    v-for="(item, i) in listProducts"
                     :key="item.id"
                     style="display: grid;grid-template-columns: 30px 350px 15px;align-items:center;padding-bottom: 5px;">
                     <p>{{ i+1 }}</p>
                     <a href="#">{{ item.item_code }} {{ item.description }}</a>
                     <button @click="addCart(item)" style="border: 1px solid #e0e0e0;width: 35px;height: 35px;cursor: pointer;">+</button>
                    </li>
                </ul>
            </div>
            <br><hr>
            <div class="model__footer">
                <button class="btn btn-light mr-2 btn__close--modal" @click="showModal = false">
                    Cancel
                </button>
                <button class="btn btn-light btn__close--modal ">Save</button>
            </div>
        </div>
    </div> 
    </div>        
    </div>
</template>
<script setup>
import axios from "axios";
import {onMounted, ref} from "vue";
import { useRouter } from "vue-router";

const router = useRouter()

let form = ref([{id: ''}])
let allCustomersList = ref([])
let customer_id = ref([])
let listCart = ref([])
const showModal = ref(false)
let listProducts = ref([])
const props = defineProps({
    id: {
        type: String,
        default: ''
    }
})

onMounted( async () => {
    getInvoice(),
    getAllCustomerList(),
    getProducts()
})

const getInvoice = async () => {
    let response = await axios.get(`/api/show_invoice/${props.id}`)
    console.log(response.data.invoice);
    form.value = response.data.invoice
}

const getAllCustomerList = async () => {
    let response = await axios.get('/api/customers')
    allCustomersList.value = response.data.customers
}  

const deleteInvoiceItem =  (id, i) => {
    form.value.invoice_items.splice(i,1)
    if(id != undefined) {
        axios.get('/api/delete_invoice_items/'+id)
    }
}

const getProducts = async () => {
    let response = await axios.get('/api/products')
    listProducts.value = response.data.products
}

const openModal = () => {
    showModal.value = !showModal.value
}

const addCart = (item) => {
    const itemCart = {
        product_id: item.id,
        item_code: item.item_code,
        description: item.description,
        unit_price: item.unit_price,
        quantity: item.quantity
    }

    form.value.invoice_items.push(itemCart)
    openModal()
}

const subTotal = () => {
    let subTotal = 0;
    if(form.value.invoice_items){
        form.value.invoice_items.map((data)  =>{
            subTotal = subTotal + (data.quantity*data.unit_price)
        })
    }
    return subTotal;
}

const grandTotal = () => {
    if(form.value.invoice_items) {
        let discountAmount = subTotal() * (form.value.discount/100)
        return subTotal() - discountAmount
     }
}

const onEdit = (id) => {
    if(form.value.invoice_items.length > 1) {
        // alert(JSON.stringify(form.value.invoice_items))
        const formData = new FormData()
        formData.append('invoice_item', JSON.stringify(form.value.invoice_items))
        formData.append('customer_id', form.value.customer_id)
        formData.append('date', form.value.date)
        formData.append('due_date', form.value.due_date)
        formData.append('invoice_number', form.value.invoice_number)
        formData.append('reference', form.value.reference)
        formData.append('discount', form.value.discount)
        formData.append('sub_total', subTotal())
        formData.append('total', grandTotal())
        formData.append('terms_and_conditions', form.value.terms_and_conditions)

        axios.post(`/api/update_invoice/${form.value.id}`, formData)
        form.value.invoice_items = [];
        router.push('/')
    }
}
</script>