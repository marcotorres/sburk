<template>
  <div class="container">
    <div class="card-header px-0 mt-2 bg-transparent clearfix">
      <h4 class="float-left pt-2"><i class="card-icon fas fa-landmark"></i> Schools</h4>
      <div v-if="can_create_school" class="card-header-actions mr-1">
        <a class="btn btn-primary" href="/schools/create">Add School</a>
      </div>
      <div v-else class="card-header-actions mr-1">
        <a class="btn btn-danger" href="/settings">Upgrade Stripe settings</a>
      </div>
    </div>

    <div class="card-body px-0">
      <div class="row justify-content-between">
        <div class="col-7 col-md-5">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text" @click="filter">
                <i class="fas fa-search"></i>
              </span>
            </div>
            <input type="text" class="form-control" placeholder="Seach" v-model.trim="filters.search" @keyup.enter="filter">
          </div>
        </div>
        <div class="col-auto">
          <multiselect
            v-model="filters.pagination.per_page"
            :options="[25,50,100,200]"
            :searchable="false"
            :show-labels="false"
            :allow-empty="false"
            @select="changeSize"
            placeholder="Search">
          </multiselect>
        </div>
      </div>
      <table class="table table-hover">
        <thead>
          <tr>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('id')">ID</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'id' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'id' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th>
              <a href="#" class="text-dark" @click.prevent="sort('name')">School admin</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'name' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'name' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('created_at')">Registered</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'created_at' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'created_at' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('plan_id')">Plan</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'plan_id' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'plan_id' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('plan_renews_at')">Renews on</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'plan_renews_at' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'plan_id' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="school in schools">
            <td class="d-none d-sm-table-cell">{{school.id}}</td>
            <td>
              <div class="media">
                <div class="avatar float-left mr-3">
                  <img class="img-avatar" :src="school.avatar_url">
                  <span class="avatar-status badge-success"></span>
                </div>
                <div class="media-body">
                  <div>{{school.name}}</div>
                  <div class="small text-muted">
                    {{school.email}}
                  </div>
                </div>
              </div>
            </td>
            <td class="d-none d-sm-table-cell">
              <small>{{school.created_at | moment("LL")}}</small> - <small class="text-muted">{{school.created_at | moment("LT")}}</small>
            </td>
            <td class="d-none d-sm-table-cell">
              <small>{{school.plan.name}}</small>
            </td>
            <td class="d-none d-sm-table-cell">
              <small v-if="school.plan.price!=0">{{school.plan_renews_at | moment("LL")}}</small>
              <small v-else>Forever</small>
            </td>
            <td class="d-sm-table-cell">
              <div class="dropdown">
                <button class="btn" type="button" id="dropdownMenuButton" 
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a class="dropdown-item card-header-action ml-1" href="#" :disabled="submitingDestroy" @click.prevent="destroy(school.id)">
                    <i title="Delete school" class="far fa-trash-alt"></i>
                    <span class="ml-1">Delete</span>
                  </a>
                  <a class="dropdown-item card-header-action ml-1" href="#" :disabled="submitingLogin" @click.prevent="login(school.id)">
                    <i title="Login as school" class="fas fa-user-edit"></i>
                    <span class="ml-1">Login as school</span>
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item card-header-action ml-1" href="#" 
                  @click="openSelectPlanModal(school.id)" :disabled="submitingChangePlan" >
                    <i title="Change plan" class="fas fa-pencil-alt"></i>
                    <span class="ml-1">Change plan</span>
                  </a>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="row" v-if='!loading && filters.pagination.total > 0'>
        <div class="col pt-2">
          {{filters.pagination.from}}-{{filters.pagination.to}} of {{filters.pagination.total}}
        </div>
        <div class="col" v-if="filters.pagination.last_page>1">
          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-end">
              <li class="page-item" :class="{'disabled': filters.pagination.current_page <= 1}">
                <a class="page-link" href="#" @click.prevent="changePage(filters.pagination.current_page -  1)"><i class="fas fa-angle-left"></i></a>
              </li>
              <li class="page-item" v-for="page in filters.pagination.last_page" :class="{'active': filters.pagination.current_page == page}">
                <span class="page-link" v-if="filters.pagination.current_page == page">{{page}}</span>
                <a class="page-link" href="#" v-else @click.prevent="changePage(page)">{{page}}</a>
              </li>
              <li class="page-item" :class="{'disabled': filters.pagination.current_page >= filters.pagination.last_page}">
                <a class="page-link" href="#" @click.prevent="changePage(filters.pagination.current_page +  1)"><i class="fas fa-angle-right"></i></a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
      <div class="no-items-found text-center mt-5" v-if="!loading && !schools.length > 0">
        <i class="icon-magnifier fa-3x text-muted"></i>
        <p class="mb-0 mt-3"><strong>Could not find any items</strong></p>
        <p class="text-muted">Try changing the filters or add a new one</p>
      </div>
      <content-placeholders v-if="loading">
        <content-placeholders-text/>
      </content-placeholders>
    </div>

