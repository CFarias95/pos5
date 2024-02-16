<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Stock por Almacén</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Stock por Almacén</h3>
        </div>
        <div class="card mb-0 card-body">
            <div class="col-md-12 col-lg-12 col-xl-12">
                <div class="row mt-2">
                    <div class="row">
                        <div class="col-12">
                            <h2>Filtrar por:</h2>
                        </div>
                        <div class="col-3">
                            <label>Almacén</label>
                            <el-select v-model="form.warehouse_id" @change="getRecords()" filterable>
                                <el-option value="0" label="Todos los almacenes" />
                                <el-option v-for="row in warehouses" :key="row.id" :label="row.name"
                                    :value="row.id"></el-option>
                            </el-select>
                        </div>
                        <div class="col-3">
                            <label> Producto</label>
                            <el-select v-model="form.item_id" @change="getRecords()" filterable>
                                <el-option value="0" label="Todos los Productos" />
                                <el-option v-for="row in items" :key="row.id" :label="row.name" :value="row.id"></el-option>
                            </el-select>
                        </div>
                        <div class="col-3">
                            <label> Marca</label>
                            <el-select v-model="form.brand_id" @change="getRecords()" filterable>
                                <el-option value="0" label="Todos las marcas" />
                                <el-option v-for="row in brands" :key="row.id" :label="row.name"
                                    :value="row.id"></el-option>
                            </el-select>
                        </div>
                        <div class="col-3">
                            <label> Categoría</label>
                            <el-cascader v-model="form.categorie_id" :options="categories" checkStrictly='true'
                                :show-all-levels="false" expandTrigger='hover' @change="getRecords"
                                change-on-select></el-cascader>
                        </div>
                        <div class="col-3">
                            <label>Linea</label>
                            <el-input v-model="form.linea" expandTrigger='hover' @change="getRecords" placeholder="Linea">
                            </el-input>
                        </div>
                    </div>

                    <br>
                    <br>
                    <div class="col-6">
                        <br>
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
            <br>
            <br>
            <div class="col-md-12 col-lg-12 col-xl-12">
                <div class="table-responsive">
                    <table  class="table table-bordered">
                        <thead>
                            <tr v-for="(key, value) in almacenList" :index="customIndex(value)" :row="key">
                                <th v-for="(value1, name) in key">
                                    <strong>{{ value1 }}</strong>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <slot v-for="(row, index) in records" :index="customIndex(index)" :row="row">
                                <tr v-for="valor in row" :row="valor" class="" slot="heading">
                                    <td v-for="(obj, nombre) in valor" :index="customIndex(nombre)" :row="obj"
                                        :key="nombre">
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
