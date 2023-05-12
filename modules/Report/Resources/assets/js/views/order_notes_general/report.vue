<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">Pedido de Venta VS Guia Despachada</h3>
        </div>
        <div class="card mb-0">
                <div class="card-body">
                    <data-table :resource="resource">
                        <tr slot="heading">
                            <th class="">Item</th>
                            <th class="">Id</th>
                            <th class="">Precio Venta</th>
                            <th class="">Pedido</th>
                            <th class="">Despachado</th>
                            <th class="">Diferencia</th>
                        </tr>
                        <tr slot-scope="{ index, row }">
                            <td>{{row.itemDescription}}</td>
                            <td>{{row.itemId}}</td>
                            <td>{{ row.unitPrice }}</td>
                            <td>{{row.saleQuantity}}</td>
                            <td>{{row.sendQuantity}}</td>
                            <td>{{row.dif}}</td>
                        </tr>
                    </data-table>
                </div>
        </div>

    </div>
</template>

<script>

    import DataTable from '../../components/DataTableOrderNotesReport.vue'

    export default {
        components: {DataTable},
        data() {
            return {
                resource: 'reports/order-notes-report',
                form: {},
                columns: {
                    customer: {
                        visible: false
                    },
                    user: {
                        visible: false
                    },

                }

            }
        },
        async created() {

            this.$eventHub.$on('changeFilterColumn', (type) => {
                this.changeVisibleColumn(type)
            })

        },
        methods: {

            changeVisibleColumn(type){

                switch (type) {
                    case 'person':
                        this.columns.user.visible = true
                        this.columns.customer.visible = false
                        break;

                    case 'seller':
                        this.columns.customer.visible = true
                        this.columns.user.visible = false
                        break;
                }

            }

        }
    }
</script>
