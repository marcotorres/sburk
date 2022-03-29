<template>
  <div class="container">
    <div class="card-header px-0 mt-2 bg-transparent clearfix">
      <h4 class="float-left pt-2"><i class="card-icon fas fa-users"></i> Parents</h4>
      <div class="card-header-actions mr-1">
        <a class="btn btn-primary large-screen" href="/parents/create">New Parent</a>
        <a class="btn btn-primary small-screen" href="/parents/create"><i class="fas fa-plus"></i></a>
      </div>
      <div class="card-header-actions mr-1">
        <button type="button" class="btn btn-secondary large-screen" data-toggle="modal" 
        data-target="#uploadParentModal">
          Upload Parents
        </button>
        <button type="button" class="btn btn-secondary small-screen" data-toggle="modal" 
        data-target="#uploadParentModal">
          <i class="fas fa-file-upload"></i>
        </button>
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
            <th></th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('id')">ID</a>
              <i class="ml-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'id' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'id' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('name')">Name</a>
              <i class="ml-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'name' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'name' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a class="text-dark">No. of children</a>
              <i class="ml-1 fas"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('tel_number')">Telephone</a>
              <i class="ml-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'created_at' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'created_at' && filters.orderBy.direction == 'desc'}"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a class="text-dark">Location</a>
              <i class="ml-1 fas"></i>
            </th>
            <th class="d-none d-sm-table-cell">
              <a href="#" class="text-dark" @click.prevent="sort('driver')">Driver</a>
              <i class="ml-1 fas" :class="{'fa-long-arrow-alt-down': filters.orderBy.column == 'driver' && filters.orderBy.direction == 'asc', 'fa-long-arrow-alt-up': filters.orderBy.column == 'driver' && filters.orderBy.direction == 'desc'}"></i>
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
            <th class="d-none d-sm-table-cell"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="parent in parents" @click="selectParent(parent.id)" :class="{'tr-not-found': parent.driver==null}">
            <td>
              <label class="form-checkbox">
                  <input type="checkbox" :value="parent.id" v-model="selectedparents">
                <i class="form-icon"></i>
                </label>
            </td>
            <td class="d-none d-sm-table-cell">{{parent.id}}</td>
            <td>{{parent.name}}</td>
            <td class="d-none d-sm-table-cell">{{parent.children_count}}</td>
            <td class="d-none d-sm-table-cell">+({{parent.country_code}}) {{parent.tel_number}}</td>
            <td class="d-none d-sm-table-cell">{{parent.address_latitude==null||parent.address_longitude==null?"Not set":parseFloat(parent.address_latitude).toFixed(2)+","+parseFloat(parent.address_longitude).toFixed(2)}}</td>
            <td class="d-none d-sm-table-cell">{{parent.driver==null? "Not set" :parent.driver.name + ":" + parent.driver.tel_number}}</td>
            <td class="d-none d-sm-table-cell">{{parent.v_code}}</td>
            <td class="d-none d-sm-table-cell">{{parent.verified==0?"No":"Yes"}}</td>
            <td class="d-none d-sm-table-cell">
              <small>{{parent.created_at | moment("LL")}}</small> - <small class="text-muted">{{parent.created_at | moment("LT")}}</small>
            </td>
            <td class="d-sm-table-cell">
              <a href="#" @click="editParent(parent.id)" class="text-muted"><i class="fas fa-pencil-alt"></i></a>
            </td>
            <td class="d-none d-sm-table-cell">
              <a v-if="parent.address_latitude!=null&&parent.address_longitude!=null" :href="'/parents/'+parent.id+'/map'"  class="text-muted"><i class="fas fa-location-arrow"></i></a>
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
        <button type="button" class="btn btn-danger mr-3" @click.prevent="deleteParents" :disabled="selectedparents.length==0">
          Delete Parents <i class="fas fa-spinner fa-spin" v-if="submitingDestroy"></i>
        </button>

        <!-- Button trigger modal -->
        <button type="button" class="btn btn-success mr-3" :disabled="selectedparents.length==0" data-toggle="modal" 
        data-target="#selectDriverModal">
          Assign Driver
        </button>


      </div>

      <div class="no-items-found text-center mt-5" v-if="!loading && !parents.length > 0">
        <i class="icon-magnifier fa-3x text-muted"></i>
        <p class="mb-0 mt-3"><strong>Could not find any items</strong></p>
        <p class="text-muted">Try changing the filters or add a new one</p>
      </div>
      <content-placeholders v-if="loading">
        <content-placeholders-text/>
      </content-placeholders>
    </div>


