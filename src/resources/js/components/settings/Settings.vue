<template>
<div class="container">
    <div class="row justify-content-md-center">
        <div class="col-md-9 col-xl-7">
            <div class="card-header px-0 mt-2 bg-transparent clearfix">
                <h4 class="float-left pt-2"><i class="card-icon fas fa-tools"></i> Settings</h4>
                <div class="card-header-actions mr-1">
                    <a class="btn btn-primary" href="#" :disabled="submiting" @click.prevent="updateSettings">
                        <i class="fas fa-spinner fa-spin" v-if="submiting"></i>
                        <i class="fas fa-check" v-else></i>
                        <span class="ml-1">Save</span>
                    </a>
                </div>
            </div>

            <form v-for="setting in settings" class="form-vertical" v-show="!loading">
                <div class="card-body px-0" v-show="!loading">
                    <div>
                        <div class="d-flex">
                            <h5>{{setting.name}}</h5> 
                            <label v-if="setting.enabled!=null && setting.name != 'SMS'" class="switch switch-pill switch-outline-success-alt block ml-4">
                                    <input class="switch-input" type="checkbox" v-model="setting.enabled" />
                                    <span class="switch-slider"></span>
                            </label>
                        </div>
                        <hr class="my-3">
                    </div>
                </div>
                
                <div v-for="setting_val in setting.settings" class="form-group row justify-content-md-center" v-if="is_setting_visible(setting_val.name)">
                    <label class="col-12" >{{setting_val.name}}</label>
                    <div class="col-12" v-if="setting_val.value_type == 'text' && is_setting_visible(setting_val.name)">
                        <input class="form-control" :class="{'is-invalid': errors.name}" 
                        type="text" :readonly="setting.enabled==false && setting.name != 'SMS'" v-model="setting_val.value" :placeholder="'Enter ' + setting_val.name">
                        <div class="invalid-feedback" v-if="errors.name">{{errors.name[0]}}</div>
                    </div>
                    <div class="col-12" v-else-if="setting_val.value_type == 'select'">
                        <multiselect
                                v-if="setting_val.name == 'Currency'"
                                v-model="currency_value"
                                placeholder="Select currency"
                                label="value"
                                track-by="value"
                                class="form-control"
                                :options="currency_options"
                                :option-height="104"
                                :show-labels="false">
                            <template slot="singleLabel" slot-scope="props">
                                <div class="option_value">
                                    <span class="option_name p-1">
                                    {{ props.option.label }} ({{ props.option.value }})
                                    </span>
                                </div>
                            </template>
                            <template slot="option" slot-scope="props">
                                <div class="option_value">
                                    <div class="option_name p-1">
                                        {{ props.option.label }} ({{ props.option.value }})
                                    </div>
                                </div>
                            </template>
                        </multiselect>

                        <multiselect
                                v-if="setting_val.name == 'Billing cycle'"
                                v-model="billing_cycle_value"
                                placeholder="Select billing cycle"
                                label="value"
                                track-by="value"
                                class="form-control"
                                :options="billing_cycle_options"
                                :option-height="104"
                                :show-labels="false">
                            <template slot="singleLabel" slot-scope="props">
                                <div class="option_value">
                                    <span class="option_name p-1">
                                    {{ props.option.label }}
                                    </span>
                                </div>
                            </template>
                            <template slot="option" slot-scope="props">
                                <div class="option_value">
                                    <div class="option_name p-1">
                                        {{ props.option.label }}
                                    </div>
                                </div>
                            </template>
                        </multiselect>

                        <multiselect
                                v-if="setting_val.name == 'SMS Gateway'"
                                v-model="sms_value"
                                placeholder="Select SMS gateway"
                                label="value"
                                track-by="value"
                                class="form-control"
                                :options="sms_options"
                                :option-height="104"
                                :show-labels="false">
                            <template slot="singleLabel" slot-scope="props">
                                <div class="option_value">
                                    <span class="option_name p-1">
                                    {{ props.option.label }} -- {{ props.option.url }}
                                    </span>
                                </div>
                            </template>
                            <template slot="option" slot-scope="props">
                                <div class="option_value">
                                    <div class="option_name p-1">
                                        {{ props.option.label }} -- {{ props.option.url }}
                                    </div>
                                </div>
                            </template>
                        </multiselect>
                    </div>
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
</template>

<script>

