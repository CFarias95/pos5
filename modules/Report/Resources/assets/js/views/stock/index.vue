<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Stock por Almacen</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Stock por Almacen</h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <div class="col-md-12 col-lg-12 col-xl-12 ">

                    <div class="row mt-2">

                        <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top:29px">

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
                        <table class="table">
                            <thead>
                                <slot v-for="(key, value) in almacenList" :index="customIndex(value)" :row="key">
                                    <tr slot="heading" :key="value">
                                        <th v-for="(value1, name) in key" :index="customIndex(name)" :row="value1" class="" slot="heading" :key="name">
                                            {{ value1 }}
                                        </th>                                                           
                                    </tr>
                                </slot>                     
                            </thead>
                            <tbody>
                                <slot v-for="(row, index) in records" :index="customIndex(index)" :row="row">
                                    <tr v-for="(valor, dato) in row" :index="customIndex(dato)" :row="valor" class="" slot="heading" :key="dato">
                                        <td v-for="(obj, nombre) in valor" :index="customIndex(nombre)" :row="obj" class="" slot="heading" :key="nombre">
                                            {{ obj }}
                                        </td>
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


export default {
    data() {
        return {
            resource: 'reports/stock',
            form: {
            },
            loading_submit: false,
            records: [],
            pagination: {},
            search: {},
            almacenList: [],
            idList: [],
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
            window.open(`/${this.resource}/pdf`, '_blank');
        },

        clickDownloadExcel() {
            window.open(`/${this.resource}/excel`, '_blank');
        },

        initForm() {

            this.form = {
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
            return this.$http.get(`/${this.resource}/datosSP`).then((response) => {
                this.records = response.data.data
                console.log('resource', this.records) //Borrar antes del commit
                this.almacenList = this.records[this.records.length - 1]
                console.log('lista', this.almacenList) //Borrar antes del commit
                this.pagination = response.data.meta
                this.pagination.per_page = parseInt(response.data.meta.per_page)
                this.loading_submit = false
            });
        },
    },
}   
</script>
