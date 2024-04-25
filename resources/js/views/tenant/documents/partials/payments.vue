<template>
    <el-dialog :title="title" :visible="showDialog" @close="close" @open="getData" width="80%">
        <div class="form-body">
            <div class="row">
                <div class="col-md-12" v-if="records.length > 0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha de cobro</th>
                                    <th>Método de cobro <span class="text-danger">*</span></th>
                                    <th>Destino <span class="text-danger">*</span></th>
                                    <th class="text-center">Monto <span class="text-danger">*</span></th>
                                    <!-- <th>Referencia</th> -->
                                    <th>¿Pago recibido?</th>
                                    <template v-if="external">
                                        <th>Imprimir</th>
                                    </template>

                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, index) in records" :key="index"
                                    :class="{ 'text-danger border-left border-danger': (row.payment < 0), }">
                                    <template v-if="row.id">
                                        <td>{{ (row.multi_pay && row.multi_pay == 'SI')?'MULTICOBRO':'COBRO'}}-{{
                                            row.sequential }}</td>
                                        <td>{{ row.date_of_payment }}</td>
                                        <td>{{ row.payment_method_type_description }}</td>
                                        <td>{{ row.destination_description }}</td>
                                        <td class="text-center">{{ row.payment }}<br> {{ row.postdated ? row.postdated :
                                            ''
                                            }}
                                        </td>
                                        <td class="text-left">
                                            <!-- pagos que no cuenten con la opcion cobro recibido -->
                                            <template v-if="row.payment_received === null">
                                                <span class="d-block" v-if="row.reference"><b>Referencia:</b> {{
                                                    row.reference }}</span>
                                                <button type="button" v-if="row.filename"
                                                    class="btn waves-effect waves-light btn-xs btn-primary mb-2  mt-2"
                                                    @click.prevent="clickDownloadFile(row.filename)">
                                                    <i class="fas fa-fw fa-file-download"></i>
                                                    Descargar voucher
                                                </button>
                                                <!-- <el-button type="primary" @click="showDialogLinkPayment(row)">Link de cobro</el-button> -->
                                            </template>
                                            <!-- nuevo flujo -->
                                            <template v-else>

                                                <span class="d-block mb-2 font-bold">{{ row.payment_received_description
                                                    }}</span>

                                                <template v-if="row.payment_received">

                                                    <span class="d-block" v-if="row.reference"><b>Referencia:</b> {{
                                                        row.reference }}</span>
                                                    <button type="button" v-if="row.filename"
                                                        class="btn btn-sm btn-primary mb-2  mt-2"
                                                        @click.prevent="clickDownloadFile(row.filename)">
                                                        <i class="fas fa-fw fa-file-download"></i>
                                                        Descargar voucher
                                                    </button>

                                                </template>
                                                <template v-else>
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        @click="showDialogLinkPayment(row)">
                                                        <i class="fas fa-fw fa-link"></i>
                                                        Link de cobro
                                                    </button>
                                                </template>

                                            </template>

                                        </td>
                                        <td class="series-table-actions text-center">
                                            <button type="button"
                                                class="btn waves-effect waves-light btn-xs btn-primary"
                                                @click.prevent="clickOptionsPrint(row.id)"><i
                                                    class="fas fa-file-upload"></i></button>
                                        </td>
                                        <td class="series-table-actions text-right">
                                            <template v-if="permissions.delete_payment">
                                                <button type="button"
                                                    class="btn waves-effect waves-light btn-xs btn-danger"
                                                    @click.prevent="clickDelete(row.id)">Eliminar</button>
                                                <button v-if="row.payment > 0" type="button"
                                                    class="btn waves-effect waves-light btn-xs btn-info"
                                                    @click.prevent="clickReverse(row)">Reversar</button>
                                                <button v-if="row.payment > 0" type="button"
                                                    class="btn waves-effect waves-light btn-xs btn-warning"
                                                    @click.prevent="clickExpenses(row)">Gastos</button>
                                                <button v-if="row.payment > 0 && row.multi_pay == 'NO'" type="button"
                                                    class="btn waves-effect waves-light btn-xs btn-success"
                                                    @click.prevent="clickEdit(row)">Editar</button>
                                            </template>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td></td>
                                        <td>
                                            <div class="form-group mb-0"
                                                :class="{ 'has-danger': row.errors.date_of_payment }">
                                                <el-date-picker v-model="row.date_of_payment" type="date"
                                                    :clearable="false" format="dd/MM/yyyy"
                                                    value-format="yyyy-MM-dd"></el-date-picker>
                                                <small class="form-control-feedback" v-if="row.errors.date_of_payment"
                                                    v-text="row.errors.date_of_payment[0]"></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group mb-0"
                                                :class="{ 'has-danger': row.errors.payment_method_type_id }">
                                                <el-select v-model="row.payment_method_type_id"
                                                    @change="changePaymentMethodType(row.payment_method_type_id)">
                                                    <el-option v-for="option in payment_method_types"
                                                        v-show="option.id != '09'" :key="option.id" :value="option.id"
                                                        :label="option.description"></el-option>
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

                                        <!-- <td>
                                        <div class="form-group mb-0" :class="{'has-danger': row.errors.reference}">
                                            <el-input v-model="row.reference"></el-input>
                                            <small class="form-control-feedback" v-if="row.errors.reference" v-text="row.errors.reference[0]"></small>
                                        </div>
                                    </td> -->
                                        <td class="row no-gutters px-0">

                                            <div class="col-md-7">
                                                <div class="row no-gutters">
                                                    <div class="col-md-3">
                                                        <el-radio class="mb-3 pt-2" v-model="row.payment_received"
                                                            label="1">SI</el-radio>
                                                        <el-radio v-model="row.payment_received" label="0">NO</el-radio>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <el-upload :data="{ 'index': index }" :headers="headers"
                                                            :multiple="false" :on-remove="handleRemove"
                                                            :action="`/finances/payment-file/upload`"
                                                            :show-file-list="true" :file-list="fileList"
                                                            :on-success="onSuccess" :limit="1"
                                                            :disabled="row.payment_received == '0'" class="pb-1">

                                                            <template v-if="row.payment_received == '0'">
                                                                <el-button type="info" class="btn btn-sm">
                                                                    <i class="fas fa-fw fa-upload"></i>
                                                                    Cargar voucher
                                                                </el-button>
                                                            </template>
                                                            <template v-else>
                                                                <button type="button" class="btn btn-sm btn-primary"
                                                                    slot="trigger">
                                                                    <i class="fas fa-fw fa-upload"></i>
                                                                    Cargar voucher
                                                                </button>
                                                            </template>
                                                        </el-upload>
                                                        <template v-if="row.payment_received == '1'">
                                                            <el-button type="info" class="btn btn-sm">
                                                                <i class="fas fa-fw fa-link"></i>
                                                                Link de cobro
                                                            </el-button>
                                                        </template>
                                                        <template v-else>
                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                @click="showDialogLinkPayment(row)">
                                                                <i class="fas fa-fw fa-link"></i>
                                                                Link de cobro
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-5">
                                                <div class="form-group mb-0"
                                                    :class="{ 'has-danger': row.errors.reference }"
                                                    v-if="row.payment_method_type_id == '14' || row.payment_method_type_id == '15'">
                                                    <el-select v-model="row.reference" placeholder="Referencia Acticipo"
                                                        @change="changeAdvance(index, $event)">
                                                        <el-option v-for="option in advances" :key="option.id"
                                                            :label="'AT' + option.id + ' - ' + option.reference"
                                                            :value="option.id"></el-option>
                                                    </el-select>
                                                    <small class="form-control-feedback" v-if="row.errors.reference"
                                                        v-text="row.errors.reference[0]"></small>

                                                </div>
                                                <div class="form-group mb-0"
                                                    :class="{ 'has-danger': row.errors.reference }"
                                                    v-else-if="row.payment_method_type_id == '99'">
                                                    <el-select v-model="row.reference"
                                                        placeholder="Referencia retención"
                                                        @change="changeRetention(index, $event)">
                                                        <el-option v-for="option in retentions" :key="option.id"
                                                            :label="option.name" :value="option.id"></el-option>
                                                    </el-select>
                                                    <small class="form-control-feedback" v-if="row.errors.reference"
                                                        v-text="row.errors.reference[0]"></small>
                                                </div>
                                                <div class="form-group mb-0"
                                                    :class="{ 'has-danger': row.errors.reference }"
                                                    v-else-if="row.payment_method_type_id == '16'">
                                                    <el-select v-model="row.reference"
                                                        placeholder="Referencia retención"
                                                        @change="changeCredits(index, $event)">
                                                        <el-option v-for="option in credits" :key="option.id"
                                                            :label="option.name" :value="option.id"></el-option>
                                                    </el-select>
                                                    <small class="form-control-feedback" v-if="row.errors.reference"
                                                        v-text="row.errors.reference[0]"></small>
                                                </div>
                                                <div class="form-group mb-0"
                                                    :class="{ 'has-danger': row.errors.reference }" v-else>
                                                    <el-input v-model="row.reference"
                                                        placeholder="Referencia y/o N° Operación"
                                                        :disabled="row.payment_received == '0'"></el-input>
                                                    <small class="form-control-feedback" v-if="row.errors.reference"
                                                        v-text="row.errors.reference[0]"></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="series-table-actions text-right px-0">
                                            <button type="button" class="btn waves-effect waves-light btn-sm btn-info"
                                                @click.prevent="clickSubmit(index)">
                                                <i class="fa fa-check d-block"></i>
                                            </button>

                                            <button type="button" class="btn waves-effect waves-light btn-sm btn-danger"
                                                @click.prevent="clickCancel(index)">
                                                <i class="fa fa-trash d-block"></i>
                                            </button>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-right">{{ title1 }}</td>
                                    <td class="text-right">{{ document.total_paid }}</td>
                                </tr>
                                <tr v-if="document.credit_notes_total">
                                    <td colspan="6" class="text-right">TOTAL NOTA CRÉDITO</td>
                                    <td class="text-right">{{ document.credit_notes_total }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-right">{{ title2 }}</td>
                                    <td class="text-right">{{ document.total }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-right">{{ title3 }}</td>
                                    <td class="text-right">{{ document.total_difference }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="col-md-12 text-center pt-2" v-if="showAddButton && (document.total_difference > 0)">
                    <template v-if="permissions.create_payment">
                        <el-button type="primary" icon="el-icon-plus" @click="clickAddRow">Nuevo</el-button>
                    </template>
                </div>
            </div>
            <template #default>
                <el-dialog style="background-color: rgb(14 14 14 / 64%);" :show-close="false"
                    :visible="this.showOverPayment" title="Generar con sobre pago" append-to-body align-center>
                    <el-form>
                        <el-form-item label="Valor extra">
                            <el-input v-model="formSubmit.overPaymentValue" autocomplete="off" readonly />
                        </el-form-item>
                        <el-form-item label="Generar anticipo">
                            <el-switch v-model="formSubmit.overPaymentAdvance">
                                <template #active-action>
                                    <span class="custom-active-action">T</span>
                                </template>
                                <template #inactive-action>
                                    <span class="custom-inactive-action">F</span>
                                </template>
                            </el-switch>
                        </el-form-item>

                        <el-form-item v-if="formSubmit.overPaymentAdvance == false" label="Cuenta Contable">
                            <el-select v-model="formSubmit.overPaymentAccount"
                                placeholder="Seleccione una cuenta contable" filterable clearable>
                                <el-option v-for="account in accounts" :key="account.id" :label="account.description"
                                    :value="account.id" />
                            </el-select>
                        </el-form-item>

                    </el-form>
                    <template #footer>
                        <span class="dialog-footer">
                            <el-button type="danger" @click="cancelOverPayment()">Cancel</el-button>
                            <el-button type="primary" @click="generateWithOverPayment()">
                                Generar
                            </el-button>
                        </span>
                    </template>
                </el-dialog>
            </template>
            <template #default>
                <el-dialog style="background-color: rgb(14 14 14 / 64%);" :show-close="false"
                    :visible="this.showReverse" title="Generar el reverso del cobro" append-to-body align-center>
                    <el-form>
                        <el-form-item label="Cobro a reversar">
                            <el-input v-model="formSubmit.id" autocomplete="off" readonly />
                        </el-form-item>
                        <el-form-item label="Motivo">
                            <el-input v-model="formSubmit.reference" autocomplete="off" />
                        </el-form-item>
                    </el-form>
                    <template #footer>
                        <span class="dialog-footer">
                            <el-button type="danger" @click="cancelReverse()">Cancel</el-button>
                            <el-button type="primary" @click="generateReverse()">
                                Generar
                            </el-button>
                        </span>
                    </template>
                </el-dialog>
            </template>
            <template #default>
                <el-dialog style="background-color: rgb(14 14 14 / 64%);" :show-close="false"
                    :visible="this.showExpense" title="Generar gasto del cobro" append-to-body align-center>
                    <el-form>
                        <el-form-item label="Gasto al cobro">
                            <el-input v-model="formSubmit.id" autocomplete="off" readonly />
                        </el-form-item>
                        <el-form-item label="Valor extra">
                            <el-input v-model="formSubmit.overPaymentValue" autocomplete="off" />
                        </el-form-item>
                        <el-form-item label="Cuenta Contable">
                            <el-select v-model="formSubmit.overPaymentAccount"
                                placeholder="Seleccione una cuenta contable" filterable clearable>
                                <el-option v-for="account in accounts" :key="account.id" :label="account.description"
                                    :value="account.id" />
                            </el-select>
                        </el-form-item>
                    </el-form>
                    <template #footer>
                        <span class="dialog-footer">
                            <el-button type="danger" @click="cancelExpenses()">Cancel</el-button>
                            <el-button type="primary" @click="generateExpenses()">
                                Generar
                            </el-button>
                        </span>
                    </template>
                </el-dialog>
            </template>
            <template #default>
                <el-dialog style="background-color: rgb(14 14 14 / 64%);" :show-close="true"
                    :visible="this.showEdit" title="Editar cobro" append-to-body align-center>
                    <table class="table" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha de cobro</th>
                                <th>Método de cobro <span class="text-danger">*</span></th>
                                <th>Destino <span class="text-danger">*</span></th>
                                <th class="text-center">Monto <span class="text-danger">*</span></th>
                                <th>Referencia</th>
                                <template v-if="external">
                                    <th>Acciones</th>
                                </template>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ (editRow.multi_pay && editRow.multi_pay == 'SI')?'MULTICOBRO':'COBRO'}}-{{
                                            editRow.sequential }}</td>
                                <td>
                                    <div class="form-group mb-0">
                                        <el-input v-model="editRow.date_of_payment" readonly></el-input>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group mb-0">
                                        <el-input readonly v-model="editRow.payment_method_type_description"></el-input>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group mb-0">
                                        <el-input readonly v-model="editRow.destination_description"></el-input>
                                    </div>
                                </td>
                                <td v-if="editRow.payment_method_type_id == '99'">
                                    <div class="form-group mb-0">
                                        <el-input v-model="editRow.payment"
                                            @change="changeRetentionInput(index, $event, editRow.payment_method_type_id, editRow.reference)"></el-input>

                                    </div>
                                </td>
                                <td v-else-if="editRow.payment_method_type_id == '16'">
                                    <div class="form-group mb-0">
                                        <el-input v-model="editRow.payment"
                                            @change="changeCreditsInput(index, $event, editRow.payment_method_type_id, editRow.reference)"></el-input>

                                    </div>
                                </td>
                                <td v-else-if="editRow.payment_method_type_id == '13'">
                                    <div class="form-group mb-0">
                                        <el-date-picker v-model="editRow.postdated" type="date" :clearable="false"
                                            format="dd/MM/yyyy" value-format="yyyy-MM-dd"
                                            placeholder="Postfechado"></el-date-picker>

                                    </div>
                                    <div class="form-group mb-0">
                                        <el-input v-model="editRow.payment"></el-input>
                                    </div>
                                </td>
                                <td v-else>
                                    <div class="form-group mb-0" >
                                        <el-input type="number" v-model="editRow.payment"></el-input>
                                    </div>
                                </td>
                                <td class="row no-gutters px-0">
                                    <div class="col-md-12">
                                        <div class="form-group mb-0"
                                            v-if="editRow.payment_method_type_id == '14' || editRow.payment_method_type_id == '15'">
                                            <el-select v-model="editRow.reference" placeholder="Referencia Acticipo"
                                                @change="changeAdvance(index, $event)">
                                                <el-option v-for="option in advances" :key="option.id"
                                                    :label="'AT' + option.id + ' - ' + option.reference"
                                                    :value="option.id"></el-option>
                                            </el-select>
                                        </div>
                                        <div class="form-group mb-0"
                                            v-else-if="editRow.payment_method_type_id == '99'">
                                            <el-select v-model="editRow.reference" placeholder="Referencia retención"
                                                @change="changeRetention(index, $event)">
                                                <el-option v-for="option in retentions" :key="option.id"
                                                    :label="option.name" :value="option.id"></el-option>
                                            </el-select>

                                        </div>
                                        <div class="form-group mb-0"
                                            v-else-if="editRow.payment_method_type_id == '16'">
                                            <el-select v-model="editRow.reference" placeholder="Referencia retención"
                                                @change="changeCredits(index, $event)">
                                                <el-option v-for="option in credits" :key="option.id"
                                                    :label="option.name" :value="option.id"></el-option>
                                            </el-select>

                                        </div>
                                        <div class="form-group mb-0"
                                            v-else>
                                            <el-input v-model="editRow.reference" placeholder="Referencia y/o N° Operación"
                                                :disabled="editRow.payment_received == '0'"></el-input>

                                        </div>
                                    </div>
                                </td>
                                <td class="series-table-actions text-right px-0">
                                    <button type="button" class="btn waves-effect waves-light btn-sm btn-info"
                                        @click.prevent="clickSaveEdit(editRow)">
                                        <i class="fa fa-check d-block"></i>
                                    </button>

                                    <button type="button" class="btn waves-effect waves-light btn-sm btn-danger"
                                        @click.prevent="clickCancelEdit">
                                        <i class="fa fa-times d-block"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </el-dialog>

            </template>
        </div>

        <dialog-link-payment :documentPaymentId="documentPayment.id" :currencyTypeId="document.currency_type_id"
            :exchangeRateSale="document.exchange_rate_sale" :payment="documentPayment.payment"
            :showDialog.sync="showDialogLink" :documentPayment="documentPayment">
        </dialog-link-payment>

        <document-options :recordId="this.payment_id" :showDialogOptions.sync="showDialogOptions"
            :showClose="showDialogClose" :type="this.type" :configuration="this.configuration"
            :monto="this.monto"></document-options>

    </el-dialog>
</template>

<script>

import { deletable } from '../../../../mixins/deletable'
import DialogLinkPayment from './dialog_link_payment'
import DocumentOptions from '../../../../../../modules/Finance/Resources/assets/js/views/unpaid/partials/options'
export default {
    props: ['showDialog', 'documentId', 'external', 'configuration', 'customerId', 'documentFeeId'],
    mixins: [deletable],
    components: {
        DialogLinkPayment,
        DocumentOptions
    },
    data() {
        return {
            title: null,
            title1: 'TOTAL COBRADO',
            title2: 'TOTAL A PAGAR',
            title3: 'PENDIENTE DE COBRO',
            resource: 'document_payments',
            records: [],
            payment_destinations: [],
            headers: headers_token,
            fileList: [],
            payment_method_types: [],
            showAddButton: true,
            document: {},
            permissions: {},
            index_file: null,
            documentPayment: {},
            showDialogLink: false,
            showDialogOptions: false,
            showDialogClose: false,
            type: 'document',
            advances: [],
            retentions: [],
            index: null,
            index_id: null,
            monto: 0,
            credits: [],
            showOverPayment: false,
            valorOverPayment: 0,
            advanceOverPayment: false,
            accounts: [],
            indexSelected: null,
            showReverse: false,
            showExpense: false,
            payment_id: null,
            formSubmit: {
                id: null,
                document_id: null,
                date_of_payment: null,
                payment_method_type_id: null,
                payment_destination_id: null,
                reference: null,
                filename: null,
                temp_path: null,
                payment: null,
                payment_received: null,
                fee_id: this.documentFeeId,
                date_of_due: null,
                postdated: null,
                overPayment: false,
                overPaymentValue: 0,
                overPaymentAdvance: false,
                overPaymentAccount: null,
            },
            showEdit:false,
            editRow:[],
        }
    },
    async created() {
        await this.initForm();
        await this.$http.get(`/${this.resource}/tables`)
            .then(response => {
                this.payment_method_types = response.data.payment_method_types;
                this.payment_destinations = response.data.payment_destinations
                this.permissions = response.data.permissions
                this.accounts = response.data.accounts
                //this.initDocumentTypes()
            })
        await this.events();
    },
    methods: {
        events() {
            this.$eventHub.$on('reloadDataPayments', () => {
                this.getData()
            })
        },
        addAdvancesCustomer() {

            this.$http.get(`/documents/advance/${this.customerId}/${this.documentId}`).then(
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
        getObjectResponse(success, message = null) {
            return {
                success: success,
                message: message,
            }
        },
        validateDataPayment(row) {

            if (!row.payment_destination_id) return this.getObjectResponse(false, 'El campo destino es obligatorio.')

            if (!row.payment_method_type_id) return this.getObjectResponse(false, 'El campo método de cobro es obligatorio.')

            if (!row.payment || row.payment <= 0 || isNaN(row.payment)) return this.getObjectResponse(false, 'El campo monto es obligatorio y debe ser mayor que 0.')

            return this.getObjectResponse(true)

        },
        showDialogLinkPayment(row) {

            if (!row.id) {
                const validate_data_payment = this.validateDataPayment(row)
                if (!validate_data_payment.success) return this.$message.error(validate_data_payment.message)
            }

            this.showDialogLink = true
            this.documentPayment = row
            this.documentPayment.document_id = this.documentId

        },
        clickDownloadFile(filename) {
            window.open(
                `/finances/payment-file/download-file/${filename}/documents`,
                "_blank"
            );
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
            this.showOverPayment = false,
                this.valorOverPayment = 0,
                this.advanceOverPayment = false,
                this.records = [];
            this.fileList = [];
            this.showAddButton = true;
            this.editRow = {
                id : null,
                date_of_payment : moment().format('YYYY-MM-DD'),
                payment_method_type_id : null,
                payment_destination_id : null,
                reference : null,
                filename : null,
                temp_path : null,
                payment : parseFloat(this.document.total_difference),
                // payment: 0,
                errors : {},
                loading : false,
                payment_received : '1',
                }
            },
        async getData() {
            this.initForm();
            if (this.documentFeeId) {
                this.title1 = "TOTAL COBRADO CUOTA"
                this.title2 = "TOTAL DOCUMENTO"
                this.title3 = "PENDIENTE DE COBRO CUOTA"
            }
            await this.$http.get(`/${this.resource}/records/${this.documentId}/${this.documentFeeId}`)
                .then(response => {
                    console.log(`/${this.resource}/records/${this.documentId}/${this.documentFeeId}`, response.data);
                    this.records = response.data.data
                }).then(

                    this.$http.get(`/${this.resource}/document/${this.documentId}/${this.documentFeeId}`)
                        .then(response => {
                            console.log(`/${this.resource}/document/${this.documentId}/${this.documentFeeId}`, response.data);
                            this.document = response.data;
                            this.title = 'Cobros del comprobante: ' + this.document.number_full;
                        })
                );

            //await
            this.addAdvancesCustomer();
            //this.$eventHub.$emit('reloadDataUnpaid')

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
                payment: parseFloat(this.document.total_difference),
                // payment: 0,
                errors: {},
                loading: false,
                payment_received: '1',
            });

            this.showAddButton = false;
        },
        clickCancel(index) {
            this.records.splice(index, 1);
            this.fileList = []
            this.showAddButton = true;
        },
        validateOverPayment(index) {

            this.formSubmit.overPaymentValue = _.round(this.records[index].payment - parseFloat(this.document.total_difference), 2);
            this.formSubmit.overPayment = true;
            this.formSubmit.overPaymentAdvance = true;
            this.formSubmit.overPaymentAccount = null;
            this.indexSelected = index;
            this.showOverPayment = true;
            console.log('El valor de cobro adicional es de: ', this.formSubmit.overPaymentValue)

            //this.records[index].payment = parseFloat(this.document.total_difference)
        },
        generateWithOverPayment() {

            if (this.formSubmit.overPaymentAdvance == false && this.formSubmit.overPaymentAccount == null) {

                this.$message.error('Debe seleccionar una cuenta contable');
                return;

            } else {

                this.formSubmit.overPayment = true;
                this.records[this.indexSelected].payment = parseFloat(this.document.total_difference);
                this.showOverPayment = false;
                this.clickSubmit(this.indexSelected);

            }
        },
        cancelOverPayment() {

            this.formSubmit.overPaymentValue = 0;
            this.formSubmit.overPayment = false;
            this.indexSelected = null;
            this.showOverPayment = false;

        },
        clickSubmit(index) {

            if (this.records[index].payment > parseFloat(this.document.total_difference)) {
                this.validateOverPayment(index);

                return;
            }

            this.formSubmit.id = this.records[index].id
            this.formSubmit.document_id = this.documentId
            this.formSubmit.date_of_payment = this.records[index].date_of_payment
            this.formSubmit.payment_method_type_id = this.records[index].payment_method_type_id
            this.formSubmit.payment_destination_id = this.records[index].payment_destination_id
            this.formSubmit.reference = this.records[index].reference
            this.formSubmit.filename = this.records[index].filename
            this.formSubmit.temp_path = this.records[index].temp_path
            this.formSubmit.payment = this.records[index].payment
            this.formSubmit.payment_received = this.records[index].payment_received
            this.formSubmit.fee_id = this.documentFeeId
            this.formSubmit.date_of_due = moment().format('YYYY-MM-DD')
            this.formSubmit.postdated = this.records[index].postdated

            this.$http.post(`/${this.resource}`, this.formSubmit)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.getData();
                        // this.initDocumentTypes()
                        this.showAddButton = true;
                        this.$eventHub.$emit('reloadData')
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
        changeAdvance(index, id) {

            let selectedAdvance = _.find(this.advances, { 'id': id })
            let maxAmount = selectedAdvance.valor

            let payment_count = this.records.length;
            // let total = this.form.total;
            let total = this.document.total_difference;

            let payment = 0;
            let amount = this.records[index].payment //_.round(total / payment_count, 2);

            if (maxAmount >= amount) {
                /* EL MONTO INGRESADO ESTA PERMITIDO */
            } else if (amount > maxAmount) {

                this.records[index].payment = maxAmount
                let message = 'El monto maximo del anticipo es de ' + maxAmount
                this.$message.warning(message)
            }
        },
        changePaymentMethodType(index) {

            let id = index;

            let payment_method_type = _.find(this.payment_method_types, { 'id': index });

            if (payment_method_type.number_days) {

                this.enabled_payments = false
                this.readonly_date_of_due = true

                let date = moment().add(payment_method_type.number_days, 'days').format('YYYY-MM-DD')

                if (this.form.fee !== undefined) {
                    for (let index = 0; index < this.form.fee.length; index++) {
                        this.form.fee[index].date = date;
                    }
                }

            } else if (payment_method_type.id == '99') {

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

            } else if (payment_method_type.id == '16') {

                this.$notify({
                    title: '',
                    message: 'Debes seleccionar una de las notas de crédito disponibles para el canje',
                    type: 'success'
                })
            }

        },
        close() {
            this.$emit('update:showDialog', false);
            // this.initDocumentTypes()
            // this.initForm()
        },
        clickDelete(id) {
            this.destroy(`/${this.resource}/${id}`).then(() => {
                this.getData()
                this.$eventHub.$emit('reloadData')
                // this.initDocumentTypes()
            }
            )
        },
        clickReverse(row) {
            console.log('ROW enviado', row)
            this.showReverse = true
            this.formSubmit.id = row.id
            this.formSubmit.reference = row.reference

        },
        clickExpenses(row) {

            console.log('ROW Expanse', row)
            this.showExpense = true
            this.formSubmit.id = row.id
            this.formSubmit.overPaymentValue = 0

        },
        cancelExpenses() {
            this.showExpense = false
            this.formSubmit.id = null
            this.formSubmit.reference = null
        },
        generateExpenses() {

            this.$http.post(`/${this.resource}/expenses`, this.formSubmit).then(() => {
                this.showExpense = false
                this.getData()
                this.$eventHub.$emit('reloadData')
            }
            )

        },
        generateReverse() {

            this.$http.post(`/${this.resource}/reverse`, this.formSubmit).then(() => {
                this.showReverse = false
                this.getData()
                this.$eventHub.$emit('reloadData')
            }
            )
        },
        cancelReverse() {
            this.showReverse = false
            this.formSubmit.id = null
            this.formSubmit.reference = null
        },
        clickDownloadReport(id) {
            window.open(`/${this.resource}/report/${this.documentId}`, '_blank');
        },
        clickPrint(external_id) {
            window.open(`/finances/unpaid/print/${external_id}/document`, '_blank');
        },
        clickOptionsPrint(row_id) {
            //this.monto = this.records[key].payment
            console.log('Paymnet a imprimir: ', row_id)
            this.payment_id = row_id
            //this.index = key
            //this.index_id = row_id
            this.showDialogOptions = true
            this.showDialogClose = true
        },
        changeRetention(index, id) {

            let selectedRetention = _.find(this.retentions, { 'id': id })
            let maxAmount = selectedRetention.valor

            let payment_count = this.records.length;
            // let total = this.form.total;
            let total = parseFloat(this.document.total_difference);

            let payment = 0;
            let amount = _.round(total / payment_count, 2);

            if (maxAmount >= amount) {
                /* EL MONTO INGRESADO ESTA PERMITIDO */
            } else if (amount > maxAmount) {

                this.records[index].payment = maxAmount
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

                    if (event > parseFloat(this.document.total_difference)) {

                        this.records[index].payment = parseFloat(this.document.total_difference);
                        let message = 'El monto maximo de la retencion es de ' + this.document.total_difference
                        this.$message.warning(message)

                    }

                } else {

                    if (event > parseFloat(this.document.total_difference)) {

                        this.records[index].payment = parseFloat(this.document.total_difference);
                        let message = 'El monto maximo de la retencion es de ' + this.document.total_difference
                        this.$message.warning(message)

                    } else {

                        this.records[index].payment = maxAmount;
                        let message = 'El monto maximo de la retencion es de ' + maxAmount
                        this.$message.warning(message)

                    }


                }
            }
        },
        changeCredits(index, id) {

            let selectedAdvance = _.find(this.credits, { 'id': id })
            let maxAmount = selectedAdvance.amount

            let payment_count = this.records.length;
            // let total = this.form.total;
            let total = this.document.total_difference;

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
        clickEdit(paymnet){
            console.log('clickEdit',paymnet)
            this.editRow = paymnet;
            console.log('editRow',this.editRow)
            this.showEdit = true;
        },
        clickSaveEdit(row){
            console.log('clickSaveEdit',row)
            this.$http.post(`/${this.resource}/update`, row)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success("Registro actualizado correctamente");
                        this.initForm();
                        this.showEdit = false
                        this.getData();
                    }else{
                        this.$message.error(response.data.message);
                    }
                });
        },
        clickCancelEdit(){
            this.showEdit = false;
            this.getData();
        }
    }
}
</script>
