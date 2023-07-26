<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">Reporte de Ventas</h3>
            <div class="data-table-visible-columns" style="top: 10px;" hidden>
                <el-dropdown :hide-on-click="false">
                    <el-button type="primary">
                        Mostrar/Ocultar columnas<i class="el-icon-arrow-down el-icon--right"></i>
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item v-for="(column, index) in columns" :key="index">
                            <el-checkbox v-model="column.visible">{{ column.title }}</el-checkbox>
                        </el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </div>
        </div>
        <div class="card mb-0">
                <div class="card-body">
                    <data-table :applyCustomer="true" :resource="resource" :visibleColumns="columns">
                    </data-table>
                </div>
        </div>
        <document-options :showDialog.sync="showDialogOptions"
                          :recordId="recordId"
                          :showClose="true"
                          :configuration="configuration"
        ></document-options>
        <product-sale :records="recordsItems" :showDialog.sync="showDialogProducts">

        </product-sale>
    </div>
</template>

<script>
    import DataTable from '../../components/DataTableReporteVentas.vue'
    import DocumentOptions from '../../../../../../../resources/js/views/tenant/documents/partials/options'
    import ProductSale from './partials/product_sale.vue'

    export default {
        props: ['configuration'],
        components: {DataTable,DocumentOptions, ProductSale},
        data() {
            return {
                showDialogOptions: false,
                recordId: null,
                resource: 'reports/reporte_ventas',
                form: {},
                columns: {
                    guides: {
                        title: 'Guias',
                        visible: false
                    },
                    options: {
                        title: 'Opciones',
                        visible: false
                    },
                    total_isc: {
                        title: 'Total ISC',
                        visible: false
                    },
                    total_charge: {
                        title: 'Total Cargos',
                        visible: false
                    },
                },
                showDialogProducts: false,
                recordsItems:[]

            }
        },
        async created() {
        },
        methods: {
            clickOptions(recordId = null) {
                this.recordId = recordId
                this.showDialogOptions = true
            },
            clickViewProducts(items = []) {
                this.recordsItems = items;
                this.showDialogProducts = true;
            }

        }
    }

</script>
