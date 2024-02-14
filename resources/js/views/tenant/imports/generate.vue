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
                            <div class="col-md-6">
                                <div :class="{'has-danger': errors.numeroImportacion}"
                                     class="form-group">
                                    <label class="control-label">Nùmero <span class="text-danger">*</span></label>
                                    <el-input v-model="form.numeroImportacion"
                                                
                                                dusk="name"></el-input>
                                    <small v-if="errors.numeroImportacion"
                                           class="form-control-feedback"
                                           v-text="errors.numeroImportacion[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div :class="{'has-danger': errors.tipoTransporte}"
                                     class="form-group">
                                    <label class="control-label">Tipo de transporte</label>
                                    <el-select v-model="form.tipoTransporte"
                                               dusk="tipoTransporte"
                                               filterable>
                                        <el-option v-for="option in tipoTransportes"
                                                   :key="option.id"
                                                   :label="option.description"
                                                   :value="option.description"></el-option>
                                    </el-select>
                                    <small v-if="errors.tipoTransporte"
                                           class="form-control-feedback"
                                           v-text="errors.tipoTransporte[0]"></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div :class="{'has-danger': errors.fechaEmbarque}"
                                     class="form-group">
                                     <label class="control-label">Fecha Embarque </label>
                                    <el-date-picker
                                        v-model="form.fechaEmbarque"
                                        type="date"
                                        style="width: 100%;"
                                        placeholder="Buscar"
                                        value-format="yyyy-MM-dd">
                                    </el-date-picker>
                                    <small v-if="errors.fechaEmbarque"
                                           class="form-control-feedback"
                                           v-text="errors.fechaEmbarque[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div :class="{'has-danger': errors.fechaLlegada}"
                                     class="form-group">
                                     <label class="control-label">Fecha Llegada </label>
                                    <el-date-picker
                                        v-model="form.fechaLlegada"
                                        type="date"
                                        style="width: 100%;"
                                        placeholder="Buscar"
                                        value-format="yyyy-MM-dd">
                                    </el-date-picker>
                                    <small v-if="errors.fechaLlegada"
                                           class="form-control-feedback"
                                           v-text="errors.fechaLlegada[0]"></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label class="control-label">Estado</label>
                                <el-select v-model="form.estado" popper-class="el-select-document_type" filterable clearable>
                                    <el-option v-for="option in estados" :key="option.id" :value="option.description" :label="option.description"></el-option>
                                </el-select>
                            </div>
                            <div class="col-md-4">
                                <label class="control-label">ISD</label>
                                <el-input-number :min="0" v-model="form.isd" clearable ></el-input-number>
                            </div>
                            <div class="col-md-5">
                                <label class="control-label">Comunicaciones</label>
                                <el-input-number :min="0" v-model="form.comunications" clearable ></el-input-number>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label class="control-label">Cta. contable</label>
                                <el-select v-model="form.cuenta_contable"  filterable clearable>
                                    <el-option v-for="cuenta in cuenta_contable" :key="cuenta.id" :value="cuenta.id" :label="cuenta.description"></el-option>
                                </el-select>
                            </div>
                            <div class="col-md-4">
                                <label class="control-label">Cta. ISD</label>
                                <el-select v-model="form.cta_isd"  filterable clearable>
                                    <el-option v-for="cuenta in cuenta_contable" :key="cuenta.id" :value="cuenta.id" :label="cuenta.description"></el-option>
                                </el-select>
                            </div>
                            <div class="col-md-4">
                                <label class="control-label">Cta. Comunicaciones</label>
                                <el-select v-model="form.cta_comunications"  filterable clearable>
                                    <el-option v-for="cuenta in cuenta_contable" :key="cuenta.id" :value="cuenta.id" :label="cuenta.description"></el-option>
                                </el-select>
                            </div>
                            <div class="col-md-4">
                                <label class="control-label">INCOTERM</label>
                                <el-select v-model="form.incoterm"  filterable clearable>
                                    <el-option v-for="row in incoterms" :key="row.id" :value="row.id" :label="row.code"></el-option>
                                </el-select>
                            </div>
                            <div class="col-md-4">
                                <label class="control-label">Flete</label>
                                <el-input-number v-model="form.flete" clearable min="0" >
                                </el-input-number>
                            </div>
                            <div class="col-md-4">
                                <label class="control-label">Gasto hasta FOB</label>
                                <el-input-number v-model="form.fob" clearable min="0" >
                                </el-input-number>
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
            parteRel:[
                {'id': 1, 'name':'SI'},
                {'id': 2, 'name':'NO'},
            ],
            pagoLocExt:[
                {'id': 1, 'name':'Local'},
                {'id': 2, 'name':'Exterior'},
            ],
            pagoLocExtDoc:[
                {'id': 1, 'name':'Natural'},
                {'id': 2, 'name':'Sociedad'},
            ],
            person_types: [],
            identity_document_types: [],
            discount_types: [],
            activeName: 'first',
            isEditForm : false,
            cuenta_contable: null,
            incoterms:[],
            estados: [{'id':1,'description':'Registrada'},{'id':2,'description':'Liberada'},{'id':3,'description':'Liquidada'}],
            tipoTransportes : [{'id':1,'description':'Aereo'},{'id':2,'description':'Maritimo'},{'id':3,'description':'Terrestre'}],
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
                id: null,
                estado:'Registrada',
                fechaLlegada: null,
                fechaEmbarque: null,
                tipoTransporte: null,
                numeroImportacion:null,
                cuenta_contable: null,
                cta_isd:null,
                cta_comunications:null,
                isd:null,
                comunications:null,
                incoterm:null,
                flete:0,
                fob:0,
            }
            this.resource = 'imports'

        },
        async opened() {

        },
        create() {

            this.titleDialog = (this.recordId) ? 'Editar Importacion' : 'Nueva Importacion';
            this.titleTabDialog = 'Datos de la importacion';
            this.typeDialog = (this.recordId) ? 'Editar' : 'Guardar';
            this.isEditForm = (this.recordId) ? true : false;
            //this.cuenta_contable = response.data.data.cuenta_contable
            this.$http.get(`/${this.resource}/tables`)
            .then(response =>{
                //console.log('DATA', response)
                this.cuenta_contable = response.data.cta_accountants
                this.incoterms = response.data.incoterms
            })
            if (this.recordId) {
                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        console.log('DATOS A EDITAR: ', response.data.data)
                        this.form = response.data.data

                        if(response.data.data.estado == 'Registrada'){
                            this.estados= [{'id':1,'description':'Registrada'},{'id':2,'description':'Liberada'}]
                        }
                        if(response.data.data.estado == 'Liberada'){
                            this.estados= [{'id':2,'description':'Liberada'},{'id':3,'description':'Liquidada'}]
                        }
                        if(response.data.data.estado == 'Liquidada'){
                            this.estados= [{'id':3,'description':'Liquidada'}]
                        }

                    }).then(() => {
                    //this.updateEmail()
                })
            }
        },
        async submit() {
            this.loading_submit = true

            if(!this.form.numeroImportacion)
            {
                this.loading_submit = false
                return this.$message.error("El número de cuenta es obligatorio")
            }
            if(!this.form.cuenta_contable)
            {
                this.loading_submit = false
                return this.$message.error("La cuenta contable es obligatorio")
            }

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
