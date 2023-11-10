<template>
    <div>
        <div class="row">

            <div class="col-md-12 col-lg-12 col-xl-12 ">

                <div class="row mt-2">

                    <div class="col-md-3">
                        <label class="control-label">Periodo</label>
                        <el-select v-model="form.period" @change="changePeriod">
                            <el-option v-for=" row in dates_array" :key="row.key" :label="row.name" :value="row.value"/>
                        </el-select>
                    </div>
                    <template v-if="form.period === 'month' || form.period === 'between_months'">
                        <div class="col-md-3">
                            <label class="control-label">Mes de</label>
                            <el-date-picker v-model="form.month_start" :clearable="false" format="MM/yyyy" type="month"
                                value-format="yyyy-MM" @change="changeDisabledMonths"></el-date-picker>
                        </div>
                    </template>
                    <template v-if="form.period === 'between_months'">
                        <div class="col-md-3">
                            <label class="control-label">Mes al</label>
                            <el-date-picker v-model="form.month_end" :clearable="false"
                                :picker-options="pickerOptionsMonths" format="MM/yyyy" type="month"
                                value-format="yyyy-MM"></el-date-picker>
                        </div>
                    </template>
                    <template v-if="form.period === 'date' || form.period === 'between_dates'">
                        <div class="col-md-3">
                            <label class="control-label">Fecha del</label>
                            <el-date-picker v-model="form.date_start" :clearable="false" format="dd/MM/yyyy" type="date"
                                value-format="yyyy-MM-dd" @change="changeDisabledDates"></el-date-picker>
                        </div>
                    </template>
                    <template v-if="form.period === 'between_dates'">
                        <div class="col-md-3">
                            <label class="control-label">Fecha al</label>
                            <el-date-picker v-model="form.date_end" :clearable="false" :picker-options="pickerOptionsDates"
                                format="dd/MM/yyyy" type="date" value-format="yyyy-MM-dd"></el-date-picker>
                        </div>
                    </template>

                    <div class="col-md-3" v-if="show_suppliers">
                        <label class="control-label">Proveedor</label>
                        <el-select v-model="form.supplier" filterable clearable>
                            <el-option v-for="supplier in suppliers" :key="supplier.id" :label="supplier.name" :value="supplier.id"></el-option>
                        </el-select>
                    </div>

                    <div class="col-md-3" v-if="show_imports">
                        <label class="control-label">Importación</label>
                        <el-select v-model="form.import" filterable clearable>
                            <el-option v-for="row in imports" :key="row.id" :label="row.name" :value="row.id"></el-option>
                        </el-select>
                    </div>

                    <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top:29px">
                        <el-button :loading="loading_submit" class="submit" icon="el-icon-search" type="primary"
                            @click.prevent="getRecordsByFilter">Buscar
                        </el-button>
                        <el-button v-if="this.records.length > 0" icon="el-icon-excel" type="success"
                            @click.prevent="clickDownload('excel')">Descargar excel
                        </el-button>

                    </div>

                </div>
                <div class="row mt-1 mb-4">
                </div>
            </div>

            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table" v-if="this.records.length > 0">
                        <thead>
                            <tr>
                                <th v-for="(rowH, indexH) in all_keys">{{ rowH }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <slot v-for="(row, index) in records" :index="customIndex(index)" :row="row"></slot>
                        </tbody>
                    </table>
                    <div v-else>
                        <el-alert title="No Data" description="No se encontraron registros para mostrar" type="error" effect="dark" :closable="false" show-icon center />
                    </div>
                    <div>
                        <el-pagination :current-page.sync="pagination.current_page" :page-size="pagination.per_page"
                            :total="pagination.total" layout="total, prev, pager, next" @current-change="getRecords">
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
                period: null,
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
            all_keys: [],
            loading_search_items: false,
            imports : [],
            suppliers : [],
            show_suppliers : true,
            show_imports : true,
            dates_array : [
                {
                    'key':'between_months',
                    'name': 'Entre meses',
                    'value': 'between_months'
                },
                {
                    'key':'month',
                    'name': 'Por mes',
                    'value': 'month'
                },
                {
                    'key':'date',
                    'name': 'Por fecha',
                    'value': 'date'
                },
                {
                    'key':'between_dates',
                    'name': 'Entre fechas',
                    'value': 'between_dates'
                },
            ]
        }
    },
    computed: {
        cantChoiseUserWithUserType() {
            if (this.form.user_type && this.form.user_type.length > 1) return false;
            return true;
        }
    },
    created() {
        this.initForm()
        this.$eventHub.$on('reloadData', () => {
            this.getRecords()
        })
        //console.log(this.resource)
    },
    async mounted() {

        if(this.resource == 'reports/payable' || this.resource == 'reports/receivable' || this.resource == 'reports/topay'){
            this.show_imports = false
            this.show_suppliers = false
            this.dates_array = [
                {
                    'key':'date',
                    'name': 'Por fecha',
                    'value': 'date'
                }
            ]
            this.form.period = 'date'
        }

        await this.getFilters()
        await this.getRecords()

    },
    methods: {

        ChangedSalesnote() {
            if (this.form.document_type_id == '80' && this.form.user_type != null) {
                this.form.user_type = 'CREADOR';
            }


            this.form.person_id = null
            this.form.user_id = [];
            this.$eventHub.$emit('changeFilterColumn', 'seller')
        },
        changePersons() {
            // this.form.type_person = this.resource === 'reports/sales' ? 'customers':'suppliers'
        },
        clickDownload(type) {
            let query = queryString.stringify({
                ...this.form
            });
            delete (query.user_id)
            delete (query.document_type_id)

            window.open(`/${this.resource}/${type}/?${query}`, '_blank');
        },

        initForm() {
            this.form = {
                period: 'month',
                date_start: moment().format('YYYY-MM-DD'),
                date_end: moment().format('YYYY-MM-DD'),
                month_start: moment().format('YYYY-MM'),
                month_end: moment().format('YYYY-MM'),
                import : 0,
                supplier : 0,
            }
        },
        customIndex(index) {
            return (this.pagination.per_page * (this.pagination.current_page - 1)) + index + 1
        },
        async getRecordsByFilter() {

            this.loading_submit = await true
            this.pagination.current_page = 1
            await this.getRecords()
            this.loading_submit = await false

        },
        async getFilters(){

            await this.$http.get(`/${this.resource}/tables`).then((response) => {
                this.suppliers = response.data.suppliers
                this.imports = response.data.imports

            });
        },
        getRecords() {

            return this.$http.get(`/${this.resource}/records?${this.getQueryParameters()}`).then((response) => {
                this.records = response.data.data
                let dataR = response.data
                delete dataR.data.data
                this.pagination = dataR
                this.pagination.per_page = parseInt(response.data.per_page)
                if(this.records.length > 0){

                    var keys = Object.keys(this.records[0]);
                    this.all_keys = keys
                }

                this.loading_submit = false

            });

        },
        getQueryParameters() {
            if (this.users.length > 0) {
                // delete(this.form.type_person)
            }
            let parameters = queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form
            })
            delete (parameters.user_id)
            delete (parameters.document_type_id)

            return `${parameters}&user_id=${JSON.stringify(this.form.user_id)}&document_type_id=${JSON.stringify(this.form.document_type_id)}`

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

                        if (this.items.length == 0) {
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
