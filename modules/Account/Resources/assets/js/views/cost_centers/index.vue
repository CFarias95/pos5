<template>
    <div class="card">
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ title }}</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i
                        class="fa fa-plus-circle"></i> Nuevo</button>
            </div>
        </div>

        <div class="card-header bg-info">
            <h3 class="my-0">Listado de {{ title }}</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <!-- <data-table :resource="resource">
                    <tr slot="heading">
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Fecha Creado</th>
                        <th>Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }" :key="index" :rowGroup="row.level_1"
                        :class="{ 'text-success border-left border-success': (row.audited > 0), }">
                        <td>{{ row.id }}</td>
                        <td>{{ row.name }}</td>
                        <td>{{ row.created_at }}</td>
                        <td class="text-right">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                @click.prevent="clickCreate(row.id)">Editar</button>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                @click.prevent="clickDelete(row.id)">Eliminar</button>
                        </td>
                    </tr>
                </data-table> -->
                <template>
                    <div>
                        <b-table :items="items" :fields="fields" striped responsive="sm">
                            <template #cell(show_details)="row">
                                <b-button size="sm" @click="row.toggleDetails" class="mr-2">
                                    {{ row.detailsShowing ? 'Hide' : 'Show' }} Details
                                </b-button>

                                <!-- As `row.showDetails` is one-way, we call the toggleDetails function on @change -->
                                <b-form-checkbox v-model="row.detailsShowing" @change="row.toggleDetails">
                                    Details via check
                                </b-form-checkbox>
                            </template>

                            <template #row-details="row">
                                <b-card>
                                    <b-row class="mb-2">
                                        <b-col sm="3" class="text-sm-right"><b>Age:</b></b-col>
                                        <b-col>{{ row.item.age }}</b-col>
                                    </b-row>

                                    <b-row class="mb-2">
                                        <b-col sm="3" class="text-sm-right"><b>Is Active:</b></b-col>
                                        <b-col>{{ row.item.isActive }}</b-col>
                                    </b-row>

                                    <b-button size="sm" @click="row.toggleDetails">Hide Details</b-button>
                                </b-card>
                            </template>
                        </b-table>
                    </div>
                </template>


            </div>
            <cost-form :showDialog.sync="showDialog" :recordId="recordId"></cost-form>
        </div>
    </div>
</template>

<script>
import DataTable from '@components/DataTableCostCenter.vue'
import CostForm from './form.vue'
export default {
    components: { DataTable, CostForm },
    data() {
        return {
            showDialog: false,
            resource: 'cost_centers',
            recordId: null,
            record: {},
            records: [],
            pagination: {},
            loading_submit: false,
            title: null,

            fields: ['first_name', 'last_name', 'show_details'],
            items: [
                { isActive: true, age: 40, first_name: 'Dickerson', last_name: 'Macdonald' },
                { isActive: false, age: 21, first_name: 'Larsen', last_name: 'Shaw' },
                {
                    isActive: false,
                    age: 89,
                    first_name: 'Geneva',
                    last_name: 'Wilson',
                    _showDetails: true
                },
                { isActive: true, age: 38, first_name: 'Jami', last_name: 'Carney' }
            ]

        }
    },
    created() {
        this.title = 'Centros de Costo'
        this.$eventHub.$on('reloadData', () => {
            this.getData()
        })
        this.getData()
    },
    methods: {
        getData() {
            this.$http.get(`/${this.resource}/records`)
                .then(response => {
                    this.records = response.data.data
                    this.pagination = response.data;
                    this.pagination.per_page = parseInt(
                        response.data.per_page
                    );
                })
        },
        clickAudit(recordId) {
            this.$http.get(`/${this.resource}/audit/${recordId}`)
                .then(response => {
                    console.log(response)
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                    } else {

                        this.$message.error(response.data.message);
                    }
                })
        },
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
