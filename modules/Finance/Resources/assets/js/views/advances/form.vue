<template>
    <el-dialog :close-on-click-modal="false"
               :title="titleDialog"
               :visible="showDialog"
               :append-to-body="true"
               @close="close"
               @open="create"
               @opened="opened">
        <form autocomplete="off"
              @submit.prevent="submit">
            <div class="form-body">
                <el-tabs v-model="activeName">
                    <el-tab-pane class
                                 name="first">
                        <span slot="label">{{ titleTabDialog }}</span>
                        <div class="row">

                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.idMethodType}"
                                     class="form-group">
                                    <label class="control-label">Tipo</label>
                                    <el-select v-model="form.idMethodType"
                                               filterable>
                                        <el-option v-for="option in methodTypes"
                                                   :key="option.id"
                                                   :label="option.description"
                                                   :value="option.id"></el-option>
                                    </el-select>
                                    <small v-if="errors.idMethodType"
                                           class="form-control-feedback"
                                           v-text="errors.idMethodType[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.id_payment}"
                                     class="form-group">
                                    <label class="control-label">Forma de pago</label>
                                    <el-select v-model="form.id_payment"
                                               filterable>
                                        <el-option v-for="option in methodTypes2"
                                                   :key="option.id"
                                                   :label="option.description"
                                                   :value="option.id"></el-option>
                                    </el-select>
                                    <small v-if="errors.id_payment"
                                           class="form-control-feedback"
                                           v-text="errors.id_payment[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.is_supplier}"
                                     class="form-group">
                                     <label class="control-label">Es de proveedor?</label>
                                    <el-switch
                                        v-model="form.is_supplier"
                                        class="ml-2"
                                        active-text="Si"
                                        inactive-text="No"
                                        style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                                        @change="changeTipy()"
                                    />
                                    <small v-if="errors.is_supplier"
                                           class="form-control-feedback"
                                           v-text="errors.is_supplier[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.idCliente}"
                                     class="form-group">
                                    <label class="control-label">{{ cliente_text }}</label>
                                    <el-select v-model="form.idCliente"
                                               dusk="tipoTransporte"
                                               filterable>
                                        <el-option v-for="option in clients"
                                                   :key="option.id"
                                                   :label="option.name"
                                                   :value="option.id"></el-option>
                                    </el-select>
                                    <small v-if="errors.idCliente"
                                           class="form-control-feedback"
                                           v-text="errors.idCliente[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-4" :class="{'has-danger': errors.valor}">
                                <label class="control-label">Valor</label>
                                <el-input-number v-model="form.valor" :step="1" :min="0"></el-input-number>
                                <small v-if="errors.valor"
                                            class="form-control-feedback"
                                            v-text="errors.valor[0]"></small>
                            </div>
                            <div class="col-md-4" :class="{'has-danger': errors.observation}">
                                <label class="control-label">Nota</label>
                                <el-input v-model="form.observation"></el-input>
                                <small v-if="errors.observation"
                                            class="form-control-feedback"
                                            v-text="errors.observation[0]"></small>
                            </div>
                            <div class="col-md-4" :class="{'has-danger': errors.reference}">
                                <label class="control-label">Referencia</label>
                                <el-input v-model="form.reference"></el-input>
                                <small v-if="errors.reference"
                                            class="form-control-feedback"
                                            v-text="errors.reference[0]"></small>
                            </div>
                        </div>

                    </el-tab-pane>
                </el-tabs>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button :loading="loading_submit"
                           native-type="submit"
                           type="primary">{{ typeDialog }}
                </el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>
import {mapActions, mapState} from "vuex/dist/vuex.mjs";
import {serviceNumber} from '@mixins/functions'

export default {
    mixins: [serviceNumber],
    props: [
        'showDialog',
        'recordId',
        'external',
        'document_type_id',
        'input_person',
        'parentId',
    ],
    data() {
        return {
            clients:[],
            clients_all:[],
            methodTypes:[],
            methodTypes2:[],
            loading_submit: false,
            titleDialog: null,
            titleTabDialog: null,
            typeDialog: null,
            resource: 'finances/advances',
            errors: {},
            activeName: 'first',
            form: {
            },
            cliente_text:'Cliente',

        }
    },
    async created() {

        this.loadConfiguration()
        await this.initForm()


    },
    computed: {
        ...mapState([
            'config',
            'person',
            'parentPerson',
        ]),
        maxLength: function () {
            if (this.form.identity_document_type_id === '6') {
                return 11
            }
            if (this.form.identity_document_type_id === '1') {
                return 8
            }
        }
    },
    methods: {
        ...mapActions([
            'loadConfiguration',
        ]),
        initForm() {
            this.errors = {}
            this.form = {
                id: null,
                is_supplier:false,
            }
            this.resource = 'finances/advances'

        },
        async opened() {

        },
        changeTipy(){
            console.log('Filtrando clientes')
            if(this.form.is_supplier){
                this.clients=_.filter(this.clients_all,{'type':'suppliers'})
            }else{
                this.clients=_.filter(this.clients_all,{'type':'customers'})
            }
            console.log('Filtrando clientes', this.clients)
        },
        create() {

            console.log('config',this.config)

            this.titleDialog = (this.recordId) ? 'Editar anticipo' : 'Nuevo anticipo';
            this.titleTabDialog = 'Datos para crear anticipo';
            this.typeDialog = (this.recordId) ? 'Editar' : 'Guardar';
            this.isEditForm = (this.recordId) ? true : false;
            if (this.recordId) {
                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        //console.log('DATOS A EDITAR: ', response.data.data)
                        this.form = response.data.data
                }).then(() => {

                })
            }

            this.$http.get(`/${this.resource}/tables`)
                .then(response => {
                    console.log('DATA CREATED: ', response)
                    this.clients_all = response.data.clients
                    this.methodTypes = response.data.methodTypes
                    this.methodTypes2 = response.data.methodTypes2
            }).then(() => {
                this.changeTipy()
            })

        },
        async submit() {
            this.loading_submit = true
            await this.$http.post(`/${this.resource}`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        if (this.external) {
                            this.$eventHub.$emit('reloadDataPersons', response.data.id)
                        } else {
                            this.$eventHub.$emit('reloadData')
                        }
                        this.close()
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
        },
        close() {
            this.$eventHub.$emit('initInputPerson')
            this.$emit('update:showDialog', false)
            this.initForm()
        },
    }
}
</script>
