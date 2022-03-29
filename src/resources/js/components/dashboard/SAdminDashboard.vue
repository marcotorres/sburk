<template>
  <div>
    <div class="container">
      <div class="card-header px-0 mt-2 bg-transparent clearfix">
        <h4 class="float-left pt-2">
          <i class="card-icon fas fa-tachometer-alt"></i> Dashborad
        </h4>
      </div>
      <div class="card-body">
        <div class="row justify-content-md-center">
          <div class="col-12">
            <div class="card" v-if="!schoolsLoading">
              <div class="card-body row p-3 d-flex justify-items-between">
                <div class="col-sm-4">
                  <i class="fas fa-landmark bg-primary p-3 font-2xl mr-3"></i>
                  <span class="text-value text-primary my-3">
                    <a href="/schools">Schools</a>
                  </span>
                  <div class="py-1">
                    <div
                      class="text-muted text-uppercase font-weight-bold small py-1"
                    >Total: {{SchoolsCount}}</div>
                  </div>
                </div>
                <div class="py-4 col-sm-8">
                  <pie-chart :colors="['#fca507', '#82A1AC', '#B24C38']" :data="SchoolsInfo"></pie-chart>
                </div>
              </div>
              <div class="card-footer px-3 py-2">
                <a
                  class="btn-block text-muted d-flex justify-content-between align-items-center"
                  href="/schools"
                >
                  <span class="small font-weight-bold">View</span>
                  <i class="fa fa-angle-right"></i>
                </a>
              </div>
            </div>
            <content-placeholders :rounded="true" v-else>
              <content-placeholders-img />
            </content-placeholders>
          </div>
        </div>
        <div class="row justify-content-md-center">
          <div class="col-12">
            <div class="card" v-if="!balanceLoading">
              <div class="card-body row p-3 d-flex justify-items-between">
                <div class="col-sm-4">
                  <i class="fas fa-landmark bg-primary p-3 font-2xl mr-3"></i>
                  <span class="text-value text-primary my-3">
                    Balance
                  </span>
                </div>
                <div class="py-4 col-sm-8 d-flex justify-content-center">
                  <div class="col-sm-6">
                    <column-chart :colors="['#fca507', '#82A1AC', '#B24C38']" :prefix="'('+currency+')'"  :data="BalanceInfo"></column-chart>
                  </div>
                </div>
              </div>
              <div class="card-footer px-3 py-2">
              </div>
            </div>
            <content-placeholders :rounded="true" v-else>
              <content-placeholders-img />
            </content-placeholders>
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
      SchoolsInfo: [],
      BalanceInfo: [],
      SchoolsCount: 0,
      schoolsLoading: false,
      balanceLoading: false,
      currency: {},
    };
  },
  mounted() {
    this.getSchoolsInfo();
    this.getStripeBalance();
  },
  methods: {
    getSchoolsInfo() {
      this.schoolsLoading = true;
      axios.get(`/api/dashboard/getSchoolsInfo`).then(response => {
        console.log(response.data);
        this.SchoolsInfo = [];
        this.SchoolsCount = 0;
        this.currency = response.data.currency;
        response.data.schools.forEach(school => {
          this.SchoolsInfo.push([school.plan.name, school.total]);
          this.SchoolsCount += school.total;
        });
        this.schoolsLoading = false;
      });
    },
    getStripeBalance() {
      this.balanceLoading = true;
      axios
        .get(`/api/dashboard/getStripeBalance`)
        .then(response => {
          this.BalanceInfo = [];
          this.BalanceInfo = [
            {name: 'Available', data: {'Available': response.data.available[0].amount/100.0}},
            {name: 'Pending', data: {'Pending': response.data.pending[0].amount/100.0}}
          ];
        })
        .catch(error => {
          this.$toasted.global.error(
            "Error retrieving balance from Stripe account!"
          );
        })
        .then(() => {
          this.balanceLoading = false;
        });
    }
  }
};
</script>
