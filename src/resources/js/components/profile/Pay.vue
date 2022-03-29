<template>
  <div class="container">
    <div class="row justify-content-md-center">
      <div class="col-md-9 col-xl-7" v-if="!loading">
        <div class="card-header px-0 mt-2 bg-transparent clearfix">
          <h4 class="float-left pt-2">Pay {{plan.price}} ({{currency}}) for "{{plan.name}}" plan</h4>
        </div>
      </div>
      <div class="col-md-9 col-xl-7" v-else>
        <content-placeholders>
          <content-placeholders-text />
        </content-placeholders>
      </div>
    </div>

    <div class="row justify-content-md-center py-4" v-show="!loading">
      <div class="col-sm-12 col-lg-6">
        <div class="card">
          <div class="card-header text-value-sm text-dark py-2">Enter your credit card information</div>
          <div class="card-body">
            <div ref="card">
              <!-- A Stripe Element will be inserted here. -->
            </div>
            <!-- Used to display form errors. -->
            <div class="invalid mt-2" role="alert" v-if="errors">{{errors}}</div>
          </div>
          <div class="card-footer py-2 d-flex justify-content-center align-items-center">
            <button class="btn btn-primary" v-on:click="purchase">
              Pay
              <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="row justify-content-md-center py-4" v-show="loading">
      <div class="col-sm-12 col-lg-6">
        <content-placeholders :rounded="true">
          <content-placeholders-img />
        </content-placeholders>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    stripe_publishable_key: String
  },
  data() {
    return {
      stripe: "",
      card: "",
      currency: {},
      plan: {},
      loading: true,
      submiting: false,
      errors: ""
    };
  },
  mounted() {
    this.stripe = Stripe(this.stripe_publishable_key);
    this.card = this.stripe.elements().create("card");
    this.card.mount(this.$refs.card);
    this.getPlan();
  },
  methods: {
    purchase() {
      if (!this.submiting) {
        this.submiting = true;
        let self = this;
        this.stripe.createPaymentMethod({
          type: 'card',
          card: this.card,
        }).then(function(result) {
          if (result.error) {
            self.hasCardErrors = true;
            self.errors = result.error.message;
            self.submiting = false;
            self.$toasted.global.error("Error!");
          } else {
            self.errors = "";
            //submit paymentMethodId to server
            self.submitPaymentMethod(result.paymentMethod.id);
          }
        });
      }
    },
    getPlan() {
      this.loading = true;
      let str = window.location.pathname;
      let res = str.split("/");
      axios
        .get(`/api/profile/getPlan/${res[2]}`)
        .then(response => {
          console.log(response.data);
          this.plan = response.data.plan;
          this.currency = response.data.currency;
        })
        .catch(error => {
          this.$toasted.global.error("Plan does not exist!");
          location.href = "/plan";
        })
        .then(() => {
          this.loading = false;
        });
    },
    submitPaymentMethod(paymentMethodId) {axios
          .post("/api/profile/updatePayment", {paymentMethodId: paymentMethodId, plan: this.plan.id})
          .then(response => {
            this.$toasted.global.error("Payment method updated!");
            this.submiting = false;
            location.href = "/plan";
          })
          .catch(error => {
            this.$toasted.global.error("Errors in payment!");
            this.submiting = false;
            if(error.response.data.errors.Payment[0])
            {
              swal("Payment Error", error.response.data.errors.Payment[0], "error")
            }
          });
    }
  }
};
</script>