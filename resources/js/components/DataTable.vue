<template>
    <div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12">
                <div class="row" v-if="applyFilter">
                    <div class="col-lg-4 col-md-4 col-sm-12 pb-2">
                        <div class="d-flex">
                            <div style="width: 100px">Filtrar por:</div>
                            <el-select v-model="search.column" placeholder="Select" @change="changeClearInput">
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
                            <el-date-picker v-model="search.value" type="date" style="width: 100%" placeholder="Buscar"
                                value-format="yyyy-MM-dd" @change="getRecords">
                            </el-date-picker>
                        </template>
                        <template v-else-if="search.column === 'date_real'">
                            <el-date-picker v-model="search.value" type="date" style="width: 100%" placeholder="Buscar"
                                value-format="ddMMyyyy" @change="getRecords">
                            </el-date-picker>
                        </template>
                        <template v-else-if="search.column === 'parent_id'">
                            <el-select v-model="search.value" style="width: 100%" placeholder="Departamento"
                                @change="getRecords">
                                <el-option v-for="(item, index) of parentsList" :key="index" :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                        </template>
                        <template v-else-if="search.column === 'user_id'">
                            <el-select v-model="search.value" style="width: 100%" placeholder="Cliente" @change="getRecords"
                                filterable clearable>
                                <el-option v-for="(item, index) of customers" :key="index"
                                    :label="item.type + ' - ' + item.name" :value="item.id">
                                </el-option>
                            </el-select>
                        </template>
                        <template v-else-if="search.column === 'user_id_2'">
                            <el-select v-model="search.value" style="width: 100%" placeholder="Proveedor"
                                @change="getRecords" filterable clearable>
                                <el-option v-for="(item, index) of suppliers" :key="index"
                                    :label="item.type + ' - ' + item.name" :value="item.id">
                                </el-option>
                            </el-select>
                        </template>
                        <template v-else-if="search.column === 'category_id_array'">
                            <el-select v-model="search.value" style="width: 100%" placeholder="Categoria"
                                @change="getRecords" filterable clearable>
                                <el-option v-for="(category, index) of category_list" :key="index" :label="category.name"
                                    :value="category.id">
                                </el-option>
                            </el-select>
                        </template>
                        <template v-else>
                            <el-input v-if="search.column != 'facturado'" placeholder="Buscar" v-model="search.value"
                                style="width: 100%" prefix-icon="el-icon-search" @input="getRecords">
                            </el-input>
                            <el-select v-if="search.column == 'finalized'" v-model="search.value" @change="getRecords">
                                <el-option value="FP">Facturado Con Pendiente</el-option>
                                <el-option value="FF">Facturado Finalizado</el-option>
                            </el-select>
                        </template>
                    </div>
                    <div class="d-flex" v-if="this.resource == 'items'">
                        <div style="width: 100px">Marca:</div>
                        <el-select placeholder="Marca" clearable filterable v-model="search.marca" @change="getRecords">
                            <el-option v-for="brand in brands" :key="brand.id" :label="brand.name" :value="brand.name">
                            </el-option>
                        </el-select>
                    </div>

                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 pb-2">
                    <template v-if="resource == 'cnp'">
                        <div class="d-flex">
                            <label>Incluir Liquidadas</label>
                            <el-switch v-model="search.included" class="ml-2" @change="getRecords"
                                style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949" />
                        </div>
                    </template>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 pb-2">
                    <template v-if="resource == 'retentions'">
                        <div class="d-flex">
                            <div style="width: 100px">Persona</div>

                            <el-select v-model="search.person_id" placeholder="Select" @change="changeClearInput" filterable
                                clearable>
                                <el-option v-for="(label, key) in persons" :key="label.id" :value="label.id"
                                    :label="label.name"></el-option>
                            </el-select>
                        </div>
                    </template>
                </div>
            </div>
            <el-button class="submit" type="success" @click.prevent="clickDownloadExcel"
                v-if="this.resource === 'inventory'"><i class="fa fa-file-excel"></i>
                Exportar Excel
            </el-button>
            <template class="col-lg-12 col-md-12 col-sm-12" v-if="this.resource == 'purchases'">
                <div class="col-lg-3 col-md-3 col-sm-3">
                    <label>
                        Secuencial:
                    </label>
                    <el-input v-model="search.sequential" @change="getRecords"></el-input>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3">
                    <label>
                        Documento interno:
                    </label>
                    <el-select clearable filterable v-model="search.intern" @change="getRecords">
                        <el-option v-for="document in documents" :key="document.id" :label="document.name" :value="document.id">
                        </el-option>
                    </el-select>
                </div>

            </template>
        </div>

        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <slot name="heading"></slot>
                    </thead>
                    <tbody>
                        <slot v-for="(row, index) in records" :row="row" :index="customIndex(index)"></slot>
                    </tbody>
                </table>
                <div>
                    <el-pagination @current-change="getRecords" layout="total, prev, pager, next" :total="pagination.total"
                        :current-page.sync="pagination.current_page" :page-size="pagination.per_page">
                    </el-pagination>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import queryString from "query-string";