export default {
    data() {
        return {
            settings: [],
            errors: {},
            loading: true,
            submiting: false,   
            currency_value : {},
            billing_cycle_value : {},
            sms_value : {},
            billing_cycle_options : [
                {value:"month", label:"Monthly"},
                {value:"year", label:"Yearly"},
            ],
            sms_options : [
                {value:"none", label:"Not configured"},
                {value:"twilio", label:"Twilio", url:"https://www.twilio.com"},
                {value:"textlocal", label:"Textlocal", url:"https://textlocal.com"},
                {value:"branded sms", label:"Branded SMS", url:"https://smstoconnect.com"},
                {value:"infobip", label:"Infobip", url:"https://www.infobip.com"},
            ],
            currency_options: [
                {value:"USD", label:"US dollar"},
                {value:"EUR", label:"Euro"},
                {value:"JPY" ,label:"Japanese yen"},
                {value:"GBP" ,label:"Pound sterling"},
                {value:"AED" ,label:"United Arab Emirates dirham"},
                {value:"AFN" ,label:"Afghan afghani"},
                {value:"ALL" ,label:"Albanian lek"},
                {value:"AMD" ,label:"Armenian dram"},
                {value:"ANG" ,label:"Netherlands Antillean guilder"},
                {value:"AOA" ,label:"Angolan kwanza"},
                {value:"ARS" ,label:"Argentine peso"},
                {value:"AUD" ,label:"Australian dollar"},
                {value:"AWG" ,label:"Aruban florin"},
                {value:"AZN" ,label:"Azerbaijani manat"},
                {value:"BAM" ,label:"Bosnia and Herzegovina convertible mark"},
                {value:"BBD" ,label:"Barbadian dollar"},
                {value:"BDT" ,label:"Bangladeshi taka"},
                {value:"BGN" ,label:"Bulgarian lev"},
                {value:"BHD" ,label:"Bahraini dinar"},
                {value:"BIF" ,label:"Burundian franc"},
                {value:"BMD" ,label:"Bermudian dollar"},
                {value:"BND" ,label:"Brunei dollar"},
                {value:"BOB" ,label:"Bolivian boliviano"},
                {value:"BRL" ,label:"Brazilian real"},
                {value:"BSD" ,label:"Bahamian dollar"},
                {value:"BTN" ,label:"Bhutanese ngultrum"},
                {value:"BWP" ,label:"Botswana pula"},
                {value:"BYN" ,label:"Belarusian ruble"},
                {value:"BZD" ,label:"Belize dollar"},
                {value:"CAD" ,label:"Canadian dollar"},
                {value:"CDF" ,label:"Congolese franc"},
                {value:"CHF" ,label:"Swiss franc"},
                {value:"CLP" ,label:"Chilean peso"},
                {value:"CNY" ,label:"Chinese yuan"},
                {value:"COP" ,label:"Colombian peso"},
                {value:"CRC" ,label:"Costa Rican colón"},
                {value:"CUC" ,label:"Cuban convertible peso"},
                {value:"CUP" ,label:"Cuban peso"},
                {value:"CVE" ,label:"Cape Verdean escudo"},
                {value:"CZK" ,label:"Czech koruna"},
                {value:"DJF" ,label:"Djiboutian franc"},
                {value:"DKK" ,label:"Danish krone"},
                {value:"DOP" ,label:"Dominican peso"},
                {value:"DZD" ,label:"Algerian dinar"},
                {value:"EGP" ,label:"Egyptian pound"},
                {value:"ERN" ,label:"Eritrean nakfa"},
                {value:"ETB" ,label:"Ethiopian birr"},
                {value:"FJD" ,label:"Fijian dollar"},
                {value:"FKP" ,label:"Falkland Islands pound"},
                {value:"GBP" ,label:"British pound"},
                {value:"GEL" ,label:"Georgian lari"},
                {value:"GGP" ,label:"Guernsey pound"},
                {value:"GHS" ,label:"Ghanaian cedi"},
                {value:"GIP" ,label:"Gibraltar pound"},
                {value:"GMD" ,label:"Gambian dalasi"},
                {value:"GNF" ,label:"Guinean franc"},
                {value:"GTQ" ,label:"Guatemalan quetzal"},
                {value:"GYD" ,label:"Guyanese dollar"},
                {value:"HKD" ,label:"Hong Kong dollar"},
                {value:"HNL" ,label:"Honduran lempira"},
                {value:"HKD" ,label:"Hong Kong dollar"},
                {value:"HRK" ,label:"Croatian kuna"},
                {value:"HTG" ,label:"Haitian gourde"},
                {value:"HUF" ,label:"Hungarian forint"},
                {value:"IDR" ,label:"Indonesian rupiah"},
                {value:"ILS" ,label:"Israeli new shekel"},
                {value:"IMP" ,label:"Manx pound"},
                {value:"INR" ,label:"Indian rupee"},
                {value:"IQD" ,label:"Iraqi dinar"},
                {value:"IRR" ,label:"Iranian rial"},
                {value:"ISK" ,label:"Icelandic króna"},
                {value:"JEP" ,label:"Jersey pound"},
                {value:"JMD" ,label:"Jamaican dollar"},
                {value:"JOD" ,label:"Jordanian dinar"},
                {value:"JPY" ,label:"Japanese yen"},
                {value:"KES" ,label:"Kenyan shilling"},
                {value:"KGS" ,label:"Kyrgyzstani som"},
                {value:"KHR" ,label:"Cambodian riel"},
                {value:"KID" ,label:"Kiribati dollar"},
                {value:"KMF" ,label:"Comorian franc"},
                {value:"KPW" ,label:"North Korean won"},
                {value:"KRW" ,label:"South Korean won"},
                {value:"KWD" ,label:"Kuwaiti dinar"},
                {value:"KYD" ,label:"Cayman Islands dollar"},
                {value:"KZT" ,label:"Kazakhstani tenge"},
                {value:"LAK" ,label:"Lao kip"},
                {value:"LBP" ,label:"Lebanese pound"},
                {value:"LKR" ,label:"Sri Lankan rupee"},
                {value:"LRD" ,label:"Liberian dollar"},
                {value:"LSL" ,label:"Lesotho loti"},
                {value:"LYD" ,label:"Libyan dinar"},
                {value:"MAD" ,label:"Moroccan dirham"},
                {value:"MDL" ,label:"Moldovan leu"},
                {value:"MGA" ,label:"Malagasy ariary"},
                {value:"MKD" ,label:"Macedonian denar"},
                {value:"MMK" ,label:"Burmese kyat"},
                {value:"MNT" ,label:"Mongolian tögrög"},
                {value:"MOP" ,label:"Macanese pataca"},
                {value:"MRU" ,label:"Mauritanian ouguiya"},
                {value:"MUR" ,label:"Mauritian rupee"},
                {value:"MVR" ,label:"Maldivian rufiyaa"},
                {value:"MWK" ,label:"Malawian kwacha"},
                {value:"MXN" ,label:"Mexican peso"},
                {value:"MYR" ,label:"Malaysian ringgit"},
                {value:"MZN" ,label:"Mozambican metical"},
                {value:"NAD" ,label:"Namibian dollar"},
                {value:"NGN" ,label:"Nigerian naira"},
                {value:"NIO" ,label:"Nicaraguan córdoba"},
                {value:"NOK" ,label:"Norwegian krone"},
                {value:"NPR" ,label:"Nepalese rupee"},
                {value:"NZD" ,label:"New Zealand dollar"},
                {value:"OMR" ,label:"Omani rial"},
                {value:"PAB" ,label:"Panamanian balboa"},
                {value:"PEN" ,label:"Peruvian sol"},
                {value:"PGK" ,label:"Papua New Guinean kina"},
                {value:"PHP" ,label:"Philippine peso"},
                {value:"PKR" ,label:"Pakistani rupee"},
                {value:"PLN" ,label:"Polish złoty"},
                {value:"PRB" ,label:"Transnistrian ruble"},
                {value:"PYG" ,label:"Paraguayan guaraní"},
                {value:"QAR" ,label:"Qatari riyal"},
                {value:"RON" ,label:"Romanian leu"},
                {value:"RON" ,label:"Romanian leu"},
                {value:"RSD" ,label:"Serbian dinar"},
                {value:"RUB" ,label:"Russian ruble"},
                {value:"RWF" ,label:"Rwandan franc"},
                {value:"SAR" ,label:"Saudi riyal"},
                {value:"SEK" ,label:"Swedish krona"},
                {value:"SGD" ,label:"Singapore dollar"},
                {value:"SHP" ,label:"Saint Helena pound"},
                {value:"SLL" ,label:"Sierra Leonean leone"},
                {value:"SLS" ,label:"Somaliland shilling"},
                {value:"SOS" ,label:"Somali shilling"},
                {value:"SRD" ,label:"Surinamese dollar"},
                {value:"SSP" ,label:"South Sudanese pound"},
                {value:"STN" ,label:"São Tomé and Príncipe dobra"},
                {value:"SYP" ,label:"Syrian pound"},
                {value:"SZL" ,label:"Swazi lilangeni"},
                {value:"THB" ,label:"Thai baht"},
                {value:"TJS" ,label:"Tajikistani somoni"},
                {value:"TMT" ,label:"Turkmenistan manat"},
                {value:"TND" ,label:"Tunisian dinar"},
                {value:"TOP" ,label:"Tongan paʻanga"},
                {value:"TRY" ,label:"Turkish lira"},
                {value:"TTD" ,label:"Trinidad and Tobago dollar"},
                {value:"TVD" ,label:"Tuvaluan dollar"},
                {value:"TWD" ,label:"New Taiwan dollar"},
                {value:"TZS" ,label:"Tanzanian shilling"},
                {value:"UAH" ,label:"Ukrainian hryvnia"},
                {value:"UGX" ,label:"Ugandan shilling"},
                {value:"UYU" ,label:"Uruguayan peso"},
                {value:"UZS" ,label:"Uzbekistani soʻm"},
                {value:"VES" ,label:"Venezuelan bolívar soberano"},
                {value:"VND" ,label:"Vietnamese đồng"},
                {value:"VUV" ,label:"Vanuatu vatu"},
                {value:"WST" ,label:"Samoan tālā"},
                {value:"XAF" ,label:"Central African CFA franc"},
                {value:"XCD" ,label:"Eastern Caribbean dollar"},
                {value:"XOF" ,label:"West African CFA franc"},
                {value:"XPF" ,label:"CFP franc"},
                {value:"ZAR" ,label:"South African rand"},
                {value:"ZMW" ,label:"Zambian kwacha"},
                {value:"ZWB" ,label:"Zimbabwean bonds"},
            ]
        }
    },
    components: {
        
    },
    mounted() {
        this.getSettings();
    },
    watch: {
        currency_value: function (val) {
            var currency_setting = this.settings[2].settings.find(item => {
                return item.name === "Currency";
            });
            if(val == null)
            {
                currency_setting.value = val;
            }
            else
            {
                currency_setting.value = val.value;
            }
        },
        billing_cycle_value: function (val) {
            var billing_cycle_setting = this.settings[2].settings.find(item => {
                return item.name === "Billing cycle";
            });
            if(val == null)
            {
                billing_cycle_setting.value = val;
            }
            else
            {
                billing_cycle_setting.value = val.value;
            }
        },
        sms_value: function (val) {
            var sms_setting = this.settings[3].settings.find(item => {
                return item.name === "SMS Gateway";
            });
            if(val == null)
            {
                sms_setting.value = val;
            }
            else
            {
                sms_setting.value = val.value;
            }
        },
    },
    methods: {
        getSettings() {
            this.loading = true
            this.settings = [];
            axios.get(`/api/settings/getSettings`)
                .then(response => {
                    if(response.data)
                    {
                    this.settings = response.data;
                    console.log(this.settings);
                    this.updateCurrentCurrency();
                    this.updateCurrentBillingCycle();
                    this.updateSMS();
                    delete response.data.data
                    }
                    this.loading = false;

                }).catch(error => {
                    if(error.response)
                        this.errors = error.response.data.errors
                    this.loading = false
                });
        },
        is_sms_setting: function(val) {
            return this.sms_options.find(item => {
                return val.indexOf(item.label) !== -1;
            });
        },
        is_sms: function(val) {
            val === 'SMS';
        },
        is_selected_sms_setting: function(val) {
            if(this.sms_value)
            {
                return this.sms_options.find(item => {
                    return val.indexOf(this.sms_value.label) !== -1;
                });
            }
        },
        is_setting_visible: function(val) {
            if(this.is_sms_setting(val))
            {
                return this.is_selected_sms_setting(val);
            }
            else
                return true;
        },
        updateCurrentCurrency()
        {
            var currency_setting = this.settings[2].settings.find(item => {
                return item.name === "Currency";
            });

            this.currency_value =  this.currency_options.find(item => {
                return item.value === currency_setting.value;
            });
        },
        updateCurrentBillingCycle()
        {
            var billing_cycle_setting = this.settings[2].settings.find(item => {
                return item.name === "Billing cycle";
            });

            this.billing_cycle_value =  this.billing_cycle_options.find(item => {
                return item.value === billing_cycle_setting.value;
            });
        },
        updateSMS()
        {
            var sms_setting = this.settings[3].settings.find(item => {
                return item.name === "SMS Gateway";
            });

            this.sms_value =  this.sms_options.find(item => {
                return item.value === sms_setting.value;
            });
        },
        updateSettings() {
            if (!this.submiting) {
                this.submiting = true
                axios.put(`/api/settings/updateSettings`, this.settings)
                    .then(response => {
                        this.errors = {}
                        this.submiting = false
                        this.$toasted.global.error('Settings updated!');
                    })
                    .catch(error => {
                        if(error.response)
                        {
                            this.errors = error.response.data.errors
                            swal("Error", error.response.data.errors, "error")
                        }
                        this.submiting = false
                    })
            }
        }
    },
}
</script>
