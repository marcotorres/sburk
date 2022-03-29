<template>
  <div class="container">
    <div class="card-header px-0 mt-2 bg-transparent clearfix">
      <h4 class="float-left pt-2"><i class="card-icon fas fa-history"></i> Driver Log</h4>
    </div>
    <div class="card-header px-0 mt-2 bg-transparent clearfix">
      <h5 class="float-left pt-2">Filter</h5> 
      <div class="card-header-actions mr-1">
        <a class="btn btn-primary" href="#" :disabled="submiting" @click.prevent="filterLog">
            <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
            <i class="fas fa-filter" v-else></i>
            <span class="ml-1">Filter</span>
        </a>
      </div>
    </div>

    <div class="card-body px-0">
      <div class="row justify-content-between">
        <div class="form-group col-lg-3 col-md-6 col-sm-12">
            <label>Start date</label>
            <datepicker v-model="startdate.date"></datepicker>
        </div>
        <div class="form-group col-lg-3 col-md-6 col-sm-12">
            <label>End date</label>
            <datepicker v-model="enddate.date"></datepicker>
        </div>     
        <div class="form-group col-lg-3 col-md-6 col-sm-12">
            <label>Event type</label>
            <multiselect v-model="event_value" :options="event_types" :searchable="false" :show-labels="false"></multiselect>
        </div>   
        <div class="form-group col-lg-3 col-md-6 col-sm-12">
          <div v-show="event_value != 'Child check-in' && event_value != 'Child check-out'" >
            <label>Place</label>
            <multiselect v-model="select_value" :options="select_types" :searchable="false" :show-labels="false"></multiselect>
          </div>
            
        </div>  
      </div>
    </div>
    <div class="card-header px-0 mt-2 bg-transparent clearfix">
      <h5 class="float-left pt-2">Log</h5>
    </div>
    <div class="card-body px-0">
      <table class="table table-hover" v-if="!loading">
        <thead>
          <tr>
            <th class="d-none d-sm-table-cell">
              Event
            </th>
            <th class="d-none d-sm-table-cell">
              Place
            </th>
            <th class="d-none d-sm-table-cell">
              Date
            </th>
            <th class="d-none d-sm-table-cell">
              Time
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="visit in visits">
            <td v-if="visit.case_id==1">Arrive</td>
            <td v-else-if="visit.case_id==2">Leave</td>
            <td v-else-if="visit.case_id==3">Check-in</td>
            <td v-else-if="visit.case_id==4">Check-out</td>
            <td class="d-sm-table-cell"> 
              <span title="School" v-if="visit.event_place==1">
                <i class="fas fa-landmark"/>
              </span>
              <span v-else-if="visit.event_place==2">
                <i class="fas fa-home"></i>
                {{visit.parent.name}}
              </span>
              <span v-else-if="visit.event_place==3">
                <i class="fas fa-bus"></i>
                {{visit.child.childName}}
              </span>
            </td>
            <td class="d-sm-table-cell">
              {{visit.updated_at | moment("LL")}}
            </td>
            <td class="d-sm-table-cell">
              {{visit.updated_at | moment("LT")}}
            </td>
          </tr>
        </tbody>
      </table>
        <content-placeholders v-if="loading">
          <content-placeholders-text/>
        </content-placeholders>
      <div class="no-items-found text-center mt-5" v-if="!loading && visits.length==0">
        <i class="icon-magnifier fa-3x text-muted"></i>
        <p class="mb-0 mt-3"><strong>Could not find any logs</strong></p>
        <p class="text-muted">Try changing the filters</p>
      </div>
    </div>
  </div>
</template>

<script>

import Datepicker from 'vuejs-datepicker';

export default {
  data () {
    return {
      visits: [],
      value: "",
      event_value:"All",
      event_types: ['All', 'Arrive', 'Leave', 'Child check-in', 'Child check-out'],
      select_value:"All",
      select_types: ['All', 'School', 'Parents', 'Bus'],
      loading: true,
      submiting: false,
      startdate: {
        date: new Date()
      }, 
      enddate: {
        date: new Date()
      },
    }
  },
  components: {
    Datepicker
  },
  mounted () {
    this.filterLog()
  },
  methods: {
    filterLog() {
      let str = window.location.pathname
      let res = str.split("/")
      let driver_id = null;
      if(res.length==4)
        driver_id = res[2];
      else
        return;

      this.getLog(driver_id, this.event_value, this.select_value, this.startdate, this.enddate)
    },
    getLog(driver_id, event_type, select_value, start_date, end_date)
    {
      if(!this.submiting)
      {
        this.loading = true;
        this.submiting = true;
        axios.get(`/api/drivers/getLog/${driver_id}`, {
          params: {
            event_type: event_type,
            select_value: select_value,
            start_date: start_date,
            end_date: end_date
          }
        })
        .then(response => {
          if(response.data)
          {
            console.log(response.data);
            this.visits = response.data;
          }
        })
        .catch(error => {
            this.$toasted.global.error('Error in retrieving log!')
        })
        .then(() => {
            this.loading = false;
            this.submiting = false;
        }); 
      }
    
    },
  }
}
</script>
<style>

input,
select {
  padding: 0.52em 0.5em;
  font-size: 100%;
  border: 1px solid #e8e8e8;
  width: 100%;
  border-radius: 5px;
}

select {
  height: 2.0em;
}

</style>