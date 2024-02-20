<template>
    <div class="card">
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

        <div class="card-header bg-info">
            <h3 class="my-0">Listado de {{ title }}</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <template>
                    <div>
                        <el-table :data="records" style="width: 100%; margin-bottom: 20px" row-key="id" border
                            default-expand-all>
                            <el-table-column prop="name" label="Nombre" sortable />
                            <el-table-column prop="code" label="Código" sortable />
                            <el-table-column prop="id" label="Id" sortable />
                            <el-table-column prop="date" label="Fecha Creado" sortable />
                            <el-table-column label="Acciones">
                                <template slot-scope="scope" v-if="scope.row">
                                    <el-button size="small" @click="handleEdit(scope.$index, scope.row.id)">Edit</el-button>
                                    <el-button size="small" type="danger"
                                        @click="handleDelete(scope.$index, scope.row.id)">Delete</el-button>
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
            <cost-form :showDialog.sync="showDialog" :recordId="recordId"></cost-form>
        </div>
    </div>
</template>

<script>
import DataTable from '@components/DataTableCostCenter.vue'
import CostForm from './form.vue'
export default {
    components: { DataTable, CostForm },
    data() {
        return {
            showDialog: false,
            resource: 'cost_centers',
            recordId: null,
            record: {},
            records: [],
            pagination: {},
            loading_submit: false,
            title: null,
        }
    },
    created() {
        this.title = 'Centros de Costo'
        this.$eventHub.$on('reloadData', () => {
            this.getData()
        })
        this.getData()
    },
    methods: {
        getData() {
            this.$http.get(`/${this.resource}/records`)
                .then(response => {
                    this.records = response.data.data
                    this.pagination = response.data;
                    this.pagination.per_page = parseInt(
                        response.data.per_page
                    );
                })
        },
        clickAudit(recordId) {
            this.$http.get(`/${this.resource}/audit/${recordId}`)
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
        getQueryParameters() {

            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.search
            });
        },
        clickCreate(recordId = null) {
            this.recordId = recordId
            this.showDialog = true
        },
        handleEdit(Index, row) {

            this.recordId = row
            this.showDialog = true
        },
        handleDelete(index, row) {

            this.$http.delete(`/${this.resource}/${row}`).then((response) => {
                if (response.data.success == true) {
                    this.$message.success(response.data.message)
                }else{
                    this.$message.error(response.data.message)
                }
                this.getData()
            }
            )
        }
    }
}
</script>
<style>
.el-el-table-v2__header-row .custom-header-cell {
  border-right: 1px solid var(--el-border-color);
}

.el-el-table-v2__header-row .custom-header-cell:last-child {
  border-right: none;
}

.el-primary-color {
  background-color: var(--el-color-primary);
  color: var(--el-color-white);
  font-size: 14px;
  font-weight: bold;
}

.el-primary-color .custom-header-cell {
  padding: 0 4px;
}
</style>
