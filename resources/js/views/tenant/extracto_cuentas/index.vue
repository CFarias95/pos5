<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Extracto de Cuentas</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Extracto de Cuentas</h3>
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
                            <label class="control-label"># Cuenta</label>
                            <el-select v-model="form.cuenta" clearable>
                                <el-option v-for="codigo in cuentas" :key="codigo" :value="codigo" :label="codigo"></el-option>
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
                                    <th class="">Asiento</th>
                                    <th class="">Linea</th>
                                    <th class="">Cuenta</th>
                                    <th class="">Descripción cuenta</th>
                                    <th class="">Comentario</th>
                                    <th class="">Fecha</th>
                                    <th class="">Serie</th>
                                    <th class="">Número</th>
                                    <th class="">Debe</th>
                                    <th class="">Haber</th>
                                    <th class="">Saldo</th>
                                    <th class="">C_C</th>
                                    <th class="">Id Persona</th>
                                    <th class="">Nombre Persona</th>
                                </tr>
                            </thead>
                            <tbody>
                                <slot v-for="(row, index) in records" :index="customIndex(index)" :row="row">
                                    <tr :key="index">
                                        <td>{{ row[0].Asiento }}</td>
                                        <td>{{ row[0].Linea }}</td>
                                        <td>{{ row[0].Cuenta }}</td>
                                        <td>{{ row[0].Descripcion_cuenta }}</td>
                                        <td>{{ row[0].Comentario }}</td>
                                        <td>{{ row[0].Fecha }}</td>
                                        <td>{{ row[0].Serie }}</td>
                                        <td>{{ row[0].Numero }}</td>
                                        <td>{{ row[0].Debe }}</td>
                                        <td>{{ row[0].Haber }}</td>
                                        <td>{{ row[0].Saldo }}</td>
                                        <td>{{ row[0].C_C }}</td>
                                        <td>{{ row[0].Id_persona }}</td>
                                        <td>{{ row[0].Nombre_persona }}</td>
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
            resource: 'extracto-cuentas',
            form: {
                //s: null,
                date_start: null,
                date_end: null,
                cuenta: null
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
                cuenta: null,
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
                //console.log('data', this.records);
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