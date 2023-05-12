<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ title }}</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i class="fa fa-plus-circle"></i> Nuevo</button>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header bg-info">
                <h3 class="my-0">{{ title }}</h3>
            </div>
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <th>#</th>
                        <th>Nombre tarifa</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th class="text-center">Es Oferta?</th>
                        <th class="text-center" width="100px">Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <td>{{ index }}</td>
                        <td>{{ row.rate_name }}</td>
                        <td>{{ row.rate_start }}</td>
                        <td>{{ row.rate_end }}</td>
                        <td class="text-center">{{ row.rate_offer?'SI':'NO' }}</td>
                        <td class="text-right" style="display: flex;justify-content: start;">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickCreate(row.id)">Editar</button>
                             <template v-if="typeUser === 'admin'">
                            <div v-if="row.is_use===false">
                            <div v-if="row.id!==1">
                             <button type="button" class="btn waves-effect waves-light btn-xs btn-danger ml-2" @click.prevent="clickDelete(row.id)">Eliminar</button>
                            </div>
                            </div>
                             </template>
                        </td>
                    </tr>
                </data-table>
            </div>

            <person-types-form :showDialog.sync="showDialog"
                          :recordId="recordId" ></person-types-form>

        </div>
    </div>
</template>

<script>
    import PersonTypesForm from './form.vue'
    import DataTable from '../../../components/DataTable.vue'
    import {deletable} from '../../../mixins/deletable'
    export default {
        props:['typeUser'],
        mixins: [deletable],
        components: {PersonTypesForm, DataTable},
        data() {
            return {
                title: null,
                showDialog: false,
                resource: 'rates-lists',
                recordId: null,
            }
        },
        created() {
            this.title = 'Lista de Tarifas'
        },
        methods: {
            clickCreate(recordId = null) {
                this.recordId = recordId
                this.showDialog = true
            },
            clickDelete(id) {
                this.destroy(`/${this.resource}/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                )
            }
        }
    }
</script>
