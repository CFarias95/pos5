<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @open="create" width="30%"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false">

        <div class="row">

            <div class="col-lg-6 col-md-6 col-sm-6 text-center font-weight-bold mt-3">
                <button type="button" class="btn btn-lg btn-info waves-effect waves-light" @click="clickPrint('a4')">
                    <i class="fa fa-file-alt"></i>
                </button>
                <p>Compra A4</p>
            </div>
            <div v-if="retentionId" class="col-lg-6 col-md-6 col-sm-6 text-center font-weight-bold mt-3">
                <button type="button" class="btn btn-lg btn-info waves-effect waves-light" @click="clickPrintRet('a4')">
                    <i class="fa fa-file-alt"></i>
                </button>
                <p>Retencion A4</p>
            </div>

            <div class="col-md-12" v-if="retentionId">
                <el-input v-model="form.customer_email">
                    <el-button slot="append"
                                :loading="loading"
                                icon="el-icon-message"
                                @click="clickSendEmail">Enviar Ret
                    </el-button>
                </el-input>
                <small v-if="errors.customer_email"
                        class="form-control-feedback"
                        v-text="errors.customer_email[0]"></small>
            </div>



        </div>
        <span slot="footer" class="dialog-footer">
            <template v-if="showClose">
                <el-button @click="clickClose">Cerrar</el-button>
            </template>
            <template v-else>
                <el-button @click="clickFinalize">Ir al listado</el-button>
                <el-button type="primary" @click="clickNewDocument">Nueva compra</el-button>
            </template>
        </span>
    </el-dialog>
</template>

<script>

    export default {
        props: ['showDialog', 'recordId', 'showClose', 'type', 'email','retentionId'],
        data() {
            return {
                titleDialog: null,
                loading: false,
                resource: 'purchases',
                errors: {},
                form: {},
            }
        },
        created() {
            this.initForm()
        },
        methods: {
            clickPrint(format){
                window.open(`/${this.resource}/print/${this.form.external_id}/${format}`, '_blank');
            },
            clickPrintRet(format){
                window.open(`/retentions/print/${this.retentionId}/${format}`, '_blank');
            },
            initForm() {
                this.errors = {}
                this.form = {
                    id: null,
                    external_id: null,
                    number: null,
                    customer_email: null,
                    download_pdf: null
                }
            },
            create() {

                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data.data
                        let typei = this.type == 'edit' ? 'editada' : 'registrada'
                        this.titleDialog = `Compra ${typei}: ` +this.form.number
                        if(this.retentionId){
                            this.form.customer_email = this.email
                        }
                    })

            },

            clickFinalize() {
                location.href = `/${this.resource}`
            },
            clickNewDocument() {
                this.clickClose()
            },
            clickClose() {
                this.$emit('update:showDialog', false)
                this.initForm()
            },
            clickSendEmail() {
                if(this.form.customer_email && this.form.customer_email != ''){
                    this.loading = true
                    this.$http.post(`/purchases/retention/email`, {
                        id: this.retentionId,
                        email: this.form.customer_email
                    })
                        .then(response => {
                            if (response.data.success) {
                                this.$message.success('El correo fue enviado satisfactoriamente')
                            } else {
                                this.$message.error('Error al enviar el correo')
                            }
                        })
                        .catch(error => {
                            if (error.response.status === 422) {
                                this.errors = error.response.data.errors
                            } else {
                                this.$message.error(error.response.data.message)
                            }
                        })
                        .then(() => {
                            this.loading = false
                        })
                }else{
                    this.$message.error('Ingresa un correo Válido')
                }
        },

        }
    }
</script>
