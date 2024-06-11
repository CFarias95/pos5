<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">Reporte cuentas por pagar</h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <template>
                    <div>
                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-xl-12 ">
                                <div class="row mt-2">

                                    <div class="col-md-3">
                                        <label class="control-label">Desde</label>
                                        <el-date-picker v-model="form.desde" :clearable="false" format="dd/MM/yyyy"
                                            type="date" value-format="yyyy-MM-dd"
                                            @change="changeDisabledDates"></el-date-picker>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="control-label">Hasta</label>
                                        <el-date-picker v-model="form.hasta" :clearable="false" format="dd/MM/yyyy"
                                            type="date" value-format="yyyy-MM-dd"
                                            @change="changeDisabledDates"></el-date-picker>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label">Cliente</label>
                                            <el-select v-model="form.customer" clearable>
                                                <el-option label="Todos los clientes" value="0" key="0"></el-option>
                                                <el-option v-for="option in customers" :key="option.id" :label="option.name"
                                                    :value="option.id"></el-option>
                                            </el-select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label">Marca</label>
                                            <el-select v-model="form.brand_id" clearable>
                                                <el-option label="Todas" :value="0" :key="0"></el-option>
                                                <el-option v-for="option in brands" :key="option.id" :label="option.name"
                                                    :value="option.id"></el-option>
                                            </el-select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label">Categoria</label>
                                            <el-select v-model="form.category_id" clearable>
                                                <el-option label="Todos las categorias" :value="0" :key="0"></el-option>
                                                <el-option v-for="option in categories" :key="option.id" :label="option.name"
                                                    :value="option.id"></el-option>
                                            </el-select>
                                        </div>
                                    </div>

                                    <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top:29px">
                                        <el-button :loading="loading_submit" class="submit" icon="el-icon-search"
                                            type="primary" @click.prevent="getRecordsByFilter">Buscar
                                        </el-button>

                                        <template v-if="records.length > 0 && resource !== 'reports/document-detractions'">
                                            <el-button class="submit" type="success"
                                                @click.prevent="clickDownload('excel')"><i class="fa fa-file-excel"></i>
                                                Exportal
                                                Excel
                                            </el-button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table" v-if="records">
                                        <thead>
                                            <tr>
                                                <th v-for="(row, index) in headers" :index="index" scope="col">{{ index }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(row, index) in records" :index="customIndex(index)">
                                                <td v-for="data in row">{{ data }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div v-else>
                                        <div>
                                            <el-alert title="Sin datos para mostrar" type="warning" :closable="false" />
                                        </div>
                                    </div>
                                    <div>
                                        <el-pagination :current-page.sync="pagination.current_page"
                                            :page-size="pagination.per_page" :total="pagination.total"
                                            layout="total, prev, pager, next" @current-change="getRecords">
                                        </el-pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment'
import queryString from 'query-string'

export default {
    props: ['configuration'],
    components: {},
    data() {
        return {
            pickerOptionsDates: {
                disabledDate: (time) => {
                    time = moment(time).format('YYYY-MM-DD')
                    return this.form.date_start > time
                }
            },
            showDialogOptions: false,
            pagination: {},
            resource: 'reports/reporte_ventas_detalle',
            form: {
                desde: null,
                hasta: null,
                customer: '0',
                brand_id: 0,
                category_id:0,
            },
            records: [],
            customers: [],
            categories: [],
            brands: [],
            loading_submit: false,
            loading_search: false,
            headers: [],

        }
    },
    async created() {

        this.initForm()
        await this.$http.get(`/${this.resource}/filter`)
            .then(response => {
                this.customers = response.data.persons
                this.brands = response.data.brands
                this.categories = response.data.categories
            });
        await this.getRecords()
    },
    methods: {
        initForm() {
            this.form = {
                desde: moment().format('YYYY-MM-DD'),
                hasta: moment().format('YYYY-MM-DD'),
                customer: '0',
                brand_id: 0,
            }
        },
        async getRecordsByFilter() {

            this.loading_submit = await true
            await this.getRecords()
            this.loading_submit = await false

        },
        getRecords() {
            return this.$http.get(`/${this.resource}/records?${this.getQueryParameters()}`).then((response) => {
                this.records = response.data.paginatedCollection.data
                this.pagination = response.data.paginatedCollection
                this.pagination.per_page = parseInt(response.data.paginatedCollection.per_page)
                this.loading_submit = false
                this.headers = response.data.header
            });
        },
        getQueryParameters() {

            let parameters = queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form
            })
            return `${parameters}`
        },
        changeDisabledDates() {
            if (this.form.date_end < this.form.date_start) {
                this.form.date_end = this.form.date_start
            }
        },
        changeDisabledMonths() {
            if (this.form.month_end < this.form.month_start) {
                this.form.month_end = this.form.month_start
            }
        },
        customIndex(index) {
            return (this.pagination.per_page * (this.pagination.current_page - 1)) + index + 1
        },
        clickDownload(type) {
            let query = queryString.stringify({
                ...this.form
            });
            window.open(`/${this.resource}/${type}/?${query}`, '_blank');
        },
    }
}

</script>
