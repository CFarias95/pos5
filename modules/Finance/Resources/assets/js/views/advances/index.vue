<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Anticipos</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button class="btn btn-custom btn-sm  mt-2 mr-2" type="button" @click.prevent="clickCreate()"><i
                    class="fa fa-plus-circle"></i> Nuevo
                </button>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <th class="text-center">Identificador</th>
                        <th class="text-center">Método</th>
                        <th class="text-center">Cliente</th>
                        <th class="text-center">Valor</th>
                        <th class="text-center">Notas</th>
                        <th class="text-center"></th>

                    </tr>
                    <tr slot-scope="{ index, row }" >
                        <td class="text-center">{{ row.id }}</td>
                        <td class="text-center">{{ row.method }}</td>
                        <td class="text-center">{{ row.cliente }}</td>
                        <td class="text-center">{{ row.valor }}</td>
                        <td class="text-center">{{ row.observation }}</td>
                        <td class="text-center">

                            <button type="button" style="min-width: 41px" class="btn waves-effect waves-light btn-xs btn-primary m-1__2"
                                    @click.prevent="clickCreate(row.id)">
                                    <i class="fas fa-edit"></i>
                            </button>

                        </td>

                    </tr>
                </data-table>
            </div>

            <tenant-finance-advances-generate
                :recordId="recordId"
                :showDialog.sync="showDialog"
            ></tenant-finance-advances-generate>
        </div>
    </div>

</template>

<script>

    import DataTable from '../../components/DataTableAdvance.vue'
    import {deletable} from '@mixins/deletable'
    import IncomePayments from './partials/payments.vue'

    export default {
        mixins: [deletable],
        components: {DataTable, IncomePayments},
        data() {
            return {
                showDialogVoided: false,
                resource: 'finances/advances',
                showDialogPayments: false,
                recordId: null,
                showDialogOptions: false,
                showDialog:false,
            }
        },
        created() {
        },
        methods: {
            clickCreate(recordId = null) {
                this.recordId = recordId
                this.showDialog = true
            },
            clickPrint(external_id){

                window.open(`/${this.resource}/print/${external_id}`, '_blank');

            },
            clickExpensePayment(recordId) {
                this.recordId = recordId;
                this.showDialogExpensePayments = true
            },
            clickVoided(recordId) {
                this.voided(`/${this.resource}/voided/${recordId}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                )
            },
            clickDownload(download) {
                window.open(download, '_blank');
            },
            clickOptions(recordId = null) {
                this.recordId = recordId
                this.showDialogOptions = true
            },
            clickPayment(recordId) {
                this.recordId = recordId;
                this.showDialogPayments = true;
            },
        }
    }
</script>
