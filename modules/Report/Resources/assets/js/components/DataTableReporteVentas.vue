<template>
    <div>
        <div class="row">

            <div class="col-md-12 col-lg-12 col-xl-12 ">

                <div class="row mt-2">

                    <div class="col-md-3">
                        <label class="control-label">Periodo</label>
                        <el-select v-model="form.period" @change="changePeriod">
                            <el-option key="month" label="Por mes" value="month"></el-option>
                            <el-option key="between_months" label="Entre meses" value="between_months"></el-option>
                            <el-option key="date" label="Por fecha" value="date"></el-option>
                            <el-option key="between_dates" label="Entre fechas" value="between_dates"></el-option>
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

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Establecimiento</label>
                            <el-select v-model="form.establishment_id" clearable>
                                <el-option v-for="option in establishments" :key="option.id" :label="option.name"
                                    :value="option.id"></el-option>
                            </el-select>
                        </div>
                    </div>
                    <div v-show="resource == 'reports/sales' || resource == 'reports/purchases' || resource == 'reports/fixed-asset-purchases' || resource == 'reports/state-account'"
                        class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Tipo de documento</label>
                            <el-select v-model="form.document_type_id" clearable>
                                <el-option v-for="option in document_types" :key="option.id" :label="option.description"
                                    :value="option.id"></el-option>
                            </el-select>
                        </div>
                    </div>

                    <div v-if="resource == 'reports/sales' || resource == 'reports/purchases' || resource == 'reports/fixed-asset-purchases' || resource == 'reports/state-account'"
                        class="col-lg-5 col-md-5">
                        <div class="form-group">
                            <label class="control-label">
                                {{ (resource == 'reports/sales' || resource == 'reports/state-account') ? 'Clientes' :
                                    'Proveedores' }}
                            </label>

                            <el-select v-model="form.person_id" :loading="loading_search"
                                :remote-method="searchRemotePersons" clearable filterable
                                placeholder="Nombre o número de documento" popper-class="el-select-customers" remote
                                @change="changePersons">
                                <el-option v-for="option in persons" :key="option.id" :label="option.description"
                                    :value="option.id"></el-option>
                            </el-select>

                        </div>
                    </div>
                    <template>
                        <template v-if="users.length < 1">
                            <div v-if="applyCustomer" :class="(
                                resource == 'reports/commissions' ||
                                resource == 'reports/sales' ||
                                resource == 'reports/purchases') ? 'col-lg-4 col-md-4' : 'col-lg-3 col-md-3'">
                                <div class="form-group">
                                    <label class="control-label">
                                        Usuarios
                                    </label>

                                    <el-select v-model="form.seller_id" clearable filterable placeholder="Nombre usuario"
                                        popper-class="el-select-customers">
                                        <el-option v-for="option in sellers" :key="option.id" :label="option.name"
                                            :value="option.id"></el-option>
                                    </el-select>

                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="col-md-2 form-group">
                                <label class="control-label">Tipo de usuario</label>
                                <el-select v-model="form.user_type" clearable @change="ChangedSalesnote">
                                    <el-option key="CREADOR" label="Registrado por" value="CREADOR"></el-option>
                                    <el-option v-show="form.document_type_id !== '80'" key="VENDEDOR"
                                        label="Vendedor asignado" value="VENDEDOR"></el-option>
                                </el-select>
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="control-label">{{
                                    form.user_type === 'CREADOR' ? 'Usuario' : 'Vendedor'
                                }}</label>
                                <el-select v-model="form.user_id" :disabled="cantChoiseUserWithUserType" clearable
                                    filterable multiple>
                                    <el-option v-for="user in users" :key="user.id" :label="user.name"
                                        :value="user.id"></el-option>
                                </el-select>
                            </div>
                        </template>
                    </template>



                    <div v-if="resource == 'reports/sales' || resource === 'reports/sale-notes' || resource != 'reports/state-account' || resource !== 'reports/state-account'"
                        class="col-lg-3 col-md-3">
                        <label v-if="resource !== 'reports/state-account'">Orden de compra</label>
                        <el-input v-if="resource !== 'reports/state-account'" v-model="form.purchase_order"
                            clearable></el-input>
                    </div>
                    <div class="col-lg-3 col-md-3" v-if="resource !== 'reports/state-account'">
                        <div class="form-group">
                            <label class="control-label">Numero de Guía</label>
                            <el-input v-model="form.guides" clearable></el-input>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3" v-if="resource !== 'reports/state-account'">
                        <div class="form-group">
                            <label class="control-label">Plataforma</label>
                            <el-select v-model="form.web_platform_id" clearable>
                                <el-option v-for="option in web_platforms" :key="option.id" :label="option.name"
                                    :value="option.id"></el-option>
                            </el-select>
                        </div>
                    </div>

                    <div v-if="resource == 'reports/sales' || resource !== 'reports/state-account'"
                        class="col-lg-3 col-md-3 mt-4">
                        <div class="form-group">
                            <el-checkbox v-model="form.include_categories">¿Incluir categorías?</el-checkbox>
                            <br>
                        </div>
                    </div>

                    <div v-if="resource == 'reports/quotations' || resource !== 'reports/state-account'"
                        class="col-lg-3 col-md-3">
                        <div class="form-group">
                            <label class="control-label">
                                Estado
                            </label>

                            <el-select v-model="form.state_type_id" clearable filterable popper-class="el-select-customers">
                                <el-option v-for="option in state_types" :key="option.id" :label="option.name"
                                    :value="option.id"></el-option>
                            </el-select>

                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label class="control-label">Productos
                            </label>

                            <el-select v-model="form.item_id" filterable remote popper-class="el-select-customers" clearable
                                placeholder="Código interno o nombre" :remote-method="searchRemoteItems"
                                :loading="loading_search_items">
                                <el-option v-for="option in items" :key="option.id" :value="option.id"
                                    :label="option.description"></el-option>
                            </el-select>

                        </div>
                    </div>


                    <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top:29px">
                        <el-button :loading="loading_submit" class="submit" icon="el-icon-search" type="primary"
                            @click.prevent="getRecordsByFilter">Buscar
                        </el-button>

                        <template v-if="records.length > 0 && resource !== 'reports/document-detractions'">

                            <!--<el-button v-if="resource != 'reports/state-account'" class="submit" icon="el-icon-tickets"
                                type="danger" @click.prevent="clickDownload('pdf')">Exportar PDF
                            </el-button>-->

                            <!-- <el-button v-if="resource == 'reports/sales'" class="submit" icon="el-icon-tickets"
                                type="danger" @click.prevent="clickDownload('pdf-simple')">Exportar PDF Simple
                            </el-button>-->

                            <el-button class="submit" type="success" @click.prevent="clickDownload('excel')"><i
                                    class="fa fa-file-excel"></i> Exportar
                                Excel
                            </el-button>

                        </template>

                    </div>

                </div>
                <div class="row mt-1 mb-4">

                </div>
            </div>
        </div>
        
        <div>
            <div>
                <DxChart ref="chart">
                    <DxTooltip :enabled="true" />
                    <DxAdaptiveLayout :width="450" />
                    <DxSize :height="200" />
                    <DxCommonSeriesSettings type="bar" />
                </DxChart>
            </div>
            <div>
                <DxPivotGrid id="pivotgrid" ref="grid" :data-source="dataSource" :allow-sorting-by-summary="true"
                    :allow-filtering="true" :show-borders="true" :show-column-grand-totals="false"
                    :show-row-grand-totals="false" :show-row-totals="false" :show-column-totals="false">
                    <DxFieldChooser :enabled="true" :height="800" />
                </DxPivotGrid>
            </div>

        </div>

        <div>
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <slot name="heading"></slot>
                        </thead>
                        <tbody>
                            <slot v-for="(row, index) in records" :index="customIndex(index)" :row="row"></slot>
                        </tbody>
                        <tfoot
                            v-if="resource == 'reports/sales' || resource == 'reports/purchases' || resource == 'reports/fixed-asset-purchases'">

                            <template v-if="resource == 'reports/sales' || this.resource === 'reports/state-account'">
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
                        <el-pagination :current-page.sync="pagination.current_page" :page-size="pagination.per_page"
                            :total="pagination.total" layout="total, prev, pager, next" @current-change="getRecords">
                        </el-pagination>
                    </div>
                </div>
            </div>

            <div>

            </div>
        </div>
    </div>