export default {
    props: {
        productType: {
            type: String,
            required: false,
            default: "",
        },
        resource: String,
        applyFilter: {
            type: Boolean,
            default: true,
            required: false,
        },
        pharmacy: Boolean,
    },
    data() {
        return {
            search: {
                column: null,
                value: null,
                included: false,
                marca: null,
            },
            columns: [],
            records: [],
            pagination: {},
            loading_submit: false,
            fromPharmacy: false,
            parentsList: [],
            suppliers: [],
            category_list: [],
            customers: [],
            brands: [],
            documents: [],
        };
    },
    created() {
        if (this.pharmacy !== undefined && this.pharmacy === true) {
            this.fromPharmacy = true;
        }
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
        this.$root.$refs.DataTable = this;
    },
    async mounted() {
        let column_resource = _.split(this.resource, "/");
        //console.log("column_resource", column_resource);
        //console.log("this.resource", this.resource);
        await this.$http.get(`/${_.head(column_resource)}/columns`).then((response) => {
            if (response.data.columns) {
                //console.log('Entro if');
                this.columns = response.data.columns;
                this.search.column = _.head(Object.keys(this.columns));
                this.parentsList = response.data.categories;
                this.category_list = response.data.categories_list;
                this.documents = response.data.documents;
                //console.log('categories', this.category_list);
                (this.suppliers = response.data.suppliers),
                    (this.customers = response.data.customers),
                    (this.persons = response.data.persons);
            } else {
                this.columns = response.data;
                //console.log('columns', this.columns);
                this.search.column = _.head(Object.keys(this.columns));
            }
        });
        if (this.resource === "items") {
            await this.getBrands();
        }

        await this.getRecords();
    },
    methods: {
        customIndex(index) {
            return this.pagination.per_page * (this.pagination.current_page - 1) + index + 1;
        },
        clickDownloadExcel() {
            let query = this.getQueryParameters();
            //let data = getRecords();
            //console.log('data', data);

            window.open(`/${this.resource}/excel?${query}`, "_blank");
        },
        getBrands() {
            return this.$http.get(`/${this.resource}/brands`).then((response) => {
                //console.log("brands", response);
                this.brands = response.data.brands;
            });
        },
        getRecords() {
            this.loading_submit = true;
            //console.log('url ', `/${this.resource}/records?${this.getQueryParameters()}`)
            return this.$http
                .get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then((response) => {
                    this.records = response.data.data;
                    console.log("records", this.records);
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(response.data.meta.per_page);
                })
                .catch((error) => { })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        getQueryParameters() {
            if (this.productType == "ZZ") {
                this.search.type = "ZZ";
            }
            if (this.productType == "PRODUCTS") {
                // Debe listar solo productos
                this.search.type = this.productType;
            }
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                isPharmacy: this.fromPharmacy,
                ...this.search,
            });
        },
        changeClearInput() {
            this.search.value = "";
            this.getRecords();
        },
        getSearch() {
            return this.search;
        },
    },
};
</script>
