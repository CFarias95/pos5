<template>
    <div>
        <div class="row ">
            <div class="col-md-12 col-lg-12 col-xl-12 ">

                <h1>Filtrar Por</h1>

                <div class="row" v-if="applyFilter">
                    <div class="col-lg-3 col-md-3">
                        <label>
                            Cuenta Contable:
                        </label>
                        <el-select v-model="search.cta" placeholder="Select" @change="changeClearInput" clearable filterable>
                            <el-option v-for="option in ctas" :key="option.id" :value="option.id"
                                :label="option.name"></el-option>
                        </el-select>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <label>
                            Visualizar
                        </label>
                        <el-select v-model="search.include" placeholder="Select" @change="changeClearInput" clearable>
                            <el-option value="2" label="Todos"></el-option>
                            <el-option value="1" label="Solo auditados"></el-option>
                            <el-option value="0" label="Solo no auditados"></el-option>
                        </el-select>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <label>
                            Fecha del asiento:
                        </label>
                        <el-date-picker v-model="search.date" type="date" placeholder="Buscar" value-format="yyyy-MM-dd"
                            @change="getRecords">
                        </el-date-picker>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <label>
                            Referencia
                        </label>
                        <el-tooltip class="box-item" effect="dark" content="Si la referencia pertenece a un anticipo colocar la letra A seguido de una ',' y la referencia" placement="top-end">
                            <el-icon><QuestionFilled /></el-icon>
                        </el-tooltip>
                        <el-input v-model="search.reference" @change="getRecords"></el-input>

                    </div>
                </div>
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

<script>
import queryString from "query-string";

export default {
    props: {
        resource: String,
        applyFilter: {
            type: Boolean,
            default: true,
            required: false
        },
    },
    data() {
        return {
            search: {
                cta: null,
                date: null,
            },
            columns: [],
            records: [],
            pagination: {},
            loading_submit: false,
            fromPharmacy: false,
            parentsList: [],
            ctas: [],
        };
    },
    created() {
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });

        this.$root.$refs.DataTable = this;
    },
    async mounted() {

        let column_resource = _.split(this.resource, "/");
        await this.$http
            .get(`/${_.head(column_resource)}/columns`)
            .then(response => {
                if (response) {
                    this.ctas = response.data.ctas;

                } else {

                }
            });

        await this.getRecords();
    },
    methods: {
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
        getRecords() {
            this.loading_submit = true;
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

            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.search
            });
        },
        changeClearInput() {

            this.getRecords();
        },
        getSearch() {
            return this.search;
        },
    }
};
</script>
