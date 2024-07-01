<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Reporte Cobros Efectuados</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Reporte Cobros Efectuados</h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <div class="col-md-12 col-lg-12 col-xl-12 ">

                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label class="control-label">Cliente:</label>
                            <el-select v-model="form.client_id" clearable filterable>
                                <el-option :value="0" label="Todos los Clientes" />
                                <el-option v-for="client in clients"
                                    :key="client.id"
                                    :label="client.name"
                                    :value="client.id">
                                </el-option>
                            </el-select>
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Desde</label>
                            <el-date-picker v-model="form.date_start" :clearable="false" format="dd/MM/yyyy" type="date"
                                value-format="yyyy-MM-dd"></el-date-picker>
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Hasta</label>
                            <el-date-picker v-model="form.date_end" :clearable="false" format="dd/MM/yyyy" type="date"
                                value-format="yyyy-MM-dd"></el-date-picker>
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Asiento Contable</label>
                            <el-input v-model="form.asiento" :clearable="true"></el-input>
                        </div >
                        <div class="col-md-3">
                            <label class="control-label">Secuencial</label>
                            <el-input type="number" v-model="form.sequential" :clearable="true"></el-input>
                        </div >
                        <div class="col-md-3">
                            <label class="control-label">Valor pagado</label>
                            <el-input type="number" v-model="form.paid" :clearable="true"></el-input>
                        </div >
                        <div class="col-md-3">
                            <label class="control-label">Forma de pago</label>
                            <el-select v-model="form.payment_method" filterable>
                                <el-option value="Todos" label="Todos" />
                                <el-option v-for="client in payment_methods"
                                    :key="client.id"
                                    :label="client.description"
                                    :value="client.description">
                                </el-option>
                            </el-select>
                        </div >
                        <div class="col-md-3">
                            <label class="control-label">Agrupar por Multicobro?</label>
                            <el-switch v-model="form.multipay" :clearable="true"></el-switch>
                        </div >
                        <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top:29px">
                            <el-button class="submit" icon="el-icon-search" type="primary"
                                @click.prevent="getRecordsByFilter">Buscar
                            </el-button>

                            <el-button class="submit" type="success" @click.prevent="clickDownloadExcel"><i
                                    class="fa fa-file-excel"></i>
                                Exportar Excel
                            </el-button>

                            <el-button class="submit" type="success" @click.prevent="clickDownloadPDF"><i
                                    class="fa fa-file-pdf"></i>
                                Exportar PDF
                            </el-button>

                        </div>

                    </div>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table" v-if="this.records.length > 0">
                            <thead>
                                <tr>
                                    <th v-for="(rowH, indexH) in all_keys" :key="indexH">{{ rowH }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(rowArray, index) in records" :index="customIndex(index)" :key="index">
                                    <td v-for="(key, index1) in all_keys" :key="index1">
                                        {{rowArray[0][key]}}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="col-md-12">
                                    <td colspan="16" class="text-right">
                                        <strong>Valor Total Pagado Agrupado: {{ parseFloat(this.total_pagado).toFixed(2) }}</strong>
                                    </td>
                                </tr>
                                <tr class="col-md-12">
                                    <td colspan="16" class="text-right">
                                        <strong>Valor Total Pagado Global: {{ parseFloat(this.total_pagado_global).toFixed(2) }}</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <div v-else>
                            <el-alert title="No Data" description="No se encontraron registros para mostrar" type="error"
                                effect="dark" :closable="false" show-icon center />
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
    </div>
</template>

<script>

import moment from 'moment'
import queryString from 'query-string'

export default {
    data() {
        return {
            resource: 'reports/cobros_efectuados',
            form: {
                client_id: null,
                date_start: null,
                date_end: null,
                asiento: null,
                multipay:false,
                paid: null,
                sequential: null,
                payment_method: 'Todos',
            },
            loading_submit: false,
            clients: [],
            payment_methods: [],
            records: [],
            pagination: {},
            search: {},
            all_keys: [],
            pickerOptionsDates: {
                disabledDate: (time) => {
                    time = moment(time).format('YYYY-MM-DD')
                    return this.form.date_start > time
                }
            },
            total_pagado: null,
            total_pagado_global: null,
        }
    },
    created() {
        this.initForm()
        this.$eventHub.$on('reloadData', () => {
            this.getRecords()
        })
    },

    async mounted() {
        this.getTables();
        this.getRecords();
    },
    methods: {
        clickDownloadPDF() {
            let query = queryString.stringify({
                ...this.form
            });

            window.open(`/${this.resource}/pdf/?${query}`, '_blank');
        },

        clickDownloadExcel() {
            let query = queryString.stringify({
                ...this.form
            });

            window.open(`/${this.resource}/excel/?${query}`, '_blank');
        },

        initForm() {

            this.form = {
                client_id: 0,
                date_start: moment().format('YYYY-MM-DD'),
                date_end: moment().format('YYYY-MM-DD'),
                multipay: false,
                payment_method: 'Todos',
            }
            this.total_pagado = 0

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
            return this.$http.get(`/${this.resource}/datosSP?${this.getQueryParameters()}`).then((response) => {
                this.records = response.data.data
                let dataR = response.data
                delete dataR.data.data
                this.total_pagado = 0
                this.total_pagado_global = 0
                this.pagination = response.data.meta
                //this.pagination = response.data.meta
                this.pagination.per_page = parseInt(response.data.meta.per_page)
                if (this.records.length > 0) {
                    this.total_pagado_global = this.records[this.records.length - 1][0]
                    var keys = Object.keys(this.records[0]['0']);
                    this.all_keys = keys
                    this.records.forEach(row => {
                        //console.log('row[0] -',row[0])
                        if(row[0].Valor_pagado >= 0)
                        {
                            this.total_pagado += parseFloat(row[0].Valor_pagado)
                        }
                    });
                }
                this.loading_submit = false
            });
        },
        getTables()
        {
            return this.$http.get(`/${this.resource}/tables`)
                .then((response) => {
                    this.clients = response.data.persons
                    this.payment_methods = response.data.payment_methods
                });
        },
        getQueryParameters() {
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form
            })
        },
    },
}
</script>
