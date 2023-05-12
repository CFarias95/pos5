<template>
    <div>
        <div class="row">

            <div class="col-md-12 col-lg-12 col-xl-12 ">

                <div class="row mt-2">

                    <div class="col-md-3">
                        <label class="control-label">Orden de compra número:</label>
                        <el-select v-model="form.order">
                            <el-option v-for="option in orders"
                                    :key="option.id"
                                    :label="option.id"
                                    :value="option.id"></el-option>

                        </el-select>
                    </div>

                <div class="row mt-1 mb-4"></div>
                </div>
                <div class="col-lg-7 col-md-7 col-md-7 col-sm-12"
                         style="margin-top:29px">
                        <el-button :loading="loading_submit"
                                   class="submit"
                                   icon="el-icon-search"
                                   type="primary"
                                   @click.prevent="getRecordsByFilter">Buscar
                        </el-button>

                        <template v-if="records.length>0 && resource  !== 'reports/document-detractions'">

                            <el-button class="submit"
                                       type="success"
                                       @click.prevent="clickDownload('excel')"><i class="fa fa-file-excel"></i> Exportar Excel
                            </el-button>

                        </template>

                    </div>
            </div>


            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <slot name="heading"></slot>
                        </thead>
                        <tbody>
                        <slot v-for="(row, index) in records"
                              :index="customIndex(index)"
                              :row="row"></slot>
                        </tbody>
                        <tfoot v-if="resource == 'reports/sales' || resource == 'reports/purchases' || resource == 'reports/fixed-asset-purchases'">

                            <template v-if="resource == 'reports/sales'|| this.resource === 'reports/state-account'">
                                <tr>
                                    <td :colspan="13"></td>
                                    <td v-if="visibleColumns.guides.visible"></td>
                                    <td v-if="visibleColumns.options.visible"></td>
                                    <td v-if="visibleColumns.web_platforms.visible"></td>
                                    <td v-if="visibleColumns.total_charge.visible"></td>
                                    <td><strong>Totales PEN</strong></td>
                                    <td>{{ totals.acum_total_exonerated }}</td>
                                    <td>{{ totals.acum_total_unaffected }}</td>
                                    <td>{{ totals.acum_total_free }}</td>

                                    <td>{{ totals.acum_total_taxed }}</td>
                                    <td>{{ totals.acum_total_igv }}</td>
                                    <td v-if="visibleColumns.total_isc.visible"></td>
                                    <td>{{ totals.acum_total }}</td>
                                </tr>
                                <tr>
                                    <td :colspan="13"></td>
                                    <td v-if="visibleColumns.guides.visible"></td>
                                    <td v-if="visibleColumns.options.visible"></td>
                                    <td v-if="visibleColumns.web_platforms.visible"></td>
                                    <td v-if="visibleColumns.total_charge.visible"></td>
                                    <td><strong>Totales USD</strong></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ totals.acum_total_taxed_usd }}</td>
                                    <td>{{ totals.acum_total_igv_usd }}</td>
                                    <td v-if="visibleColumns.total_isc.visible"></td>
                                    <td>{{ totals.acum_total_usd }}</td>
                                </tr>

                            </template>
                            <template v-else>
                                <!-- mostrar si no se aplica conversion a soles -->
                                <template v-if="!applyConversionToPen">
                                <tr>
                                    <td :colspan="colspanFootPurchase"></td>
                                    <td><strong>Totales PEN</strong></td>
                                    <td>{{ totals.acum_total_exonerated }}</td>
                                    <td>{{ totals.acum_total_unaffected }}</td>
                                    <td>{{ totals.acum_total_free }}</td>

                                    <td>{{ totals.acum_total_taxed }}</td>
                                    <td>{{ totals.acum_total_igv }}</td>
                                    <td>{{ totals.acum_total }}</td>
                                </tr>
                                <tr>
                                    <td :colspan="colspanFootPurchase"></td>
                                    <td><strong>Totales USD</strong></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>

                                    <td>{{ totals.acum_total_taxed_usd }}</td>
                                    <td>{{ totals.acum_total_igv_usd }}</td>
                                    <td>{{ totals.acum_total_usd }}</td>
                                </tr>
                                </template>
                            </template>
                        </tfoot>
                    </table>
                    <div>
                        <el-pagination
                            :current-page.sync="pagination.current_page"
                            :page-size="pagination.per_page"
                            :total="pagination.total"
                            layout="total, prev, pager, next"
                            @current-change="getRecords">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<style>
.font-custom {
    font-size: 15px !important
}
</style>
<script>

import moment from 'moment'
import queryString from 'query-string'

