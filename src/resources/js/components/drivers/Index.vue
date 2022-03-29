<template>
  <div class="container">
    <div class="card-header px-0 mt-2 bg-transparent clearfix">
      <h4 class="float-left pt-2"><i class="card-icon fas fa-user-tie"></i> Drivers</h4>
      <div v-if="canCreateDriver" class="card-header-actions mr-1">
        <a class="btn btn-primary large-screen" href="/drivers/create">New Driver</a>
        <a class="btn btn-primary small-screen" href="/drivers/create"><i class="fas fa-plus"></i></a>
      </div>
      <div v-else class="card-header-actions mr-1">
        <a class="btn btn-danger" href="/plan">Upgrade plan to add new drivers</a>
      </div>
      <div class="card-header-actions mr-1">
        <a class="btn btn-secondary large-screen" href="/drivers/map">View Map</a>
        <a class="btn btn-secondary small-screen" href="/drivers/map"><i class="fas fa-location-arrow"></i></a>
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
              <i class="ml-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'id' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'id' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('name')">Name</a>
              <i class="ml-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'name' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'name' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('tel_number')">Telephone</a>
              <i class="ml-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'tel_number' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'created_at' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a class="text-dark">Last location</a>
              <i class="ml-1 fas"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('bus_id')">Bus license</a>
              <i class="ml-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'bus_id' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'bus_id' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              Verification Code
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('verified')">Verified?</a>
              <i class="ml-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'verified' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'verified' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('created_at')">Created</a>
              <i class="ml-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'created_at' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'created_at' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="driver in drivers">
            <td class="d-none d-sm-table-cell">{{driver.id}}</td>
            <td>{{driver.name}}</td>
            <td class="d-none d-sm-table-cell">+({{driver.country_code}}) {{driver.tel_number}}</td>
            <td class="d-none d-sm-table-cell">{{driver.last_latitude==null||driver.last_longitude==null?"Not set":parseFloat(driver.last_latitude).toFixed(2)+","+parseFloat(driver.last_longitude).toFixed(2)}}</td>
            <td class="d-none d-sm-table-cell">{{driver.bus.license}}</td>
            <td class="d-none d-sm-table-cell">{{driver.v_code}}</td>
            <td class="d-none d-sm-table-cell">{{driver.verified==0?"No":"Yes"}}</td>
            <td class="d-none d-sm-table-cell">
              <small>{{driver.created_at | moment("LL")}}</small> - <small class="text-muted">{{driver.created_at | moment("LT")}}</small>
            </td>
            <td class="d-sm-table-cell">
              <div class="dropdown">
                <button class="btn" type="button" id="dropdownMenuButton" 
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a href="#" class="dropdown-item text-muted" @click="editDriver(driver.id)"><i class="fas fa-pencil-alt"></i>Edit</a>
                  <a v-if="driver.last_latitude!=null&&driver.last_longitude!=null" :href="'/drivers/'+driver.id+'/map'"  class="dropdown-item text-muted"><i class="fas fa-location-arrow"></i>Map</a>
                  <a class="dropdown-item text-muted" :href="'/drivers/'+driver.id+'/history'" ><i class="fas fa-history"></i>History</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-muted" href="#" @click="openSendMessageModal(driver.id)"><i class="far fa-bell"></i>Notify</a>
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
                <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page -  1)"><i class="fas fa-angle-left"></i></a>
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
      <div class="no-items-found text-center mt-5" v-if="!loading && !drivers.length > 0">
        <i class="icon-magnifier fa-3x text-muted"></i>
        <p class="mb-0 mt-3"><strong>Could not find any items</strong></p>
        <p class="text-muted">Try changing the filters or add a new one</p>
      </div>
      <content-placeholders v-if="loading">
        <content-placeholders-text/>
      </content-placeholders>
    </div>

    <!-- Send Message Modal -->
    <div class="modal fade" id="sendMessageModal" ref="sendMessageModal" tabindex="-1" role="dialog" 
    aria-labelledby="sendMessageModalTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Write message to parents</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <textarea class="form-control" v-model="message"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click.prevent="sendMessage">
              <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
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
  data () {
    return {
      drivers: [],
      driverId:"",
      message: "",
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
      submiting: false,
    }
  },
  props: {
    canCreateDriver: Boolean,
  },
  mounted () {

    if (localStorage.getItem("filtersTableDrivers")) {
      this.filters = JSON.parse(localStorage.getItem("filtersTableDrivers"))
    } else {
      localStorage.setItem("filtersTableDrivers", this.filters);
    }
    this.getDrivers()
  },
  methods: {
    openSendMessageModal(driverId) {
      this.driverId = driverId;
      $(this.$refs.sendMessageModal).modal('show');
    },
    sendMessage () {
      if(!this.submiting)
      {
        this.submiting = true;
        axios.post(`/api/drivers/sendMessage`, {
          params: {
            driver_id: this.driverId,
            message: this.message,
          }
        })
        .then(response => {
          if(response.data)
          {
            this.$toasted.global.error('Message sent successfully')
          }
        })
        .catch(error => {
            this.$toasted.global.error('Error in sending message')
            swal("Error", 'Error in sending message', "error")
        })
        .then(() => {
            this.submiting = false;
            $(this.$refs.sendMessageModal).modal('hide');
        });
      }

    },
    getDrivers () {
      this.loading = true
      this.drivers = []

      localStorage.setItem("filtersTableDrivers", JSON.stringify(this.filters));

      axios.post(`/api/drivers/filter?page=${this.filters.pagination.current_page}`, this.filters)
      .then(response => {
        if(response.data)
        {
          this.drivers = response.data.data
          console.log(this.drivers);
          delete response.data.data
          this.filters.pagination = response.data
        }
        this.loading = false
      })
    },
    editDriver(driverId) {
      location.href = `/drivers/${driverId}/edit`
    },

    // Filters
    filter() {
      this.filters.pagination.current_page = 1
      this.getDrivers()
    },
    changeSize (perPage) {
      this.filters.pagination.current_page = 1
      this.filters.pagination.per_page = perPage
      this.getDrivers()
    },
    sort (column) {
      if(column == this.filters.orderBy.column) {
        this.filters.orderBy.direction = this.filters.orderBy.direction == 'asc' ? 'desc' : 'asc'
      } else {
        this.filters.orderBy.column = column
        this.filters.orderBy.direction = 'asc'
      }

      this.getDrivers()
    },
    changePage (page) {
      this.filters.pagination.current_page = page
      this.getDrivers()
    },
  }
}
</script>
<style scoped>
#dropdownMenuButton{
  background-color: transparent;
  padding: 0;
}
</style>