<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Reporte ATS</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Reporte ATS</h3>
        </div>
        <div class="card mb-0 card-body">
            <div class="col-md-12 col-lg-12 col-xl-12">
                <div class="row mt-2">
                    <div class="row">
                        <div class="col-4">
                            <label>Del</label>
                            <el-date-picker v-model="form.date_start" :clearable="false" format="dd/MM/yyyy" type="date"
                                value-format="yyyy-MM-dd"></el-date-picker>
                        </div>
                        <div class="col-4">
                            <label> Al</label>
                            <el-date-picker v-model="form.date_end" :clearable="false" format="dd/MM/yyyy" type="date"
                                value-format="yyyy-MM-dd"></el-date-picker>
                        </div>
                        <div class="col-4">
                            <label>Generar ATS</label>
                            <el-button class="submit" icon="el-icon-search" type="primary"
                                    @click.prevent="getRecords">Generar
                            </el-button>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <div class="col-md-12 col-lg-12 col-xl-12">
                <div class="form-body el-dialog__body_custom">
                    <el-tab-pane label="Imprimir A4" name="quarter">
                            <embed :src="form.print_a4" type="application/xml" width="100%" height="450px"/>
                    </el-tab-pane>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import queryString from 'query-string'
export default {
    data() {
        return {
            resource: 'reports/stock',
            form: {
                warehouse_id: '0',
                item_id: '0',
                brand_id: '0',
                categorie_id: 0,
                linea: 'NA',
            },
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
            window.open(`/${this.resource}/pdf?${this.getQueryParameters()}`, '_blank');
        },

        clickDownloadExcel() {
            window.open(`/${this.resource}/excel?${this.getQueryParameters()}`, '_blank');
        },

        initForm() {

            this.$http.get(`/${this.resource}/tables`).then((response) => {
                this.warehouses = response.data.warehouses
                this.items = response.data.items
                this.brands = response.data.brands
                this.categories = response.data.categories
            });

            this.form = {
                warehouse_id: '0',
                item_id: '0',
                categorie_id: 0,
                brand_id: '0',
                linea: 'NA',
            }
            this.search = {
                //value: null
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
                //console.log('data', this.records)
                this.almacenList = this.records[this.records.length - 1]
                let len = this.records.length
                this.records.splice(len - 1, 1)
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
