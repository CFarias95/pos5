<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">Cuentas por Cobrar
                <el-tooltip class="item" effect="dark" content="Reporte cuentas por cobrar" placement="top-end">
                    <i class="fa fa-info-circle"></i>
                </el-tooltip>
            </h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot-scope="{ index, row }">
                        <td v-for="row2 in row">{{ row2 }}</td>
                    </tr>
                </data-table>
            </div>
        </div>

        
    </div>
</template>

<script>

import DataTable from '../../../components/DataTableReportPurchaseStatement.vue'

export default {
    components: { DataTable },
    data() {
        return {
            resource: 'reports/tocollect',
            form: {},
        }
    },
    async created() {
    },
    methods: {


    }
}
</script>
