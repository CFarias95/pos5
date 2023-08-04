<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ title }}</span></li>
            </ol>
            <div v-if="typeUser == 'admin'" class="right-wrapper pull-right">
                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate(null)"><i class="fa fa-plus-circle"></i>Nuevo</button>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header bg-info">
                <h3 class="my-0">Listado de {{ title }}</h3>
            </div>
            <div class="card-body">
                <data-table :resource="resource" ref="datatable">
                    <tr slot="heading">
                        <th>#</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th class="text-right">Cuenta contable</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }" :key="index">
                        <td>{{ index }}</td>
                        <td>{{ row.name }}</td>
                        <td>{{ row.type }}</td>
                        <td class="text-right">{{ row.ctaCountant }}</td>
                        <td class="text-right">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickCreate(row.id)">Editar</button>
                            <button v-if="typeUser == 'admin'" type="button" class="btn waves-effect waves-light btn-xs btn-warning"
                                    @click.prevent="clickRemove(row.id)">Remover</button>
                        </td>
                    </tr>
                </data-table>
            </div>

            <tenant-inventory-transactions-form
                            :showDialog.sync="showDialog"
                            :recordId="idTransaction"
                                ></tenant-inventory-transactions-form>

        </div>
    </div>
</template>

<script>

    import TransactionsForm from './form.vue'
    import DataTable from '../../components/DataTable.vue'


    export default {
        props: ['type', 'typeUser'],
        components: {DataTable, TransactionsForm},
        data() {
            return {
                showHideModalMoveGlobal: false,
                selectedItems: [],
                title: null,
                showDialog: false,
                showDialogMove: false,
                showDialogRemove: false,
                showDialogOutput: false,
                resource: 'inventory/transactions',
                recordId: null,
                typeTransaction:null,
                showDialogMovementReport:false,
                showDialogStock: false,
                showHideStockMoveGlobal: false,
                showImportDialog: false,
                showDialogStockReport:false,
                idTransaction:null,
            }
        },
        created() {
            this.title = 'Motivos de Ajustes'
        },
        methods: {
            clickCreate(id) {
                this.recordId = id
                this.showDialog = true
            },
            clickRemove(recordId) {
                this.recordId = recordId
                this.showDialogRemove = true
            },
            async onOpenModalStockGlobal() {
                const itemsSelecteds = await this.$refs.datatable.records.filter(p => p.selected);
                if (itemsSelecteds.length > 0) {
                    this.selectedItems = itemsSelecteds;
                    this.showHideStockMoveGlobal = true;
                } else {
                    this.$message({
                        message: 'Selecciona uno o más productos.',
                        type: 'warning'
                    });
                }
            },
            clickImport(){
                this.showImportDialog = true
            },
            clickReportStock(){
                this.showDialogStockReport = true
            },

        }
    }
</script>
