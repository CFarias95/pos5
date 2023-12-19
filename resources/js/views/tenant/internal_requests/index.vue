<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Documentos Avanzados</span></li>
                <li><span class="text-muted">Pedidos Internos</span>
                </li>
            </ol>
            <div class="right-wrapper pull-right">

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
                        <th class="text-center" style="min-width: 95px;">Asunto</th>
                        <th class="text-center" style="min-width: 95px;">Solicitante</th>
                        <th class="text-center" style="min-width: 95px;">Solicitado a</th>
                        <th class="text-center" style="min-width: 95px;">Detalles</th>
                        <th class="text-center" style="min-width: 95px;">Estado</th>
                        <th class="text-center" style="min-width: 95px;">Etapa</th>
                        <th class="text-center" style="min-width: 95px;">Acciones</th>

                    </tr>
                    <tr slot-scope="{ index, row }">
                        <td>{{ 'IR-' + row.id }}</td>
                        <td class="text-center"> {{ row.title }}</td>
                        <td class="text-center"> {{ row.user }}</td>
                        <td class="text-center"> {{ row.manage }}</td>
                        <td class="text-center"> {{ row.text }}</td>
                        <td v-if="row.is_manager == false" class="text-center">
                            <span class="badge bg-secondary text-white"
                                :class="{ 'bg-success': (row.status === 'Acepted'), 'bg-warning': (row.estado === 'Rejected'), 'bg-secondary': (row.estado === 'Created') }">
                                {{ row.status }}
                            </span>
                        </td>
                        <td v-if="row.is_manager" class="text-center">
                            <el-select v-model="row.status" @change="changeManager(row.id, row.status)">
                                <el-option value="Created" label="Creada"></el-option>
                                <el-option value="Acepted" label="Aceptada"></el-option>
                                <el-option value="Rejected" label="Rechazada"></el-option>
                                <el-option value="Partially" label="Parcial"></el-option>
                            </el-select>
                        </td>
                        <td class="text-center"> {{ row.phase }}</td>
                        <td class="text-right">

                            <button v-if="!row.aproved && row.is_user" class="btn btn-custom btn-sm  mt-2 mr-2"
                                type="button" @click.prevent="clickCreate(row.id)">Editar
                            </button>

                            <button v-if="row.status == 'Created'" class="btn btn-danger btn-sm  mt-2 mr-2" type="button"
                                @click.prevent="clickDelete(row.id)">Eliminar
                            </button>

                            <button v-if="row.upload_filename" class="btn btn-success btn-sm  mt-2 mr-2" type="button"
                                @click.prevent="clickDownload(row.id)">PDF
                            </button>

                        </td>
                    </tr>
                </data-table>
            </div>
            <tenant-internal-request-form :recordId="recordId" :showDialog.sync="showDialog"></tenant-internal-request-form>
        </div>
    </div>
</template>
<script>
import DataTable from '../../../components/DataTableInternalRequest.vue'
import InternalRequestForm from './form.vue'

export default {
    computed: {

    },
    components: {

        DataTable, InternalRequestForm
    },
    data() {
        return {
            showDialog: false,
            resource: 'internal-request',
            recordId: null,
        }
    },
    methods: {
        clickCreate(recordId = null) {
            this.recordId = recordId
            this.showDialog = true
        },
        clickDownload(id){

            window.open(`/${this.resource}/dowload/${id}/`, "_blank");

        },
        async clickDelete(id_d) {

            await this.$http.delete(`/${this.resource}/delete/` + id_d)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    console.log('Error:', error);
                })
                .finally(() => {
                    this.loading_submit = false
                });
                this.$eventHub.$emit('reloadData')

        },
        async changeManager(id_a, status_a) {

            let form = {
                id: id_a,
                status: status_a
            }
            await this.$http.post(`/${this.resource}/update/status`, form)

                .then(response => {

                    console.log(response)

                    if (response.data.success) {

                        this.$message.success(response.data.message)

                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {

                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        console.log(error)
                    }
                })
                .finally(() => {
                    this.loading_submit = false
                })
        }
    }

}
</script>
