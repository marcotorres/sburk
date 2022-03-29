<template>
  <div>
    <div class="container">
      <div class="card-header px-0 mt-2 bg-transparent clearfix">
        <h4 class="float-left pt-2"><i class="card-icon fas fa-tachometer-alt"></i> Dashborad</h4>
      </div>
      <div class="card-body">
        <div class="row justify-content-md-center">
          <div class="col-12">
            <div class="card" v-if="!isLoading">
              <div class="card-body row p-3 d-flex justify-items-between">
                <div class="col-sm-4">
                  <i class="fas fa-bus-alt bg-primary p-3 font-2xl mr-3"></i>
                  <span class="text-value text-primary my-3">
                    <a href="/drivers">Drivers</a>
                  </span>
                  <div class="py-1">
                    <div
                      class="text-muted text-uppercase font-weight-bold small py-1"
                    >Total: {{ParentsDriversInfo.drivers_count}}</div>
                    <div
                      class="text-muted text-uppercase font-weight-bold small py-1"
                    >Verified: {{ParentsDriversInfo.verified_drivers_count}}</div>
                    <div
                      class="text-muted text-uppercase font-weight-bold small py-1"
                    >Not Verified: {{ParentsDriversInfo.not_verified_drivers_count}}</div>
                  </div>
                </div>
                <div class="py-4 col-sm-8">
                  <pie-chart
                    :colors="['#fca507', '#82A1AC']"
                    :data="[['Verified', ParentsDriversInfo.verified_drivers_count], ['Not verified', ParentsDriversInfo.not_verified_drivers_count]]"
                  ></pie-chart>
                </div>
              </div>
              <div class="card-footer px-3 py-2">
                <a
                  class="btn-block text-muted d-flex justify-content-between align-items-center"
                  href="/drivers"
                >
                  <span class="small font-weight-bold">View</span>
                  <i class="fa fa-angle-right"></i>
                </a>
              </div>
            </div>
            <div class="py-4" v-else>
              <content-placeholders :rounded="true">
                <content-placeholders-img />
              </content-placeholders>
            </div>
          </div>
        </div>
        <div class="row justify-content-md-center">
          <div class="col-12">
            <div class="card" v-if="!isLoading">
              <div class="card-body row p-3 d-flex justify-items-between">
                <div class="col-sm-4">
                  <i class="fas fa-users bg-primary p-3 font-2xl mr-3"></i>
                  <span class="text-value text-primary my-3">
                    <a href="/parents">Parents</a>
                  </span>
                  <div class="py-1">
                    <div
                      class="text-muted text-uppercase font-weight-bold small py-1"
                    >Total: {{ParentsDriversInfo.parents_count}}</div>
                    <div
                      class="text-muted text-uppercase font-weight-bold small py-1"
                    >Verified: {{ParentsDriversInfo.verified_parents_count}}</div>
                    <div
                      class="text-muted text-uppercase font-weight-bold small py-1"
                    >Not Verified: {{ParentsDriversInfo.not_verified_parents_count}}</div>
                  </div>
                </div>
                <div class="py-4 col-sm-8">
                  <pie-chart
                    :colors="['#fca507', '#82A1AC']"
                    :data="[['Verified', ParentsDriversInfo.verified_parents_count], ['Not verified', ParentsDriversInfo.not_verified_parents_count]]"
                  ></pie-chart>
                </div>
              </div>
              <div class="card-footer px-3 py-2">
                <a
                  class="btn-block text-muted d-flex justify-content-between align-items-center"
                  href="/parents"
                >
                  <span class="small font-weight-bold">View</span>
                  <i class="fa fa-angle-right"></i>
                </a>
              </div>
            </div>
            <div class="py-4" v-else>
              <content-placeholders :rounded="true">
                <content-placeholders-img />
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
  data() {
    return {
      ParentsDriversInfo: {},
      isLoading:false,
    };
  },
  mounted() {
    this.getParentsDriversInfo();
  },
  methods: {
    getParentsDriversInfo() {
      this.isLoading = true;
      axios.get(`/api/dashboard/getParentsDriversInfo`).then(response => {
        console.log(response.data);
        this.ParentsDriversInfo = response.data;
        this.isLoading = false;
      });
    }
  }
};
</script>
