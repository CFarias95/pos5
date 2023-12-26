<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @open="create" width="30%"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false">

        <div class="row">

            <div class="col-lg-12 col-md-12 col-sm-12 text-center font-weight-bold mt-3">
                <button type="button" class="btn btn-lg btn-info waves-effect waves-light" @click="clickPrint('a4')">
                    <i class="fa fa-file-alt"></i>
                </button>
                <p>A4</p>
            </div>
            <div class="col-md-12">
                <el-input v-model="email">
                    <el-button slot="append"
                                :loading="loading"
                                icon="el-icon-message"
                                @click="clickSendEmail">Enviar
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
            </template>
        </span>
    </el-dialog>
</template>

<script>

    export default {
        props: ['showDialog', 'recordId', 'showClose', 'type'],
        data() {
            return {
                titleDialog: null,
                loading: false,
                resource: 'inventory',
                errors: {},
                form: {},
                email:'Correo destinatario',
            }
        },
        created() {
            this.initForm()
        },
        methods: {
            clickPrint(format){
                window.open(`/${this.resource}/print/${this.recordId}/${this.type}`, '_blank');
            },
            initForm() {
                this.errors = {}
                this.email=''
                this.form = {
                    id: null,
                    external_id: null,
                    number: null,
                    customer_email: '',
                    download_pdf: null
                }
            },
            create() {

                let typei = this.type == 'output' ? 'Salida' : 'Ingreso'
                if(this.type == 'fix'){
                    typei = 'Ajuste'
                }
                this.titleDialog = `${typei} de inventario: INV-` +this.recordId

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
                if(this.email && this.email != ''){
                    this.loading = true
                    this.$http.post(`/${this.resource}/email`, {
                        id: this.recordId,
                        email: this.email,
                        type:this.type
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
