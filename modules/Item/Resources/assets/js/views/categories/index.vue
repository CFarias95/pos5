<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ title }}</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i
                        class="fa fa-plus-circle"></i> Nuevo</button>

            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header bg-info">
                <h3 class="my-0">Listado de {{ title }}</h3>
            </div>
            <div class="card-body">
                <template>
                    <div>
                        <div class="row ">
                            <div class="col-md-12 col-lg-12 col-xl-12 ">
                                <div class="row" v-if="applyFilter">
                                    <div class="col-lg-4 col-md-4 col-sm-12 pb-2">
                                        <div class="d-flex">
                                            <div style="width:100px">
                                                Filtrar por:
                                            </div>
                                            <el-select v-model="search.column" placeholder="Select"
                                                @change="changeClearInput">
                                                <el-option v-for="(label, key) in columns" :key="key" :value="key"
                                                    :label="label"></el-option>
                                            </el-select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-4 col-sm-12 pb-2">
                                        <template v-if="search.column === 'date_of_issue' ||
                                            search.column === 'date_of_due' ||
                                            search.column === 'date_of_payment' ||
                                            search.column === 'delivery_date' ||
                                            search.column === 'created_at'
                                            ">
                                            <el-date-picker v-model="search.value" type="date" style="width: 100%;"
                                                placeholder="Buscar" value-format="yyyy-MM-dd" @change="getRecords">
                                            </el-date-picker>
                                        </template>
                                        <template v-else-if="search.column === 'parent_id'">
                                            <el-select v-model="search.value" style="width: 100%;"
                                                placeholder="Departamento" @change="getRecords" clearable>
                                                <el-option v-for="(item, index) of parentsList" :key="index"
                                                    :label="item.name" :value="item.id">
                                                </el-option>
                                            </el-select>
                                        </template>
                                        <template v-else>
                                            <el-input v-model="search.value" style="width: 100%;"
                                                placeholder="Ingrese un dato" @change="getRecords" clearable>
                                            </el-input>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table">
                                            <el-table :data="records" style="width: 100%; margin-bottom: 20px" row-key="id"
                                                border default-expand-all>
                                                <el-table-column prop="name" label="Nombre" sortable />
                                                <el-table-column prop="id" label="Id" sortable />
                                                <el-table-column prop="created_at" label="Fecha Creado" sortable />
                                                <el-table-column label="Acciones">
                                                    <template slot-scope="scope" v-if="scope.row">
                                                        <el-button size="small"
                                                            @click="clickCreate(scope.row.id)">Edit</el-button>
                                                        <el-button size="small" type="danger"
                                                            @click="clickDelete(scope.row.id)">Delete</el-button>
                                                    </template>
                                                </el-table-column>
                                            </el-table>
                                    </table>
                                    <div>
                                        <el-pagination @current-change="getRecords" layout="total, prev, pager, next"
                                            :total="pagination.total" :current-page.sync="pagination.current_page"
                                            :page-size="pagination.per_page">
                                        </el-pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <category-form :showDialog.sync="showDialog" :recordId="recordId"></category-form>
        </div>
    </div>
</template>

<script>

import CategoryForm from './form.vue'
import queryString from "query-string";

export default {
    props: {
        productType: {
            type: String,
            required: false,
            default: ''
        },
        applyFilter: {
            type: Boolean,
            default: true,
            required: false
        },
    },
    components: { CategoryForm },
    data() {
        return {
            title: null,
            showDialog: false,
            resource: 'categories',
            recordId: null,
            search: {
                column: null,
                value: null,
            },
            columns: [],
            records: [],
            pagination: {},
            loading_submit: false,
            fromPharmacy: false,
            parentsList: [],
        }
    },
    created() {
        if (this.pharmacy !== undefined && this.pharmacy === true) {
            this.fromPharmacy = true;
        }
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
        this.title = 'Categorías'
    },
    async mounted() {
        let column_resource = _.split(this.resource, "/");
        await this.$http
            .get(`/${_.head(column_resource)}/columns`)
            .then(response => {
                if (response.data.columns) {
                    this.columns = response.data.columns;
                    this.search.column = _.head(Object.keys(this.columns));
                    this.parentsList = response.data.categories;
                } else {
                    this.columns = response.data;
                    this.search.column = _.head(Object.keys(this.columns));
                }
            });
        await this.getRecords();
    },
    methods: {
        clickCreate(recordId = null) {
            this.recordId = recordId
            this.showDialog = true
        },
        clickDelete(id) {
            this.$http
                .delete(`/${this.resource}/${id}`).then(() =>
                this.$message.error('Se elimino el registro correctamente'),
                this.$eventHub.$emit('reloadData')
            )
        },
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
        getRecords() {
            this.loading_submit = true;
            //console.log('url ', `/${this.resource}/records?${this.getQueryParameters()}`)
            return this.$http
                .get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then(response => {
                    this.records = response.data.data;
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                })
                .catch(error => { })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        getQueryParameters() {
            if (this.productType == 'ZZ') {
                this.search.type = 'ZZ';
            }
            if (this.productType == 'PRODUCTS') {
                // Debe listar solo productos
                this.search.type = this.productType;
            }
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                isPharmacy: this.fromPharmacy,
                ...this.search
            });
        },
        changeClearInput() {
            this.search.value = "";
            this.getRecords();
        },
        getSearch() {
            return this.search;
        },
    }
}
</script>
