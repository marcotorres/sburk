<template>
<div class="container">
    <div class="row justify-content-md-center">
        <div class="col-md-9 col-xl-7">
            <div class="card-header px-0 mt-2 bg-transparent clearfix">
                <h4 class="float-left pt-2"><i class="card-icon fas fa-landmark"></i> School</h4>
                <div class="card-header-actions mr-1">
                    <a class="btn btn-primary" href="#" :disabled="submiting" @click.prevent="updateSchool">
                        <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
                        <i class="fas fa-check" v-else></i>
                        <span class="ml-1">Save</span>
                    </a>
                </div>
            </div>
            <div class="card-body px-0">
                <form class="form-horizontal" v-show="!loading">
                    <hr class="my-3">
                    <div class="form-group row justify-content-md-center">
                        <label class="col-md-3">School Name</label>
                        <div class="col-md-9">
                            <input class="form-control" :class="{'is-invalid': errors.name}" 
                            type="text" v-model="school.name" placeholder="Enter school name">
                            <div class="invalid-feedback" v-if="errors.name">{{errors.name[0]}}</div>
                        </div>
                    </div>
                    <div class="form-group row justify-content-md-center">
                        <label class="col-md-3">School Address</label>
                        <div class="col-md-9">
                            <input type="text" id="address-input" v-model="school.address" 
                            class="form-control" :class="{'is-invalid': errors.address||errors.latitude||errors.longitude}">
                            <small class="form-text text-muted">
                                Click on map to fine tune the school address.
                            </small>
                            <div class="invalid-feedback" v-if="errors.address">{{errors.address[0]}}</div>
                            <div class="invalid-feedback" v-else-if ="errors.latitude||errors.longitude">Invalid address</div>
                            <input type="hidden" v-model="school.latitude" id="address-latitude" value="0" />
                            <input type="hidden" v-model="school.longitude" id="address-longitude" value="0" />
                        </div>
                    </div>
                    <div id="address-map-container" style="width:100%;height:400px; ">
                        <div style="width: 100%; height: 100%" id="address-map"></div>
                    </div>
                </form>
                <div class="row justify-content-md-center" v-show="loading">
                    <div class="col-md-12">
                        <content-placeholders>
                            <content-placeholders-heading :img="true" />
                            <content-placeholders-text />
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
            school: {
                id: '',
                name: '',
                channel: '',
                address: '',
                latitude: '',
                longitude: '',
            },
            errors: {},
            loading: true,
            submiting: false,
            preview: {},
            optionsAvatar: {
                headers: {'X-CSRF-TOKEN': Laravel.csrfToken},
                url: '/api/school/uploadLogo',
                paramName: 'file',
                parallelUploads: 1,
                maxFilesize: {
                limit: 10,
                message: 'The size image is {{filesize}}MB is greater than the {{maxFilesize}}MB'
                },
                acceptedFiles: {
                extensions: ['image/*'],
                message: 'You are uploading an invalid file'
                }
            }
        }
    },
    components: {
        
    },
    mounted() {
        this.getSchool();
    },
    methods: {
        getSchool() {
            this.loading = true
            axios.get(`/api/school/getSchool`)
                .then(response => {
                    if (response.data) {
                        //console.log(response.data);
                        this.school.id = response.data.id;
                        this.school.name = response.data.name;
                        this.school.channel = response.data.channel;
                        this.school.address = response.data.address;
                        this.school.latitude = response.data.latitude;
                        this.school.longitude = response.data.longitude;

                    }
                    this.updateMap();
                    this.loading = false;

                }).catch(error => {
                    if(error.response)
                        this.errors = error.response.data.errors
                    this.loading = false
                });
        },

        setSchoolLocationAddress(lat, lng, address) {
            this.school.latitude = lat;
            this.school.longitude = lng;
            this.school.address = address;
        },

        updateMap() {

            var v_comp = this;
            const address_input = document.getElementById("address-input");

            //const autocompletes = [];
            const geocoder = new google.maps.Geocoder;

            const isEdit = this.school.latitude != '' && this.school.longitude != '';

            const latitude = this.school.latitude || 37.782685;
            const longitude = this.school.longitude || -122.411364;

            let map = new google.maps.Map(document.getElementById('address-map'), {
                center: {
                    lat: latitude,
                    lng: longitude
                },
                zoom: 13
            });

            let marker = new google.maps.Marker({
                map: map,
                position: {
                    lat: latitude,
                    lng: longitude
                },
            });

            map.addListener('click', function (e) {

                var currentlatlng = e.latLng;
                geocoder.geocode({
                    'location': currentlatlng
                }, function (results, status) {
                    if (status === 'OK') {
                        if (results[0]) {

                            marker.setPosition(currentlatlng);

                            v_comp.school.latitude = currentlatlng.lat();
                            v_comp.school.longitude = currentlatlng.lng();
                            v_comp.school.address = results[0].formatted_address;

                            map.setCenter(marker.getPosition());
                        } else {
                            window.alert('No results found');
                        }
                    } else {
                        window.alert('Geocoder failed due to: ' + status);
                    }
                });

            });

            marker.setVisible(isEdit);

            let autocomplete = new google.maps.places.Autocomplete(address_input);

            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                marker.setVisible(false);
                const place = autocomplete.getPlace();

                geocoder.geocode({
                    'placeId': place.place_id
                }, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        const lat = results[0].geometry.location.lat();
                        const lng = results[0].geometry.location.lng();
                        //v_comp.setLocationCoordinates(lat, lng);
                        v_comp.school.latitude = lat;
                        v_comp.school.longitude = lng;
                        v_comp.school.address = place.formatted_address;
                    }
                });

                if (!place.geometry) {
                    window.alert("No details available for input: '" + place.name + "'");
                    address_input.value = "";
                    return;
                }

                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);

            });
        },
        updateSchool() {
            if (!this.submiting) {
                this.submiting = true
                axios.put(`/api/school/updateSchool`, this.school)
                    .then(response => {
                        this.errors = {}
                        this.submiting = false
                        this.$toasted.global.error('School updated!');
                    })
                    .catch(error => {
                        if(error.response)
                            this.errors = error.response.data.errors
                        this.submiting = false
                    })
            }
        }
    },
}
</script>
