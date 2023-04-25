<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Asientos Contables</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <a :href="`/${resource}/create`" class="btn btn-custom btn-sm  mt-2 mr-2"><i
                    class="fa fa-plus-circle"></i> Nuevo</a>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header bg-info py-1">
                <h4 class="my-0">Búsqueda</h4>
            </div>
            <div class="">
                <data-table :resource="resource">
                    <div slot="heading">
                    </div>
                    <div slot-scope="{ index, row }" :class="{ anulate_color : row.state_type_id == '11' }">

                          <div class="card mb-0 mt-3">
                            <div class="card-body">
                            <div class="row">
                            <div class="col-md-6">
                                <b>Creado por: {{row.user}} </b>
                                <p class="m-0">Fecha de creación {{ row.created_at }} </p>
                            </div>
                            <div class="col-md-6 text-right my-auto">
                                <a    :href="`/${resource}/edit/${row.id}`" type="button"
                              data-toggle="tooltip" data-placement="top" title="Editar" class="btn waves-effect waves-light btn-xs btn-info" ><i class="fa fa-edit"></i></a>
                            <button type="button" data-toggle="tooltip" data-placement="top" title="Generar PDF" class="btn waves-effect waves-light btn-xs btn-warning" @click.prevent="clickOptionsPdf(row.id)"><i class="fa fa-file-pdf"></i> PDF</button>
                             <template v-if="typeUser === 'admin'">
                             <button type="button" data-toggle="tooltip" data-placement="top" title="Eliminar"  class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickDelete(row.id)"><i class="fa fa-trash"></i> Eliminar</button>
                             </template>
                        </div>
                        </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr class="table-active">
                                            <th  colspan="2" class="font-weight-bold text-center">Cuenta</th>
                                            <th width="13%" class="font-weight-bold text-center">Debe</th>
                                            <th width="13%" class="font-weight-bold text-center">Haber</th>
                                            <th width="15%" class="font-weight-bold text-center">Centro de Costo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                          <tr>
                                            <td colspan="2" class="text-center py-0">
                                                <p class="my-0" >-- {{ row.seat_date | toDate }} --</p>
                                                <p class="my-0">Asiento N°: {{row.filename}} -- {{row.type}} </p>
                                            </td>
                                            <td  class="text-center"></td>
                                            <td  class="text-center"></td>
                                            <td  class="text-center"></td>
                                            </tr>

                                          <tr v-for="(det, index) in row.detalles" :key="`detalle-${index}`">
                                            <td colspan="2">
                                                <p class="ml-5 my-0" v-if="det.haber > 0" >
                                                {{det.account_movement.code }}   {{det.account_movement.description}}
                                                </p>
                                                <p class="my-0" v-else>
                                                {{det.account_movement.code }}   {{det.account_movement.description}}
                                                </p>
                                            </td>

                                            <td  class="text-right">$ {{ det.debe | toDecimals }}  </td>
                                            <td  class="text-right">$ {{ det.haber | toDecimals }} </td>
                                            <td  class="text-center"> {{ det.seat_cost}} </td>
                                            </tr>
                                            
                                          <tr>
                                            <td>
                                                <p class="my-0" >
                                                <b>Comentario:</b>
                                                {{row.comment}}
                                                </p>
                                            </td>
                                            <td width="7%" class="font-weight-bold">
                                                
                                                <p class="my-0" >TOTAL: </p>
                                            </td>
                                            <td  class="text-right">$ {{subTotalDebe(row.detalles)| toDecimals }} </td>
                                            <td  class="text-right">$ {{subTotalHaber(row.detalles)| toDecimals }} </td>
                                            <td  class="text-center"></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                 </div>
                            </div>
                            </div>
                    </div>
                </data-table>
            </div>
  
              <account-options-pdf :showDialog.sync="showDialogOptionsPdf"
                                   :recordId="recordId"
                                   :showClose="true"></account-options-pdf>

        </div>
    </div>
</template>
<style scoped>
.anulate_color {
    color: red;
}
</style>
<script>

import DataTable from '../../../components/DataTableAccountEntries.vue'
import AccountOptionsPdf from './partials/options_pdf.vue'
import {deletable} from '../../../mixins/deletable'
import {mapActions, mapState} from "vuex";
export default {
    props: [
        'typeUser',
        'soapCompany',
        'generateOrderNoteFromQuotation',
    ],
    mixins: [
        deletable
    ],
    components: {
        DataTable,
        AccountOptionsPdf
    },
    computed: {
        ...mapState([
            'config',
        ]),

    },
    data() {
        return {
            resource: 'accounting-entries',
            recordId: null,
            showDialogOptionsPdf: false,
            state_types: [],
            columns: {
                total_exportation: {
                    title: 'T.Exportación',
                    visible: false
                },
                total_unaffected: {
                    title: 'T.Inafecto',
                    visible: false
                },
                total_exonerated: {
                    title: 'T.Exonerado',
                    visible: false
                },
                total_free: {
                    title: 'T.Gratuito',
                    visible: false
                },
                contract: {
                    title: 'Contrato',
                    visible: false
                },
                delivery_date: {
                    title: 'T.Entrega',
                    visible: false
                },
                referential_information: {
                    title: 'Inf.Referencial',
                    visible: false,
                },
                order_note: {
                    title: 'Pedidos',
                    visible: false,
                },
                exchange_rate_sale: {
                    title: 'Tipo de cambio',
                    visible: false
                },
            }
        }
    },
    async created() {
        
    },
    mounted() {
        this.loadConfiguration()
    },
    methods: {
        ...mapActions([
            'loadConfiguration',
        ]),

        clickEdit(id) {
            this.recordId = id
            this.showDialogFormEdit = true
        },


        clickOptionsPdf(recordId = null) {
            this.recordId = recordId
            this.showDialogOptionsPdf = true
        },
        subTotalDebe(items) {
            return items.reduce((acc, ele) => {
            return acc + ele.debe;
            }, 0)
        },
        subTotalHaber(items) {
            return items.reduce((acc, ele) => {
            return acc + ele.haber;
            }, 0)
        },

        clickDelete(id) {
                this.destroy(`/${this.resource}/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                )
            }


    }
}
</script>
