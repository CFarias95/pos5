<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create" :loading="loading_form">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="form- col-md-2" :class="{ 'has-danger': errors.id }">
                        <label class="control-label">Id</label>
                        <el-input v-model="form.id" readonly></el-input>
                        <small class="form-control-feedback" v-if="errors.id" v-text="errors.id[0]"></small>
                    </div>
                    <div class="form-group col-md-2" :class="{ 'has-danger': errors.active }">
                        <label class="control-label">Activo</label>
                        <el-switch v-model="form.active"></el-switch>
                        <small class="form-control-feedback" v-if="errors.active" v-text="errors.active[0]"></small>
                    </div>
                    <div class="form-group col-md-2" :class="{ 'has-danger': errors.percentage }">
                        <label class="control-label">Porcentaje</label>
                        <el-input type="number" :step="0.01" :max="100" v-model="form.percentage"></el-input>
                        <small class="form-control-feedback" v-if="errors.percentage" v-text="errors.percentage[0]"></small>
                    </div>
                    <div class="form-group col-md-6" :class="{ 'has-danger': errors.description }">
                        <label class="control-label">Descripción</label>
                        <el-input type="text" v-model="form.description" ></el-input>
                        <small class="form-control-feedback" v-if="errors.description" v-text="errors.description[0]"></small>
                    </div>
                    <div class="form-group col-md-3" :class="{ 'has-danger': errors.code }">
                        <label class="control-label">Código</label>
                        <el-input v-model="form.code" :readonly="form.id != null"></el-input>
                        <small class="form-control-feedback" v-if="errors.code" v-text="errors.code[0]"></small>
                    </div>
                    <div class="form-group col-md-3" :class="{ 'has-danger': errors.month }">
                        <label class="control-label">Tipo</label>
                            <el-select v-model="form.type_id" filterable :required="true" :readonly="form.id != null" >
                                <el-option key="1" value="01" label="RENTA"></el-option>
                                <el-option key="2" value="02" label="IVA"></el-option>
                            </el-select>
                        <small class="form-control-feedback" v-if="errors.month" v-text="errors.month[0]"></small>
                    </div>
                    <div class="form-group col-md-3" :class="{ 'has-danger': errors.code2 }" v-if="form.type_id == '02'">
                        <label class="control-label">Código ATS</label>
                        <el-input v-model="form.code2" :required="form.type_id == '02'" :readonly="form.id != null" ></el-input>
                        <small class="form-control-feedback" v-if="errors.code2" v-text="errors.code2[0]"></small>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" :class="{ 'has-danger': errors.account_id }">
                            <label class="control-label">Cuenta movimiento</label>
                            <el-select v-model="form.account_id" filterable :required="true">
                                <el-option v-for="option in ctas" :key="option.id" :label="option.name" :value="option.id"
                                ></el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right pt-2">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button type="primary" native-type="submit" :loading="loading_submit">Guardar</el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>
export default {
    props: ['showDialog', 'recordId'],
    data() {
        return {
            loading_submit: false,
            titleDialog: null,
            resource: 'purchase-retentions',
            errors: {},
            form: {},
            ctas: [],
            movements: [],
            loading_form: false,
        }
    },
    created() {
        this.initForm()
    },
    methods: {
        initForm() {
            this.errors = {}
            this.form = {
                id: null,
                active: false,
                percentage: 0,
                description: null,
                code: null,
                type_id: null,
                code2: null,
                account_id:null,
                type:'Create',
            }
            this.ctas = []

        },
        create() {
            this.loading_form = true
            this.titleDialog = (this.recordId) ? 'Editar retencion de compra' : 'Nueva retencion de compra'
            this.loadTable()
            if (this.recordId) {
                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data
                        this.form.type = 'Edit'
                    })
            }
            this.loading_form = false
        },
        loadTable() {

            this.$http.get(`/${this.resource}/columns`).then(response => {
                this.ctas = response.data.ctas
            })
        },
        submit() {

            this.loading_submit = true
            this.$http.post(`${this.resource}`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                        this.close()
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        console.log(error.response)
                    }
                })
                .then(() => {
                    this.loading_submit = false
                })

        },
        close() {
            this.$emit('update:showDialog', false)
            this.initForm()
        },
        recalculateDif() {
            this.form.diference_value = this.form.initial_value + this.form.total_debe - this.form.total_haber
        },
        closeReconciliation() {

            this.$http.get(`/${this.resource}/close/${this.form.id}`)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                        this.close()
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        console.log(error.response)
                    }
                })
                .then(() => {
                    this.loading_submit = false
                })
        },
    }
}
</script>
