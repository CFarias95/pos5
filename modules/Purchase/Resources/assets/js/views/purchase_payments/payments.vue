<template>
    <el-dialog :title="title" :visible="showDialog" @close="close" @open="getData" width="65%">
        <div class="form-body">
            <div class="row">
                <div class="col-md-12" v-if="records.length > 0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha de pago</th>
                                    <th>Método de pago</th>
                                    <th>Destino</th>
                                    <th>Referencia</th>
                                    <th>Archivo</th>
                                    <th class="text-right">Monto</th>
                                    <th class="text-center">Descargar Comprobante</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, index) in records" :key="index">
                                    <template v-if="row.id">
                                        <td>PAGO-{{ row.id }}</td>
                                        <td>{{ row.date_of_payment }}</td>
                                        <td>{{ row.payment_method_type_description }}</td>
                                        <td>{{ row.destination_description }}</td>
                                        <td>{{ row.reference }}</td>
                                        <td class="text-center">
                                            <button type="button" v-if="row.filename"
                                                class="btn waves-effect waves-light btn-xs btn-primary"
                                                @click.prevent="clickDownloadFile(row.filename)">
                                                <i class="fas fa-file-download"></i>
                                            </button>
                                        </td>
                                        <td class="text-right">{{ row.payment }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-lg btn-info waves-effect waves-light" type="button"
                                                @click="clickPrint('a4', index)">
                                                <i class="fa fa-file-alt"></i>
                                            </button>
                                            <p>A4</p>
                                        </td>
                                        <td class="series-table-actions text-right">
                                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                                @click.prevent="clickDelete(row.id)">Eliminar</button>
                                            <!--<el-button type="danger" icon="el-icon-delete" plain @click.prevent="clickDelete(row.id)"></el-button>-->
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td></td>
                                        <td>
                                            <div class="form-group mb-0"
                                                :class="{ 'has-danger': row.errors.date_of_payment }">
                                                <el-date-picker v-model="row.date_of_payment" type="date" :clearable="false"
                                                    format="dd/MM/yyyy" value-format="yyyy-MM-dd"></el-date-picker>
                                                <small class="form-control-feedback" v-if="row.errors.date_of_payment"
                                                    v-text="row.errors.date_of_payment[0]"></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group mb-0"
                                                :class="{ 'has-danger': row.errors.payment_method_type_id }">
                                                <el-select v-model="row.payment_method_type_id"
                                                    @change="changePaymentMethodType(index)">
                                                    <el-option v-for="option in payment_method_types" :key="option.id"
                                                        :value="option.id" :label="option.description"></el-option>
                                                </el-select>
                                                <small class="form-control-feedback"
                                                    v-if="row.errors.payment_method_type_id"
                                                    v-text="row.errors.payment_method_type_id[0]"></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group mb-0"
                                                :class="{ 'has-danger': row.errors.payment_destination_id }">
                                                <el-select v-model="row.payment_destination_id" filterable
                                                    :disabled="row.payment_destination_disabled">
                                                    <el-option v-for="option in payment_destinations" :key="option.id"
                                                        :value="option.id" :label="option.description"></el-option>
                                                </el-select>
                                                <small class="form-control-feedback"
                                                    v-if="row.errors.payment_destination_id"
                                                    v-text="row.errors.payment_destination_id[0]"></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group mb-0" :class="{ 'has-danger': row.errors.reference }"
                                                v-if="row.payment_method_type_id == '14' || row.payment_method_type_id == '15'">
                                                <el-select v-model="row.reference" @change="changeAdvance(index, $event)"
                                                    placeholder="Referencia Anticipo">
                                                    <el-option v-for="option in advances" :key="option.id"
                                                        :label="'AT' + option.id + ' - ' + option.reference"
                                                        :value="option.id"></el-option>
                                                </el-select>
                                                <small class="form-control-feedback" v-if="row.errors.reference"
                                                    v-text="row.errors.reference[0]"></small>
                                            </div>
                                            <div class="form-group mb-0" :class="{ 'has-danger': row.errors.reference }"
                                                v-if="row.payment_method_type_id == '16'">
                                                <el-select v-model="row.reference" @change="changeCredits(index, $event)"
                                                    placeholder="Referencia Anticipo">
                                                    <el-option v-for="option in credits" :key="option.id"
                                                        :label="option.name" :value="option.id"></el-option>
                                                </el-select>
                                                <small class="form-control-feedback" v-if="row.errors.reference"
                                                    v-text="row.errors.reference[0]"></small>
                                            </div>

                                            <div class="form-group mb-0" :class="{ 'has-danger': row.errors.reference }"
                                                v-else-if="row.payment_method_type_id == '99'">
                                                <el-select v-model="row.reference" placeholder="Referencia Retención"
                                                    @change="changeRetention(index, $event)">
                                                    <el-option v-for="option in retentions" :key="option.id"
                                                        :label="option.name" :value="option.id"></el-option>
                                                </el-select>
                                                <small class="form-control-feedback" v-if="row.errors.reference"
                                                    v-text="row.errors.reference[0]"></small>
                                            </div>

                                            <div class="form-group mb-0" :class="{ 'has-danger': row.errors.reference }"
                                                v-else>
                                                <el-input v-model="row.reference" placeholder="Referencia y/o N° Operación"
                                                    :disabled="row.payment_received == '0'"></el-input>
                                                <small class="form-control-feedback" v-if="row.errors.reference"
                                                    v-text="row.errors.reference[0]"></small>
                                            </div>

                                        </td>
                                        <td>
                                            <div class="form-group mb-0">
                                                <el-upload :data="{ 'index': index }" :headers="headers" :multiple="false"
                                                    :on-remove="handleRemove" :action="`/finances/payment-file/upload`"
                                                    :show-file-list="true" :file-list="fileList" :on-success="onSuccess"
                                                    :limit="1">
                                                    <el-button slot="trigger" type="primary">Seleccione un
                                                        archivo</el-button>
                                                </el-upload>
                                            </div>
                                        </td>
                                        <td v-if="row.payment_method_type_id == '99'">
                                            <div class="form-group mb-0" :class="{ 'has-danger': row.errors.payment }">
                                                <el-input v-model="row.payment"
                                                    @change="changeRetentionInput(index, $event, row.payment_method_type_id, row.reference)"></el-input>
                                                <small class="form-control-feedback" v-if="row.errors.payment"
                                                    v-text="row.errors.payment[0]"></small>
                                            </div>
                                        </td>
                                        <td v-else-if="row.payment_method_type_id == '16'">
                                            <div class="form-group mb-0" :class="{ 'has-danger': row.errors.payment }">
                                                <el-input v-model="row.payment"
                                                    @change="changeCreditsInput(index, $event, row.payment_method_type_id, row.reference)"></el-input>
                                                <small class="form-control-feedback" v-if="row.errors.payment"
                                                    v-text="row.errors.payment[0]"></small>
                                            </div>
                                        </td>
                                        <td v-else-if="row.payment_method_type_id == '13'">
                                            <div class="form-group mb-0" :class="{ 'has-danger': row.errors.payment }">
                                                <el-date-picker v-model="row.postdated" type="date" :clearable="false"
                                                    format="dd/MM/yyyy" value-format="yyyy-MM-dd"
                                                    placeholder="Postfechado"></el-date-picker>
                                                <small class="form-control-feedback" v-if="row.errors.payment"
                                                    v-text="row.errors.payment[0]"></small>
                                            </div>
                                            <div class="form-group mb-0" :class="{ 'has-danger': row.errors.payment }">
                                                <el-input v-model="row.payment"></el-input>
                                                <small class="form-control-feedback" v-if="row.errors.payment"
                                                    v-text="row.errors.payment[0]"></small>
                                            </div>
                                        </td>
                                        <td v-else>
                                            <div class="form-group mb-0" :class="{ 'has-danger': row.errors.payment }">
                                                <el-input v-model="row.payment"
                                                    @change="changeAdvanceInput(index, $event, row.payment_method_type_id, row.reference)"></el-input>
                                                <small class="form-control-feedback" v-if="row.errors.payment"
                                                    v-text="row.errors.payment[0]"></small>
                                            </div>
                                        </td>
                                        <td class="series-table-actions text-right">
                                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                                @click.prevent="clickSubmit(index)">
                                                <i class="fa fa-check"></i>
                                            </button>
                                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                                @click.prevent="clickCancel(index)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-right">{{ this.title1 }}</td>
                                    <td class="text-right">{{ purchase.total_paid }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-right">{{ this.title2 }}</td>
                                    <td class="text-right">{{ purchase.total }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-right">{{ this.title3 }}</td>
                                    <td class="text-right">{{ purchase.total_difference }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="col-md-12 text-center pt-2" v-if="showAddButton && (purchase.total_difference > 0)">
                    <el-button type="primary" icon="el-icon-plus" @click="clickAddRow">Nuevo</el-button>
                </div>
            </div>
        </div>
    </el-dialog>
</template>

<script>

import { deletable } from '@mixins/deletable'

export default {
    props: ['showDialog', 'purchaseId', 'customerId', 'documentFeeId'],
    mixins: [deletable],
    data() {
        return {
            title: null,
            title1: 'TOTAL PAGADO',
            title2: 'TOTAL A PAGAR',
            title3: 'PENDIENTE DE PAGO',
            resource: 'purchase-payments',
            records: [],
            payment_destinations: [],
            payment_method_types: [],
            headers: headers_token,
            index_file: null,
            fileList: [],
            showAddButton: true,
            purchase: {},
            advances: [],
            retentions: [],
            index: null,
            credits: [],
        }
    },
    async created() {
        await this.initForm();
        await this.$http.get(`/${this.resource}/tables`)
            .then(response => {
                this.payment_destinations = response.data.payment_destinations
                this.payment_method_types = response.data.payment_method_types;
            })
    },
    methods: {
        clickDownloadFile(filename) {
            window.open(
                `/finances/payment-file/download-file/${filename}/purchases`,
                "_blank"
            );
        },
        addAdvancesCustomer() {

            this.$http.get(`/documents/advance/${this.customerId}`).then(
                response => {
                    this.advances = response.data.advances;
                    this.retentions = response.data.retentions;
                }
            )
            this.$http.get(`/cnp/list/${this.customerId}`).then(
                response => {
                    this.credits = response.data.credits;
                }
            );
        },
        changeRetention(index, id) {

            let selectedRetention = _.find(this.retentions, { 'id': id })
            let maxAmount = selectedRetention.valor

            let payment_count = this.form.payments.length;
            // let total = this.form.total;
            let total = this.getTotal()

            let payment = 0;
            let amount = _.round(total / payment_count, 2);

            if (maxAmount >= amount) {
                /* EL MONTO INGRESADO ESTA PERMITIDO */
            } else if (amount > maxAmount) {

                this.form.payments[index].payment = maxAmount
                let message = 'El monto maximo de la retencion es de ' + maxAmount
                this.$message.warning(message)
            }
        },
        changeRetentionInput(index, event, methodType, id) {
            let selectedRetention = _.find(this.retentions, { 'id': id })
            let payment_method_type = _.find(this.payment_method_types, { 'id': methodType });
            if (payment_method_type.id.includes('99')) {

                let maxAmount = selectedRetention.valor

                if (maxAmount >= event) {
                    /*EL VALOR INGRESADO EN PERMITIDO EN EL ANTICIPO */

                } else {
                    this.form.payments[index].payment = maxAmount
                    let message = 'El monto maximo de la retencion es de ' + maxAmount
                    this.$message.warning(message)

                }
            }
        },
        changeAdvanceInput(index, event, methodType, id) {

            let selectedAdvance = _.find(this.advances, { 'id': id })
            let payment_method_type = _.find(this.payment_method_types, { 'id': methodType });

            if (payment_method_type.description.includes('Anticipo')) {

                let maxAmount = selectedAdvance.valor

                if (maxAmount >= event) {
                    /*EL VALOR INGRESADO EN PERMITIDO EN EL ANTICIPO */
                } else {

                    this.records[index].payment = maxAmount
                    let message = 'El monto maximo del anticipo es de ' + maxAmount
                    this.$message.warning(message)

                }
            }
        },
        changeAdvance(index, id) {

            let selectedAdvance = _.find(this.advances, { 'id': id })
            let maxAmount = selectedAdvance.valor

            let payment_count = this.records.length;
            // let total = this.form.total;
            let total = this.purchase.total_difference;

            let payment = 0;
            let amount = _.round(total / payment_count, 2);

            if (maxAmount >= amount) {
                /* EL MONTO INGRESADO ESTA PERMITIDO */
                this.records[index].payment = amount

            } else if (amount > maxAmount) {

                this.records[index].payment = maxAmount
                let message = 'El monto maximo del anticipo es de ' + maxAmount
                this.$message.warning(message)
            }


        },
        changeCredits(index, id) {

            let selectedAdvance = _.find(this.credits, { 'id': id })
            let maxAmount = selectedAdvance.amount

            let payment_count = this.records.length;
            // let total = this.form.total;
            let total = this.purchase.total_difference;

            let payment = 0;
            let amount = _.round(total / payment_count, 2);

            if (maxAmount >= amount) {
                /* EL MONTO INGRESADO ESTA PERMITIDO */
                this.records[index].payment = amount

            } else if (amount > maxAmount) {

                this.records[index].payment = maxAmount
                let message = 'El monto maximo utilizable es de ' + maxAmount
                this.$message.warning(message)
            }


        },
        changeCreditsInput(index, event, methodType, id) {

            let selectedCredit = _.find(this.credits, { 'id': id })
            let payment_method_type = _.find(this.payment_method_types, { 'id': methodType });

            //if (payment_method_type.description.includes('Anticipo')) {

            let maxAmount = selectedCredit.amount

            if (maxAmount >= event) {
                /*EL VALOR INGRESADO EN PERMITIDO EN EL ANTICIPO */
            } else {

                this.records[index].payment = maxAmount
                let message = 'El monto maximo utilizable es de ' + maxAmount
                this.$message.warning(message)

            }
            //}
        },
        changePaymentMethodType(index) {

            let id = '01';

            if (this.records[index] !== undefined &&
                this.records[index].payment_method_type_id !== undefined) {
                id = this.records[index].payment_method_type_id;

            }
            let payment_method_type = _.find(this.payment_method_types, { 'id': id });

            if (payment_method_type.number_days) {

                this.form.date_of_due = moment(this.form.date_of_issue).add(payment_method_type.number_days, 'days').format('YYYY-MM-DD')
                // this.form.payments = []
                this.enabled_payments = false
                this.readonly_date_of_due = true
                this.form.payment_method_type_id = payment_method_type.id

                let date = moment(this.form.date_of_issue).add(payment_method_type.number_days, 'days').format('YYYY-MM-DD')

                // let date = moment()
                //     .add(payment_method_type.number_days, 'days')
                //     .format('YYYY-MM-DD')

                if (this.form.fee !== undefined) {
                    for (let index = 0; index < this.form.fee.length; index++) {
                        this.form.fee[index].date = date;
                    }
                }

            } else if (payment_method_type.id == '09') {

                this.form.payment_method_type_id = payment_method_type.id
                this.form.date_of_due = this.form.date_of_issue
                // this.form.payments = []
                this.enabled_payments = false

                this.$notify({
                    title: '',
                    message: 'Debes seleccionar una retencion disponible',
                    type: 'success'
                })

            } else if (payment_method_type.id == '14' || payment_method_type.id == '15') {

                this.$notify({
                    title: '',
                    message: 'Debes seleccionar un anticipo disponible',
                    type: 'success'
                })
            }
            else if (payment_method_type.id == '16') {

                this.$notify({
                    title: '',
                    message: 'Debes seleccionar una de las notas de crédito disponibles para canje',
                    type: 'success'
                })
                //this.records[index].payment_method_type_id_desc = 'Anticipo';
            }

        },
        onSuccess(response, file, fileList) {

            this.fileList = fileList

            if (response.success) {

                this.index_file = response.data.index
                this.records[this.index_file].filename = response.data.filename
                this.records[this.index_file].temp_path = response.data.temp_path

            } else {
                this.cleanFileList()
                this.$message.error(response.message)
            }

        },
        cleanFileList() {
            this.fileList = []
        },
        handleRemove(file, fileList) {

            this.records[this.index_file].filename = null
            this.records[this.index_file].temp_path = null
            this.fileList = []
            this.index_file = null

        },
        initForm() {
            this.records = [];
            this.fileList = [];
            this.showAddButton = true;
        },
        async getData() {
            if (this.documentFeeId) {
                this.title1 = "TOTAL PAGADO CUOTA"
                this.title2 = "TOTAL DOCUMENTO"
                this.title3 = "PENDIENTE DE PAGO CUOTA"
            }
            this.initForm();
            await this.$http.get(`/${this.resource}/purchase/${this.purchaseId}/${this.documentFeeId}`)
                .then(response => {
                    this.purchase = response.data;
                    this.title = 'Pagos de la compra: ' + this.purchase.number_full;
                });
            await this.$http.get(`/${this.resource}/records/${this.purchaseId}/${this.documentFeeId}`)
                .then(response => {
                    this.records = response.data.data
                    this.addAdvancesCustomer()
                });
            this.$eventHub.$emit('reloadDataToPay')

        },
        clickAddRow() {
            this.records.push({
                id: null,
                date_of_payment: moment().format('YYYY-MM-DD'),
                payment_method_type_id: null,
                payment_destination_id: null,
                reference: null,
                filename: null,
                temp_path: null,
                payment: 0,
                errors: {},
                loading: false
            });
            this.showAddButton = false;
        },
        clickCancel(index) {
            this.records.splice(index, 1);
            this.showAddButton = true;
            this.fileList = []
        },
        clickSubmit(index) {

            if (this.records[index].payment > parseFloat(this.purchase.total_difference)) {
                this.$message.error('El monto ingresado supera al monto pendiente de pago, verifique.');
                return;
            }

            let form = {
                id: this.records[index].id,
                purchase_id: this.purchaseId,
                date_of_payment: this.records[index].date_of_payment,
                payment_method_type_id: this.records[index].payment_method_type_id,
                payment_destination_id: this.records[index].payment_destination_id,
                reference: this.records[index].reference,
                filename: this.records[index].filename,
                temp_path: this.records[index].temp_path,
                payment: this.records[index].payment,
                fee_id: this.documentFeeId,
            }

            this.$http.post(`/${this.resource}`, form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.getData();
                        // this.initpurchaseTypes()
                        this.$eventHub.$emit('reloadData')
                        this.showAddButton = true;
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.records[index].errors = error.response.data;
                    } else {
                        this.$message.error(error.response.data.message)
                    }
                })
        },
        close() {
            this.$emit('update:showDialog', false);
        },
        clickDelete(id) {
            this.destroy(`/${this.resource}/${id}`).then(() => {
                this.getData()
                this.$eventHub.$emit('reloadData')
            }
            )
        },

        clickPrint(format, index) {
            this.index = index;
            window.open(`to-pay/print/${format}/${this.purchaseId}/${this.index}`, '_blank');
        }
    }
}
</script>
