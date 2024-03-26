<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Stock Fecha por Lote/Serie</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Stock Fecha por Lote/Serie</h3>
        </div>
        <div class="card mb-0 card-body">
            <div class="col-md-12 col-lg-12 col-xl-12">
                <div class="row mt-2">
                    <div class="row">
                        <div class="col-12">
                            <h2>Filtrar por:</h2>
                        </div>
                        <div class="col-md-6">
                            <label class="control-label">Fecha del</label>
                            <el-date-picker
                                v-model="form.date"
                                :clearable="false"
                                format="dd/MM/yyyy"
                                type="date"
                                value-format="yyyy-MM-dd"
                            ></el-date-picker>
                        </div>
                    </div>
                </div>
                <br />
                <div class="row mt-2 d-flex justify-content-left">
                    <div class="">
                        <el-button :loading="loading_submit" class="submit" type="primary" @click.prevent="getRecordsByFilter">
                            <i class="el-icon-search"></i> Buscar
                        </el-button>
                    </div>
                    <div class="col-md-1">
                        <el-button class="submit" type="success" @click.prevent="clickDownloadExcel">
                            <i class="fa fa-file-excel"></i> Exportar Excel
                        </el-button>
                    </div>
                    <div class="col-md-1">
                        <el-button class="submit" type="danger" @click.prevent="clickDownloadPDF">
                            <i class="fa fa-file-pdf"></i> Exportar PDF
                        </el-button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered" v-if="this.records.length > 0">
                                <thead>
                                    <tr
                                        v-for="(key, value) in recordsList"
                                        :index="customIndex(value)"
                                        :row="key"
                                    >
                                        <th v-for="(value1, name) in key">
                                            <strong>{{ value1 }}</strong>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <slot
                                        v-for="(row, index) in records"
                                        :index="customIndex(index)"
                                        :row="row"
                                    >
                                        <tr
                                            v-for="valor in row"
                                            :row="valor"
                                            class=""
                                            slot="heading"
                                        >
                                            <td
                                                v-for="(obj, nombre) in valor"
                                                :index="customIndex(nombre)"
                                                :row="obj"
                                                :key="nombre"
                                            >
                                                {{ obj }}
                                            </td>
                                        </tr>
                                    </slot>
                                </tbody>
                            </table>
                            <div v-else>
                                <el-alert title="No Data" description="No se encontraron registros para mostrar" type="error" effect="dark" :closable="false" show-icon center />
                            </div>
                            <el-pagination
                                :current-page.sync="pagination.current_page"
                                :page-size="pagination.per_page"
                                :total="pagination.total"
                                layout="total, prev, pager, next"
                                @current-change="getRecords"
                            >
                            </el-pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import queryString from "query-string";
export default {
    data() {
        return {
            resource: "reports/stock_fecha",
            form: {
                date: null
            },
            loading_submit: false,
            records: [],
            data: [],
            pagination: {},
            search: {},
            warehouses: [],
            items: [],
            recordsList: [],
        };
    },
    created() {
        this.initForm();
        this.$eventHub.$on("reloadData", () => {
            this.getRecordsByFilter();
        });
    },

    async mounted() {
        await this.getRecordsByFilter();
    },
    methods: {
        clickDownloadPDF() {
            window.open(
                `/${this.resource}/pdf?${this.getQueryParameters()}`,
                "_blank"
            );
        },

        clickDownloadExcel() {
            window.open(
                `/${this.resource}/excel?${this.getQueryParameters()}`,
                "_blank"
            );
        },

        initForm() {
            this.form = {
                date: moment().format("YYYY-MM-DD")
            };
        },
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
        async getRecordsByFilter() {
            this.loading_submit = await true;
            await this.getRecords();
            this.loading_submit = await false;
        },
        getRecords() {
            return this.$http
                .get(`/${this.resource}/datosSP?${this.getQueryParameters()}`)
                .then(response => {
                    this.records = response.data.data;
                    //console.log('data', this.records)
                    this.recordsList = this.records[this.records.length - 1];
                    let len = this.records.length;
                    this.records.splice(len - 1, 1);
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                    this.loading_submit = false;
                });
        },
        getQueryParameters() {
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form
            });
        }
    }
};
</script>
