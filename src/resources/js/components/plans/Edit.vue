<template>
  <div class="container">
    <div class="row justify-content-md-center">
      <div class="col-md-9 col-xl-7">
        <div class="card-header px-0 mt-2 bg-transparent clearfix">
          <h4 class="float-left pt-2">Edit Plan</h4>
          <div class="card-header-actions mr-1">
            <a class="btn btn-primary" href="#" v-if="plan.price==0"
            :disabled="submiting" @click.prevent="update">
              <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
              <i class="fas fa-check" v-else></i>
              <span class="ml-1">Save</span>
            </a>
            <a
              class="card-header-action ml-1" v-else
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
        <div class="card-body px-0">
          <div class="row" v-if="!loading">
            <div class="form-group col-md-9">
              <label>Name</label>
              <input type="text" class="form-control" :class="{'is-invalid': errors.name}" 
              v-model="plan.name" placeholder="Enter plane name" :disabled="plan.price!=0">
              <div class="invalid-feedback" v-if="errors.name">{{errors.name[0]}}</div>
            </div>
            <div class="form-group col-md-3">
              <label>ID</label>
              <input class="form-control" type="text" v-model="plan.id" readonly>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Price per {{billing_cycle}}</label>
                <input type="text"
                class="form-control" :class="{'is-invalid': errors.price}" v-model="plan.price" 
                :placeholder=currency readonly>
                <div class="invalid-feedback" v-if="errors.price">{{errors.price[0]}}</div>
              </div>
              <div class="form-group">
                <label>Maximum number of drivers</label>
                <input type="text" class="form-control" 
                :class="{'is-invalid': errors.allowed_drivers}" v-model="plan.allowed_drivers" :disabled="plan.price!=0">
                <div class="invalid-feedback" v-if="errors.allowed_drivers">{{errors.allowed_drivers[0]}}</div>
                <small>Enter -1 for unlimited</small>
              </div>
            </div>
          </div>
          <div class="row" v-else>
            <div class="col-md-12">
              <content-placeholders>
                <content-placeholders-text/>
              </content-placeholders>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      plan: {},
      errors: {},
      currency: {},
      billing_cycle: {},
      loading: true,
      submiting: false,
      submitingDestroy : false,
    }
  },
  mounted () {
    this.getPlan()
  },
  methods: {
    getPlan() {
      this.loading = true
      let str = window.location.pathname
      let res = str.split("/")
      axios.get(`/api/plans/${res[2]}`)
      .then(response => {
        this.plan = response.data.plan
        this.currency = response.data.currency
        this.billing_cycle = response.data.billing_cycle
      })
      .catch(error => {
        this.$toasted.global.error('Plan does not exist!')
        location.href = '/plans'
      })
      .then(() => {
        this.loading = false
      })
    },
    update () {
      if (!this.submiting) {
        this.submiting = true
        axios.put(`/api/plans/update/${this.plan.id}`, this.plan)
        .then(response => {
          this.$toasted.global.error('Plan updated!')
          this.submiting = false
          location.href = '/plans'
        })
        .catch(error => {
          this.$toasted.global.error('Error in updating plan')
          this.errors = error.response.data.errors
          swal("Error", error.response.data.errors.Stripe[0], "error")
          this.submiting = false
        })
      }
    },
    destroy() {
      if (!this.submitingDestroy) {
        this.submitingDestroy = true;
        swal({
          title: "Are you sure?",
          text: "If you delete this plan, you won't be able to create new subscriptions to it. Existing subscriptions won't be affected and will continue to be billed as usual until canceled!",
          icon: "warning",
          buttons: true,
          dangerMode: true
        }).then(willDelete => {
          if (willDelete) {
            axios
              .delete(`/api/plans/${this.plan.id}`)
              .then(response => {
                this.submitingDestroy = false;
                this.$toasted.global.error("Deleted plan!");
                location.href = "/plans";
              })
              .catch(error => {
                this.submitingDestroy = false;
                this.errors = error.response.data.errors;
                if(error.response.data.errors.Stripe[0])
                {
                  swal("Error", error.response.data.errors.Stripe[0], "error")
                }
              });
          }
          else
            this.submitingDestroy = false;
          
        });
      }
    }
  }
}
</script>
