<template>
    <el-dialog :close-on-click-modal="false" :title="titleDialog" :visible="showDialog" :append-to-body="true"
        @close="close" @open="create" @opened="opened">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <el-tabs v-model="activeName">
                    <el-tab-pane class name="first">
                        <span slot="label">{{ titleTabDialog }}</span>
                        <div class="row">
                            <div class="col-md-4">
                                <div :class="{ 'has-danger': errors.title }" class="form-group">
                                    <label class="control-label">Asunto<span class="text-danger">*</span></label>
                                    <el-input v-model="form.title" :disabled="isEditForm" dusk="name"></el-input>
                                    <small v-if="errors.title" class="form-control-feedback"
                                        v-text="errors.title[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div :class="{ 'has-danger': errors.confirmed }" class="form-group">
                                    <label class="control-label">Mandar solicitud de pedido? </label>
                                    <el-switch :disabled="isEditForm" v-model="form.confirmed" class="ml-2" inline-prompt
                                        style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                                        active-text="Si" inactive-text="No" />
                                    <small v-if="errors.confirmed" class="form-control-feedback"
                                        v-text="errors.confirmed[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div :class="{ 'has-danger': errors.confirmed }" class="form-group">
                                    <label class="control-label">Se solicita a: </label>
                                    <el-select :disabled="isEditForm" v-model="form.user_manage">
                                        <el-option v-for="user in users" :key="user.id" :value="user.id"
                                            :label="user.name"></el-option>
                                    </el-select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div :class="{ 'has-danger': errors.description }" class="form-group">
                                    <label class="control-label">Descripción</label>
                                    <el-input v-model="form.description" dusk="name" type="textarea" rows="3"></el-input>
                                    <small v-if="errors.description" class="form-control-feedback"
                                        v-text="errors.description[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-actions text-left mt-4">
                                    <el-upload :data="{ 'type': 'internal-request-attached' }" :headers="headers"
                                        :multiple="false" :action="`/${resource}/upload`" :show-file-list="true"
                                        :file-list="fileList" :on-remove="handleRemove" :on-success="onSuccess" :limit="1">
                                        <el-button slot="trigger" type="primary">Seleccione un archivo (PDF/JPG)
                                        </el-button>
                                    </el-upload>
                                </div>
                                <div>
                                    <label>Archivo cargado: </label>
                                    <label v-if="this.form.upload_filename != null">{{ this.form.upload_filename }}</label>
                                    <label v-else>N/A</label>
                                </div>
                            </div>

                        </div>
                    </el-tab-pane>
                </el-tabs>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button :loading="loading_submit" native-type="submit" type="primary">{{ typeDialog }}
                </el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>
import { mapActions, mapState } from "vuex/dist/vuex.mjs";

import { serviceNumber } from '../../../mixins/functions'

export default {
    mixins: [serviceNumber],
    props: [
        'showDialog',
        'recordId',
    ],
    data() {
        return {
            headers: headers_token,
            loading_submit: false,
            titleDialog: null,
            titleTabDialog: null,
            typeDialog: null,
            resource: 'internal-request',
            api_service_token: false,
            form: {
                optional_email: []
            },
            users: [],
            activeName: 'first',
            isEditForm: false,
            fileList: [],
        }
    },
    async created() {

        this.loadConfiguration()
        await this.initForm()

        this.$http.get(`/${this.resource}/tables`)
            .then(response => {
                this.users = response.data.users
            })

    },
    computed: {
        ...mapState([
            'config',
            'person',
            'parentPerson',
        ]),
    },
    methods: {
        ...mapActions([
            'loadConfiguration',
        ]),
        initForm() {
            this.errors = {}
            this.form = {
                id: null,
                user_id: null,
                user_manage: null,
                title: null,
                description: null,
                status: "Created",
                phase: null,
                confirmed: false,
                upload_filename:null,
            }
            this.resource = 'internal-request'

        },
        async opened() {

        },
        create() {

            this.titleDialog = (this.recordId) ? 'Editar pedido interno' : 'Nuevo pedido interno';
            this.titleTabDialog = 'Pedido Interno';
            this.typeDialog = (this.recordId) ? 'Editar' : 'Guardar';
            //this.isEditForm = (this.recordId) ? true : false;

            if (this.recordId) {
                console.log("seteando data")
                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        console.log(response.data.data)
                        this.form = response.data.data;
                        this.isEditForm = response.data.data.confirmed;
                    })
            }
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
                    this.initForm()
                    this.fileList = []
                })
        },
        close() {
            this.$eventHub.$emit('initInputPerson')
            this.$emit('update:showDialog', false)
            this.initForm()
        },
        handleRemove(file, fileList) {
            this.form.upload_filename = null
            this.form.temp_path = null
            this.fileList = []
        },
        onSuccess(response, file, fileList) {
            // console.log(response, file, fileList)
            this.fileList = fileList
            if (response.success) {
                this.form.upload_filename = response.data.filename
                this.form.image_url = response.data.temp_image
                this.form.attached_temp_path = response.data.temp_path
            } else {
                this.cleanFileList()
                this.$message.error(response.message)
            }
        },
    }
}
</script>
