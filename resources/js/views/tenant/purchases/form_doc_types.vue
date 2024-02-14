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
                                <div :class="{'has-danger': errors.idType}"
                                     class="form-group">
                                    <label class="control-label">Identificador<span class="text-danger">*</span></label>
                                    <el-input v-model="form.idType"
                                                :disabled="isEditForm"
                                                :minLength ="2"
                                                dusk="name"></el-input>
                                    <small v-if="errors.idType"
                                           class="form-control-feedback"
                                           v-text="errors.idType[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.description}"
                                     class="form-group">
                                    <label class="control-label">Descripción</label>
                                    <el-input v-model="form.description"
                                                dusk="name"></el-input>
                                    <small v-if="errors.description"
                                           class="form-control-feedback"
                                           v-text="errors.description[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.DocumentTypeID}"
                                     class="form-group">
                                    <label class="control-label">Tipo documento asociado</label>
                                    <el-select v-model="form.DocumentTypeID"
                                                dusk="name">
                                                <el-option v-for="option in doc_types"
                                               :key="option.id"
                                               :label="option.id + ' - ' + option.description"
                                               :value="option.id"></el-option>
                                            </el-select>
                                    <small v-if="errors.DocumentTypeID"
                                           class="form-control-feedback"
                                           v-text="errors.DocumentTypeID[0]"></small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.short}"
                                     class="form-group">
                                    <label class="control-label">Abreviatura</label>
                                    <el-input v-model="form.short"
                                                dusk="name"></el-input>
                                    <small v-if="errors.short"
                                           class="form-control-feedback"
                                           v-text="errors.short[0]"></small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.sign}"
                                     class="form-group">
                                    <label class="control-label">Signo de operacion?</label>
                                    <el-switch
                                        v-model="form.sign"
                                        class="ml-2"
                                        inline-prompt
                                        style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                                        active-text="+"
                                        inactive-text="-"
                                    />
                                    <small v-if="errors.sign"
                                           class="form-control-feedback"
                                           v-text="errors.sign[0]"></small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.accountant}"
                                     class="form-group">
                                    <label class="control-label">Es contable?</label>
                                    <el-switch
                                        v-model="form.accountant"
                                        class="ml-2"
                                        inline-prompt
                                        style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                                        active-text="Si"
                                        inactive-text="No"
                                    />
                                    <small v-if="errors.accountant"
                                           class="form-control-feedback"
                                           v-text="errors.accountant[0]"></small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.stock}"
                                     class="form-group">
                                    <label class="control-label">Afecta stock?</label>
                                    <el-switch
                                        v-model="form.stock"
                                        class="ml-2"
                                        inline-prompt
                                        style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                                        active-text="Si"
                                        inactive-text="No"
                                    />
                                    <small v-if="errors.stock"
                                           class="form-control-feedback"
                                           v-text="errors.stock[0]"></small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.active}"
                                     class="form-group">
                                     <label class="control-label">Habilitar documento?</label>
                                    <el-switch
                                        v-model="form.active"
                                        class="ml-2"
                                        active-text="Si"
                                        inactive-text="No"
                                        style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                                    />
                                    <small v-if="errors.active"
                                           class="form-control-feedback"
                                           v-text="errors.active[0]"></small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.active}"
                                     class="form-group">
                                     <label class="control-label">Afecta Costo?</label>
                                    <el-switch
                                        v-model="form.cost"
                                        class="ml-2"
                                        active-text="Si"
                                        inactive-text="No"
                                        style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                                    />
                                    <small v-if="errors.cost"
                                           class="form-control-feedback"
                                           v-text="errors.cost[0]"></small>
                                </div>
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

import {serviceNumber} from '../../../mixins/functions'

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
            form_zone: {add: false, name: null, id: null},
            parent: null,
            loading_submit: false,
            titleDialog: null,
            titleTabDialog: null,
            typeDialog: null,
            resource: 'imports',
            errors: {},
            api_service_token: false,
            form: {
                optional_email: []
            },
            temp_optional_email: [],
            temp_email: null,
            countries: [],
            zones: [],
            sellers: [],
            person_types: [],
            identity_document_types: [],
            discount_types: [],
            activeName: 'first',
            isEditForm : false,
            doc_types : [],
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
        },
    },
    methods: {
        ...mapActions([
            'loadConfiguration',
        ]),
        initForm() {
            this.errors = {}
            this.form = {
                active: false,
                accountant : false,
                stock: false,
                sign: false,
                cost: false,
            }
            this.resource = 'purchases'
        },
        async opened() {

        },
        create() {

            this.titleDialog = (this.recordId) ? 'Editar Tipo Documento de Compra ' : 'Nuevo Tipo Documento de Compra';
            this.titleTabDialog = 'Datos para crear el tipo documento de compra';
            this.typeDialog = (this.recordId) ? 'Editar' : 'Guardar';
            this.isEditForm = (this.recordId) ? true : false;


            this.$http.get(`/${this.resource}/document_types/tables`)
                .then(response => {
                    console.log("datos para tabla", response.data)
                    this.doc_types = response.data.doc_types
                })

            if (this.recordId) {
                this.$http.get(`/${this.resource}/document_types/record/${this.recordId}`)
                    .then(response => {

                        this.form = response.data.data
                        this.form.active = (response.data.data.active > 0) ? true:false
                        this.form.accountant = (response.data.data.accountant > 0) ? true:false
                        this.form.stock = (response.data.data.stock > 0) ? true:false
                        this.form.sign = (response.data.data.sign > 0) ? true:false

                    }).then(() => {

                })
            }
        },
        async submit() {
            this.loading_submit = true

            await this.$http.post(`/${this.resource}/document_types`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)

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
            //this.$eventHub.$emit('initInputPerson')
            this.$emit('update:showDialog', false)
            this.initForm()
        },
    }
}
</script>
