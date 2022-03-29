<template>
<div class="container">
    <div class="row justify-content-md-center">
        <div class="col-md-12 col-xl-9">
            <div class="card-header px-0 mt-2 bg-transparent clearfix">
                <h4 class="float-left pt-2">Parent location</h4>
            </div>
            <div class="form-group row justify-content-md-center py-4">
                <label class="col-md-3">Parent name</label>
                <div class="col-md-9">
                    {{parent.name}}
                </div>
            </div>
            <div class="form-group row justify-content-md-center py-4">
                <label class="col-md-3">Parent Address</label>
                <div class="col-md-9">
                    {{address}}
                </div>
            </div>
            <div id="address-map-container" style="width:100%;height:400px; ">
                <div style="width: 100%; height: 100%" id="address-map"></div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
    data() {
        return {
            parent: {
                driver: []
            },
            address:'',
            errors: {},
            loading: true,
        }
    },
    mounted() {
        this.getParent();
    },
    methods: {
        getParent() {
            this.loading = true
            let str = window.location.pathname
            let res = str.split("/")
            axios.get(`/api/parents/getParent/${res[2]}`)
                .then(response => {
                    this.parent = response.data;
                    this.updateMap();
                })
                .catch(error => {
                    this.$toasted.global.error('Parent does not exist!')
                })
                .then(() => {
                    this.loading = false
                })
        },
        updateMap() {

            var v_comp = this;
            const address_input = document.getElementById("address-input");

            const geocoder = new google.maps.Geocoder;

            const isEdit = this.parent.address_latitude != '' && this.parent.address_longitude != '';

            const latitude = this.parent.address_latitude;
            const longitude = this.parent.address_longitude;

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

            marker.setVisible(isEdit);

            
            if (isEdit) {
              var currentlatlng = marker.position;
                geocoder.geocode({
                    'location': currentlatlng
                }, function (results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            v_comp.address = results[0].formatted_address;
                            map.setCenter(marker.getPosition());
                        } else {
                            //window.alert('No results found');
                        }
                    } else {
                        //window.alert('Geocoder failed due to: ' + status);
                    }
                });
            }

        },
    },
}
</script>

<style scoped>
.multiselect.form-control {
    padding-left: 0 !important;
    padding-right: 0 !important;
    border: none !important;
}
</style>
