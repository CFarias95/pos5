<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Balance Comprobación</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Balance Comprobación</h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <div class="col-md-12 col-lg-12 col-xl-12 ">

                    <div class="row mt-2">
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
                            <label class="control-label">Cuenta Inicio</label>
                            <el-select v-model="form.icuenta" clearable>
                                <el-option v-for="cuenta in cuentas" :key="cuenta" :value="cuenta" :label="cuenta"></el-option>
                            </el-select>
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Cuenta Fin</label>
                            <el-select v-model="form.fcuenta" clearable>
                                <el-option v-for="cuenta in cuentas" :key="cuenta" :value="cuenta" :label="cuenta"></el-option>
                            </el-select>
                        </div>
                        <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top:29px">
                            <el-button class="submit" icon="el-icon-search" type="primary"
                                @click.prevent="getRecordsByFilter">Buscar
                            </el-button>

                            <el-button class="submit" type="success" @click.prevent="clickDownloadExcel"><i class="fa fa-file-excel"></i>
                                Exportar Excel
                            </el-button>

                            <el-button class="submit" type="success" @click.prevent="clickDownloadPDF"><i class="fa fa-file-pdf"></i>
                                Exportar PDF
                            </el-button>

                        </div>

                    </div>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr slot="heading">
                                    <th class="">Cuenta</th>
                                    <th class="">Descripción Cuenta</th>
                                    <th class="">Saldo Inicial</th>
                                    <th class="">Debe cuenta</th>
                                    <th class="">Haber</th>
                                    <th class="">Saldo Deudor</th>
                                    <th class="">Saldo Acreedor</th>
                                    <th class="">Saldo Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                <slot v-for="(row, index) in records" :index="customIndex(index)" :row="row">
                                    <tr :key="index">
                                        <td>{{ row[0].Cuenta }}</td>
                                        <td>{{ row[0].Descripcion_cuenta }}</td>
                                        <td>{{ row[0].Saldo_inicial.toFixed(2) }}</td>
                                        <td>{{ row[0].Debe.toFixed(2) }}</td>
                                        <td>{{ row[0].Haber.toFixed(2) }}</td>
                                        <td>{{ row[0].Saldo_deudor.toFixed(2) }}</td>
                                        <td>{{ row[0].Saldo_acreedor.toFixed(2) }}</td>
                                        <td>{{ row[0].Saldo_final.toFixed(2) }}</td>
                                    </tr>
                                </slot>
                            </tbody>
                        </table>
                        <el-pagination :current-page.sync="pagination.current_page" :page-size="pagination.per_page"
                            :total="pagination.total" layout="total, prev, pager, next" @current-change="getRecords">
                        </el-pagination>
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
            resource: 'balance-comprobacion',
            form: {
                //s: null,
                date_start: null,
                date_end: null,
                icuenta: null,
                fcuenta: null,
            },
            loading_submit: false,
            records: [],
            pagination: {},
            search: {},
            pickerOptionsDates: {
                disabledDate: (time) => {
                    time = moment(time).format('YYYY-MM-DD')
                    return this.form.date_start > time
                }
            },
            cuentas: [],
        }
    },
    created() {
        this.initForm()
        this.$eventHub.$on('reloadData', () => {
            //this.getRecords()
            this.getCuentas()
        })
    },

    async mounted() {
        //await this.getRecords();
        await this.getCuentas();
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
                //s: 0.0,
                date_start: moment().format('YYYY-MM-DD'),
                date_end: moment().format('YYYY-MM-DD'),
                icuenta: null,
                fcuenta: null,
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
        getCuentas(){
            return this.$http.get(`/${this.resource}/cuentas`).then((response) => {
                this.cuentas = response.data
            });
        },
        getRecords() {
            return this.$http.get(`/${this.resource}/datosSP?${this.getQueryParameters()}`).then((response) => {
                this.records = response.data.data
                /*console.log('data', this.records);
                this.records.forEach((row) => {
                    this.cuentas.push(row[0].Cuenta)
                })   
                console.log('rows', this.cuentas)*/         
                this.pagination = response.data.meta
                this.pagination.per_page = parseInt(response.data.meta.per_page)
                this.loading_submit = false
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