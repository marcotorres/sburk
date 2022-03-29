<template>
  <div class="container">
    <div class="row justify-content-md-center">
      <div class="col-md-9 col-xl-7">
        <div class="card-header px-0 mt-2 bg-transparent clearfix">
          <h4 class="float-left pt-2">New School</h4>
          <div class="card-header-actions mr-1">
            <a class="btn btn-primary" href="#" :disabled="submiting" @click.prevent="create">
              <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
              <i class="fas fa-check" v-else></i>
              <span class="ml-1">Save</span>
            </a>
          </div>
        </div>
        <div class="card-header px-0 bg-transparent">
          <strong>General</strong>
          <br />
          <small class="text-muted">Add new school.</small>
        </div>
        <div class="card-body px-0">
          <div class="form-group">
            <label>Name</label>
            <input
              type="text"
              class="form-control"
              :class="{'is-invalid': errors.name}"
              v-model="school.name"
              placeholder="name"
              autofocus
            />
            <div class="invalid-feedback" v-if="errors.name">{{errors.name[0]}}</div>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input
              type="email"
              class="form-control"
              :class="{'is-invalid': errors.email}"
              v-model="school.email"
              placeholder="email"
            />
            <div class="invalid-feedback" v-if="errors.email">{{errors.email[0]}}</div>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input
              type="password"
              class="form-control"
              :class="{'is-invalid': errors.password}"
              v-model="school.password"
              placeholder="password"
            />
            <div class="invalid-feedback" v-if="errors.password">{{errors.password[0]}}</div>
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
      school: {
        email:""
      },
      errors: {},
      loading: true,
      submiting: false,
    };
  },
  mounted() {

  },
  methods: {
    create() {
      if (!this.submiting) {
        this.submiting = true;
        axios
          .post("/api/schools/store", this.school)
          .then(response => {
            this.$toasted.global.error("School created!");
            location.href = "/schools";
          })
          .catch(error => {
            this.errors = error.response.data.errors;
            this.submiting = false;
          });
      }
    }
  }
};
</script>
<style lang="scss" scoped>
.option_name {
  color: gray;
}
.multiselect.form-control {
  padding-left: 0 !important;
  padding-right: 0 !important;
  border: none !important;
}
</style>
