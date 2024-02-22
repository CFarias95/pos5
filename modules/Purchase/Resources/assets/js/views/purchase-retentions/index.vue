<template>
    <div class="card">
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Retenciones compras</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i
                        class="fa fa-plus-circle"></i> Nuevo</button>
            </div>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Lista de retenciones para compras</h3>
        </div>
        <div class="card-body">
            <div>
                <form autocomplete="off">
                    <div class="form-body">
                        <div class="row">
                            <div class="form-group col-md-4" :class="{ 'has-danger': errors.account_id }">
                                <label class="control-label">Cuenta movimiento</label>
                                <el-select v-model="search.account_id" filterable clearable @change="getData">
                                    <el-option v-for="option in ctas" :key="option.id" :label="option.name"
                                        :value="option.id"></el-option>
                                </el-select>
                            </div>
                            <!-- <div class="form-group col-md-4" :class="{ 'has-danger': errors.month }">
                                <label class="control-label">Mes a conciliar</label>
                                <el-date-picker v-model="search.month" type="month" value-format="yyyy-MM" @change="getData"
                                    format="MM/yyyy" :clearable="true"></el-date-picker>
                                <small class="form-control-feedback" v-if="errors.month" v-text="errors.month[0]"></small>
                            </div> -->
                        </div>
                    </div>

                </form>
            </div>
            <div class="table-responsive">
                <template>
                    <div>
                        <el-table :data="records" style="width: 100%" row-key="id" :row-class-name="tableRowClassName">
                            <el-table-column prop="id" label="Número" sortable />
                            <el-table-column prop="active" label="Estado" sortable />
                            <el-table-column prop="percentage" label="Porcentaje" sortable />
                            <el-table-column prop="description" label="Descripción" sortable />
                            <el-table-column prop="code" label="Código" sortable />
                            <el-table-column prop="type" label="Tipo" sortable />
                            <el-table-column prop="account" label="Cta. Contable" sortable />
                            <el-table-column label="Acciones">
                                <template slot-scope="scope" v-if="scope.row">
                                    <div>
                                        <el-button size="small" type="info"
                                            @click="clickCreate(scope.row.id)">Editar</el-button>
                                        <br><br>
                                        <!-- <el-button size="small" type="danger"
                                            @click="handleDelete(scope.row.id)">Eliminar</el-button> -->
                                    </div>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                    <div>
                        <el-pagination @current-change="getData()" layout="total, prev, pager, next"
                            :total="pagination.total" :current-page.sync="pagination.current_page"
                            :page-size="pagination.per_page">
                        </el-pagination>
                    </div>
                </template>

            </div>
        </div>
        <bank-reconciliation-form :showDialog.sync="showDialog" :recordId="recordId"></bank-reconciliation-form>
    </div>
</template>

<script>
import BankReconciliationForm from './form.vue'
import queryString from "query-string";
export default {
    components: { BankReconciliationForm },
    data() {
        return {
            showDialog: false,
            resource: 'purchase-retentions',
            recordId: null,
            record: {},
            records: [],
            pagination: {},
            limit:null,
            loading_submit: false,
            search: {},
            ctas: [],
        }
    },
    created() {
        this.initForm()

        this.$eventHub.$on('reloadData', () => {
            this.getData()
        })
        this.$http.get(`/${this.resource}/columns`).then(response => {
            this.ctas = response.data.ctas
        })

        this.getData()
    },
    methods: {
        initForm() {
            this.errors = {}
            this.search = {
                month: null,
                account_id: null,
            }
            this.ctas = []
        },
        getData() {

            this.search.page = this.pagination.current_page
            this.search.limit = this.limit

            this.$http.post(`/${this.resource}/records`, this.search)
                .then(response => {
                    this.records = response.data.data
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                })
        },
        clickConciliate(recordId) {
            this.$http.get(`/${this.resource}/reconciliate/${recordId}`)
                .then(response => {
                    console.log(response)
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                    } else {

                        this.$message.error(response.data.message);
                    }
                })
        },
        clickDownload(type) {

            let query = queryString.stringify({
                ...this.form
            });

            window.open(`/reports/retention/${type}/?${query}`, "_blank");

        },
        clickCreate(recordId = null) {
            this.recordId = recordId
            this.showDialog = true
        },
        tableRowClassName(row, rowIndex) {
            if (row.status = 'Cerrada') {
                return 'success-row'
            }
        }
    }
}
</script>
