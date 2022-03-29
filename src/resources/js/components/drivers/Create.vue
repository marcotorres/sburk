<template>
<div class="container">
    <div class="row justify-content-md-center">
        <div class="col-md-9 col-xl-7">
            <div class="card-header px-0 mt-2 bg-transparent clearfix">
                <h4 class="float-left pt-2">New Driver</h4>
                <div class="card-header-actions mr-1">
                    <a class="btn btn-primary" href="#" :disabled="submiting_driver" @click.prevent="create">
                        <i class="fas fa-spinner fa-spin" v-if="submiting_driver"></i>
                        <i class="fas fa-check" v-else></i>
                        <span class="ml-1">Save</span>
                    </a>
                </div>
            </div>
            <div class="card-body px-0" v-if="!loading">
                <div class="form-group">
                    <label>Driver name</label>
                    <input type="text" class="form-control" :class="{'is-invalid': errors.name}" v-model="driver.name" placeholder="name" autofocus>
                    <div class="invalid-feedback" v-if="errors.name">{{errors.name[0]}}</div>
                </div>
                <div class="row form-group">
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
                </div>
                <div class="form-group">
                    <label class="form-label">Select bus</label>
                    <div id="busSelectInput" :class="{'d-flex': !isAddBus}" v-show="!isAddBus">
                        <multiselect v-model="driver.bus" :options="buses" placeholder="Select bus" label="license" track-by="license" class="form-control" :class="{'is-invalid': errors.bus_id}">
                        </multiselect>
                        <button id="addBus-button" class="btn btn-link" @click="showNewBus">New</button>
                    </div>
                    <div class="invalid" v-if="errors.bus_id">{{errors.bus_id[0]}}</div>
                    <div id="addBusInput" :class="{'d-flex': isAddBus}" v-show="isAddBus">
                        <span class="clearable">
                            <input ref="clearable_bus" type="text" v-on:keyup="enterKeyAddNewBus"
                            v-model="bus.license" placeholder="Bus license">
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
            <content-placeholders v-if="loading">
                <content-placeholders-text />
            </content-placeholders>
        </div>
    </div>
</div>
</template>

<script>
export default {
    data() {
        return {
            driver: {
                bus: {}
            },
            errors: {},
            loading: true,
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
                        this.submiting_bus = false
                        this.$toasted.global.error('Errors in creating bus!')
                        this.errors = error.response.data.errors
                    })
            }
        },
        create() {
            if (!this.submiting_driver) {
                this.submiting_driver = true
                axios.post('/api/drivers/store', this.driver)
                    .then(response => {
                        this.$toasted.global.error('Created driver!')
                        location.href = '/drivers'
                    })
                    .catch(error => {
                        this.submiting_driver = false
                        this.$toasted.global.error('Error in creating driver!')
                        this.errors = error.response.data.errors
                    })
            }
        },
    },
}
</script>
