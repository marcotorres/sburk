<template>
  <div class="container">
    <div class="row justify-content-md-center">
      <div class="col-md-12 col-xl-9">
        <div class="card-header px-0 mt-2 bg-transparent clearfix">
          <h4 class="float-left pt-2">Edit Parent</h4>
          <div class="card-header-actions mr-1">
            <a class="btn btn-primary" href="#" :disabled="submiting" @click.prevent="update">
              <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
              <i class="fas fa-check" v-else></i>
              <span class="ml-1">Save</span>
            </a>
            <a
              class="card-header-action ml-1"
              href="#"
              :disabled="submitingDestroy"
              @click.prevent="destroy"
            >
              <i class="fas fa-spinner fa-spin" v-if="submitingDestroy"></i>
              <i class="far fa-trash-alt" v-else></i>
              <span class="d-md-down-none ml-1">Delete</span>
            </a>
          </div>
        </div>
        <div class="card-header px-0 bg-transparent">
          <strong>General</strong>
          <br />
          <small class="text-muted">Update data of parent.</small>
        </div>
        <div class="card-body px-0">
          <div class="row" v-if="!loading">
            <div class="form-group col-sm-9">
              <label>Parent name</label>
              <input
                      type="text"
                      class="form-control"
                      :class="{'is-invalid': errors['parent.name']}"
                      v-model="parent.name"
                      placeholder="Name"
                      autofocus
              />
              <div class="invalid-feedback" v-if="errors['parent.name']">{{errors['parent.name'][0]}}</div>
            </div>
            <div class="form-group col-sm-3">
              <label>Parent ID</label>
              <input type="text" class="form-control" v-model="parent.id" readonly />
            </div>
            <div class="form-group col-sm-3">
              <label>Country code</label>
              <div class="d-flex">
                <label class="pr-2">+</label>
                <input type="text" class="form-control" :class="{'is-invalid': errors['parent.country_code'] || errors['parent.tel_number']}" v-model="parent.country_code" placeholder="country code">
              </div>
            </div>
            <div class="form-group col-sm-9">
              <label>Parent telephone</label>
              <input type="text" class="form-control" :class="{'is-invalid': errors['parent.tel_number']}" v-model="parent.tel_number" placeholder="telephone number">
              <div class="invalid-feedback" v-if="errors['parent.tel_number']">{{errors['parent.tel_number'][0]}}</div>
            </div>
            <div class="form-group col-sm-6">
              <label>Verification Code</label>
              <input type="text" class="form-control" v-model="parent.v_code" readonly />
            </div>
            <div class="form-group col-sm-6">
              <label>Verified?</label>
              <div class="ml-4">
                <label class="switch switch-pill switch-outline-success-alt block">
                  <input class="switch-input" type="checkbox" v-model="parent.verified" />
                  <span class="switch-slider"></span>
                </label>
              </div>
            </div>
            <div class="form-group col-sm-12">
              <label class="typo_label">Driver</label>
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
              <div class="invalid-feedback" v-if="errors.driver">{{errors.driver[0]}}</div>
            </div>
          </div>
          <content-placeholders v-else>
            <content-placeholders-heading />
          </content-placeholders>
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

              <td>
                <input type="text"
                      class="form-control"
                      :class="{'is-invalid': errors[`rowData.${index}.childName`]}"
                      v-model = "item.childName"
                      placeholder="Child name"
                      autofocus/>
              </td>

              <td>
                <div class="card-header-actions mr-1">
                  <a class="btn btn-danger" @click="removeItem(index)" ><i class="fas fa-minus"></i></a>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
          <div class="card-header-actions mr-1">
            <a class="btn btn-secondary" @click="addItem" ><i class="fas fa-plus"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      rowData:[{"childName" : ""}] ,
      parent: { driver: [] },
      options: [],
      errors: {},
      loading: true,
      submiting: false,
      submitingDestroy: false
    };
  },
  mounted() {
    this.getParent();
    this.getDrivers();
    this.getChildren();

  },
  methods: {

    addItem(){
      this.childName = "";
      var my_object = {
        childName:this.childName,
      };
      this.rowData.push(my_object)
    },
    removeItem(index){

      // console.log(this.rowData)
      this.rowData.splice(index, 1)
      // console.log(this.rowData)
    },

    getParents() {
      this.loading = true;
      this.parents = [];

      localStorage.setItem("filtersTableParents", JSON.stringify(this.filters));

      axios
        .post(
          `/api/parents/filter?page=${this.filters.pagination.current_page}`,
          this.filters
        )
        .then(response => {
          this.parents = response.data.data;
          delete response.data.data;
          this.filters.pagination = response.data;
          this.loading = false;
        });
    },
    getChildren() {
      this.loading = true;
      let str = window.location.pathname;
      let res = str.split("/");
      let list=[];
      axios
              .get(`/api/parents/child/${res[2]}`)
              .then(response => {

                // this.rowData = response.data;
                let response_data = response.data['children'];
                $.each(response_data, function(key, value) {
                  // this.childName = value['childName'];
                  // this.driver= value;
                  var my_object = {
                    childName:value['childName'],
                  };
                  list.push(my_object);

                });
                this.rowData = list;

              })
              .catch(error => {
                this.$toasted.global.error("Children does not exist!");
                //location.href = '/parents'
              })
              .then(() => {
                this.loading = false;
              });
    },
    getDrivers() {
      this.loading = true;
      this.drivers = [];

      axios.get(`/api/drivers/all`).then(response => {
        this.drivers = response.data;
        console.log(this.drivers);
        this.options = this.drivers;
        this.loading = false;
      });
    },
    getParent() {
      this.loading = true;
      let str = window.location.pathname;
      let res = str.split("/");
      axios
        .get(`/api/parents/getParent/${res[2]}`)
        .then(response => {
          this.parent = response.data;
        })
        .catch(error => {
          this.$toasted.global.error("Parent does not exist!");
          //location.href = '/parents'
        })
        .then(() => {
          this.loading = false;
        });
    },
    update() {
      if (!this.submiting) {
        this.submiting = true;

        axios.put(`/api/parents/update/${this.parent.id}`, {
                params: {
                  rowData: this.rowData,
                  parent: this.parent
                }
              })
              .then(response => {
                console.log(response);
                this.$toasted.global.error("Updated parent!");
                location.href = "/parents";
              })
              .catch(error => {
                this.errors = error.response.data.errors;
                this.submiting = false;
                swal("Error", error.response.data.errors[0], "error")
              });
      }
    },
    destroy() {
      if (!this.submitingDestroy) {
        this.submitingDestroy = true;
        swal({
          title: "Are you sure?",
          text: "Once deleted, you will not be able to recover this parent!",
          icon: "warning",
          buttons: true,
          dangerMode: true
        }).then(willDelete => {
          if (willDelete) {
            axios
              .delete(`/api/parents/${this.parent.id}`)
              .then(response => {
                this.$toasted.global.error("Parent deleted!");
                location.href = "/parents";
              })
              .catch(error => {
                this.errors = error.response.data.errors;
                swal("Error", error.response.data.errors[0], "error")
              });
          }
          this.submitingDestroy = false;
        });
      }
    }
  }
};
</script>
<style scoped>
.multiselect.form-control {
  padding-left: 0 !important;
  padding-right: 0 !important;
  border: none !important;
}
</style>
