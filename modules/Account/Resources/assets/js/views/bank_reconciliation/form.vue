<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="form- col-md-6" :class="{ 'has-danger': errors.code }">
                        <label class="control-label">Código</label>
                        <el-input v-model="form.id" readonly></el-input>
                        <small class="form-control-feedback" v-if="errors.code" v-text="errors.code[0]"></small>
                    </div>
                    <div class="form-group col-md-6" :class="{ 'has-danger': errors.name }">
                        <label class="control-label">Saldo inicial</label>
                        <el-input type="number" v-model="form.initial_value"></el-input>
                        <small class="form-control-feedback" v-if="errors.name" v-text="errors.name[0]"></small>
                    </div>
                    <div class="form-group col-md-6" :class="{ 'has-danger': errors.name }">
                        <label class="control-label">Total debe</label>
                        <el-input v-model="form.total_debe" readonly></el-input>
                        <small class="form-control-feedback" v-if="errors.name" v-text="errors.name[0]"></small>
                    </div>
                    <div class="form-group col-md-6" :class="{ 'has-danger': errors.name }">
                        <label class="control-label">Total haber</label>
                        <el-input v-model="form.total_haber" readonly></el-input>
                        <small class="form-control-feedback" v-if="errors.name" v-text="errors.name[0]"></small>
                    </div>
                    <div class="form-group col-md-6" :class="{ 'has-danger': errors.diference_value }">
                        <label class="control-label">Diferencia</label>
                        <el-input type="number" v-model="form.diference_value" readonly></el-input>
                        <small class="form-control-feedback" v-if="errors.diference_value" v-text="errors.name[0]"></small>
                    </div>
                    <div class="form-group col-md-6" :class="{ 'has-danger': errors.month }">
                        <label class="control-label">Mes a conciliar</label>
                        <el-date-picker v-model="form.month" type="month" required value-format="yyyy-MM" format="MM/yyyy"
                            :clearable="false"></el-date-picker>
                        <small class="form-control-feedback" v-if="errors.month" v-text="errors.month[0]"></small>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group" :class="{ 'has-danger': errors.account_id }">
                            <label class="control-label">Cuenta movimiento</label>
                            <el-select v-model="form.account_id" filterable required>
                                <el-option v-for="option in ctas" :key="option.id" :label="option.name" :value="option.id"
                                    v-if="option.id != form.id"></el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <br>
                        <div class="form-group">
                            <el-button :v-if="form.account_id != null && form.month != null" type="warning"
                                @click.prevent="getMovements()">Colsultar Movimientos</el-button>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Comment</th>
                                        <th>Debe</th>
                                        <th>Haber</th>
                                        <th>Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template>
                                        <tr v-for="(row, index) in movements" :key="index" :class="{
                                            'bg-success text-white': row.bank_reconciliated == 1,
                                        }">
                                            <td>
                                                {{ row.comment }}
                                            </td>
                                            <td>
                                                {{ row.debe }}
                                            </td>
                                            <td>
                                                {{ row.haber }}
                                            </td>
                                            <td>
                                                <el-button @click.prevent="reconciliate(row)">Reconciliar</el-button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
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
            resource: 'bank_reconciliation',
            errors: {},
            form: {},
            ctas: [],
            movements: [],
        }
    },
    created() {
        this.initForm()
    },
    methods: {
        initForm() {
            this.errors = {}
            this.form = {
                initial_value: 0,
                id: null,
                total_debe: 0,
                total_haber: 0,
                diference_value: 0,
                month: null,
                account_id: null,
            }
        },
        create() {

            this.titleDialog = (this.recordId) ? 'Editar conciliacion bancaria' : 'Nueva conciliacion bancaria'
            this.loadTable()
            if (this.recordId) {
                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data
                    })
                    .finally(() => {
                        this.getMovements()
                    })
            }
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
        getMovements() {

            this.$http.post(`/${this.resource}/movements`, this.form).then(response => {
                this.movements = response.data
            })
        },
        reconciliate(asiento) {
            this.$http.get(`/${this.resource}/reconciliate/${this.form.id}/${asiento.id}`)
            .then(response => {
                console.log('reconciliate', response.data)
                if (response.data.success) {
                    this.$message.success(response.data.message)
                    this.form.total_debe += parseFloat(asiento.debe)
                    this.form.total_haber += parseFloat(asiento.haber)
                    this.form.diference_value = parseFloat(this.form.initial_value) + parseFloat(this.form.total_debe) - parseFloat(this.form.total_haber)

                    this.getMovements()
                } else {
                    this.$message.error(response.data.message)
                }
            })
        }
    }
}
</script>
