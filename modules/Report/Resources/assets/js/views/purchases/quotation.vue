<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">Orden de compras Vs Compra</h3>
        </div>
        <div class="card mb-0">
                <div class="card-body">
                    <data-table :resource="resource"  :applyCustomer="true" :colspanFootPurchase="9" :applyConversionToPen="applyConversionToPen">
                        <tr slot="heading">
                            <th class="">Item</th>
                            <th class="">Id</th>
                            <th class="">Precio Compra</th>
                            <th class="">Comprado</th>
                            <th class="">Solicitado</th>
                            <th class="">Diferencia</th>
                        </tr>
                        <tr slot-scope="{ index, row }">
                            <td>{{row.itemDescription}}</td>
                            <td>{{row.itemId}}</td>
                            <td>{{ row.unitValue }}</td>
                            <td>{{row.purchaseQuantity}}</td>
                            <td>{{row.purchaseOrderQuantity}}</td>
                            <td>{{row.dif}}</td>
                        </tr>
                    </data-table>
                </div>
        </div>
    </div>
</template>

<script>

    import DataTable from '../../components/DataTableReportsOrders.vue'

    export default {
        components: {DataTable},
        props:[
            'applyConversionToPen',
        ],
        data() {
            return {
                resource: 'reports/purchases/quotations',
                form: {},

            }
        },
        async created() {
        },
        methods: {


        }
    }
</script>
