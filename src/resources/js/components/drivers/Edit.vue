<template>
<div class="container">
    <div class="row justify-content-md-center">
        <div class="col-md-12 col-xl-9">
            <div class="card-header px-0 mt-2 bg-transparent clearfix">
                <h4 class="float-left pt-2">Edit Driver</h4>
                <div class="card-header-actions mr-1">
                    <a class="btn btn-primary" href="#" :disabled="submiting_driver" @click.prevent="update">
                        <i class="fas fa-spinner fa-spin" v-if="submiting_driver"></i>
                        <i class="fas fa-check" v-else></i>
                        <span class="ml-1">Save</span>
                    </a>
                    <a class="card-header-action ml-1" href="#" :disabled="submitingDestroy" @click.prevent="destroy">
                        <i class="fas fa-spinner fa-spin" v-if="submitingDestroy"></i>
                        <i class="far fa-trash-alt" v-else></i>
                        <span class="d-md-down-none ml-1">Delete</span>
                    </a>
                </div>
            </div>
 
            <div class="card-header px-0 bg-transparent">
                <div>
                    <strong>General</strong><br>
                    <small class="text-muted">Update data of driver.</small>
                </div>
            </div>
            <div class="card-body px-0">
                <div class="row" v-if="!loading">
                    <div class="form-group col-sm-9">
                        <label>Driver name</label>
                        <input type="text" class="form-control" :class="{'is-invalid': errors.name}" v-model="driver.name" placeholder="Name" autofocus>
                        <div class="invalid-feedback" v-if="errors.name">{{errors.name[0]}}</div>
                    </div>
                    <div class="form-group col-sm-3">
                        <label>Driver ID</label>
                        <input type="text" class="form-control" v-model="driver.id" readonly>
                    </div>
                    <div class="form-group col-sm-3">
                        <label>Country code</label>
                        <div class="d-flex">
                            <label class="pr-2">+</label>
                            <input type="text" class="form-control" :class="{'is-invalid': errors.country_code || errors.tel_number}" v-model="driver.country_code" placeholder="country code">
                        </div>
                    </div>
                    <div class="form-group col-sm-9">
                        <label>Driver telephone</label>
                        <input type="text" class="form-control" :class="{'is-invalid': errors.tel_number}" v-model="driver.tel_number" placeholder="telephone number">
                        <div class="invalid-feedback" v-if="errors.tel_number">{{errors.tel_number[0]}}</div> 
                    </div>
                    <div class="form-group col-sm-6">
                        <label>Verification Code</label>
                        <input type="text" class="form-control" v-model="driver.v_code" readonly />
                    </div>
                    <div class="form-group col-sm-6">
                    <label>Verified?</label>
                        <div class="ml-4">
                        <label class="switch switch-pill switch-outline-success-alt block">
                            <input class="switch-input" type="checkbox" v-model="driver.verified" />
                            <span class="switch-slider"></span>
                        </label>
                        </div>            
                    </div>
                    <div class="form-group col-sm-12">
                        <label class="form-label">Bus</label>
                        <div id="busSelectInput" :class="{'d-flex': !isAddBus}" v-show="!isAddBus">
                            <multiselect v-model="driver.bus" :options="buses" 
                            placeholder="Select bus" 
                            label="license" track-by="license" 
                            class="form-control" :class="{'is-invalid': errors.bus_id}">
                            </multiselect>
                            <button id="addBus-button" class="btn btn-link" @click="showNewBus">New</button>
                        </div>
                        <div class="invalid" v-if="errors.bus_id">{{errors.bus_id[0]}}</div>
                        <div id="addBusInput" :class="{'d-flex': isAddBus}" v-show="isAddBus">
                            <span class="clearable">
                                <input ref="clearable_bus" type="text" v-on:keyup="enterKeyAddNewBus" v-model="bus.license" placeholder="Bus license">
                                <i class="clearable__clear" @click="hideNewBus">&times;</i>
                            </span>
                            <span>
                                <a class="btn accept">
                                    <i class="fas fa-check" :disabled="submiting_bus" @click="addNewBus"></i>
                                </a>
                            </span>
                            <span class="fa fa-spinner fa-spin visspinner" v-show="submiting_bus"></span>
                        </div>
                        <div class="invalid" v-if="errors.license">{{errors.license[0]}}</div>
                    </div>
                </div>
                <content-placeholders v-else>
                    <content-placeholders-heading />
                </content-placeholders>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
    data() {
        return {
            errors: {},
            loading: true,
            submitingDestroy: false,
            driver: {
                bus: {}
            },
            submiting_driver: false,
            submiting_bus: false,
            isAddBus: false,
            bus: {
                license: ''
            },
            buses: [],
        }
    },
    mounted() {
        this.getBuses();
        this.getDriver();
    },
    methods: {
        showNewBus() {
            this.isAddBus = true;
            this.$nextTick(() => this.$refs.clearable_bus.focus());
            this.clearErrors();
        },
        hideNewBus() {
            this.isAddBus = false;
            this.clearErrors();
        },
        clearErrors() {
            this.errors = {};
        },
        getBuses(select_first = false) {
            this.loading = true
            this.buses = []

            axios.get(`/api/buses/all`)
                .then(response => {
                    this.buses = response.data
                    this.loading = false
                    if (select_first)
                        this.driver.bus = this.buses[0];
                })
        },
        enterKeyAddNewBus: function (e) {
            if (e.keyCode === 13) {
                this.addNewBus();
            }
        },
        addNewBus() {
            if (!this.submiting_bus) {
                this.submiting_bus = true
                axios.post('/api/buses/store', this.bus)
                    .then(response => {
                        this.$toasted.global.error('Bus created!')
                        this.submiting_bus = false
                        this.hideNewBus();
                        this.getBuses(true);
                    })
                    .catch(error => {
                        this.errors = error.response.data.errors
                        this.$toasted.global.error('Errors in creating bus!')
                        this.submiting_bus = false
                    })
            }
        },
        getDriver() {
            this.loading = true
            let str = window.location.pathname
            let res = str.split("/")
            axios.get(`/api/drivers/getDriver/${res[2]}`)
                .then(response => {
                    console.log(response.data);
                    this.driver = response.data
                })
                .catch(error => {
                    this.$toasted.global.error('Driver does not exist!')
                    location.href = '/drivers'
                })
                .then(() => {
                    this.loading = false
                })
        },
        update() {
            if (!this.submiting_driver) {
                this.submiting_driver = true
                axios.put(`/api/drivers/update/${this.driver.id}`, this.driver)
                    .then(response => {
                        this.$toasted.global.error('Updated driver!')
                        location.href = '/drivers'
                    })
                    .catch(error => {
                        this.errors = error.response.data.errors
                        this.submiting_driver = false
                        swal("Error", error.response.data.errors[0], "error")
                    })
            }
        },
        testUpdatePos() {
            this.driver.last_latitude = this.driver.last_latitude + 0.0001;
            this.driver.last_longitude = this.driver.last_longitude + 0.0001;
            axios.put(`/api/drivers/updatePosition/`, this.driver)
                .then(response => {
                    this.$toasted.global.error('Updated driver!')
                })
                .catch(error => {
                    this.errors = error.response.data.errors
                })
        },
        destroy() {
            if (!this.submitingDestroy) {
                this.submitingDestroy = true
                swal({
                        title: "Are you sure?",
                        text: "Once deleted, you will not be able to recover this driver!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            axios.delete(`/api/drivers/${this.driver.id}`)
                                .then(response => {
                                    this.$toasted.global.error('Driver deleted!')
                                    location.href = '/drivers'
                                })
                                .catch(error => {
                                    this.errors = error.response.data.errors
                                    swal("Error", error.response.data.errors[0], "error")
                                })
                        }
                        this.submitingDestroy = false
                    })
            }
        },
    },
}
</script>
<style scoped>

/* Hover tooltips */
.field-tip {
    position:relative;
    cursor:help;
}
    .field-tip .tip-content {
        position:absolute;
        top:-10px; /* - top padding */
        right:9999px;
        width:200px;
        margin-right:-220px; /* width + left/right padding */
        padding:10px;
        color:#fff;
        background:#333;
        -webkit-box-shadow:2px 2px 5px #aaa;
           -moz-box-shadow:2px 2px 5px #aaa;
                box-shadow:2px 2px 5px #aaa;
        opacity:0;
        -webkit-transition:opacity 250ms ease-out;
           -moz-transition:opacity 250ms ease-out;
            -ms-transition:opacity 250ms ease-out;
             -o-transition:opacity 250ms ease-out;
                transition:opacity 250ms ease-out;
    }
        /* <http://css-tricks.com/snippets/css/css-triangle/> */
        .field-tip .tip-content:before {
            content:' '; /* Must have content to display */
            position:absolute;
            top:50%;
            left:-16px; /* 2 x border width */
            width:0;
            height:0;
            margin-top:-8px; /* - border width */
            border:8px solid transparent;
            border-right-color:#333;
        }
        .field-tip:hover .tip-content {
            right:-20px;
            opacity:1;
        }
</style>