<!-- Upload Parents Modal -->
<div class="modal fade" id="uploadParentModal" ref="uploadParentModal" tabindex="-1" role="dialog" 
aria-labelledby="uploadParentModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Upload Template File</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row justify-content-center file-upload cell">
          <div class="m-4"> 
              The template contains placeholders for three children per parent. But you can add columns for more children.
          </div>
          <div class="m-4">
            <label class="file-upload__label">
              Upload .xlsx file <i class="fas fa-upload ml-2"></i>
              <input type="file"  id="file" accept=".xlsx" ref="file" v-on:change="handleFileUpload()"/>
            </label>
            <button class="file-upload__input" v-on:click="selectFile()">Add File</button>
          </div>
          <div class="m-4">
            <a class=" file-upload__label btn large-screen" href="/school_parents.xlsx">
            Download template <i class="fas fa-download"></i>
            </a>
          </div>
        </div>
        <div>{{ file.name }}</div>
        <br>
        <br>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" @click.prevent="submitFile">
          <i class="fas fa-spinner fa-spin" v-if="submiting_file"></i>
          OK
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Select Driver Modal -->
<div class="modal fade" id="selectDriverModal" ref="selectDriverModal" tabindex="-1" role="dialog" 
aria-labelledby="selectDriverModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Select Driver</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            <multiselect
              v-model="selecteddriver"
              placeholder="Select driver"
              label="tel_number"
              track-by="tel_number"
              class="form-control"
              :options="drivers"
              :option-height="104"
              :show-labels="false"
            >
              <template slot="singleLabel" slot-scope="props">
                <div class="option_tel_number">
                  <span class="option_name p-1">
                    <i class="icon-user px-2"></i>
                    {{ props.option.name }}
                  </span>
                  <span class="option_small p-1">
                    <i class="icon-phone px-2"></i>
                    {{ props.option.tel_number }}
                  </span>
                </div>
              </template>
              <template slot="option" slot-scope="props">
                <div class="option_tel_number">
                  <div class="option_name p-1">
                    <i class="icon-user px-2"></i>
                    {{ props.option.name }}
                  </div>
                  <div class="option_small p-1">
                    <i class="icon-phone px-2"></i>
                    {{ props.option.tel_number }}
                  </div>
                </div>
              </template>
            </multiselect>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" @click.prevent="assignDriver">
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
import Multiselect from "vue-multiselect";

