<template>
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="my-0">Lista de asientos contables para puntear</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <th>#</th>
                        <th>Referencia</th>
                        <th>Valor</th>
                        <th>Fecha</th>
                        <th>Cta Debe</th>
                        <th>Cta Haber</th>
                        <th>Comentario</th>
                        <th>Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }" :key="index"
                        :class="{ 'border-left border-success': (row.reconciliated > 0), }">
                        <td>{{ index + 1 }}</td>
                        <td>{{ row.reference }}</td>
                        <td>{{ row.value }}</td>
                        <td>{{ row.date }}</td>
                        <td>{{ row.ctaDebe }}</td>
                        <td>{{ row.ctaHaber }}</td>
                        <td>{{ row.comment }}</td>
                        <td>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-primary"
                                @click.prevent="clickConciliate(row.id)"> Puntear</button>
                        </td>
                    </tr>
                </data-table>
            </div>
        </div>
    </div>
</template>

<script>
import DataTable from '@components/DataTable.vue'
export default {
    components: { DataTable },
    data() {
        return {
            showDialog: false,
            resource: 'accounting_reconciliation',
            recordId: null,
            record: {},
            records: [],
            pagination: {},
            loading_submit: false,
        }
    },
    created() {
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
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                })
        },
        clickConciliate(recordId) {

        }
    }
}
</script>
