<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-9 col-xl-7">
                <div class="card-header px-0 mt-2 bg-transparent clearfix">
                    <h4 class="float-left pt-2">New Parent</h4>
                    <div class="card-header-actions mr-1">
                        <a class="btn btn-primary" href="#" :disabled="submiting" @click.prevent="create">
                            <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
                            <i class="fas fa-check" v-else></i>
                            <span class="ml-1">Save</span>
                        </a>
                    </div>
                </div>
                <div class="card-header px-0 bg-transparent">
                    <strong>General</strong>
                    <br />
                    <small class="text-muted">Create a new parent.</small>
                </div>
                <div class="card-body px-0">
                    <div class="form-group">
                        <label>Parent name</label>
                        <input
                                type="text"
                                class="form-control"
                                :class="{'is-invalid': errors['parent.name']}"
                                v-model="parent.name"
                                placeholder="name"
                                autofocus
                        />
                        <div class="invalid-feedback" v-if="errors['parent.name']">{{errors['parent.name'][0]}}</div>
                    </div>
                    <div class="row form-group">
                        <div class="form-group col-sm-3">
                            <label>Country code</label>
                            <div class="d-flex">
                                <label class="pr-2">+</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors['parent.country_code'] }" v-model="parent.country_code" placeholder="country code">
                            </div>
                        </div>
                        <div class="form-group col-sm-9">
                            <label>Parent telephone</label>
                            <input type="text" class="form-control" :class="{'is-invalid': errors['parent.tel_number']}" v-model="parent.tel_number" placeholder="telephone number">
                            <div class="invalid-feedback" v-if="errors['parent.tel_number']">{{errors['parent.tel_number'][0]}}</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="typo_label">Select driver</label>
                        <multiselect
                                v-model="parent.driver"
                                placeholder="Select driver"
                                label="tel_number"
                                track-by="tel_number"
                                class="form-control"
                                :class="{'is-invalid': errors['parent.driver']}"
                                :options="options"
                                :option-height="104"
                                :show-labels="false"
                        >
                            <template slot="singleLabel" slot-scope="props">
                                <div class="option_tel_number">
                  <span class="option_name p-1">
                    <i class="icon-user px-2"></i>
                    {{ props.option.name }}
                  </span>
                                    <span class="option_small p-1">
                    <i class="icon-phone px-2"></i>
                    {{ props.option.tel_number }}
                  </span>
                                </div>
                            </template>
                            <template slot="option" slot-scope="props">
                                <div class="option_tel_number">
                                    <div class="option_name p-1">
                                        <i class="icon-user px-2"></i>
                                        {{ props.option.name }}
                                    </div>
                                    <div class="option_small p-1">
                                        <i class="icon-phone px-2"></i>
                                        {{ props.option.tel_number }}
                                    </div>
                                </div>
                            </template>
                        </multiselect>
                        <div class="invalid-feedback" v-if="errors['parent.driver']">{{errors['parent.driver'][0]}}</div>
                        <!-- <div>{{ parent.driver  }}</div> -->
                    </div>
                    <table class="table table-hover" >
                        <thead>
                        <tr>
                            <th></th>
                            <th class="d-none d-sm-table-cell">
                                <a href="#" class="text-dark" ></a>
                                <i class="ml-1 fas"></i>
                            </th>
                            <th  class="d-none d-sm-table-cell">
                                <a href="#" class="text-dark" >Child Name</a>
                                <i class="ml-1 fas" ></i>
                            </th>
                            <th class="d-none d-sm-table-cell">
                                <a href="#" class="text-dark" ></a>
                                <i class="ml-1 fas" ></i>
                            </th>

                        </tr>
                        </thead>
                        <tbody >
                        <tr  v-for="(item, index) in rowData" >
                            <td>
                                <label class="form-checkbox">
                                    <i class="form-icon"></i>
                                </label>
                            </td>
                            <td class="d-none d-sm-table-cell">{{index+1}}</td>

                            <td> <input
                                    type="text"
                                    class="form-control"
                                    :class="{'is-invalid': errors[`rowData.${index}.cname`]}"
                                    v-model = "item.cname"
                                    placeholder="Child name"
                                    autofocus/>
                            </td>
                            <td>
                                <div class="card-header-actions mr-1">
                                    <a class="btn btn-danger" @click="removeItem(index)" href="#"><i class="fas fa-minus"></i></a>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="card-header-actions mr-1">
                        <a class="btn btn-secondary" @click="addItem" href="#"><i class="fas fa-plus"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Multiselect from "vue-multiselect";

    export default {
        components: {
            Multiselect
        },
        data() {
            return {
                rowData:[{"cname":""}] ,
                drivers: {},
                parent: {},
                errors: {},
                loading: true,
                submiting: false,
                options: []
            };
        },
        mounted() {
            this.getDrivers();
        },
        methods: {
            addItem(){
                // console.log(this.parent);
                this.cname = '';
                var my_object = {
                    cname:this.cname,
                };
                this.rowData.push(my_object)
            },
            removeItem(index){
                // console.log("ssssss")
                // console.log(index)
                // console.log(this.rowData)
                this.rowData.splice(index, 1)
                // console.log(this.rowData)
            },
            getDrivers() {
                this.loading = true;
                this.drivers = [];

                axios.get(`/api/drivers/all`).then(response => {
                    this.drivers = response.data;
                    // console.log(this.drivers);
                    this.options = this.drivers;

                    this.loading = false;
                });
            },
            create() {
                // console.log(this.rowData)
                // console.log(this.parent)
                if (!this.submiting) {
                    this.submiting = true;
                    axios
                        .post("/api/parents/store", {
                            params: {
                                rowData: this.rowData,
                                parent: this.parent
                            }
                        })
                        .then(response => {
                            this.$toasted.global.error("Created parent!");
                            location.href = "/parents";
                        })
                        .catch(error => {
                            this.errors = error.response.data.errors;
                            this.submiting = false;
                        });
                }
            }
        }
    };
</script>
<style lang="scss" scoped>
    .option_name {
        color: gray;
    }
    .multiselect.form-control {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 0 !important;
        border: none !important;
    }
</style>
