<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Importaciones</span></li>
                <li><span class="text-muted">Carpeta</span>
                </li>
            </ol>
            <div class="right-wrapper pull-right"
                 v-if="typeUser != 'integrator'">
                 <button class="btn btn-custom btn-sm  mt-2 mr-2" type="button" @click.prevent="clickCreate()"><i
                    class="fa fa-plus-circle"></i> Nuevo
                </button>
            </div>
        </div>
        <div class="card mb-0">

            <div class="card-body ">
                <data-table :resource="resource">
                    <tr slot="heading">

                        <th>#</th>
                        <th class="text-center" style="min-width: 95px;">Importacion</th>
                        <th class="text-center" style="min-width: 95px;">Transporte</th>
                        <th class="text-center" style="min-width: 95px;">Embarque</th>
                        <th class="text-center" style="min-width: 95px;">Llegada</th>
                        <th class="text-center" style="min-width: 95px;">Estado</th>
                        <th class="text-center"></th>
                        <th class="text-center"></th>

                    </tr>
                    <tr slot-scope="{ index, row }"
                        :class="{'text-success': (row.estado === 'Liquidada'),
                            'text-warning': (row.estado === 'Liberada'),
                            'border-light': (row.estado === 'Registrada'),
                            'border-left border-info': (row.estado === 'Registrada'),
                            'border-left border-success': (row.estado === 'Liquidada'),
                            'border-left border-secondary': (row.estado === '07'),
                            'border-left border-dark': (row.estado === '09'),
                            'border-left border-danger': (row.estado === 'Liquidada')}">
                        <td>{{ index }}</td>
                        <td class="text-center" > {{ row.numeroImportacion }}</td>
                        <td class="text-center" > {{ row.tipoTransporte }}</td>
                        <td class="text-center">{{ row.fechaEmbarque }}</td>
                        <td class="text-center">{{ row.fechaLlegada }}</td>
                        <td class="text-center" >
                            <span
                                  class="badge bg-secondary text-white"
                                  :class="{'bg-success': (row.estado === 'Liquidada'), 'bg-warning': (row.estado === 'Liberada'), 'bg-secondary': (row.estado === 'Registrada')}">
                                {{ row.estado }}
                            </span>
                        </td>
                        <td class="text-right">

                            <button class="btn btn-success btn-sm" type="button" @click.prevent="clickGenerteReport(row.id)">
                                <i class="fas fa-table"></i> Reporte
                            </button>
                            <button class="btn btn-info btn-sm" type="button" @click.prevent="clickGenerteiIsd(row.id)">
                                <i class="fas fa-file-alt"></i> Asiento ISD
                            </button>
                            <button class="btn btn-warning btn-sm" type="button" @click.prevent="clickGenerteComu(row.id)">
                                <i class="fas fa-file-alt"></i> Asiento Comunicaciones
                            </button>

                        </td>
                        <td class="text-right"
                        v-if="row.estado != 'Liquidada'" >
                            <div class="dropdown">
                                <button class="btn btn-default btn-sm" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" >
                                    <div >
                                        <button class="dropdown-item"
                                                @click.prevent="clickCreate(row.id)">Editar
                                        </button>
                                    </div>

                                </div>
                            </div>

                        </td>
                    </tr>
                </data-table>
            </div>
            <tenant-imports-generate
                          :recordId="recordId"
                          :showDialog.sync="showDialog"
                          ></tenant-imports-generate>

        </div>
    </div>
</template>

<script>

import DocumentsVoided from '../documents/partials/voided.vue'
import DocumentOptions from '../documents/partials/options.vue'
import DocumentPayments from '../documents/partials/payments.vue'
//import DocumentImportSecond from './partials/import_second'
//import DocumentImportExcel from './partials/ImportExcel'
import DataTable from '../../../components/DataTableImports.vue'
import ItemsImport from '../documents/import.vue'
import {deletable} from '../../../mixins/deletable'
import DocumentConstancyDetraction from '../documents/partials/constancy_detraction.vue'
import ReportPayment from '../documents/partials/report_payment.vue'
import ReportPaymentComplete from '../documents/partials/report_payment_complete.vue'
import DocumentValidate from '../documents/partials/validate.vue';
import MassiveValidateCpe from '../../../../../modules/ApiPeruDev/Resources/assets/js/components/MassiveValidateCPE';
import {mapActions, mapState} from "vuex/dist/vuex.mjs";
//import DocumentRetention from './partials/retention'