</template>
<style>
.font-custom {
    font-size: 15px !important
}

#pivotgrid {
    margin-top: 20px;
}

.currency {
    text-align: center;
}
</style>

<script>

import moment from 'moment'
import queryString from 'query-string'

import {
    DxChart,
    DxAdaptiveLayout,
    DxCommonSeriesSettings,
    DxSize,
    DxTooltip,
} from 'devextreme-vue/chart';

import {
    DxPivotGrid,
    DxFieldChooser,
} from 'devextreme-vue/pivot-grid';

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
    components: {
        DxChart,
        DxAdaptiveLayout,
        DxCommonSeriesSettings,
        DxSize,
        DxTooltip,
        DxPivotGrid,
        DxFieldChooser,
    },
    data() {
        return {
            dataSource: {},
            store: null,
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
        cantChoiseUserWithUserType() {
            if (this.form.user_type && this.form.user_type.length > 1) return false;
            return true;
        }
    },
    created() {
        this.initForm()
        this.initTotals()
        this.$eventHub.$on('reloadData', () => {
            this.getRecords()
        })
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
                // this.form.establishment_id = (this.establishments.length > 0)?this.establishments[0].id:null;
                if (response.data.users !== undefined) {
                    this.users = response.data.users;
                }
            });


        await this.getRecords()
        await this.filterPersons()
        // await this.getTotals()
        this.form.type_person = this.resource === 'reports/sales' || this.resource === 'reports/state-account' ? 'customers' : 'suppliers'


        const pivotGrid = this.$refs.grid.instance;
        const chart = this.$refs.chart.instance;
        pivotGrid.bindChart(chart, {
            dataFieldsDisplayMode: 'splitPanes',
            alternateDataFields: false,
        });

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
        searchRemotePersons(input) {

            if (input.length > 0) {

                this.loading_search = true
                let parameters = `input=${input}`

                this.form.type_person = this.resource === 'reports/sales' || this.resource === 'reports/state-account' ? 'customers' : 'suppliers'


                this.$http.get(`/reports/data-table/persons/${this.form.type_person}?${parameters}`)
                    .then(response => {
                        this.persons = response.data.persons
                        this.loading_search = false

                        if (this.persons.length == 0) {
                            this.filterPersons()
                        }
                    })
            } else {
                this.filterPersons()
            }

        },
        filterPersons() {
            this.persons = this.all_persons
        },
        getTotals(records) {

            this.initTotals()

            records.forEach(row => {

                let signal = row.document_type_id;
                let state = row.state_type_id;

                if (row.currency_type_id == 'PEN') {

                    if ((signal == '07' && state != '11')) {

                        this.totals.acum_total += parseFloat(-row.total);
                        this.totals.acum_total_taxed += parseFloat(-row.total_taxed);
                        this.totals.acum_total_igv += parseFloat(-row.total_igv);


                        this.totals.acum_total_exonerated += parseFloat(-row.total_exonerated);
                        this.totals.acum_total_unaffected += parseFloat(-row.total_unaffected);
                        this.totals.acum_total_free += parseFloat(-row.total_free);


                    } else if (signal != '07' && state == '11') {

                        this.totals.acum_total += 0;
                        this.totals.acum_total_taxed += 0;
                        this.totals.acum_total_igv += 0;

                        this.totals.acum_total_exonerated += 0;
                        this.totals.acum_total_unaffected += 0;
                        this.totals.acum_total_free += 0;

                    } else {

                        this.totals.acum_total += parseFloat(row.total);
                        this.totals.acum_total_taxed += parseFloat(row.total_taxed);
                        this.totals.acum_total_igv += parseFloat(row.total_igv);

                        this.totals.acum_total_exonerated += parseFloat(row.total_exonerated);
                        this.totals.acum_total_unaffected += parseFloat(row.total_unaffected);
                        this.totals.acum_total_free += parseFloat(row.total_free);
                    }


                } else if (row.currency_type_id == 'USD') {

                    if ((signal == '07' && state != '11')) {

                        this.totals.acum_total_usd += parseFloat(-row.total);
                        this.totals.acum_total_taxed_usd += parseFloat(-row.total_taxed);
                        this.totals.acum_total_igv_usd += parseFloat(-row.total_igv);


                    } else if (signal != '07' && state == '11') {

                        this.totals.acum_total_usd += 0;
                        this.totals.acum_total_taxed_usd += 0;
                        this.totals.acum_total_igv_usd += 0;


                    } else {

                        this.totals.acum_total_usd += parseFloat(row.total);
                        this.totals.acum_total_taxed_usd += parseFloat(row.total_taxed);
                        this.totals.acum_total_igv_usd += parseFloat(row.total_igv);

                    }


                }
                this.totals.acum_total_taxed = _.round(this.totals.acum_total_taxed, 2)
                this.totals.acum_total_igv = _.round(this.totals.acum_total_igv, 2)
                this.totals.acum_total = _.round(this.totals.acum_total, 2)
                this.totals.acum_total_exonerated = _.round(this.totals.acum_total_exonerated, 2)
                this.totals.acum_total_unaffected = _.round(this.totals.acum_total_unaffected, 2)
                this.totals.acum_total_free = _.round(this.totals.acum_total_free, 2)

                this.totals.acum_total_taxed_usd = _.round(this.totals.acum_total_taxed_usd, 2)
                this.totals.acum_total_igv_usd = _.round(this.totals.acum_total_igv_usd, 2)
                this.totals.acum_total_usd = _.round(this.totals.acum_total_usd, 2)
            })
        },
        clickDownload(type) {
            let query = queryString.stringify({
                ...this.form
            });
            delete (query.user_id)
            delete (query.document_type_id)

            window.open(`/${this.resource}/${type}/?${query}&user_id=${JSON.stringify(this.form.user_id)}&document_type_id=${JSON.stringify(this.form.document_type_id)}`, '_blank');
        },

        initForm() {

            this.form = {
                establishment_id: null,
                person_id: null,
                type_person: null,
                document_type_id: null,
                period: 'month',
                date_start: moment().format('YYYY-MM-DD'),
                date_end: moment().format('YYYY-MM-DD'),
                month_start: moment().format('YYYY-MM'),
                month_end: moment().format('YYYY-MM'),
                seller_id: null,
                state_type_id: null,
                include_categories: false,
                guides: null,
                user_type: null,
                user_id: [],
                item_id: null,
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
                this.pagination.per_page = parseInt(response.data.meta.per_page)
                this.loading_submit = false
                this.dataTable()
                if (this.resource == 'reports/sales' || this.resource == 'reports/purchases' || this.resource == 'reports/fixed-asset-purchases' || this.resource === 'reports/state-account') this.getTotals(response.data.data)
            });
        },
        dataTable() {
            const pivotData = [];

            this.records.forEach((item) => {
                item.items.forEach((element) => {
                    pivotData.push({
                        id: item.id,
                        group_id: item.group_id,
                        soap_type_id: item.soap_type_id,
                        soap_type_description: item.soap_type_description,
                        date_of_issue: item.date_of_issue,
                        time_of_issue: item.time_of_issue,
                        total_charge_item: element.total_charge_item,
                        category: element.category,
                        marca: element.marca,
                        customer_name: item.customer_name,
                        number: item.number,
                        customer_number: item.customer_number,
                        total: element.unit_price,
                        total_exportation: item.total_exportation,
                        total_exonerated: item.total_exonerated,
                        total_free: item.total_free,
                        total_taxed: item.total_taxed,
                        total_igv: item.total_igv,
                        document_type_description: item.document_type_description,
                        user_name: item.user_name,
                        description: element.description,
                        quantity: element.quantity,
                    })
                })
            });
            this.dataSource = {
                fields: [{
                    caption: 'ID Vendedor',
                    dataField: 'id',
                    dataType: 'number',
                    summaryType: 'count',
                    width: 60,
                }, {
                    caption: 'ID de Grupo',
                    dataField: 'group_id',
                    width: 120,
                    dataType: 'String',
                }, {
                    caption: 'Tipo ID Soap',
                    dataField: 'soap_type_id',
                    width: 120,
                    dataType: 'String',
                }, {
                    caption: 'Descrip. tipo Soap',
                    dataField: 'soap_type_description',
                    width: 120,
                    dataType: 'String',
                }, {
                    caption: 'Fecha Emisión',
                    dataField: 'date_of_issue',
                    dataType: 'date',
                    area: 'column',
                }, {
                    groupName: 'date_of_issue',
                    groupInterval: 'month',
                    visible: false,
                }, {
                    caption: 'Hora Emisión',
                    dataField: 'time_of_issue',
                    dataType: 'String',
                    area: 'column',
                }, {
                    caption: 'Cliente',
                    width: 120,
                    dataField: 'customer_name',
                    dataType: 'String',
                    area: 'row',
                }, {
                    caption: '# Comprobante',
                    width: 120,
                    dataField: 'number',
                    dataType: 'String',
                    area: 'row',
                }, {
                    caption: 'Categoria',
                    width: 120,
                    dataField: 'category',
                    dataType: 'String',
                }, {
                    caption: 'Marca',
                    width: 120,
                    dataField: 'marca',
                    dataType: 'String',
                }, {
                    caption: 'Cedula/RUC',
                    dataField: 'customer_number',
                    dataType: 'String',
                    width: 120,
                }, {
                    caption: 'Total Ventas',
                    dataField: 'total',
                    dataType: 'number',
                    summaryType: 'sum',
                    format: 'decimal',
                    area: 'data',
                    width: 120,
                }, {
                    caption: 'Total Exportado',
                    dataField: 'total_exportation',
                    dataType: 'number',
                    summaryType: 'sum',
                    format: 'decimal',
                    width: 60,
                }, {
                    caption: 'Total Exonerado',
                    dataField: 'total_exonerated',
                    dataType: 'number',
                    summaryType: 'sum',
                    format: 'decimal',
                    width: 60,
                }, {
                    caption: 'Total Gratuito',
                    dataField: 'total_free',
                    dataType: 'String',
                    dataType: 'number',
                    summaryType: 'sum',
                    format: 'decimal',
                    width: 60,
                }, {
                    caption: 'Subtotal',
                    dataField: 'total_taxed',
                    dataType: 'number',
                    summaryType: 'sum',
                    format: 'decimal',
                    width: 60,
                }, {
                    caption: 'IVA 12%',
                    dataField: 'total_igv',
                    dataType: 'number',
                    summaryType: 'sum',
                    format: 'decimal',
                    width: 60,
                }, {
                    caption: 'Tipo Documento',
                    dataField: 'document_type_description',
                    dataType: 'String',
                    width: 60,
                }, {
                    caption: 'Vendedor',
                    dataField: 'user_name',
                    dataType: 'String',
                    width: 60,
                }, {
                    caption: 'Producto',
                    dataField: 'description',
                    dataType: 'String',
                    area: 'row',
                    width: 60,
                }, {
                    caption: 'Cantidad Comprada',
                    dataField: 'quantity',
                    dataType: 'number',
                    summaryType: 'sum',
                    width: 60,
                }, {
                    caption: 'Cargos Adicionales Item',
                    dataField: 'total_charge_item',
                    dataType: 'number',
                    summaryType: 'sum',
                    width: 60,
                }],
                store: pivotData,

            };
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
