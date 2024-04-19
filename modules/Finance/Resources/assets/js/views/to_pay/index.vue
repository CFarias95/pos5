<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">Cuentas por pagar</h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-12">
                        <section>
                            <div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="control-label">Usuario</label>
                                            <el-select v-model="form.user">
                                                <el-option v-for="option in users" :key="option.id" :value="option.id"
                                                    :label="option.name"></el-option>
                                            </el-select>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="control-label">Establecimiento</label>
                                            <el-select v-model="form.establishment_id">
                                                <el-option v-for="option in establishments" :key="option.id"
                                                    :value="option.id" :label="option.name"></el-option>
                                            </el-select>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="control-label">Periodo</label>
                                        <el-select v-model="form.period" @change="changePeriod">
                                            <el-option key="month" value="month" label="Por mes"></el-option>
                                            <el-option key="between_months" value="between_months"
                                                label="Entre meses"></el-option>
                                            <el-option key="date" value="date" label="Por fecha"></el-option>
                                            <el-option key="between_dates" value="between_dates"
                                                label="Entre fechas"></el-option>
                                            <el-option key="expired" value="expired"
                                                label="Fecha de vencimiento"></el-option>
                                            <el-option key="posdated" value="posdated" label="POSfechado"></el-option>
                                        </el-select>
                                    </div>
                                    <template v-if="form.period === 'month' || form.period === 'between_months'">
                                        <div class="col-md-3">
                                            <label class="control-label">Mes de</label>
                                            <el-date-picker v-model="form.month_start" type="month"
                                                @change="changeDisabledMonths" value-format="yyyy-MM" format="MM/yyyy"
                                                :clearable="false"></el-date-picker>
                                        </div>
                                    </template>
                                    <template v-if="form.period === 'between_months'">
                                        <div class="col-md-3">
                                            <label class="control-label">Mes al</label>
                                            <el-date-picker v-model="form.month_end" type="month"
                                                :picker-options="pickerOptionsMonths" value-format="yyyy-MM"
                                                format="MM/yyyy" :clearable="false"></el-date-picker>
                                        </div>
                                    </template>
                                    <template v-if="form.period === 'date' ||
                                        form.period === 'between_dates' ||
                                        form.period == 'expired' ||
                                        form.period == 'posdated'
                                        ">
                                        <div class="col-md-3">
                                            <label class="control-label">Fecha del</label>
                                            <el-date-picker v-model="form.date_start" type="date"
                                                @change="changeDisabledDates" value-format="yyyy-MM-dd" format="yyyy/MM/dd"
                                                :clearable="false"></el-date-picker>
                                        </div>
                                    </template>
                                    <template v-if="form.period === 'between_dates' ||
                                        form.period == 'expired' ||
                                        form.period == 'posdated'
                                        ">
                                        <div class="col-md-3">
                                            <label class="control-label">Fecha al</label>
                                            <el-date-picker v-model="form.date_end" type="date"
                                                :picker-options="pickerOptionsDates" value-format="yyyy-MM-dd"
                                                format="yyyy/MM/dd" :clearable="false"></el-date-picker>
                                        </div>
                                    </template>

                                    <div class="col-md-4">
                                        <label class="control-label">Proveedor</label>
                                        <el-select filterable clearable multiple v-model="form.supplier_id"
                                            placeholder="Seleccionar proveedor">
                                            <el-option v-for="item in suppliers" :key="item.id" :label="item.name"
                                                :value="item.id"></el-option>
                                        </el-select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="control-label">Importacion</label>
                                        <el-select filterable clearable v-model="form.import_id"
                                            placeholder="Seleccionar importacion">
                                            <el-option v-for="item in imports" :key="item.id" :label="item.name"
                                                :value="item.id"></el-option>
                                        </el-select>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="form-group">
                                            <label>Importe</label>
                                            <el-input @change="changeLiquidated" v-model="form.importe"
                                                clearable></el-input>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="form-group">
                                            <br />
                                            <el-checkbox v-model="form.include_liquidated"
                                                @change="changeLiquidated">Incluir Liquidadas</el-checkbox>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="padding-top:10px">
                                        <el-button type="primary" @click="loadToPay" class="mb-2">
                                            <i class="fa fa-search mr-2"></i>
                                            Buscar
                                        </el-button>
                                        <el-button class="submit mb-2" type="success" @click.prevent="clickOpen()">
                                            <i class="fa fa-file-excel"></i> Exportar Todo
                                        </el-button>

                                        <el-button v-if="records.length > 0" class="submit mb-2" type="success"
                                            @click.prevent="clickDownload('excel')">
                                            <i class="fa fa-file-excel"></i> Exportar Excel
                                        </el-button>

                                        <el-tooltip class="item" effect="dark" content="Reporte por formas de pago (Días)"
                                            placement="top-start">
                                            <el-button v-if="records.length > 0" class="submit mb-2" type="primary"
                                                @click.prevent="clickDownloadPaymentMethod()">
                                                <i class="fa fa-file-excel"></i> Formas de pago (Días)
                                            </el-button>
                                        </el-tooltip>

                                        <el-button v-if="records.length > 0" class="submit mb-2" type="danger"
                                            @click.prevent="clickDownload('pdf')">
                                            <i class="fa fa-file-pdf"></i> Exportar PDF
                                        </el-button>

                                        <el-button v-if="records.length > 0" class="submit" type="warning"
                                            @click.prevent="clickMultiPay()">
                                            <i class="fa fa-check-square-o"></i>
                                            Generar Pago Multiple
                                        </el-button>

                                    </div>
                                </div>
                                <div class="row mt-5 mb-3 text-right">
                                    <div class="col-md-1 text-right"></div>

                                    <div class="col-md-2 text-right">
                                        <el-badge :value="getTotalRowsUnpaid" class="item">
                                            <span size="small">Total Vencimientos</span>
                                        </el-badge>
                                    </div>
                                    <div class="col-md-2 text-right">
                                        <el-badge :value="getTotalAmountUnpaidUsd" class="item">
                                            <span size="small">Monto general (USD)</span>
                                        </el-badge>
                                    </div>
                                    <div class="col-md-2 text-right">
                                        <el-badge :value="getCurrentBalanceUsd" class="item">
                                            <span size="small">Saldo pendiente</span>
                                        </el-badge>
                                    </div>
                                    <div class="col-md-2 text-right">
                                        <el-badge :value="getCurrentBalanceMultipayUsd" class="item" type="warning">
                                            <span size="small">MultiCobros</span>
                                        </el-badge>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>P. Multi</th>
                                                <th>#</th>
                                                <th>F.Emisión</th>
                                                <th>F.Vencimiento</th>
                                                <th>Fecha Posfechado</th>
                                                <th>Ref. Posfechado</th>
                                                <th>Número/Secuencial</th>
                                                <th>Importación</th>
                                                <th># Cuota</th>
                                                <th>Proveedor</th>
                                                <th>Días de retraso</th>
                                                <th>Ver Cartera</th>
                                                <th>Moneda</th>
                                                <th class="text-right">Por pagar</th>
                                                <th class="text-right">Total</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template >
                                                <tr v-for="(row, index) in records" :key="index" :class="{ 'bg-success text-white': row.total_to_pay == 0 }">
                                                    <td>
                                                        <el-switch v-if="row.total_to_pay > 0" v-model="row.selected"></el-switch>
                                                    </td>
                                                    <td>{{ index + 1 }}</td>
                                                    <td>{{ row.date_of_issue }}</td>
                                                    <td>
                                                        {{
                                                            row.date_of_due
                                                            ? row.date_of_due
                                                            : "No tiene fecha de vencimiento."
                                                        }}
                                                    </td>
                                                    <td>{{ row.f_posdated ? row.f_posdated : "" }}</td>
                                                    <td>{{ row.posdated }}</td>
                                                    <td>{{ row.number_full }}</td>
                                                    <td>{{ row.import_number }}</td>
                                                    <td>C - {{ row.num_couta }}</td>
                                                    <td>{{ row.supplier_name }}</td>
                                                    <td>
                                                        {{
                                                            row.delay_payment < 0 ? row.delay_payment * -1
                                                            : "No tiene días atrasados." }} </td>
                                                    <td>
                                                        <el-popover placement="right" width="300" trigger="click">
                                                            <p>
                                                                Saldo actual:
                                                                <span class="custom-badge">{{ row.total_to_pay }}</span>
                                                            </p>
                                                            <p>
                                                                Fecha ultimo pago:
                                                                <span class="custom-badge">{{
                                                                    row.date_payment_last
                                                                    ? row.date_payment_last
                                                                    : "No registra pagos."
                                                                }}</span>
                                                            </p>

                                                            <p>
                                                                Dia de retraso en el pago:
                                                                <span class="custom-badge">{{
                                                                    row.delay_payment
                                                                    ? row.delay_payment
                                                                    : "No tiene días atrasados."
                                                                }}</span>
                                                            </p>

                                                            <p>
                                                                Fecha de vencimiento:
                                                                <span class="custom-badge">{{
                                                                    row.date_of_due
                                                                    ? row.date_of_due
                                                                    : "No tiene fecha de vencimiento."
                                                                }}</span>
                                                            </p>
                                                            <el-button icon="el-icon-view" slot="reference"></el-button>
                                                        </el-popover>
                                                    </td>
                                                    <td>{{ row.currency_type_id }}</td>
                                                    <td class="text-right text-danger">{{ row.total_to_pay }}</td>
                                                    <td class="text-right">{{ row.total }}</td>
                                                    <td class="text-right">
                                                        <template v-if="row.type === 'purchase'">
                                                            <button type="button" style="min-width: 41px"
                                                                class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                                                @click.prevent="
                                                                    clickPurchasePayment(
                                                                        row.fee_id,
                                                                        row.id,
                                                                        row.supplier_id
                                                                    )
                                                                    ">
                                                                Pagos
                                                            </button>
                                                        </template>
                                                        <template v-else>
                                                            <button type="button" style="min-width: 41px"
                                                                class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                                                @click.prevent="clickExpensePayment(row.id)">
                                                                Pagos
                                                            </button>
                                                        </template>
                                                        <template>
                                                            <button type="button" style="min-width: 41px"
                                                                v-if="row.total_to_pay > 0"
                                                                class="btn waves-effect waves-light btn-xs btn-primary m-1__2"
                                                                @click.prevent="clickPosFechado(row.fee_id, row.id)">
                                                                POSfechar
                                                            </button>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            <template #default>
                <el-dialog style="background-color: rgb(14 14 14 / 64%);" :show-close="false" :visible="this.showMultiPay"
                    title="Generar multipago" append-to-body align-center>
                    <el-form>
                        <el-form-item label="Fecha de pago">
                            <el-date-picker v-model="formMultiPay.date_of_payment" type="date" :clearable="false"
                                format="yyyy/MM/dd" value-format="yyyy-MM-dd"></el-date-picker>
                        </el-form-item>
                        <el-form-item label="Forma de pago">
                            <el-select v-model="formMultiPay.payment_method_type_id" :rules="[{ required: true, message: 'La forma de pago es obligatoria' }]" >
                                <el-option v-for="option in payment_method_types" v-show="option.id != '09'" :key="option.id"
                                    :value="option.id" :label="option.description"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="Referencia">
                            <el-input v-model="formMultiPay.reference"
                            :rules="[{ required: true, message: 'La referencia es obligatoria' }]"></el-input>

                        </el-form-item>
                        <el-form-item label="Destino">
                            <el-select v-model="formMultiPay.payment_destination_id" filterable :rules="[{ required: true, message: 'Destino es obligatorio' }]" >
                                <el-option v-for="option in payment_destinations" :key="option.id" :value="option.id"
                                    :label="option.description"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="Valor">
                            <el-input type="number" :step="0.01" :min="0" v-model="formMultiPay.payment" readonly></el-input>
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
                                    <tr v-for="(row, index) in formMultiPay.extras" :key="index" >
                                        <td>
                                            <el-select v-model="row.account_id" placeholder="Seleccione una cuenta contable" filterable clearable>
                                                <el-option v-for="account in accounts" :key="account.id" :label="account.description" :value="account.id" />
                                            </el-select>
                                        </td>
                                        <td>
                                            <el-input v-model="row.debe" type="number" :disabled="row.haber > 0" :step="0.01" :min="0" :max="999999999999999999999">
                                            </el-input>
                                        </td>
                                        <td>
                                            <el-input v-model="row.haber" type="number" :disabled="row.debe > 0" :step="0.01" :min="0" :max="999999999999999999999">
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
                                        <tr v-for="(row, index) in formMultiPay.unpaid" :key="index" :class="{
                                        'bg-success text-white': row.total_to_pay == 0,
                                        }">
                                            <td>
                                                {{ row.document }}
                                            </td>
                                            <td>
                                                {{ row.customer }}
                                            </td>
                                            <td>
                                                <el-input-number v-model="row.amount" :max="row.maxamount" :step="0.01"
                                                    :min="0.01" @change="changeInMultiPay"></el-input-number>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </el-form-item>

                    </el-form>
                    <template #footer>
                        <span class="dialog-footer">
                            <el-button type="danger" @click="cancelMultiPay()">Cancel</el-button>
                            <el-button :loading="loading_submit_multipay" v-if="formMultiPay.payment > 0" type="primary" @click="generateMultiPay()">
                                Generar
                            </el-button>
                        </span>
                    </template>
                </el-dialog>
            </template>
        </div>

        <purchase-payments :showDialog.sync="showDialogPurchasePayments" :purchaseId="recordId" :customerId="customerId"
            :external="true" :documentFeeId="feeID"></purchase-payments>

        <pos-fechado :showDialog.sync="showDialogPosFechado" :documentId="recordId" :documentFeeId="feeID"></pos-fechado>
        <expense-payments :showDialog.sync="showDialogExpensePayments" :expenseId="recordId"
            :external="true"></expense-payments>
    </div>
