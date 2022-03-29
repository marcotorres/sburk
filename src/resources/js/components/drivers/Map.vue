<template>
<div class="container">
    <div class="row justify-content-md-center">
        <div class="col-md-12 col-xl-9">
            <div class="card-header px-0 mt-2 bg-transparent clearfix">
                <h4 v-if="driver.name" class="float-left pt-2">Driver location</h4>
                <h4 v-else class="float-left pt-2">Drivers locations</h4>
            </div>
            <div v-if="driver.name" class="form-group row justify-content-md-center py-4">
                <label class="col-md-3">Driver name</label>
                <div class="col-md-9">
                    {{driver.name}}
                </div>
            </div>
            <div v-if="driver.name" class="form-group row justify-content-md-center py-4">
                <label class="col-12">Driver on map</label>
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
            driver: {
                marker: {},
                infowindow:{},
                last_loc_update_time:{},
                speed:{}
            },
            drivers: {},
            errors: {},
            loading: true,
            map: {},
        }
    },
    mounted() {
        this.getDriversData();
    },
    methods: {
        distance(lat1, lon1, lat2, lon2, unit) {
            if ((lat1 == lat2) && (lon1 == lon2)) {
                return 0;
            }
            else {
                var radlat1 = Math.PI * lat1/180;
                var radlat2 = Math.PI * lat2/180;
                var theta = lon1-lon2;
                var radtheta = Math.PI * theta/180;
                var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
                if (dist > 1) {
                    dist = 1;
                }
                dist = Math.acos(dist);
                dist = dist * 180/Math.PI;
                dist = dist * 60 * 1.1515;
                if (unit=="K") { dist = dist * 1.609344 }
                if (unit=="N") { dist = dist * 0.8684 }
                return dist;
            }
        },
        getDriversData() {
            let str = window.location.pathname
            let res = str.split("/")
            let driver_id = null;
            if(res.length==4)
                driver_id = res[2];
            
            if(driver_id)
            {
                this.getDriver(driver_id);
            }
            else
            {
                this.getAllDrivers();
            }
        },
        getDriver(driver_id){
            this.loading = true
            axios.get(`/api/drivers/getDriver/${driver_id}`)
                .then(response => {
                    this.driver = response.data;
                    this.updateMap();
                    window.Echo.channel(this.driver.channel)
                    .listen('LocationChangeEvent', (e) => {
                        var payloadData = JSON.parse(e.data);
                        var latlng = new google.maps.LatLng(payloadData.lat, payloadData.lng);
                        if(this.driver.last_loc_update_time)
                        {
                            var old_t = this.driver.last_loc_update_time;
                            var current_t = payloadData.time;

                            var old_loc_latitude = this.driver.last_latitude;
                            var old_loc_longitude = this.driver.last_longitude;

                            var new_loc_latitude = payloadData.lat;
                            var new_loc_longitude = payloadData.lng;

                            var distnce = this.distance(old_loc_latitude, old_loc_longitude, 
                            new_loc_latitude, new_loc_longitude, "K");

                            var speed = 3.6 * 1000.0 * (distnce*1000.0) / (current_t - old_t);

                            this.driver.speed = parseFloat(speed).toFixed(1);

                        }
                        this.driver.last_latitude = payloadData.lat;
                        this.driver.last_longitude = payloadData.lng;

                        this.driver.last_loc_update_time = payloadData.time;
                        this.driver.marker.setPosition(latlng);
                        this.fitBounds();
                    });
                })
                .catch(error => {
                    this.$toasted.global.error('Driver does not exist!')
                })
                .then(() => {
                    this.loading = false
                })
        },
        getAllDrivers(){
            this.loading = true
            axios.get(`/api/drivers/all`)
                .then(response => {
                    this.drivers = response.data;
                    console.log(this.drivers);
                    this.updateMapAllDrivers();
                    this.drivers.forEach(d => {
                        window.Echo.channel(d.channel)
                        .listen('LocationChangeEvent', (e) => {
                            var payloadData = JSON.parse(e.data);
                            var index = this.drivers.map(function(o) { return o.id; }).indexOf(payloadData.id);
                            var latlng = new google.maps.LatLng(payloadData.lat, payloadData.lng);
                            this.drivers[index].marker.setPosition(latlng);

                            if(this.drivers[index].last_loc_update_time)
                            {
                                var old_t = this.drivers[index].last_loc_update_time;
                                var current_t = payloadData.time;

                                console.log((current_t-old_t)/1000);
                                
                                var old_loc_latitude = this.drivers[index].last_latitude;
                                var old_loc_longitude = this.drivers[index].last_longitude;

                                var new_loc_latitude = payloadData.lat;
                                var new_loc_longitude = payloadData.lng;

                                var distnce = this.distance(old_loc_latitude, old_loc_longitude, 
                                new_loc_latitude, new_loc_longitude, "K");

                                console.log(distnce*1000);

                                var speed = 3.6 * 1000.0 * (distnce*1000.0) / (current_t - old_t);

                                this.drivers[index].speed = parseFloat(speed).toFixed(1);

                            }
                            this.drivers[index].last_latitude = payloadData.lat;
                            this.drivers[index].last_longitude = payloadData.lng;
                            this.drivers[index].last_loc_update_time = payloadData.time;
                            this.fitBoundsAllDrivers();
                        });
                    });

                })
                .catch(error => {
                    this.$toasted.global.error('Error in retrieving drivers!')
                })
                .then(() => {
                    this.loading = false
                })
        },
        updateMapAllDrivers() {
            var init_latlng;
            this.drivers.forEach(d => {
                init_latlng = new google.maps.LatLng(d.last_latitude, d.last_longitude);
                return;
            });
            this.map = new google.maps.Map(document.getElementById('address-map'), {
                center: init_latlng,
                zoom: 13
            });
            for (var i = 0; i < this.drivers.length; i++) {
                const isEdit = this.drivers[i].last_latitude != '' && this.drivers[i].last_longitude != '';
                var driver_latlng = new google.maps.LatLng(this.drivers[i].last_latitude, this.drivers[i].last_longitude);
                this.drivers[i].marker = new google.maps.Marker({
                    map: this.map,
                    position: driver_latlng,
                    icon: '/icon/bus.png', 
                });
                this.drivers[i].marker.setVisible(isEdit);
                let driver = this.drivers[i];
                this.drivers[i].infowindow = new google.maps.InfoWindow({
                    content: this.drivers[i].name
                });

                this.drivers[i].marker.addListener('click', function() {
                    driver.infowindow.open(this.map, driver.marker);
                });
                driver.infowindow.open(this.map, driver.marker);
            }
            this.fitBoundsAllDrivers();
        },
        updateMap() {
            const isEdit = this.driver.last_latitude != '' && this.driver.last_longitude != '';

            var driver_latlng = new google.maps.LatLng(this.driver.last_latitude, this.driver.last_longitude);

            this.map = new google.maps.Map(document.getElementById('address-map'), {
                center: driver_latlng,
                zoom: 17
            });

            this.driver.marker = new google.maps.Marker({
                map: this.map,
                position: driver_latlng,
                icon: '/icon/bus.png', 
            });

            this.driver.marker.setVisible(isEdit);

            let drv = this.driver;
            this.driver.infowindow = new google.maps.InfoWindow({
                content: drv.speed
            });

            this.driver.marker.addListener('click', function() {
                drv.infowindow.open(this.map, drv.marker);
            });
            

            this.fitBounds();
        },
        fitBoundsAllDrivers() {

            var bounds = this.map.getBounds(); 
            var bounds_updated = false;
            var school_added = false;
            this.drivers.forEach(d => {
                var driver_latlng = new google.maps.LatLng(d.last_latitude, d.last_longitude);
                if(bounds==null)
                {
                    bounds_updated = true;
                    bounds = new google.maps.LatLngBounds();
                }
                if (!bounds.contains(driver_latlng)) {
                    // marker is not within map bounds
                    bounds_updated = true;
                    bounds.extend(driver_latlng);
                }
                if(d.speed)
                {
                    d.infowindow.setContent(d.name + "</br>" + d.speed + " km/h");
                    //d.infowindow.open(this.map, d.marker);
                }
                if(!school_added && d.school)
                {
                    school_added = true;
                    if(d.school.latitude && d.school.longitude)
                    {
                        console.log("Adding school");
                        var latlng = new google.maps.LatLng(d.school.latitude, d.school.longitude);
                        let school_marker = new google.maps.Marker({
                            map: this.map,
                            position: latlng,
                            icon: '/icon/school.png',
                        });
                        if (!bounds.contains(latlng)) {
                            // marker is not within map bounds
                            bounds_updated = true;
                            bounds.extend(latlng);
                        }
                        
                        school_marker.setVisible(true);

                        var infowindow = new google.maps.InfoWindow({
                            content: d.school.name
                        });

                        school_marker.addListener('click', function() {
                            infowindow.open(this.map, school_marker);
                        });
                    }
                }
            });
            if (bounds_updated)
                this.map.fitBounds(bounds);
        },
        fitBounds() {
            var bounds_updated = false;
            var driver_latlng = new google.maps.LatLng(this.driver.last_latitude, this.driver.last_longitude);
            var bounds = this.map.getBounds(); 
            if(bounds==null)
            {
                bounds_updated = true;
                bounds = new google.maps.LatLngBounds();
            }
            if(this.driver.speed)
            {
                this.driver.infowindow.setContent(this.driver.speed + " km/h");
                this.driver.infowindow.open(this.map, this.driver.marker);
            }

            if (!bounds.contains(driver_latlng)) {
                // marker is not within map bounds
                bounds_updated = true;
                bounds.extend(driver_latlng);
            }
            if(this.driver.parents)
            {
                console.log(this.driver.parents);
                
                this.driver.parents.forEach(parent => {
                    if(parent.address_latitude && parent.address_longitude)
                    {
                        var latlng = new google.maps.LatLng(parent.address_latitude, parent.address_longitude);
                        let parent_marker = new google.maps.Marker({
                            map: this.map,
                            position: latlng,
                        });
                        if (!bounds.contains(latlng)) {
                            // marker is not within map bounds
                            bounds_updated = true;
                            bounds.extend(latlng);
                        }
                        
                        parent_marker.setVisible(true);

                        var infowindow = new google.maps.InfoWindow({
                            content: parent.name
                        });

                        parent_marker.addListener('click', function() {
                            infowindow.open(this.map, parent_marker);
                        });

                    }
                });
            }

            if(this.driver.school)
            {
                console.log(this.driver.school);
                if(this.driver.school.latitude && this.driver.school.longitude)
                {
                    var latlng = new google.maps.LatLng(this.driver.school.latitude, this.driver.school.longitude);
                    let school_marker = new google.maps.Marker({
                        map: this.map,
                        position: latlng,
                        icon: '/icon/school.png',
                    });
                    if (!bounds.contains(latlng)) {
                        // marker is not within map bounds
                        bounds_updated = true;
                        bounds.extend(latlng);
                    }
                    
                    school_marker.setVisible(true);

                    var infowindow = new google.maps.InfoWindow({
                        content: this.driver.school.name
                    });

                    school_marker.addListener('click', function() {
                        infowindow.open(this.map, school_marker);
                    });
                }
            }
            if (bounds_updated)
                this.map.fitBounds(bounds);
        },
    },
}
</script>
