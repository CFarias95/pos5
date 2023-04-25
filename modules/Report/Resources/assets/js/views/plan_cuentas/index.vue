<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Asientos Contables</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <div class="card mb-0 pt-2 pt-md-0">
                <div class="card mb-0">
                    <div class="card-header bg-info py-1">
                        <h4 class="my-0">Imprimir en PDF</h4>
                    </div>
                </div>
            </div>
        </div>


        <div class="card-body">
            <data-table :resource="resource">
                <tr slot="heading">
                    <th class="">Código</th>
                    <th class="">Nombre</th>
                    <th class="">Saldo</th>
                </tr>
                <tr slot-scope="{ index, row }">
                    <td>{{ row[0].codigo }}</td>
                    <td>{{ row[0].nombre }}</td>
                    <td>${{ row[0].saldo }}</td>
                </tr>

            </data-table>
        </div>

    </div>
</template>
<style scoped>
.anulate_color {
    color: red;
}
</style>
<script>

import DataTable from '../../components/DataTablePlanCuentas.vue'

export default {
    components: {
        DataTable,
    },
    computed: {
    },
    data() {
        return {
            resource: 'reports/plan_cuentas',
            sp: [],
        }
    },
    async created() {

    },
    mounted() {
    },
    methods: {
    },

}
</script>