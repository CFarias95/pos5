<template>
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="my-0">Métodos de pago - ingreso
                <el-tooltip class="item" effect="dark" content="Manejo interno de la empresa / Ingresos" placement="top-start">
                    <i class="fa fa-info-circle"></i>
                </el-tooltip>
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i class="fa fa-plus-circle"></i> Nuevo</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Condición de pago</th>
                        <th>Condición SRI</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(row, index) in records" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ row.id }}</td>
                        <td>{{ row.description }}</td>
                        <td v-if="row.is_credit == 1">Crédito</td>
                        <td v-if="row.is_cash == 1">Contado</td>
                        <td v-if="row.is_advance == 1">Anticipo</td>
                        <td>{{ (row.sri_desciption) ? row.sri_desciption:'N/A' }}</td>
                        <td class="text-right">

                            <template v-if="row.show_actions">

                                <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickCreate(row.id)">Editar</button>

                                <template v-if="typeUser === 'admin'">
                                    <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"  @click.prevent="clickDelete(row.id)">Eliminar</button>
                                </template>

                            </template>

                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <!-- <div class="row">
                <div class="col">
                    <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i class="fa fa-plus-circle"></i> Nuevo</button>
                </div>
            </div> -->
        </div>
        <payment-method-types-form :showDialog.sync="showDialog"
                         :recordId="recordId"></payment-method-types-form>
    </div>
</template>

<script>

    import PaymentMethodTypesForm from './form.vue'
    import {deletable} from '@mixins/deletable'

    export default {
        mixins: [deletable],
        props: ['typeUser'],
        components: {PaymentMethodTypesForm},
        data() {
            return {
                showDialog: false,
                resource: 'payment-method-types',
                recordId: null,
                records: [],
            }
        },
        created() {
            this.$eventHub.$on('reloadData', () => {
                this.getData()
            })
            this.getData()
        },
        methods: {
            getData() {
                this.$http.get(`/${this.resource}/records`)
                    .then(response => {
                        //console.log(this.resource, response.data);
                        this.records = response.data.data
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