export default {
  components: {
    Multiselect
  },
  data () {
    return {
      file: '',
      parents: [],
      drivers: [],
      selecteddriver:{},
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
      submiting_file: false,
      submitingDestroy: false,
      selectedparents: [],
      selectAll: false
    }
  },
  mounted () {
    if (localStorage.getItem("filtersTableParents")) {
      this.filters = JSON.parse(localStorage.getItem("filtersTableParents"))
    } else {
      localStorage.setItem("filtersTableParents", this.filters);
    }
    this.getParents();
    this.getDrivers();
  },
  methods: {
    selectFile(){
      this.$refs.file.click();
    },
    handleFileUpload(){
      this.file = this.$refs.file.files[0];
    },
    submitFile(){
      if (!this.submiting_file) {
        this.submiting_file = true;
        this.loading = true;
      
        let formData = new FormData();
        formData.append('school_parents_file', this.file);
        axios.post( 'api/parents/upload',
            formData,
            {
              headers: {
                'Content-Type': 'multipart/form-data'
              },
            }
        )
        .then(response => {
          $(this.$refs.uploadParentModal).modal('hide');
          this.file = '';
          this.$toasted.global.error("Parents uploaded successfully!");
          this.getParents();
          this.submiting_file = false;
          this.loading = false;
        })
        .catch(error => {
          this.errors = error.response.data.errors;
          this.$toasted.global.error(this.errors[0]);
          this.submiting_file = false;
          this.loading = false;
        });
      }
    },
    assignDriver()
    {
      if (!this.submiting) {
        this.submiting = true;
        this.loading = true;
        axios.post(`/api/parents/assign`, {
          params: {
            selecteddriver: this.selecteddriver,
            selectedparents: this.selectedparents,
          }
        })
        .then(response => {
          $(this.$refs.selectDriverModal).modal('hide');
          this.$toasted.global.error("Driver assigned successfully!");
          this.getParents();
          this.submiting = false;
          this.loading = false;
        })
        .catch(error => {
          this.errors = error.response.data.errors;
          this.$toasted.global.error(this.errors[0]);
          this.submiting = false;
          this.loading = false;
        });
      }
    },
    selectParent(id) {
			if (!this.selectAll) {
        if(this.selectedparents.includes(id))
        {
          var index = this.selectedparents.indexOf(id);
          if (index > -1) {
            this.selectedparents.splice(index, 1);
          }
        }
        else
          this.selectedparents.push(id);
			}
    },
    deleteParents() {
      if (!this.submitingDestroy) {
        this.submitingDestroy = true
        swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover these parents!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                  axios.delete(`/api/parents/deleteMany`, {
                    params: {
                      selectedparents: this.selectedparents,
                    }
                  })
                  .then(response => {
                    this.$toasted.global.error("Parents deleted successfully!");
                    this.selectedparents = [];
                    this.getParents();
                  })
                  .catch(error => {
                      this.errors = error.response.data.errors
                      swal("Error", error.response.data.errors[0], "error")
                  })
                }
                this.submitingDestroy = false
            })
      }
    },
    getDrivers() {
      this.loading = true;
      this.drivers = [];

      axios.get(`/api/drivers/all`).then(response => {
        this.drivers = response.data;
        console.log(this.drivers);
        if(this.drivers.length>0)
          this.selecteddriver = this.drivers[0];
        this.loading = false;
      });
    },
    getParents () {
      this.loading = true
      this.parents = []

      localStorage.setItem("filtersTableParents", JSON.stringify(this.filters));

      axios.post(`/api/parents/filter?page=${this.filters.pagination.current_page}`, this.filters)
      .then(response => {
        if(response.data)
        {
          this.parents = response.data.data
          console.log(this.parents);
          delete response.data.data
          this.filters.pagination = response.data
        }
        this.loading = false
      })
    },
    editParent(parentId) {
      location.href = `/parents/${parentId}/edit`
    },    

    // Filters
    filter() {
      this.filters.pagination.current_page = 1
      this.getParents()
    },
    changeSize (perPage) {
      this.filters.pagination.current_page = 1
      this.filters.pagination.per_page = perPage
      this.getParents()
    },
    sort (column) {
      if(column == this.filters.orderBy.column) {
        this.filters.orderBy.direction = this.filters.orderBy.direction == 'asc' ? 'desc' : 'asc'
      } else {
        this.filters.orderBy.column = column
        this.filters.orderBy.direction = 'asc'
      }

      this.getParents()
    },
    changePage (page) {
      this.filters.pagination.current_page = page
      this.getParents()
    }
  }
}
</script>
<style>
  input[type="file"]{
    position: absolute;
    top: -500px;
  }

.file-upload {
	position: relative;
}

.file-upload__label {
  display: block;
  padding: 1em 2em;
  color: #fff;
  background: #38c172;
  border-radius: .4em;
  transition: background .3s;
}
.file-upload__label:hover {
    color: #fff;
    background-color: #82A1AC;
    border-color: #82A1AC;
    cursor: pointer;
}

.file-upload__input {
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    font-size: 1;
    width:0;
    height: 100%;
    opacity: 0;
}
</style>