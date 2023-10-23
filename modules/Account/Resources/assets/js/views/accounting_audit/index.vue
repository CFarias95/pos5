<template>
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="my-0">Lista de asientos contables AUDITORIA</h3>
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
                        :class="{'text-success border-left border-success': (row.audited > 0),  }">
                        <td>{{ index }}</td>
                        <td>{{ row.reference }}</td>
                        <td>{{ row.value }}</td>
                        <td>{{ row.date }}</td>
                        <td>{{ row.ctaDebe }}</td>
                        <td>{{ row.ctaHaber }}</td>
                        <td>{{ row.comment }}</td>
                        <td>
                            <button v-if="row.audited == 0 " type="button" class="btn waves-effect waves-light btn-xs btn-primary"
                                @click.prevent="clickAudit(row.id)">Auditar</button>
                        </td>
                    </tr>
                </data-table>
            </div>
        </div>
    </div>
</template>

<script>
import DataTable from '@components/DataTableAudit.vue'
export default {
    components: { DataTable },
    data() {
        return {
            showDialog: false,
            resource: 'accounting_audit',
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
        }
    }
}
</script>
