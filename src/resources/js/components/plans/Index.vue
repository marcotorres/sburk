<template>
  <div class="container">
    <div class="card-header px-0 mt-2 bg-transparent clearfix">
      <h4 class="float-left pt-2"><i class="card-icon far fa-handshake"></i> Plans</h4>
      <div v-if="can_create_plan" class="card-header-actions mr-1">
        <a class="btn btn-primary" href="/plans/create">New plan</a>
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
      <table class="table table-hover" v-if="!loading">
        <thead>
          <tr>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('id')">ID</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'id' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'id' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th>
              <a href="#" class="text-dark" @click.prevent="sort('name')">Plan</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'name' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'name' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th>
              <a href="#" class="text-dark" @click.prevent="sort('price')">price per {{billing_cycle}}</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'price' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'price' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('allowed_drivers')">Allowed drivers</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'allowed_drivers' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'allowed_drivers' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          
          <tr v-for="plan in plans" v-if=!loading @click="editPlan(plan)">
            <td class="d-none d-sm-table-cell">{{plan.id}}</td>
            <td>{{plan.name}}</td>
            <td> <span v-if="currency=='USD'" > ${{plan.price}} </span> 
                 <span v-else> {{plan.price}} {{currency}} </span></td>
            <td class="d-none d-sm-table-cell">{{plan.allowed_drivers==-1?"Unlimited":plan.allowed_drivers}}</td>
            
            <td>
              <a href="#" class="text-muted" @click="editPlan(plan)" v-if="plan.price==0">
                <i class="fas fa-pencil-alt"></i>
              </a>
              <a class="text-muted" href="#" v-else
               @click.prevent="deletePlan(plan.id)">
                  <i class="far fa-trash-alt"></i>
              </a>
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
      <div class="no-items-found text-center mt-5" v-if="!loading && !plans.length > 0">
        <i class="icon-magnifier fa-3x text-muted"></i>
        <p class="mb-0 mt-3"><strong>Could not find any items</strong></p>
        <p class="text-muted">Try changing the filters or add a new one</p>
        <a class="btn btn-success" href="/plans/create" role="button">
          <i class="fa fa-plus"></i>&nbsp; New Plan
        </a>
      </div>
    </div>
      <content-placeholders v-if="loading">
        <content-placeholders-text/>
      </content-placeholders>
  </div>
</template>

<script>
export default {
  props: {
    can_create_plan: Boolean
  },
  data () {
    return {
      plans: [],
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
      currency: {},
      billing_cycle: {},
      loading: true
    }
  },
  mounted () {
    if (localStorage.getItem("filtersTablePlans")) {
      this.filters = JSON.parse(localStorage.getItem("filtersTablePlans"))
    } else {
      localStorage.setItem("filtersTablePlans", this.filters);
    }
    this.getPlans()
  },
  methods: {
    getPlans () {
      this.loading = true
      this.plans = []

      localStorage.setItem("filtersTablePlans", JSON.stringify(this.filters));

      axios.post(`/api/plans/filter?page=${this.filters.pagination.current_page}`, this.filters)
      .then(response => {
        this.plans = response.data.plans.data
        this.currency = response.data.currency
        this.billing_cycle = response.data.billing_cycle
        delete response.data.plans.data
        this.filters.pagination = response.data
        this.loading = false
      })
    },
    editPlan (plan) {
      location.href = `/plans/${plan.id}/edit`
    },
    // filters
    filter() {
      this.filters.pagination.current_page = 1
      this.getPlans()
    },
    changeSize (perPage) {
      this.filters.pagination.current_page = 1
      this.filters.pagination.per_page = perPage
      this.getPlans()
    },
    sort (column) {
      if(column == this.filters.orderBy.column) {
        this.filters.orderBy.direction = this.filters.orderBy.direction == 'asc' ? 'desc' : 'asc'
      } else {
        this.filters.orderBy.column = column
        this.filters.orderBy.direction = 'asc'
      }

      this.getPlans()
    },
    changePage (page) {
      this.filters.pagination.current_page = page
      this.getPlans()
    }
  }
}
</script>
