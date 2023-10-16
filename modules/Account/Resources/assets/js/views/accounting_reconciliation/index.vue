<template>
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="my-0">Clientes</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Referencia</th>
                        <th>Valor</th>
                        <th>Fecha</th>
                        <th>Comentario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in records" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ row.reference }}</td>
                        <td>{{ row.value }}</td>
                        <td>{{ row.date }}</td>
                        <td>{{ row.comment }}</td>
                        <td>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-primary"  @click.prevent="clickConciliate(row.id)"> Puntear</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

    </div>
</template>

<script>

    export default {
        components: {AccountingForm},
        data() {
            return {
                showDialog: false,
                resource: 'accounting_reconciliation',
                recordId: null,
                record: {},
                records: [],
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
                    })
            },
            clickConciliate(recordId) {

            }
        }
    }
</script>