<!-- Select Plan Modal -->
<div class="modal fade" id="selectPlanModal" ref="selectPlanModal" tabindex="-1" role="dialog" 
aria-labelledby="selectPlanModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Select Plan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            <multiselect
              v-model="selectedschool.plan"
              placeholder="Select plan"
              label="name"
              track-by="name"
              class="form-control"
              :options="plans"
              :option-height="104"
              :show-labels="false"
            >
              <template slot="option" slot-scope="props">
                <div class="option_tel_number">
                  <div class="option_name p-1">
                    <i class="fas fa-trophy px-2"></i>
                    {{ props.option.name }}
                  </div>
                </div>
              </template>
            </multiselect>
          <div class="card" v-if="selectedschool.plan">
            <div class="card-body p-3 d-flex align-items-center">
              <div>
                <div class="text-value-sm text-dark">{{selectedschool.plan.name}}</div>
                <div class="text-muted font-weight-bold py-1 pt-3">Price per {{billing_cycle}}: {{selectedschool.plan.price==0?"Free": selectedschool.plan.price + ' ' + currency}}</div>
                <div class="text-muted font-weight-bold py-1">Maximum number of drivers: {{selectedschool.plan.allowed_drivers==-1?"Unlimited":selectedschool.plan.allowed_drivers}}</div>
              </div>
            </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click.prevent="restoreOriginalPlan" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" @click.prevent="selectPlan">
          <i class="fas fa-spinner fa-spin" v-if="submitingChangePlan"></i>
          OK
        </button>
      </div>
    </div>
  </div>
</div>
  </div>
</template>

