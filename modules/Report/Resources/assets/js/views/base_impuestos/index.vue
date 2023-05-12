<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Compras Bases e Impuestos</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Reporte Compras Base e Impuestos</h3>
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
                                    <th class="">Tipo Doc. Interno</th>
                                    <th class="">Tipo Documento</th>
                                    <th class="">Código Tipo Doc. SRI</th>
                                    <th class="">Tipo Doc. SRI</th>
                                    <th class="">Serie Interna</th>
                                    <th class="">Número interno</th>
                                    <th class="">Secuencial</th>
                                    <th class="">Número Autorización</th>
                                    <th class="">Nombre Proveedor</th>
                                    <th class="">CI/RUC</th>
                                    <th class="">Tipo</th>
                                    <th class="">Fecha Documento</th>
                                    <th class="">Base IVA 12%</th>
                                    <th class="">Base IVA 0%</th>
                                    <th class="">Total IVA</th>
                                    <th class="">Base Imponible</th>
                                    <th class="">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <slot v-for="(row, index) in records" :index="customIndex(index)" :row="row">
                                    <tr :key="index">
                                        <td>{{ row[0].Tipodocinterno }}</td>
                                        <td>{{ row[0].Tipodocumento }}</td>
                                        <td>{{ row[0].Codtipodocsri }}</td>
                                        <td>{{ row[0].Tipodocsri }}</td>
                                        <td>{{ row[0].Serieinterna }}</td>
                                        <td>{{ row[0].Numerointerno }}</td>
                                        <td>{{ row[0].secuencial }}</td>
                                        <td>{{ row[0].Numautorizacion }}</td>
                                        <td>{{ row[0].Nombreproveedor }}</td>
                                        <td>{{ row[0].CIRUC }}</td>
                                        <td>{{ row[0].Tipo }}</td>
                                        <td>{{ row[0].fechadoducmento }}</td>
                                        <td>{{ row[0].Baseiva12 }}</td>
                                        <td>{{ row[0].Baseiva0 }}</td>
                                        <td>{{ row[0].Totaliva }}</td>
                                        <td>{{ row[0].Baseimponible }}</td>
                                        <td>{{ row[0].total }}</td>
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
            resource: 'reports/purchases/base_impuestos',
            form: {
                date_start: null,
                date_end: null,
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
            }
        }
    },
    created() {
        this.initForm()
        this.$eventHub.$on('reloadData', () => {
            this.getRecords()
        })
    },

    async mounted() {
        await this.getRecords();
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
                date_start: moment().format('YYYY-MM-DD'),
                date_end: moment().format('YYYY-MM-DD'),
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
            return this.$http.get(`/${this.resource}/datosSP?${this.getQueryParameters()}`).then((response) => {
                this.records = response.data.data
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