export default {
    mixins: [deletable],
    props: [
        'configuration',

    ],
    computed: {
        ...mapState([
            'config',
        ]),
    },
    components: {
        DocumentsVoided,
        ItemsImport,
        //DocumentImportSecond,
        DocumentOptions,
        DocumentPayments,
        DataTable,
        DocumentConstancyDetraction,
        ReportPayment,
        ReportPaymentComplete,
        DocumentValidate,
        MassiveValidateCpe,
        //DocumentImportExcel,
        //DocumentRetention
    },
    data() {
        return {
            showDialog: false,
            resource: 'imports',
            recordId: null,
            showDialogOptions: false,
            showDialogPayments: false,
            columns: {
            }
        }
    },
    created() {
        this.$store.commit('setConfiguration', this.configuration)
        this.loadConfiguration();
        //this.getColumnsToShow();

    },
    methods: {
        ...mapActions(['loadConfiguration']),

        getColumnsToShow(updated) {

            this.$http.post('/validate_columns', {
                columns: this.columns,
                report: 'document_index', // Nombre del reporte.
                updated: (updated !== undefined),
            })
                .then((response) => {
                    if (updated === undefined) {
                        let currentCols = response.data.columns;
                        if (currentCols !== undefined) {
                            this.columns = currentCols
                        }
                    }
                })
                .catch((error) => {
                    console.error(error)
                })
        },
        clickCreate(recordId = null) {
            this.recordId = recordId
            this.showDialog = true
        },
        clickVoided(recordId = null) {
            this.recordId = recordId
            this.showDialogVoided = true
        },
        clickDownload(download) {
            window.open(download, '_blank');
        },
        clickResend(document_id) {
            this.$http.get(`/${this.resource}/send/${document_id}`)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.data.message)
                })
        },
        clickSendOnline(document_id) {
            this.$http.get(`/${this.resource}/send_server/${document_id}/1`).then(response => {
                if (response.data.success) {
                    this.$message.success('Se envio satisfactoriamente el comprobante.');
                    this.$eventHub.$emit('reloadData');

                    this.clickCheckOnline(document_id);
                } else {
                    this.$message.error(response.data.message);
                }
            }).catch(error => {
                this.$message.error(error.response.data.message)
            });
        },
        clickGenerteReport(importId){
            //alert('SE generara el reporte de la importacion '+importId)
            //console.log('DATA: ',importId);
            //console.log(`/imports/liquidation-report/${importId}`);
            window.open(`/imports/liquidation-report/${importId}`, '_blank');
        },
        clickGenerteiIsd(importId){
        },

        clickGenerteComu(importId){

        },
        clickCheckOnline(document_id) {
            this.$http.get(`/${this.resource}/check_server/${document_id}`)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success('Consulta satisfactoria.')
                        this.$eventHub.$emit('reloadData')
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.data.message)
                })
        },
        clickCDetraction(recordId) {
            this.recordId = recordId
            this.showDialogCDetraction = true
        },
        clickOptions(recordId = null) {
            this.recordId = recordId
            this.showDialogOptions = true
        },
        clickReStore(document_id) {
            this.$http.get(`/${this.resource}/re_store/${document_id}`)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.data.message)
                })
        },
        tooltip(row, message = true) {
            if (message) {
                if (row.shipping_status) return row.shipping_status.message;

                if (row.sunat_shipping_status) return row.sunat_shipping_status.message;

                if (row.query_status) return row.query_status.message;
            }

            if ((row.shipping_status) || (row.sunat_shipping_status) || (row.query_status)) return true;

            return false;
        },
        clickPayment(recordId) {
            this.recordId = recordId;
            this.showDialogPayments = true;
        },
        clickChangeToRegisteredStatus(document_id) {
            this.$http.get(`/${this.resource}/change_to_registered_status/${document_id}`)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.data.message)
                })
        },
        clickImport() {
            this.showImportDialog = true
        },
        clickDownloadReportPagos() {
            this.showDialogReportPaymentComplete = true
        },
        clickImportSecond() {
            this.showImportSecondDialog = true
        },
        clickImportExcel() {
            this.showImportExcelDialog = true
        },
        clickDeleteDocument(document_id) {
            this.destroy(`/${this.resource}/delete_document/${document_id}`).then(() =>
                this.$eventHub.$emit('reloadData')
            )
        },
        clickReportPayments() {
            this.showDialogReportPayment = true
        },
        clickForceSendBySummary(id)
        {
            this.forceSendBySummary(`/${this.resource}/force-send-by-summary`, { id : id}).then(() =>
                this.$eventHub.$emit('reloadData')
            )
        },
        clickRetention(recordId) {
            this.recordId = recordId;
            this.showDialogRetention = true
        }
    }
}
</script>
