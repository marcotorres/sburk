<template>
  <div class="container">
    <div class="row justify-content-md-center">
      <div class="col-md-9 col-xl-7" v-if="!loading">
        <div class="card-header px-0 mt-2 bg-transparent clearfix">
          <h4 class="float-left pt-2">New Plan</h4>
          <div class="card-header-actions mr-1">
            <a class="btn btn-primary" href="#" :disabled="submiting" @click.prevent="create">
              <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
              <i class="fas fa-check" v-else></i>
              <span class="ml-1">Save</span>
            </a>
          </div>
        </div>
        <div class="card-body px-0">
          <div class="row">
            <div class="form-group col-md-12">
              <label>Name</label>
              <input type="text" class="form-control" :class="{'is-invalid': errors.name}" 
              v-model="plan.name" placeholder="Enter plane name">
              <div class="invalid-feedback" v-if="errors.name">{{errors.name[0]}}</div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Price per {{billing_cycle}}</label>
                <input type="text"
                class="form-control" :class="{'is-invalid': errors.price}" v-model="plan.price" 
                :placeholder=currency>
                <div class="invalid-feedback" v-if="errors.price">{{errors.price[0]}}</div>
              </div>
              <div class="form-group">
                <label>Maximum number of drivers</label>
                <input type="text" class="form-control" 
                :class="{'is-invalid': errors.allowed_drivers}" v-model="plan.allowed_drivers">
                <div class="invalid-feedback" v-if="errors.allowed_drivers">{{errors.allowed_drivers[0]}}</div>
                <small>Enter -1 for unlimited</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-9 col-xl-7" v-if="loading">
        <content-placeholders>
          <content-placeholders-text/>
        </content-placeholders>
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
      submiting: false,
      currency: {},
      billing_cycle: {},
      loading: true,
    }
  },
  mounted () {
      axios.post(`/api/plans/filter`)
      .then(response => {
        this.plans = response.data.plans.data
        this.currency = response.data.currency
        this.billing_cycle = response.data.billing_cycle
        this.loading = false
      })
  },
  methods: {
    create () {
      if (!this.submiting) {
        this.submiting = true
        axios.post(`/api/plans/store`, this.plan)
        .then(response => {
          console.log(response);
          this.$toasted.global.error('Created plan!')
          location.href = '/plans'
        })
        .catch(error => {
          this.$toasted.global.error('Error in creating plan!')
          this.errors = error.response.data.errors
          this.submiting = false
          if(error.response.data.errors.Stripe[0])
          {
            swal("Stripe Error", error.response.data.errors.Stripe[0], "error")
          }
        })
      }
    },
  }
}
</script>