export default {
    props: {
        resource: String,
        applyCustomer: {
            type: Boolean,
            required: false,
            default: false
        },
        visibleColumns: Object,
        colspanFootPurchase: {
            type: Number,
            required: false,
            default: 8
        },
        applyConversionToPen: {
            type: Boolean,
            required: false,
            default: false
        },
    },
    data() {
        return {
            orders:null,
            loading_submit: false,
            persons: [],
            all_persons: [],
            loading_search: false,
            columns: [],
            records: [],
            headers: headers_token,
            document_types: [],
            pagination: {},
            search: {},
            totals: {},
            establishment: null,
            establishments: [],
            web_platforms: [],
            state_types: [],
            users: [],
            form: {
                document_type_id: null,
                user_type: null,
                user_id: [],
            },
            pickerOptionsDates: {
                disabledDate: (time) => {
                    time = moment(time).format('YYYY-MM-DD')
                    return this.form.date_start > time
                }
            },
            pickerOptionsMonths: {
                disabledDate: (time) => {
                    time = moment(time).format('YYYY-MM')
                    return this.form.month_start > time
                }
            },
            sellers: [],
            items: [],
            all_items: [],
            loading_search_items: false
        }
    },
    computed: {
        cantChoiseUserWithUserType(){
            if(this.form.user_type && this.form.user_type.length > 1) return false;
            return true;
        }
    },
    created() {
        this.initForm()
        this.initTotals()
        this.$eventHub.$on('reloadData', () => {
            this.getRecords()
        })
        //console.log(this.resource)
    },
    async mounted() {

        await this.$http.get(`/${this.resource}/filter`)
            .then(response => {
                this.establishments = response.data.establishments;
                this.all_persons = response.data.persons
                this.document_types = response.data.document_types;
                this.sellers = response.data.sellers
                this.state_types = response.data.state_types
                this.web_platforms = response.data.web_platforms
                this.orders = response.data.orders
                // this.form.establishment_id = (this.establishments.length > 0)?this.establishments[0].id:null;
                if(response.data.users !== undefined) {
                    this.users = response.data.users;
                }
            });
        await this.filterPersons()
        this.form.type_person = this.resource === 'reports/sales' || this.resource === 'reports/state-account'? 'customers' : 'suppliers'

    },
    methods: {

        ChangedSalesnote(){
            if(this.form.document_type_id == '80' && this.form.user_type != null ){
                this.form.user_type = 'CREADOR';
            }


            this.form.person_id = null
            this.form.user_id = [];
            this.$eventHub.$emit('changeFilterColumn', 'seller')
        },
        changePersons() {
            // this.form.type_person = this.resource === 'reports/sales' ? 'customers':'suppliers'
        },
        filterPersons() {
            this.persons = this.all_persons
        },

        clickDownload(type) {
            let query = queryString.stringify({
                ...this.form
            });
            delete(query.user_id)
            delete(query.document_type_id)

            window.open(`/${this.resource}/${type}/?${query}&order=${JSON.stringify(this.form.order)}`, '_blank');
        },

        initForm() {

            this.form = {
                order:null,
                apply_conversion_to_pen: this.applyConversionToPen
            }

        },
        initTotals() {

            this.totals = {
                acum_total_taxed: 0,
                acum_total_igv: 0,
                acum_total: 0,
                acum_total_exonerated: 0,
                acum_total_unaffected: 0,
                acum_total_free: 0,

                acum_total_taxed_usd: 0,
                acum_total_igv_usd: 0,
                acum_total_usd: 0,
            }
        },
        customIndex(index) {
            return (this.pagination.per_page * (this.pagination.current_page - 1)) + index + 1
        },
        async getRecordsByFilter() {

            this.loading_submit = await true
            await this.getRecords()
            this.loading_submit = await false

        },
        getRecords() {
            return this.$http.get(`/${this.resource}/records?${this.getQueryParameters()}`).then((response) => {
                this.records = response.data.data
                this.pagination = response.data.meta
                this.pagination.per_page = parseInt(response.data.per_page)
                this.loading_submit = false
                // this.initTotals()
            });


        },
        getQueryParameters() {
            if(this.users.length  > 0){
                // delete(this.form.type_person)
            }
            let parameters = queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form
            })
            delete(parameters.user_id)
            delete(parameters.document_type_id)

            return `${parameters}&order=${JSON.stringify(this.form.order)}`

        },

        changeDisabledDates() {
            if (this.form.date_end < this.form.date_start) {
                this.form.date_end = this.form.date_start
            }
            // this.loadAll();
        },
        changeDisabledMonths() {
            if (this.form.month_end < this.form.month_start) {
                this.form.month_end = this.form.month_start
            }
            // this.loadAll();
        },
        changePeriod() {
            if (this.form.period === 'month') {
                this.form.month_start = moment().format('YYYY-MM');
                this.form.month_end = moment().format('YYYY-MM');
            }
            if (this.form.period === 'between_months') {
                this.form.month_start = moment().startOf('year').format('YYYY-MM'); //'2019-01';
                this.form.month_end = moment().endOf('year').format('YYYY-MM');

            }
            if (this.form.period === 'date') {
                this.form.date_start = moment().format('YYYY-MM-DD');
                this.form.date_end = moment().format('YYYY-MM-DD');
            }
            if (this.form.period === 'between_dates') {
                this.form.date_start = moment().startOf('month').format('YYYY-MM-DD');
                this.form.date_end = moment().endOf('month').format('YYYY-MM-DD');
            }
            // this.loadAll();
        },
        searchRemoteItems(input) {
                if (input.length > 0) {

                    this.loading_search = true
                    let parameters = `input=${input}`


                    this.$http.get(`/reports/data-table/items/?${parameters}`)
                            .then(response => {
                                this.items = response.data.items
                                this.loading_search = false

                                if(this.items.length == 0){
                                    this.filterItems()
                                }
                            })
                } else {
                    this.filterItems()
                }

        },
        filterItems() {
            this.items = this.all_items
        },
    }
}
</script>