<script>
export default {
  props: {
    can_create_school: Boolean
  },
  data () {
    return {
      schools: [],
      selectedschool:{},
      plans:[],
      selectedplan:{},
      filters: {
        pagination: {
          from: 0,
          to: 0,
          total: 0,
          per_page: 25,
          current_page: 1,
          last_page: 0
        },
        orderBy: {
          column: 'id',
          direction: 'asc'
        },
        search: ''
      },
      loading: true,
      submitingDestroy: false,
      submitingLogin: false,
      submitingChangePlan: false,
      currency: {},
      billing_cycle: {},
    }
  },
  mounted () {
    if (localStorage.getItem("filtersTableSchools")) {
      this.filters = JSON.parse(localStorage.getItem("filtersTableSchools"))
    } else {
      localStorage.setItem("filtersTableSchools", this.filters);
    }
    this.getSchools()
  },
  methods: {
    openSelectPlanModal(schoolId) {
      this.selectedschool = null;
      for (var i = 0; i < this.schools.length; i++) {
        if (this.schools[i].id == schoolId){
          this.selectedschool = this.schools[i];
          break;
        }
      }
      if(this.selectedschool!=null)
        $(this.$refs.selectPlanModal).modal('show');
    },
    selectPlan(school_id) {
      console.log(this.selectedschool.plan.id, this.selectedschool.plan_id);
      if (!this.submitingChangePlan && this.selectedschool.plan.id!=this.selectedschool.plan_id) {
        this.submitingChangePlan = true;
        swal({
          title: "Are you sure?",
          text: "You are about to change the plan of this school, do you want to continue?",
          icon: "warning",
          buttons: true,
          dangerMode: true
        }).then(willChange => {
          if (willChange) {
            console.log(this.selectedschool.plan)
            axios.post(`/api/profile/changePlan`, this.selectedschool)
            .then(response => {
              $(this.$refs.selectPlanModal).modal('hide');
              this.$toasted.global.error("Plan changed successfully!");
              this.getSchools();
            })
            .catch(error => {
                this.$toasted.global.error('Error in changing plan');
            })
            .then(() => {
                this.submitingChangePlan = false;
                this.loading = false;
            });
          }
          else
          {
            this.restoreOriginalPlan();
          }
          this.submitingChangePlan = false;
        });
      }
    },
    getSchools () {
      this.loading = true
      this.schools = []

      localStorage.setItem("filtersTableSchools", JSON.stringify(this.filters));

      axios.post(`/api/schools/filter?page=${this.filters.pagination.current_page}`, this.filters)
      .then(response => {
        this.schools = response.data.schools.data;
        this.plans = response.data.plans;
        this.currency = response.data.currency;
        this.billing_cycle = response.data.billing_cycle;
        delete response.data.data;
        this.filters.pagination = response.data
        this.loading = false
      })
    },
    restoreOriginalPlan()
    {
      var original_plan = null;
      for (var i = 0; i < this.plans.length; i++) {
        if (this.plans[i].id == this.selectedschool.plan_id){
          original_plan = this.plans[i];
          break;
        }
      }
      this.selectedschool.plan = original_plan;
    },
    // filters
    filter() {
      this.filters.pagination.current_page = 1
      this.getSchools()
    },
    changeSize (perPage) {
      this.filters.pagination.current_page = 1
      this.filters.pagination.per_page = perPage
      this.getSchools()
    },
    sort (column) {
      if(column == this.filters.orderBy.column) {
        this.filters.orderBy.direction = this.filters.orderBy.direction == 'asc' ? 'desc' : 'asc'
      } else {
        this.filters.orderBy.column = column
        this.filters.orderBy.direction = 'asc'
      }

      this.getSchools()
    },
    changePage (page) {
      this.filters.pagination.current_page = page
      this.getSchools()
    },
    destroy(school_id) {
      if (!this.submitingDestroy) {
        this.submitingDestroy = true;
        swal({
          title: "Are you sure?",
          text: "Once deleted, you will not be able to recover this school account!",
          icon: "warning",
          buttons: true,
          dangerMode: true
        }).then(willDelete => {
          if (willDelete) {
            axios
              .delete(`/api/schools/${school_id}`)
              .then(response => {
                this.$toasted.global.error("Deleted school account!");
                location.href = "/schools";
              })
              .catch(error => {
                this.errors = error.response.data.errors;
                swal("Error", error.response.data.errors[0], "error")
              });
          }
          this.submitingDestroy = false;
        });
      }
    },
    login(school_id) {
      if (!this.submitingLogin) {
        this.submitingLogin = true;
        swal({
          title: "Are you sure?",
          text: "You are about to log in as a school admin user, do you want to continue?",
          icon: "warning",
          buttons: true,
          dangerMode: true
        }).then(willLogin => {
          if (willLogin) {
            axios.post(`/api/dashboard/loginAsSchoolAdmin`, {'school_id': school_id})
            .then(response => {
              if(response.data)
              {
                location.href = "/dashboard";
              }
            })
            .catch(error => {
                this.$toasted.global.error('Error in switching acount')
            })
            .then(() => {
                
            });
            
          }
          this.submitingLogin = false;
        });
      }
    },
  }
}
</script>