</template>

<script>
import ExpensePayments from "@viewsModuleExpense/expense_payments/payments.vue";
import PurchasePayments from "@viewsModulePurchase/purchase_payments/payments.vue";
import PosFechado from "@views/purchases/partials/posFechado.vue";
import DataTable from "../../components/DataTableWithoutPaging.vue";
import queryString from "query-string";

export default {
    components: { ExpensePayments, PurchasePayments, DataTable, PosFechado },
    data() {
        return {
            resource: "finances/to-pay",
            users: [],
            showDialogPosFechado: false,
            form: {},
            suppliers: [],
            recordId: null,
            customerId: null,
            feeID: null,
            records: [],
            establishments: [],
            loading_submit_multipay: false,
            pickerOptionsDates: {
                disabledDate: (time) => {
                    time = moment(time).format("YYYY-MM-DD");
                    return this.form.date_start > time;
                },
            },
            pickerOptionsMonths: {
                disabledDate: (time) => {
                    time = moment(time).format("YYYY-MM");
                    return this.form.month_start > time;
                },
            },
            showDialogPurchasePayments: false,
            showDialogExpensePayments: false,
            formMultiPay: {
                unpaid: [],
                extras: [],
                date_of_payment: moment().format('YYYY-MM-DD'),
                payment : 0,
                payment_method_type_id : '01',
                payment_destination_id : null,
                reference : 'N/A',
            },
            payment_method_types: [],
            payment_destinations: [],
            showMultiPay: false,
            multiPayArray: [],
            accounts:[],
            imports : [],
        };
    },
    async created() {
        this.$eventHub.$on("reloadDataToPay", () => {
            this.loadToPay();
        });

        await this.initForm();
        await this.filter();
        await this.changePeriod();
        this.form.supplier_id = [0];
    },
    computed: {
        getCurrentBalanceMultipayUsd() {
            const self = this;
            let source = [];

            source = _.filter(self.records, function (item) {
                return (
                    item.total_to_pay > 0 &&
                    item.selected == true
                );
            });

            return _.sumBy(source, function (item) {
                return parseFloat(item.total_to_pay);
            }).toFixed(2);
        },
        getCurrentBalance() {
            const self = this;
            let source = [];
            if (self.form.supplier_id) {
                source = _.filter(self.records, function (item) {
                    return (
                        item.total_to_pay > 0 &&
                        item.supplier_id == self.form.supplier_id &&
                        item.currency_type_id == "PEN"
                    );
                });
            } else {
                source = _.filter(this.records, function (item) {
                    return item.total_to_pay > 0 && item.currency_type_id == "PEN";
                });
            }

            return _.sumBy(source, function (item) {
                return parseFloat(item.total_to_pay);
            }).toFixed(2);
        },
        getCurrentBalanceUsd() {
            const self = this;
            let source = [];
            if (self.form.supplier_id) {
                source = _.filter(self.records, function (item) {
                    return (
                        item.total_to_pay > 0 &&
                        //item.supplier_id == self.form.supplier_id &&
                        item.currency_type_id == "USD"
                    );
                });
            } else {
                source = _.filter(this.records, function (item) {
                    return item.total_to_pay > 0 && item.currency_type_id == "USD";
                });
            }

            return _.sumBy(source, function (item) {
                return parseFloat(item.total_to_pay);
            }).toFixed(2);
        },
        getTotalRowsUnpaid() {
            const self = this;

            if (self.form.supplier_id) {
                return _.filter(self.records, function (item) {
                    return item.total_to_pay > 0 /*&& item.supplier_id == self.form.supplier_id*/;
                }).length;
            } else {
                return _.filter(this.records, function (item) {
                    return item.total_to_pay > 0;
                }).length;
            }
        },
        getTotalAmountUnpaid() {
            const self = this;
            let source = [];
            if (self.form.supplier_id) {
                source = _.filter(self.records, function (item) {
                    return (
                        item.total_to_pay > 0 &&
                        item.supplier_id == self.form.supplier_id &&
                        item.currency_type_id == "PEN"
                    );
                });
            } else {
                source = _.filter(this.records, function (item) {
                    return item.total_to_pay > 0 && item.currency_type_id == "PEN";
                });
            }

            return _.sumBy(source, function (item) {
                return parseFloat(item.total);
            }).toFixed(2);
        },
        getTotalAmountUnpaidUsd() {
            const self = this;
            let source = [];
            if (self.form.supplier_id) {
                source = _.filter(self.records, function (item) {
                    return (
                        item.total_to_pay > 0 &&
                        //item.supplier_id == self.form.supplier_id &&
                        item.currency_type_id == "USD"
                    );
                });
            } else {
                source = _.filter(this.records, function (item) {
                    return item.total_to_pay > 0 && item.currency_type_id == "USD";
                });
            }

            return _.sumBy(source, function (item) {
                return parseFloat(item.total);
            }).toFixed(2);
        },
    },

    methods: {
        clickDownloadPaymentMethod() {
            let query = queryString.stringify({
                ...this.form,
            });
            window.open(`/${this.resource}/report-payment-method-days/?${query}`, "_blank");
        },
        initForm() {
            this.form = {
                establishment_id: null,
                period: "between_dates",
                date_start: moment().format("YYYY-MM-DD"),
                date_end: moment().format("YYYY-MM-DD"),
                month_start: moment().format("YYYY-MM"),
                month_end: moment().format("YYYY-MM"),
                supplier_id: [],
                user: null,
                include_liquidated: false,
            };
        },
        filter() {
            this.$http.get(`/${this.resource}/filter`, this.form).then((response) => {
                this.establishments = response.data.establishments;
                this.suppliers = response.data.suppliers;
                this.users = response.data.users;
                this.form.establishment_id =
                this.establishments.length > 0 ? this.establishments[0].id : null;
                this.payment_method_types = response.data.payment_method_types;
                this.payment_destinations = response.data.payment_destinations;
                this.accounts = response.data.accounts;
                this.imports = response.data.imports;
            });
        },
        loadToPay() {
            this.$http.post(`/${this.resource}/records`, this.form).then((response) => {
                this.records = response.data.records;
                //console.log('to-pay records', this.records)
            });
        },
        clickPurchasePayment(feeID, recordId, customer) {
            this.recordId = recordId;
            this.customerId = customer;
            this.feeID = feeID;
            this.showDialogPurchasePayments = true;
        },
        clickExpensePayment(recordId) {
            this.recordId = recordId;
            this.showDialogExpensePayments = true;
        },
        clickDownloadDispatch(download) {
            window.open(download, "_blank");
        },
        clickDownload(type) {
            let query = queryString.stringify({
                ...this.form,
            });

            if (type == "pdf") {
                return window.open(`/${this.resource}/${type}?${query}`, "_blank");
            }

            window.open(`/${this.resource}/to-pay/?${query}`, "_blank");
        },
        clickOpen() {
            window.open(`/${this.resource}/to-pay-all`, "_blank");
        },
        changeDisabledDates() {
            if (this.form.date_end < this.form.date_start) {
                this.form.date_end = this.form.date_start;
            }
            this.loadToPay();
        },
        changeDisabledMonths() {
            if (this.form.month_end < this.form.month_start) {
                this.form.month_end = this.form.month_start;
            }
            this.loadToPay();
        },
        changeLiquidated() {
            this.loadToPay();
        },
        changePeriod() {
            if (this.form.period === "month") {
                this.form.month_start = moment().format("YYYY-MM");
                this.form.month_end = moment().format("YYYY-MM");
            }
            if (this.form.period === "between_months") {
                this.form.month_start = moment().startOf("year").format("YYYY-MM"); //'2019-01';
                this.form.month_end = moment().endOf("year").format("YYYY-MM");
            }
            if (this.form.period === "date") {
                this.form.date_start = moment().format("YYYY-MM-DD");
                this.form.date_end = moment().format("YYYY-MM-DD");
            }
            if (this.form.period === "between_dates") {
                this.form.date_start = moment().startOf("month").format("YYYY-MM-DD");
                this.form.date_end = moment().endOf("month").format("YYYY-MM-DD");
            }
        },
        //agregado 19-10-23
        clickPosFechado(feeID, recordId) {
            this.recordId = recordId;
            this.feeID = feeID;
            this.showDialogPosFechado = true;
        },

        //Add multipayment
        initFormMultiPay() {

            this.formMultiPay = {
                unpaid: [],
                extras: [],
                date_of_payment: moment().format('YYYY-MM-DD'),
                payment: 0,
                payment_method_type_id: '01',
                payment_destination_id: null,
                reference: 'N/A',
            }
        },
        clickMultiPay() {
            console.log('clickMultiPay show dialog')

            this.records.forEach(element => {
                if (element.selected) {
                    this.formMultiPay.unpaid.push({ document: element.number_full, document_id: element.id, fee_id: element.fee_id, maxamount: parseFloat(element.total_to_pay), amount: parseFloat(element.total_to_pay), customer_id: element.supplier_id, customer: element.supplier_name })
                }
            });
            this.formMultiPay.date_of_payment = moment().format('YYYY-MM-DD');
            this.changeInMultiPay()
            this.showMultiPay = true;
        },
        changeInMultiPay() {
            console.log('cambio de valores a liquidar')
            let total = 0;
            this.formMultiPay.unpaid.forEach(element => {
                total += element.amount;
            });

            this.formMultiPay.payment = _.round(total, 2);
        },
        cancelMultiPay() {

            this.showMultiPay = false;
            this.loadToPay();
            this.initFormMultiPay()
        },
        async generateMultiPay() {
            this.loading_submit_multipay = true
            await this.$http.post(`/${this.resource}/multipay`, this.formMultiPay)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.formMultiPay = [];
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .finally(()=>{
                    this.loadToPay();
                    this.loading_submit_multipay=false;
                    this.showMultiPay = false;
                    this.initFormMultiPay()
                });
        },
        addExtra() {

            this.formMultiPay.extras.push({
                account_id: null,
                debe: 0,
                haber: 0,
            });

        },
        deleteExtra(index) {
            this.formMultiPay.extras.splice(index, 1);
        }
    },
};
</script>
