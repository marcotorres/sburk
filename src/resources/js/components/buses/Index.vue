<template>
  <div class="container">
    <div class="card-header px-0 mt-2 bg-transparent clearfix">
      <h4 class="float-left pt-2"><i class="card-icon fas fa-bus-alt"></i> Buses</h4>
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
              <a href="#" class="text-dark" @click.prevent="sort('license')">License</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'license' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'license' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th>
              <a href="#" class="text-dark">Driver</a>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('created_at')">Registered</a>
              <i class="mr-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'created_at' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'created_at' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="bus in buses">
            <td class="d-none d-sm-table-cell">{{bus.id}}</td>
            <td class="d-none d-sm-table-cell">
              <small>{{bus.license}}</small>
            </td>
            <td class="d-none d-sm-table-cell">
              <small>{{bus.driver?bus.driver.name:'Not assigned'}}{{bus.driver?':'+bus.driver.tel_number:''}} </small>
            </td>
            <td class="d-none d-sm-table-cell">
              <small>{{bus.created_at | moment("LL")}}</small> - <small class="text-muted">{{bus.created_at | moment("LT")}}</small>
            </td>
            <td class="d-sm-table-cell">
                  <a class="card-header-action ml-1" href="#" :disabled="submitingDestroy" @click.prevent="destroy(bus.id)">
                    <i title="Delete bus" class="far fa-trash-alt"></i>
                    <span class="ml-1">Delete</span>
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
      <div class="no-items-found text-center mt-5" v-if="!loading && !buses.length > 0">
        <i class="icon-magnifier fa-3x text-muted"></i>
        <p class="mb-0 mt-3"><strong>Could not find any items</strong></p>
        <p class="text-muted">Try changing the filters</p>
      </div>
      <content-placeholders v-if="loading">
        <content-placeholders-text/>
      </content-placeholders>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      buses: [],
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
    }
  },
  mounted () {
    // if (localStorage.getItem("filtersTableBuses")) {
    //   this.filters = JSON.parse(localStorage.getItem("filtersTableBuses"))
    // } else {
    //   localStorage.setItem("filtersTableBuses", this.filters);
    // }
    this.getBuses()
  },
  methods: {
    getBuses () {
      this.loading = true
      this.buses = []

      localStorage.setItem("filtersTableBuses", JSON.stringify(this.filters));

      axios.post(`/api/buses/filter?page=${this.filters.pagination.current_page}`, this.filters)
      .then(response => {
        console.log(response.data.buses.data);
        this.buses = response.data.buses.data;
        //delete response.data.data;
        this.filters.pagination = response.data
        this.loading = false
      })
      .catch(error => {
          this.$toasted.global.error('Error in retrieving buses data')
      })
      .then(() => {
          this.loading = false;
      });
    },
    // filters
    filter() {
      this.filters.pagination.current_page = 1
      this.getBuses()
    },
    changeSize (perPage) {
      this.filters.pagination.current_page = 1
      this.filters.pagination.per_page = perPage
      this.getBuses()
    },
    sort (column) {
      if(column == this.filters.orderBy.column) {
        this.filters.orderBy.direction = this.filters.orderBy.direction == 'asc' ? 'desc' : 'asc'
      } else {
        this.filters.orderBy.column = column
        this.filters.orderBy.direction = 'asc'
      }

      this.getBuses()
    },
    changePage (page) {
      this.filters.pagination.current_page = page
      this.getBuses()
    },
    destroy(bus_id) {
      if (!this.submitingDestroy) {
        this.submitingDestroy = true;
        swal({
          title: "Are you sure?",
          text: "Once deleted, you will not be able to recover this bus!",
          icon: "warning",
          buttons: true,
          dangerMode: true
        }).then(willDelete => {
          if (willDelete) {
            axios
              .delete(`/api/buses/${bus_id}`)
              .then(response => {
                this.$toasted.global.error("Bus deleted!");
                location.href = "/buses";
              })
              .catch(error => {
                console.log(error.response.data.errors);
                this.$toasted.global.error("Error deleting bus");
                this.errors = error.response.data.errors;
                swal("Error", error.response.data.errors[0], "error")
              });
          }
          this.submitingDestroy = false;
        });
      }
    },
  }
}
</script>
