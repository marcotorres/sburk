<template>
    <div class="container">
    <div class="card-header px-0 mt-2 bg-transparent clearfix">
      <h4 class="float-left pt-2"><i class="card-icon fas fa-trophy"></i> Current plan</h4>
    </div>
    <div class="row justify-content-md-center py-4">
      <div class="col-sm-6 col-xl-4" v-if="!loading">
        <div class="card">
          <div class="card-body p-3 d-flex align-items-center">
            <div>
              <div class="text-value-sm text-dark">{{school.plan.name}}</div>
              <div class="text-muted font-weight-bold py-1 pt-3">Price per {{billing_cycle}}: {{school.plan.price==0?"Free": school.plan.price + ' ' + currency}}</div>
              <div class="text-muted font-weight-bold py-1">Maximum number of drivers: {{school.plan.allowed_drivers==-1?"Unlimited":school.plan.allowed_drivers}}</div>

              <div class="text-muted font-weight-bold py-1" v-if="school.plan.price!=0">
                Renews on: {{school.plan_renews_at}}
              </div>
            </div>
          </div>
          <div class="card-footer px-3 py-2">
            <div class="btn-block text-success d-flex justify-content-center align-items-center">
              <i class="fas fa-check"></i>
              <span class="font-weight-bold px-2">Current plan</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-4" v-else>
        <content-placeholders :rounded="true">
          <content-placeholders-img />
        </content-placeholders>
      </div>
    </div>

    <div class="card-header px-0 mt-2 bg-transparent clearfix">
      <h4 class="float-left pt-2"><i class="card-icon fas fa-pencil-alt"></i> Change plan</h4>
    </div>
      <div class="row justify-content-md-center py-4">
        <div v-for="plan in plans" v-if="!loading" v-bind:class="{ 'col-sm-6 col-xl-4': school.plan_id != plan.id }" >
          <div class="card" v-if="school.plan_id != plan.id">
            <div class="card-body p-3 d-flex align-items-center">
              <div>
                <div class="text-value-sm text-dark">{{plan.name}}</div>
                <div class="text-muted font-weight-bold py-1 pt-3">Price per {{billing_cycle}}: {{plan.price==0?"Free": plan.price + ' ' + currency}}</div>
                <div class="text-muted font-weight-bold py-1">Maximum number of drivers: {{plan.allowed_drivers==-1?"Unlimited":plan.allowed_drivers}}</div>


              </div>
            </div>
          <div class="card-footer p-4 d-flex justify-content-center align-items-center">
            <button v-if="is_stripe_enabled || plan.price==0" 
            class="btn btn-block btn-primary p-4" v-on:click="selectPlan(plan.id)">
              Select
              <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
            </button>
            <button v-else class="btn btn-block btn-secondary p-4" v-on:click="selectPlan(plan.id)">
              How to change
            </button>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-4" v-else>
          <content-placeholders :rounded="true">
            <content-placeholders-img />
          </content-placeholders>
        </div>
      </div>
    </div>
</template>

<script>
import avatar from "./Avatar.vue";

export default {
  data() {
    return {
      plans: [],
      school: [],
      errors: {},
      loading: true,
      submiting: false,
      is_stripe_enabled:'',
      currency: {},
      billing_cycle: {},
    };
  },
  components: {
    avatar
  },
  mounted() {
    this.getAuthUser();
  },
  methods: {
    getAuthUser() {
      this.loading = true;
      axios.get(`/api/profile/getAuthUser`).then(response => {
        this.school = response.data;
        this.getPlans();
        this.loading = false;
      });
    },
    getPlans() {
      this.loading = true;
      axios.get(`/api/plans/getPlans`).then(response => {
        console.log(response.data);
        this.plans = response.data.plans;
        this.currency = response.data.currency;
        this.billing_cycle = response.data.billing_cycle;
        this.is_stripe_enabled = response.data.is_stripe_enabled;
        this.loading = false;
      });
    },
    selectPlan(planId) {
      //check if stripe is enabled
      if(this.is_stripe_enabled)
      {
        //if so,
        if(this.school.plan.price!=0)
        {
          swal({
            title: "Are you sure?",
            text: "The new plan will take effect immediately and you will not be able to continue the current subscription!",
            icon: "warning",
            buttons: true,
            dangerMode: true
          }).then(willProceed => {
            if (willProceed) {
              location.href = `/plan/${planId}/pay`
            }
          });
        }
        else
          location.href = `/plan/${planId}/pay`
      }
      else
      {
        var selected_plan = null;
        for (var i = 0; i < this.plans.length; i++) {
          if (this.plans[i].id == planId){
            selected_plan = this.plans[i];
            break;
          }
        }
        if(selected_plan!=null)
        {
          if(selected_plan.price==0)
          {
            swal({
              title: "Are you sure?",
              text: "The new plan will take effect immediately and you will not be able to continue the current subscription!",
              icon: "warning",
              buttons: true,
              dangerMode: true
            }).then(willProceed => {
              if (willProceed) {
                location.href = `/plan/${planId}/pay`
              }
            });
          }
          else
            swal("Contact system administrator", "In order to change your current plan, please contant the system administrator.", "info");
        }
      }
    },
  }
};
</script>
<style scoped>
.btn
{
  padding: 0 !important;
}
.btn:focus, .btn.focus {
    outline: 0;
    -webkit-box-shadow: 0 0 0;
    box-shadow: 0 0 0;
}
</style>