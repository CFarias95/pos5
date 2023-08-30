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
                        <th class="text-center" style="min-width: 95px;">Detalles</th>
                        <th class="text-center" style="min-width: 95px;">Estado</th>
                        <th class="text-center" style="min-width: 95px;">Etapa</th>
                        <th class="text-center" style="min-width: 95px;">Acciones</th>

                    </tr>
                    <tr slot-scope="{ index, row }">
                        <td>{{ index }}</td>
                        <td class="text-center"> {{ row.title }}</td>
                        <td class="text-center"> {{ row.text }}</td>
                        <td class="text-center"> {{ row.phase }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary text-white"
                                :class="{ 'bg-success': (row.status === 'Acepted'), 'bg-warning': (row.estado === 'Rejected'), 'bg-secondary': (row.estado === 'Created') }">
                                {{ row.status }}
                            </span>
                        </td>
                        <td class="text-right">

                            <button v-if="!row.aproved" class="dropdown-item" @click.prevent="clickCreate(row.id)">Editar
                            </button>

                        </td>
                    </tr>
                </data-table>
            </div>

        </div>
    </div>
</template>
<script>
import DataTable from '../../../components/DataTableInternalRequest.vue'
export default {
    computed: {

    },
    components: {

        DataTable,
    },
    data() {
        return {
            showDialog: false,
            resource: 'internal-request',
            recordId: null,
        }
    },
    methods: {
        clickCreate() {
            console.log("click en boton crear")
        },
    }

}
</script>
