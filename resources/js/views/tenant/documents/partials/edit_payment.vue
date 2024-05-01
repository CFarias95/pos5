<template>
    <el-dialog style="background-color: rgb(14 14 14 / 64%);" :close-on-click-modal="false"
        :close-on-press-escape="false" :show-close="false" append-to-body width="75%" @open="create" title="Editar Pago"
        align-center :visible="showDialogEdit">
        <el-form>
            <el-form-item label="Fecha de pago">
                <el-date-picker v-model="formMultiPay.date_of_payment" type="date" :clearable="false"
                    format="dd/MM/yyyy" value-format="yyyy-MM-dd"></el-date-picker>
            </el-form-item>
            <el-form-item label="Forma de pago">
                <el-select v-model="formMultiPay.payment_method_type_id"
                    :rules="[{ required: true, message: 'La forma de pago es obligatoria' }]">
                    <el-option v-for="option in payment_method_types" v-show="option.id != '09'" :key="option.id"
                        :value="option.id" :label="option.description"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="Referencia">
                <el-input v-model="formMultiPay.reference"
                    :rules="[{ required: true, message: 'La referencia es obligatoria' }]"></el-input>

            </el-form-item>
            <el-form-item label="Destino">
                <el-select v-model="formMultiPay.payment_destination_id" filterable
                    :rules="[{ required: true, message: 'Destino es obligatorio' }]">
                    <el-option v-for="option in payment_destinations" :key="option.id" :value="option.id"
                        :label="option.description"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="">
                <el-row :gutter="20">
                    <el-col :span="6">
                        <el-label>Valor</el-label>
                        <el-input type="number" :step="0.01" :min="0" v-model="formMultiPay.payment"
                            readonly></el-input>
                    </el-col>
                    <el-col :span="6">
                        <el-label>Debe</el-label>
                        <el-input type="number" :step="0.01" :min="0" v-model="formMultiPay.debe" readonly></el-input>
                    </el-col>
                    <el-col :span="6">
                        <el-label>Haber</el-label>
                        <el-input type="number" :step="0.01" :min="0" v-model="formMultiPay.haber" readonly></el-input>
                    </el-col>
                </el-row>

            </el-form-item>
            <el-form-item label="Extras">
                <el-button @click="addExtra()">
                    <i class="fa fa-plus-circle d-block"></i>
                </el-button>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, index) in formMultiPay.extras" :key="index">
                            <td>
                                <el-select v-model="row.account_id" placeholder="Seleccione una cuenta contable"
                                    filterable clearable>
                                    <el-option v-for="account in accounts" :key="account.id"
                                        :label="account.description" :value="account.id" />
                                </el-select>
                            </td>
                            <td>
                                <el-input v-model="row.debe" type="number" :disabled="row.haber > 0"
                                    @change="changeDebe(row.debe)" :step="0.01" :min="0" :max="999999999999999999999">
                                </el-input>
                            </td>
                            <td>
                                <el-input v-model="row.haber" type="number" :disabled="row.debe > 0"
                                    @change="changeHaber(row.haber)" :step="0.01" :min="0" :max="999999999999999999999">
                                </el-input>
                            </td>
                            <td>
                                <el-button @click="deleteExtra(index)">
                                    <i class="fa fa-trash d-block"></i>
                                </el-button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </el-form-item>
            <el-form-item label="Cuotas a liquidar">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Cliente</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template>
                            <tr v-for="(row, index) in formMultiPay.unpaid" :key="index"
                                :class="{ 'bg-success text-white': row.total_to_pay == 0 }">
                                <td>
                                    {{ row.document }}
                                </td>
                                <td>
                                    {{ row.customer }}
                                </td>
                                <td>
                                    <el-input-number v-model="row.amount" :max="row.maxamount" :step="0.01" :min="0.01"
                                        @change="changeInMultiPay"></el-input-number>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </el-form-item>
        </el-form>
        <template #footer>
            <span class="dialog-footer">
                <el-button type="danger" @click="clickClose()">Cancel</el-button>
                <el-button :loading="loading_submit_multipay" v-if="formMultiPay.payment > 0" type="primary"
                    @click="editPayment()">
                    Editar
                </el-button>
            </span>
        </template>
    </el-dialog>
</template>
</template>

<script>

export default {
    props: ['showDialogEdit', 'recordId', 'resource', 'payment_method_types', 'payment_destinations', 'accounts'],
    components: {
    },
    data() {
        return {
            titleDialog: null,
            loading: false,
            errors: {},
            formMultiPay: {},
            company: {},
            locked_emission: {},
            loading_submit_multipay: false,
        }
    },
    created() {
    },
    mounted() {
        //this.initForm()
    },
    computed: {
    },
    methods: {
        initForm() {
            this.errors = {};
            this.formMultiPay = {
                unpaid: [],
                extras: [],
                date_of_payment: moment().format('YYYY-MM-DD'),
                payment: 0,
                payment_method_type_id: '01',
                payment_destination_id: null,
                reference: 'N/A',
                debe: 0,
                haber: 0,
            },
                this.locked_emission = {
                    success: true,
                    message: null
                }
            this.company = {
                soap_type_id: null,
            }
        },
        async create() {
            await this.getRecord()
        },
        async getRecord() {
            this.loading = true;
            await this.$http.get(`/${this.resource}/record/edit/${this.recordId}`).then(response => {
                console.log(response.data)
                this.formMultiPay = response.data.data[0];
            }).finally(() => {
                this.loading = false
            });
        },
        clickClose() {
            this.$emit('update:showDialogEdit', false)
            this.initForm()
        },
        editPayment() {
            this.loading_submit_multipay = true
            this.$http.post(`/${this.resource}/save/edit`, this.formMultiPay).then(response => {
                console.log(response.data)
                if (response.data.success == true) {
                    console.log(response.data.message)
                    this.$message.success(response.data.message)
                    this.initForm();
                    this.$eventHub.$emit('reloadDataPayments')
                    this.$emit('update:showDialogEdit', false)

                } else {
                    this.$message.error(response.data.message)
                }

            }).finally(() => {
                this.loading_submit_multipay = false

            });
        },
        changeInMultiPay() {
            console.log('cambio de valores a liquidar')
            let total = 0;
            this.formMultiPay.unpaid.forEach(element => {
                total += element.amount;
            });

            this.formMultiPay.payment = _.round(total, 2);
            this.formMultiPay.debe = _.round(total, 2);
            this.formMultiPay.haber = _.round(total, 2);

            this.changeDebeHaber()

        },
        addExtra() {

            this.formMultiPay.extras.push({
                account_id: null,
                debe: 0,
                haber: 0,
            });

        },
        changeDebe(value) {
            this.formMultiPay.debe -= parseFloat(value)
            this.formMultiPay.haber -= parseFloat(value)
        },
        changeHaber(value) {
            this.formMultiPay.haber += parseFloat(value)
            this.formMultiPay.debe += parseFloat(value)
        },
        deleteExtra(index) {
            this.formMultiPay.extras.splice(index, 1);
        },
        changeDebeHaber() {
            this.formMultiPay.extras.forEach((extra) => {
                this.formMultiPay.debe -= extra.debe
                this.formMultiPay.haber -= extra.debe
                this.formMultiPay.debe += extra.haber
                this.formMultiPay.haber += extra.haber
            });
        }
    }
}
</script>
