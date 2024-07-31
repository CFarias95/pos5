<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>General</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Reporte DINARDAP</h3>
        </div>
        <div class="card mb-0 card-body">
            <div class="col-md-12 col-lg-12 col-xl-12">
                <div class="row mt-2">
                    <div class="row">
                        <div class="col-3">
                            <label>Fecha Inicio</label>
                            <el-date-picker v-model="form.inicio" :clearable="false" format="dd/MM/yyyy" type="date"
                                value-format="yyyy-MM-dd"></el-date-picker>
                        </div>
                        <div class="col-3">
                            <label>Feha Fin</label>
                            <el-date-picker v-model="form.fin" :clearable="false" format="dd/MM/yyyy" type="date"
                                value-format="yyyy-MM-dd" :picker-options="pickerOptionsDates"></el-date-picker>
                        </div>
                        <div class="col-3">
                            <label>Fecha corte</label>
                            <el-date-picker v-model="form.corte" :clearable="false" format="dd/MM/yyyy" type="date"
                                value-format="yyyy-MM-dd" :picker-options="pickerOptionsDates"></el-date-picker>
                        </div>
                        <div class="col-3">
                            <el-button class="submit" icon="el-icon-download" type="primary"
                                    @click.prevent="clickDownloadExcel">Generar
                            </el-button>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <div class="form-body el-dialog__body_custom">
                <div class="col-md-12 m-bottom">
                    <el-tabs v-model="activeName">
                        <!-- <el-tab-pane label="Imprimir A4" name="first">
                            <embed :src="form.print_a4" type="application/xml" width="100%" height="450px"/>
                        </el-tab-pane> -->
                    </el-tabs>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { now } from 'moment'
import queryString from 'query-string'
export default {
    data() {
        return {
            resource: 'reports/dinardap',
            activeName: 'first',
            form: { },
            loading_submit: false,
            records: [],
            data: [],
            pagination: {},
            search: {},
            warehouses: [],
            items: [],
            almacenList: [],
            brands: [],
            categories: [],
            pickerOptionsDates: {
            disabledDate: (time) => {
                time = moment(time).format("YYYY-MM-DD");
                return this.form.date_start > time;
            },},
        }
    },
    created() {
        this.initForm()
        this.$eventHub.$on('reloadData', () => {
            this.getRecords()
        })
    },

    async mounted() {
        //await this.getRecords();
    },
    methods: {

        clickDownloadExcel() {
            let query = queryString.stringify({
                ...this.form
            });

            window.open(`/${this.resource}/generate/?${query}`, '_blank');
        },

        initForm() {
            this.form = {
                inicio: moment().format("YYYY-MM-DD"),
                fin: moment().format("YYYY-MM-DD"),
                corte: moment().format("YYYY-MM-DD"),
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
            this.loading_submit = true
            return this.$http.post(`/${this.resource}/generate`,this.form).then((response) => {

                console.log('data', response)
                this.form.print_a4 = response.data
                this.loading_submit = false
                window.open(this.form.print_a4, '_blank');
            });
        },
        getQueryParameters() {
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form
            })
        },
        changeClearInput() {
            this.search.value = "";
            this.getRecords();
        },
        /*filtarData()
        {
            this.records.forEach((row) =>{
                row.forEach((obj) => {
                    //console.log('dato', obj.Nombreproducto)
                    if(this.search.value = obj.Nombreproducto)
                    {
                        console.log('dato', row)
                    }
                    else
                    {
                        console.log('No entra en if')
                    }
                })
            })
        }*/

    },
}
</script>
