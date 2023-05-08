<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">Editar Compra</h3>
        </div>
        <div class="tab-content">
            <form autocomplete="off" @submit.prevent="submit">
                <div class="form-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group" :class="{'has-danger': errors.document_type_intern}">
                                <label class="control-label">Tipo comprobante Interno</label>
                                <el-select v-model="form.document_type_intern" @change="changeDocumentType2">
                                    <el-option v-for="option in document_types2" :key="option.idType" :value="option.idType"
                                               :label="option.description"></el-option>
                                </el-select>
                                <small class="form-control-feedback" v-if="errors.document_type_intern"
                                       v-text="errors.document_type_intern[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group" :class="{'has-danger': errors.document_type_id}">
                                <label class="control-label">Tipo comprobante SRI</label>
                                <el-select v-model="form.document_type_id" :disabled="true" @change="changeDocumentType">
                                    <el-option v-for="option in document_types" :key="option.id" :value="option.id"
                                               :label="option.description"></el-option>
                                </el-select>
                                <small class="form-control-feedback" v-if="errors.document_type_id"
                                       v-text="errors.document_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div :class="{'has-danger': errors.codSustento}"
                                 class="form-group">
                                <label class="control-label">Código Tributario</label>
                                <el-select v-model="form.codSustento" :required="is_countable">
                                    <el-option v-for="option in codSustentos"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.codSustento"></el-option>
                                </el-select>
                                <small v-if="errors.codSustento"
                                       class="form-control-feedback"
                                       v-text="errors.codSustento[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group" :class="{'has-danger': errors.series}">
                                <label class="control-label">Serie</label>
                                <el-input v-model="form.series" :maxlength="4" @input="inputSeries" :disabled="true"></el-input>

                                <small class="form-control-feedback" v-if="errors.series"
                                       v-text="errors.series[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group" :class="{'has-danger': errors.number}">
                                <label class="control-label">Número</label>
                                <el-input v-model="form.number" :disabled="true"></el-input>

                                <small class="form-control-feedback" v-if="errors.number"
                                       v-text="errors.number[0]"></small>
                            </div>
                        </div>


                        <div class="col-lg-2">
                            <div class="form-group" :class="{'has-danger': errors.date_of_issue}">
                                <label class="control-label">Fec Emisión</label>
                                <el-date-picker v-model="form.date_of_issue" :readonly="readonly_date_of_due"
                                                type="date" value-format="yyyy-MM-dd" :disabled="true" :clearable="false"
                                                @change="changeDateOfIssue"></el-date-picker>
                                <small class="form-control-feedback" v-if="errors.date_of_issue"
                                       v-text="errors.date_of_issue[0]"></small>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <div class="form-group" :class="{'has-danger': errors.date_of_due}">
                                <label class="control-label">Fec. Vencimiento</label>
                                <el-date-picker v-model="form.date_of_due" type="date" :readonly="readonly_date_of_due"
                                                value-format="yyyy-MM-dd" :clearable="false"></el-date-picker>
                                <small class="form-control-feedback" v-if="errors.date_of_due"
                                       v-text="errors.date_of_due[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group" :class="{'has-danger': errors.currency_type_id}">
                                <label class="control-label">Moneda</label>
                                <el-select v-model="form.currency_type_id" @change="changeCurrencyType">
                                    <el-option v-for="option in currency_types" :key="option.id" :value="option.id"
                                               :label="option.description"></el-option>
                                </el-select>
                                <small class="form-control-feedback" v-if="errors.currency_type_id"
                                       v-text="errors.currency_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-2" v-if="form.currency_type_id != configuration.currency_type_id">
                            <div :class="{'has-danger': errors.exchange_rate_sale}"
                                 class="form-group">
                                <label class="control-label">Tipo de cambio
                                    <el-tooltip class="item"
                                                content="Tipo de cambio del día"
                                                effect="dark"
                                                placement="top-end">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <el-input v-model="form.exchange_rate_sale" disabled></el-input>
                                <small v-if="errors.exchange_rate_sale"
                                       class="form-control-feedback"
                                       v-text="errors.exchange_rate_sale[0]"></small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group" :class="{'has-danger': errors.supplier_id}">
                                <label class="control-label">
                                    Proveedor
                                    <a href="#" @click.prevent="showDialogNewPerson = true">[+ Nuevo]</a>
                                </label>
                                <el-select v-model="form.supplier_id" filterable @change="changeSupplier"
                                           ref="select_person" @keyup.native="keyupSupplier"
                                           @keyup.enter.native="keyupEnterSupplier">
                                    <el-option v-for="option in suppliers" :key="option.id" :value="option.id"
                                               :label="option.description"></el-option>
                                </el-select>
                                <small class="form-control-feedback" v-if="errors.supplier_id"
                                       v-text="errors.supplier_id[0]"></small>
                            </div>
                        </div>

                        <div class="col-lg-2"
                             v-if="purchase_order_id === null">
                            <div class="form-group">
                                <label>
                                    Orden de compra
                                </label>
                                <el-select v-model="form.purchase_order_id"
                                           :loading="loading_search"
                                           clearable
                                           filterable
                                           placeholder="Número de documento"
                                           >
                                    <!--
                                    :remote-method="searchPurchaseOrder"
                                    remote-->
                                    <el-option v-for="option in purchase_order_data"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.id"></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="form-group col-sm-12 col-md-6 col-lg-4 "
                            :class="{ 'has-danger': errors.created_at }"
                            >
                            <label class="control-label">
                                Observaciones
                            </label>
                            <el-input v-model="form.observation"
                                      placeholder="Observaciones"></el-input>
                        </div>
                        <!--JOINSOFTWARE-->
                        <div class="form-group col-sm-12 col-md-6 col-lg-2"
                            :class="{'has-danger': errors.sequential_number}"
                            >
                            <label class="control-label">Secuencial</label>

                            <el-input
                                v-model="form.sequential_number"
                                :maxlength="15"
                                :required="is_countable"
                                show-word-limit
                                dusk="sequential_number">
                            </el-input>

                            <small v-if="errors.sequential_number"
                                    class="form-control-feedback"
                                    v-text="errors.sequential_number[0]"></small>
                        </div>
                        <!--JOINSOFTWARE-->
                        <div class="form-group col-sm-12 col-md-6 col-lg-4"
                            :class="{'has-danger': errors.auth_number}"
                            >
                            <label class="control-label">Número autorización</label>

                            <el-input
                                v-model="form.auth_number"
                                :maxlength="49"
                                :required="is_countable"
                                show-word-limit
                                dusk="auth_number">
                            </el-input>

                            <small v-if="errors.auth_number"
                                    class="form-control-feedback"
                                    v-text="errors.auth_number[0]"></small>
                        </div>

                        <div class="col-lg-2">
                            <div :class="{'has-danger': errors.import_id}"
                                 class="form-group">
                                <label class="control-label">Importacion</label>
                                <el-select v-model="form.import_id" >
                                    <el-option v-for="option in imports"
                                               :key="option.id"
                                               :label="option.numeroImportacion"
                                               :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.import_id"
                                       class="form-control-feedback"
                                       v-text="errors.imports_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div :class="{'has-danger': errors.tipo_doc_id}"
                                 class="form-group">
                                <label class="control-label">Tipo documento</label>
                                <el-select v-model="form.tipo_doc_id" >
                                    <el-option v-for="option in type_docs"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.tipo_doc_id"
                                       class="form-control-feedback"
                                       v-text="errors.tipo_doc_id[0]"></small>
                            </div>
                        </div>
                        <div class="form-group col-sm-12 col-md-6 col-lg-2"
                            :class="{'has-danger': errors.afected_document}"
                            v-if="is_credit_note">
                            <label class="control-label">Documento Afectado</label>
                            <el-input
                                v-model="form.afected_document"
                                dusk="afected_document"
                                :required="is_credit_note"
                                >
                            </el-input>
                            <small v-if="errors.afected_document"
                                    class="form-control-feedback"
                                    v-text="errors.afected_document[0]"></small>
                        </div>

                        <div class="col-12">&nbsp;</div>

                         <div class="col-md-8 mt-2">
                            <div class="form-group">
                                <el-checkbox v-model="form.is_aproved"
                                            >¿Desea Autorizar las retenciones de esta compra?
                                </el-checkbox>
                            </div>
                        </div>

                        <div class="col-md-8 mt-4">
                            <div class="form-group">
                                <el-checkbox v-model="form.has_client" @change="changeHasClient">¿Desea agregar el
                                    cliente para esta compra?
                                </el-checkbox>
                            </div>
                        </div>

                        <div class="col-md-8 mt-2 mb-2">
                            <div class="form-group">
                                <el-checkbox v-model="form.has_payment" @change="changeHasPayment">¿Desea agregar pagos
                                    a esta compra?
                                </el-checkbox>
                            </div>
                        </div>


                        <div class="col-md-8 mt-2 mb-2" v-if="configuration.enabled_global_igv_to_purchase">
                            <div class="form-group">
                                <el-checkbox v-model="localHasGlobalIgv"
                                             :disabled="(form.items.length != 0 && configuration.enabled_global_igv_to_purchase)"
                                             @change="changeHasGlobalIgv">¿La compra tiene igv?
                                    <el-tooltip class="item"
                                                content="Al estar la configuracion activa, sobreescribe el igv del item. Si no esta checado, el producto no tendra igv."
                                                effect="dark"
                                                placement="top-end">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </el-checkbox>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6" v-if="form.has_client">
                            <div class="form-group">
                                <label class="control-label">
                                    Clientes
                                </label>

                                <el-select v-model="form.customer_id" filterable remote
                                           popper-class="el-select-customers" clearable
                                           placeholder="Nombre o número de documento"
                                           :remote-method="searchRemotePersons"
                                           :loading="loading_search">
                                    <el-option v-for="option in customers" :key="option.id" :value="option.id"
                                               :label="option.description"></el-option>
                                </el-select>

                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <template v-if="form.has_payment">

                            <div class="col-lg-2 col-md-2">
                                <div :class="{'has-danger': errors.payment_condition_id}"
                                     class="form-group">
                                    <label class="control-label">Condición de pago</label>
                                    <el-select v-model="form.payment_condition_id"
                                               @change="changePaymentCondition">
                                        <el-option v-for="option in payment_conditions"
                                                   :key="option.id"
                                                   :label="option.name"
                                                   :value="option.id"></el-option>
                                    </el-select>
                                    <small v-if="errors.payment_condition_id"
                                           class="form-control-feedback"
                                           v-text="errors.payment_condition_id[0]"></small>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-12 mt-2">
                                <!-- Contado -->
                                <template v-if="form.payment_condition_id === '01'">
                                    <table>
                                        <thead>
                                        <tr width="100%">
                                            <th v-if="form.payments.length>0"
                                                class="pb-2">Forma de pago
                                            </th>
                                            <th v-if="form.payments.length>0"
                                                class="pb-2">Desde
                                                <el-tooltip class="item"
                                                            content="Aperture caja o cuentas bancarias"
                                                            effect="dark"
                                                            placement="top-start">
                                                    <i class="fa fa-info-circle"></i>
                                                </el-tooltip>
                                            </th>
                                            <th v-if="form.payments.length>0"
                                                class="pb-2">Referencia/Anticipo
                                            </th>
                                            <th v-if="form.payments.length>0"
                                                class="pb-2">Monto
                                            </th>
                                            <th width="15%"><a class="text-center font-weight-bold text-info"
                                                            href="#"
                                                            @click.prevent="clickAddPayment">[+ Agregar]</a>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(row, index) in form.payments"
                                            :key="index">
                                            <td>
                                                <div class="form-group mb-2 mr-2">
                                                    <el-select v-model="row.payment_method_type_id"
                                                            @change="changePaymentMethodType(index)">
                                                        <el-option v-for="option in cashPaymentMethod"
                                                                :key="option.id"
                                                                :label="option.description"
                                                                :value="option.id"></el-option>
                                                    </el-select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group mb-2 mr-2">
                                                    <el-select v-model="row.payment_destination_id"
                                                            filterable>
                                                        <el-option v-for="option in payment_destinations"
                                                                :key="option.id"
                                                                :label="option.description"
                                                                :value="option.id"></el-option>
                                                    </el-select>
                                                </div>
                                            </td>
                                            <td v-if="!row.payment_method_type_id_desc">
                                                <el-input
                                                    v-model="row.reference"></el-input>

                                            </td>
                                            <td v-else>
                                                <el-select
                                                    v-model="row.reference"
                                                    @change="changeAdvance(index,$event)">
                                                    <el-option
                                                        v-for="option in advances"
                                                        :key="option.id"
                                                        :label="option.id"
                                                        :value="option.id"></el-option>
                                                </el-select>
                                            </td>
                                            <td>
                                                <el-input v-model="row.payment"  @change="changeAdvanceInput(index,$event,row.payment_method_type_id,row.reference)"></el-input>
                                            </td>
                                            <td class="series-table-actions text-center">
                                                <button class="btn waves-effect waves-light btn-xs btn-danger"
                                                        type="button"
                                                        @click.prevent="clickCancel(index)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                            <br>
                                        </tr>
                                        </tbody>
                                    </table>
                                </template>

                                <!-- Credito -->
                                <template v-else-if="form.payment_condition_id === '02'">
                                    <table v-if="form.fee.length>0">
                                        <thead>
                                        <tr width="100%">
                                            <th class="pb-2" v-if="form.fee.length>0"
                                            >Método de pago
                                            </th>
                                            <th class="pb-2"
                                            >Fecha
                                            </th>
                                            <th class="pb-2"
                                            >Monto
                                            </th>
                                            <th class="pb-2"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(row, index) in form.fee"
                                            :key="index">
                                            <td>
                                                <el-select
                                                    v-model="row.payment_method_type_id"
                                                    @change="changePaymentMethodType(index)">
                                                    <el-option
                                                        v-for="option in creditPaymentMethod"
                                                        :key="option.id"
                                                        :label="option.description"
                                                        :value="option.id"
                                                    ></el-option>
                                                </el-select>
                                            </td>
                                            <td>
                                                <el-date-picker
                                                    v-model="row.date"
                                                    :clearable="false"
                                                    format="dd/MM/yyyy"
                                                    type="date"
                                                    :readonly="readonly_date_of_due"
                                                    value-format="yyyy-MM-dd"></el-date-picker>
                                            </td>
                                            <td>
                                                <el-input v-model="row.amount"></el-input>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </template>

                                <!-- Crédito con cuotas -->
                                <template v-else>
                                    <table v-if="form.fee.length > 0">
                                        <thead>
                                        <tr width="100%">
                                            <th class="pb-2">Fecha
                                            </th>
                                            <th class="pb-2">Monto
                                            </th>
                                            <th class="pb-2"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(row, index) in form.fee"
                                            :key="index">
                                            <td>
                                                <el-date-picker v-model="row.date"
                                                                :clearable="false"
                                                                format="dd/MM/yyyy"
                                                                type="date"
                                                                value-format="yyyy-MM-dd"></el-date-picker>
                                            </td>
                                            <td>
                                                <el-input v-model="row.amount"></el-input>
                                            </td>
                                            <td class="text-center">
                                                <button v-if="index > 0"
                                                        class="btn waves-effect waves-light btn-xs btn-danger"
                                                        type="button"
                                                        @click.prevent="clickRemoveFee(index)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">
                                                <label class="control-label">
                                                    <a class=""
                                                       href="#"
                                                       @click.prevent="clickAddFee"><i
                                                        class="fa fa-plus font-weight-bold text-info"></i>
                                                        <span style="color: #777777">Agregar cuota</span></a>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="row">

                        <div class="col-lg-12 col-md-6 d-flex align-items-end mt-4">
                            <div class="form-group">
                                <button type="button" class="btn waves-effect waves-light btn-primary"
                                        @click.prevent="showDialogAddItem = true">+ Agregar Producto
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row" v-if="form.items.length > 0">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Descripción</th>
                                        <th>Almacén</th>
                                        <th>Lote</th>
                                        <th class="text-center">Unidad</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">Valor Unitario</th>
                                        <th class="text-right">Precio Unitario</th>
                                        <th class="text-right">Descuento</th>
                                        <th class="text-right">Cargo</th>
                                        <th class="text-right">Total</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, index) in form.items" :key="index">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{
                                                row.item.description
                                            }}<br/><small>{{ row.affectation_igv_type.description }}</small></td>
                                        <td class="text-left">{{ getWarehouseDescription(row) }}</td>
                                        <!-- <td class="text-left">{{ (row.warehouse_description) ? row.warehouse_description : row.warehouse.description  }}</td> -->
                                        <td class="text-left">{{ row.lot_code }}</td>
                                        <td class="text-center">{{ row.item.unit_type_id }}</td>
                                        <td class="text-right">{{ row.quantity }}</td>
                                        <!-- <td class="text-right">{{ currency_type.symbol }} {{ row.unit_price }}</td> -->
                                        <td class="text-right">{{ currency_type.symbol }}
                                            {{ getFormatUnitPriceRow(row.unit_value) }}
                                        </td>
                                        <td class="text-right">{{ currency_type.symbol }}
                                            {{ getFormatUnitPriceRow(row.unit_price) }}
                                        </td>
                                        <td class="text-right">{{ currency_type.symbol }} {{ row.total_discount }}</td>
                                        <td class="text-right">{{ currency_type.symbol }} {{ row.total_charge }}</td>
                                        <td class="text-right">{{ currency_type.symbol }} {{ row.total }}</td>
                                        <td class="text-right">
                                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                                    @click.prevent="clickRemoveItem(index)">x
                                            </button>
                                            <button class="btn waves-effect waves-light btn-xs btn-info"
                                                type="button"
                                                @click="ediItem(row, index)">
                                            <span style='font-size:10px;'>&#9998;</span></button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <p v-if="form.total_ret > 0"
                               class="text-left" >RETENCIONES</p>
                            <div class="table-responsive" v-if="form.total_ret > 0">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tipo</th>
                                            <th>Código</th>
                                            <th>Descripción</th>
                                            <th>Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, index) in form.ret"
                                            :key="index">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ row.tipo}}</td>
                                            <td>{{ row.code}}</td>
                                            <td>{{ row.desciption}}</td>
                                            <td>{{ row.valor}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <div class="col-md-6">

                            <!-- descuentos -->

                            <div class="row mt-1 mb-2"  v-if="form.total_discount > 0">

                                <div class="col-lg-10 float-right">
                                    <label class="float-right control-label">

                                        <el-tooltip class="item"
                                            :content="global_discount_type.description"
                                            effect="dark"
                                            placement="top">
                                            <i class="fa fa-info-circle"></i>
                                        </el-tooltip>

                                        DESCUENTO {{ is_amount ? 'MONTO' : '%' }}
                                        <el-checkbox v-model="is_amount" class="ml-1 mr-1" @change="changeTypeDiscount"></el-checkbox>
                                        :
                                    </label>
                                </div>

                                <div class="col-lg-2 float-right">
                                    <el-input-number v-model="total_global_discount"
                                                        :min="0"
                                                        class="input-custom"
                                                        controls-position="right"
                                                        @change="changeTotalGlobalDiscount"></el-input-number>
                                </div>

                            </div>

                            <!-- descuentos -->

                            <p v-if="form.total_exportation > 0"
                               class="text-right">OP.EXPORTACIÓN: {{ currency_type.symbol }}
                                                                 {{ form.total_exportation }}</p>
                            <p v-if="form.total_free > 0"
                               class="text-right">OP.GRATUITAS: {{ currency_type.symbol }} {{
                                    form.total_free
                                                          }}</p>
                            <p v-if="form.total_unaffected > 0"
                               class="text-right">SUBTOTAL 0%: {{ currency_type.symbol }}
                                                                {{ form.total_unaffected }}</p>
                            <p v-if="form.total_exonerated > 0"
                               class="text-right">OP.EXONERADAS: {{ currency_type.symbol }}
                                                                {{ form.total_exonerated }}</p>
                            <p v-if="form.total_taxed > 0"
                               class="text-right">SUBTOTAL 12%: {{ currency_type.symbol }} {{
                                    form.total_taxed
                                                           }}</p>
                            <p v-if="form.total_igv > 0"
                               class="text-right">IVA: {{ currency_type.symbol }} {{ form.total_igv }}</p>

                            <p v-if="form.total_isc > 0"
                               class="text-right">ISC: {{ currency_type.symbol }} {{ form.total_isc }}</p>

                            <p v-if="form.total_ret > 0"
                               class="text-right" >RETENIDO: {{ currency_type.symbol }} {{ form.total_ret }}</p>

                            <p v-if="form.total_discount > 0" class="text-right">DESCUENTOS TOTALES: {{ currency_type.symbol }} {{ form.total_discount }}</p>

                            <h3 v-if="form.total > 0"
                                class="text-right"><b>TOTAL COMPRAS: </b>{{ currency_type.symbol }} {{ form.total }}
                            </h3>

                            <template v-if="is_perception_agent">
                                <hr>
                                <div class="row mt-1">
                                    <div class="col-lg-10 float-right">
                                        <label class="float-right control-label">NÚMERO PERCEPCIÓN: </label>
                                    </div>
                                    <div class="col-lg-2 float-right">
                                        <div :class="{'has-danger': errors.perception_number}"
                                             class="form-group">
                                            <el-input v-model="form.perception_number"></el-input>
                                            <small v-if="errors.perception_number"
                                                   class="form-control-feedback"
                                                   v-text="errors.perception_number[0]"></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-1">
                                    <div class="col-lg-10 float-right">
                                        <label class="float-right control-label">FEC EMISIÓN PERCEPCIÓN: </label>
                                    </div>
                                    <div class="col-lg-2 float-right">
                                        <div :class="{'has-danger': errors.perception_date}"
                                             class="form-group">
                                            <el-date-picker v-model="form.perception_date"
                                                            :clearable="false"
                                                            type="date"
                                                            value-format="yyyy-MM-dd"
                                                            @change="changeDateOfIssue"></el-date-picker>

                                            <small v-if="errors.perception_date"
                                                   class="form-control-feedback"
                                                   v-text="errors.perception_date[0]"></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-1">
                                    <div class="col-lg-10 float-right">
                                        <label class="float-right control-label">IMPORTE PERCEPCIÓN: </label>
                                    </div>
                                    <div class="col-lg-2 float-right">
                                        <div :class="{'has-danger': errors.total_perception}"
                                             class="form-group">
                                            <el-input v-model="form.total_perception"
                                                      :readonly="true"
                                                      @input="inputTotalPerception"></el-input>

                                            <small v-if="errors.total_perception"
                                                   class="form-control-feedback"
                                                   v-text="errors.total_perception[0]"></small>
                                        </div>
                                    </div>
                                </div>
                                <h3 v-if="form.total > 0 && !hide_button"
                                    class="text-right"><b>MONTO TOTAL : </b>{{ currency_type.symbol }} {{ total_amount }}
                                </h3>
                            </template>
                        </div>

                    </div>
                </div>
                <div class="form-actions text-right mt-4">
                    <el-button @click.prevent="close()">Cancelar</el-button>
                    <el-button type="primary" native-type="submit" :loading="loading_submit"
                               v-if="form.items.length > 0 && !hide_button">Guardar cambios
                    </el-button>
                </div>
            </form>
        </div>

        <purchase-form-item :showDialog.sync="showDialogAddItem"
                            :currency-type-id-active="form.currency_type_id"
                            :exchange-rate-sale="form.exchange_rate_sale"
                            :record-item="recordItem"
                            :localHasGlobalIgv="localHasGlobalIgv"
                            :percentage-igv="percentage_igv"
                            @add="addRow"></purchase-form-item>

        <person-form :showDialog.sync="showDialogNewPerson"
                     type="suppliers"
                     :input_person="input_person"
                     :external="true"></person-form>

        <purchase-options :type="type" :showDialog.sync="showDialogOptions"
                          :recordId="purchaseNewId"
                          :showClose="false"></purchase-options>
    </div>
</template>
<script>

import PurchaseFormItem from './partials/item.vue'
import PersonForm from '../persons/form.vue'
import PurchaseOptions from './partials/options.vue'
import {functions, exchangeRate, fnPaymentsFee} from '../../../mixins/functions'
import {calculateRowItem} from '../../../helpers/functions'

export default {
    props: {
        'resourceId': {
            required: true,
            default: 0
        }
    },
    components: {PurchaseFormItem, PersonForm, PurchaseOptions},
    mixins: [functions, exchangeRate, fnPaymentsFee],
    data() {
        return {
            input_person: {},
            type: 'edit',
            recordItem: null,
            resource: 'purchases',
            maxLength1: null,
            maxLength2: null,
            showDialogAddItem: false,
            showDialogNewPerson: false,
            showDialogOptions: false,
            loading_submit: false,
            hide_button: false,
            is_perception_agent: false,
            errors: {},
            form: {},
            aux_supplier_id: null,
            total_amount: 0,
            document_types: [],
            currency_types: [],
            discount_types: [],
            charges_types: [],
            payment_method_types: [],
            all_suppliers: [],
            suppliers: [],
            all_customers: [],
            customers: [],
            company: null,
            operation_types: [],
            establishment: {},
            all_series: [],
            payment_destinations: [],
            payment_conditions: [],
            series: [],
            loading_search: false,
            currency_type: {},
            readonly_date_of_due: false,
            configuration: {},
            purchaseNewId: null,
            localHasGlobalIgv: false,
            warehouses: [],
            retention_types_income: [],
            retention_types_iva: [],
            retencionesActuales: [],
            imports:[],
            type_docs:[],
            codSustentos:[],
            codSustentos_all:[],
            haveRetentions: false,
            advances:[],

            is_countable:false,
            is_credit_note:false,
        }
    },
    async created() {
        await this.initForm()
        await this.$http.get(`/${this.resource}/tables`)
            .then(response => {

                this.document_types = response.data.document_types_invoice
                this.currency_types = response.data.currency_types
                this.establishment = response.data.establishment
                this.all_suppliers = response.data.suppliers
                this.discount_types = response.data.discount_types
                this.payment_method_types = response.data.payment_method_types
                this.payment_destinations = response.data.payment_destinations
                this.all_customers = response.data.customers
                this.configuration = response.data.configuration
                this.payment_conditions = response.data.payment_conditions
                this.warehouses = response.data.warehouses

                this.retention_types_iva = response.data.retention_types_iva
                this.retention_types_income = response.data.retention_types_income
                this.imports = response.data.imports
                this.type_docs = response.data.typeDocs
                this.codSustentos_all = response.data.codSustentos
                this.document_types2 = response.data.typeDocs2
                //console.log('tipos de documentos Local: ',response.data.typeDocs2)

                this.charges_types = response.data.charges_types
                this.form.currency_type_id = (this.currency_types.length > 0) ? this.currency_types[0].id : null
                this.form.establishment_id = (this.establishment.id) ? this.establishment.id : null
                //this.form.document_type_id = (this.document_types.length > 0) ? this.document_types[0].id : null
                //this.form.document_type_intern = (this.document_types2.length > 0 )? this.document_types2[0].id : null

                this.retention_types_income = response.data.retention_types_income
                this.retention_types_iva = response.data.retention_types_iva


                this.changeDateOfIssue()
                //this.changeDocumentType()
                //this.changeDocumentType2()
                this.changeCurrencyType()

            })

        this.$eventHub.$on('reloadDataPersons', (supplier_id) => {
            this.reloadDataSuppliers(supplier_id)
        })

        this.$eventHub.$on('initInputPerson', () => {
            this.initInputPerson()
        })

        await this.filterCustomers()
        await this.changeHasPayment()
        await this.changeHasClient()
        this.initGlobalIgv()

        this.initRecord()
        //this.changeDocumentType2()
        //this.addRetentions()
    },
    computed: {
        creditPaymentMethod: function () {
            return _.filter(this.payment_method_types, {'is_credit': true})
        },
        cashPaymentMethod: function () {
            return _.filter(this.payment_method_types, {'is_credit': false})
        },
        isCreditPaymentCondition: function () {
            return ['02', '03'].includes(this.form.payment_condition_id)
        },
    },
    methods: {
        getWarehouse(id) {
            return _.find(this.warehouses, {id: id})
        },
        getWarehouseDescription(row) {

            let description = null

            if (row.warehouse_description) {
                description = row.warehouse_description
            } else if (row.warehouse) {
                description = row.warehouse.description
            } else {
                const warehouse = this.getWarehouse(row.warehouse_id)
                if (warehouse) description = warehouse.description
            }

            return description
        },
        changeHasGlobalIgv() {

        },
        changeHasPayment() {

            if (!this.form.has_payment) {
                this.form.payments = []
                this.form.fee = []
                this.form.payment_condition_id = '01'
            } else {
                this.changePaymentCondition()
            }

        },
        changeHasClient() {

            if (!this.form.has_client) {
                this.form.customer_id = null
            }
        },
        searchRemotePersons(input) {

            if (input.length > 1) {

                this.loading_search = true
                let parameters = `input=${input}`

                this.$http.get(`/reports/data-table/persons/customers?${parameters}`)
                    .then(response => {
                        this.customers = response.data.persons
                        this.loading_search = false

                        if (this.customers.length == 0) {
                            this.filterCustomers()
                        }
                    })
            } else {
                this.filterCustomers()
            }

        },
        filterCustomers() {
            this.customers = this.all_customers
        },
        getFormatUnitPriceRow(unit_price) {
            return _.round(unit_price, 6)
            // return unit_price.toFixed(6)
        },
        async validate_payments() {

            let error_by_item = 0
            let acum_total = 0
            let q_affectation_free = 0

            await this.form.payments.forEach((item) => {
                acum_total += parseFloat(item.payment)
                if (item.payment <= 0 || item.payment == null) error_by_item++;
            })

            //determinate affectation igv
            await this.form.items.forEach((item) => {
                if (item.affectation_igv_type.free) {
                    q_affectation_free++
                }
            })

            let all_free = (q_affectation_free == this.form.items.length) ? true : false

            if (!all_free && (acum_total > parseFloat(this.form.total) || error_by_item > 0)) {
                return {
                    success: false,
                    message: 'Los montos ingresados superan al monto a pagar o son incorrectos'
                }
            }

            if (this.form.has_client && !this.form.customer_id) {
                return {
                    success: false,
                    message: 'Debe seleccionar un cliente'
                }
            }

            if (this.form.has_payment) {

                if (this.form.payment_condition_id === '01' && this.form.payments.length == 0) {

                    return {
                        success: false,
                        message: 'Debe registrar al menos un pago'
                    }

                }

                if (this.isCreditPaymentCondition && this.form.fee.length == 0) {

                    return {
                        success: false,
                        message: 'Debe registrar al menos una cuota'
                    }

                }
            }

            return {
                success: true,
                message: null
            }
        },
        clickCancel(index) {
            this.form.payments.splice(index, 1);
            this.calculatePayments()
        },
        clickAddPayment() {

            this.form.payments.push({
                id: null,
                purchase_id: null,
                date_of_payment: moment().format('YYYY-MM-DD'),
                payment_method_type_id: '01',
                reference: null,
                payment_destination_id: this.getPaymentDestinationId(),
                payment: 0,
            });

            this.calculatePayments()

        },
        setTotalDefaultPayment() {

            if (this.form.payments.length > 0) {

                this.form.payments[0].payment = this.form.total
            }
        },
        getPaymentDestinationId() {

            if (this.configuration.destination_sale && this.payment_destinations.length > 0) {

                let cash = _.find(this.payment_destinations, {id: 'cash'})

                return (cash) ? cash.id : this.payment_destinations[0].id

            }

            return null

        },
        initInputPerson() {
            this.input_person = {
                number: '',
                identity_document_type_id: ''
            }
        },
        keyupEnterSupplier() {

            if (this.input_person.number) {

                if (!isNaN(parseInt(this.input_person.number))) {

                    switch (this.input_person.number.length) {
                        case 8:
                            this.input_person.identity_document_type_id = '1'
                            this.showDialogNewPerson = true
                            break;

                        case 11:
                            this.input_person.identity_document_type_id = '6'
                            this.showDialogNewPerson = true
                            break;
                        default:
                            this.input_person.identity_document_type_id = '6'
                            this.showDialogNewPerson = true
                            break;
                    }
                }
            }
        },
        keyupSupplier(e) {

            if (e.key !== "Enter") {

                this.input_person.number = this.$refs.select_person.$el.getElementsByTagName('input')[0].value
                let exist_persons = this.suppliers.filter((supplier) => {
                    let pos = supplier.description.search(this.input_person.number);
                    return (pos > -1)
                })

                this.input_person.number = (exist_persons.length == 0) ? this.input_person.number : null
            }

        },
        inputSeries() {

            const pattern = new RegExp('^[A-Z0-9]+$', 'i');
            if (!pattern.test(this.form.series)) {
                this.form.series = this.form.series.substring(0, this.form.series.length - 1);
            } else {
                this.form.series = this.form.series.toUpperCase()
            }

        },
        setCurrencyType() {
            this.currency_type = _.find(this.currency_types, {'id': this.form.currency_type_id})
        },
        async initRecord() {
            await this.$http.get(`/${this.resource}/record/${this.resourceId}`)
                .then(response => {
                    //console.log('PURCHASE DATA: ',response.data.data.purchase)
                    let dato = response.data.data.purchase
                    //console.log('RESPONSE: ',dato)
                    this.form.id = dato.id
                    this.form.document_type_intern = dato.document_type_intern
                    this.form.document_type_id = dato.document_type_id
                    this.form.series = dato.series
                    this.form.number = dato.number
                    this.form.date_of_due = dato.date_of_due
                    this.form.date_of_issue = dato.date_of_issue
                    this.form.supplier_id = dato.supplier_id
                    this.aux_supplier_id = dato.supplier_id
                    this.form.payment_method_type_id = dato.purchase_payments.payment_method_type_id
                    this.form.currency_type_id = dato.currency_type_id
                    this.form.exchange_rate_sale = dato.exchange_rate_sale
                    this.form.items = dato.items
                    this.form.payments = dato.purchase_payments
                    this.form.purchase_payments_id = dato.purchase_payments.id
                    this.form.purchase_order_id = dato.purchase_order_id
                    this.form.customer_id = dato.customer_id
                    this.form.establishment_id = dato.establishment_id
                    this.form.codSustento = dato.codSustento
                    this.form.auth_number = dato.auth_number
                    this.form.sequential_number = dato.sequential_number
                    this.form.observation = dato.observation
                    this.form.is_aproved = (dato.is_aproved && dato.is_aproved > 0) ? true:false
                    this.form.import_id = dato.import_id
                    this.form.tipo_doc_id = dato.tipo_doc_id
                    //this.form.haveRetentions = dato.haveRetentions
                    //this.retencionesActuales = dato.retenciones

                    this.setCurrencyType()
                    this.changeCurrencyType()

                    if (this.form.customer_id) {
                        this.searchRemotePersons(dato.customer_number)
                    }

                    // this.form.has_payment = (this.form.payments.length>0) ? true:false
                    this.form.has_client = (this.form.customer_id) ? true : false

                    this.form.payment_condition_id = dato.payment_condition_id
                    this.form.fee = dato.fee
                    this.form.has_payment = (this.form.fee.length > 0 || this.form.payments.length > 0) ? true : false

                    if (this.form.payment_condition_id == '02') this.readonly_date_of_due = true

                    this.changeDocumentType()
                    this.changeDocumentType2()
                    this.calculateTotal()

                })

            //await this.getPercentageIgv();
        },
        getPayments(payments) {

            payments.forEach(it => {
                it.payment_destination_id = it.global_payment.destination_type == "App\Models\Tenant\Cash" ? 'cash' : it.global_payment.destination_id
            });

            return payments
        },
        changeAdvanceInput(index,event,methodType, id){

            let selectedAdvance = _.find(this.advances,{'id':id})
            let payment_method_type = _.find(this.payment_method_types, {'id': methodType});
            if(payment_method_type.description.includes('Anticipo')){

                let maxAmount = selectedAdvance.valor

                if(maxAmount >= event){
                    /*EL VALOR INGRESADO EN PERMITIDO EN EL ANTICIPO */

                }else{
                    this.form.payments[index].payment = maxAmount
                    let message = 'El monto maximo del anticipo es de '+maxAmount
                    this.$message.warning(message)

                }
            }
        },
        changeAdvance(index, id){

            let selectedAdvance = _.find(this.advances,{'id':id})
            let maxAmount = selectedAdvance.valor

            let payment_count = this.form.payments.length;
            // let total = this.form.total;
            let total = this.getTotal()

            let payment = 0;
            let amount = _.round(total / payment_count, 2);

            if(maxAmount >= amount ){
                /* EL MONTO INGRESADO ESTA PERMITIDO */
            }else if(amount > maxAmount ){

                this.form.payments[index].payment = maxAmount
                let message = 'El monto maximo del anticipo es de '+maxAmount
                this.$message.warning(message)
            }


        },
        addAdvancesCustomer(){

            this.$http.get(`/documents/advance/${this.form.supplier_id}`).then(
                response => {

                    this.advances = response.data
                }
            )
        },
        changePaymentMethodType(index) {

            let id = '01'

            if (this.form.payments.length > 0) {
                id = this.form.payments[index].payment_method_type_id
            } else if (this.form.fee.length > 0) {
                id = this.form.fee[index].payment_method_type_id
            }

            let payment_method_type = _.find(this.payment_method_types, {'id': id})

            if (payment_method_type.number_days) {

                this.form.date_of_due = moment(this.form.date_of_issue).add(payment_method_type.number_days, 'days').format('YYYY-MM-DD')
                this.readonly_date_of_due = true

                let date = moment(this.form.date_of_issue).add(payment_method_type.number_days, 'days').format('YYYY-MM-DD')

                if (this.form.fee.length > 0) {
                    for (let index = 0; index < this.form.fee.length; index++) {
                        this.form.fee[index].date = date
                    }
                }

            } else {

                this.form.date_of_due = this.form.date_of_issue
                this.readonly_date_of_due = false

            }

        },
        inputTotalPerception() {
            this.total_amount = parseFloat(this.form.total) + parseFloat(this.form.total_perception)
            if (isNaN(this.total_amount)) {
                this.hide_button = true
            } else {
                this.hide_button = false

            }
        },
        changeSupplier() {
            this.calculatePerception()
        },
        filterSuppliers() {

            this.suppliers = this.all_suppliers;
            this.selectSupplier()

        },
        selectSupplier() {

            let supplier = _.find(this.suppliers, {'id': this.aux_supplier_id})
            this.form.supplier_id = (supplier) ? supplier.id : null
            this.aux_supplier_id = null

        },
        initForm() {
            this.errors = {}
            this.form = {
                purchase_payments_id: 0,
                id: 0,
                establishment_id: null,
                document_type_id: null,
                document_type_intern: null,
                series: null,
                number: null,
                date_of_issue: moment().format('YYYY-MM-DD'),
                time_of_issue: moment().format('HH:mm:ss'),
                supplier_id: null,
                payment_method_type_id: '01',
                // JOINSOFTWARE
                currency_type_id: this.configuration.currency_type_id,
                purchase_order: null,
                exchange_rate_sale: 0,
                total_prepayment: 0,
                total_charge: 0,
                total_discount: 0,
                total_exportation: 0,
                total_free: 0,
                total_taxed: 0,
                total_unaffected: 0,
                total_exonerated: 0,
                total_igv: 0,
                total_base_isc: 0,
                total_isc: 0,
                total_base_other_taxes: 0,
                total_other_taxes: 0,
                total_taxes: 0,
                total_value: 0,
                total: 0,
                perception_date: null,
                perception_number: null,
                total_perception: 0,
                date_of_due: moment().format('YYYY-MM-DD'),
                items: [],
                charges: [],
                discounts: [],
                attributes: [],
                payments: [],
                guides: [],
                customer_id: null,
                has_client: false,
                has_payment: false,
                payment_condition_id: '01',
                fee: [],
                ret:[],
                codSustento: '',
                auth_number: '',
                sequential_number: '',
                observation: '',
                is_aproved:false,

            }

            // this.clickAddPayment()
            this.initInputPerson()
            this.readonly_date_of_due = false
            this.initGlobalIgv()
            this.recordItem = null
        },
        initGlobalIgv() {
            this.localHasGlobalIgv = this.configuration.checked_global_igv_to_purchase
        },
        resetForm() {
            this.initForm()
            this.form.currency_type_id = (this.currency_types.length > 0) ? this.currency_types[0].id : null
            this.form.establishment_id = this.establishment.id
            this.form.document_type_id = (this.document_types.length > 0) ? this.document_types[0].id : null

            this.changeDateOfIssue()
            this.changeDocumentType()
            this.changeCurrencyType()
        },
        changePaymentCondition() {

            this.form.fee = []
            this.form.payments = []

            if (this.form.payment_condition_id === '01') {

                this.clickAddPayment()
                this.initDataPaymentCondition()

            }
            if (this.form.payment_condition_id === '02') {
                this.clickAddFeeNew()
                this.readonly_date_of_due = true
            }
            if (this.form.payment_condition_id === '03') {
                this.clickAddFee()
                this.initDataPaymentCondition()
            }

        },
        async changeDateOfIssue() {
            this.form.date_of_due = this.form.date_of_issue
            await this.searchExchangeRateByDate(this.form.date_of_issue).then(response => {
                this.form.exchange_rate_sale = response
            })
            //await this.getPercentageIgv();
            //await this.getPercentageIgv();
            this.changeCurrencyType();
        },
        changeDocumentType() {
            this.filterSuppliers()
        },
        changeDocumentType2() {
            var document = _.find(this.document_types2,{'idType': this.form.document_type_intern})
            //console.log('documento seleccionado',document.DocumentTypeID)
            this.form.document_type_id = document.DocumentTypeID
            //this.codSustentos = _.find(this.codSustentos,{'idTipoComprobante':this.form.document_type_id})
            this.codSustentos = _.filter(this.codSustentos_all,{'idTipoComprobante':this.form.document_type_id})

            this.is_countable  = (document.accountant > 0)
            this.is_credit_note = (document.DocumentTypeID == '04')
        },
        addRow(row) {

            if (this.recordItem) {
                //this.form.items.$set(this.recordItem.indexi, row)
                this.form.items[this.recordItem.indexi] = row
                this.recordItem = null
            } else {

                this.form.items.push(row);
            }

            this.calculateTotal();
        },
        clickRemoveItem(index) {
            this.form.items.splice(index, 1)
            this.calculateTotal()
        },
        changeCurrencyType() {
            this.currency_type = _.find(this.currency_types, {'id': this.form.currency_type_id})
            let items = []
            this.form.items.forEach((row) => {
                items.push(calculateRowItem(row, this.form.currency_type_id, this.form.exchange_rate_sale, this.percentage_igv))
            });
            this.form.items = items
            this.calculateTotal()
        },
        addRetenciones(){
            this.form.ret = []
            let total = this.form.total_taxed + this.form.total_unaffected + this.form.total_igv
            let retenido = 0
            this.retencionesActuales.forEach((row) =>{

                let retencionLocal = {}
                retencionLocal.valor  = parseFloat(row.valorRet)
                const retIvaDesc = _.find(this.retention_types_income, {'code': row.codRetencion})
                if(retIvaDesc){
                    retencionLocal.tipo = 'RENTA'
                    retencionLocal.desciption  = retIvaDesc.description
                    retencionLocal.code  = retIvaDesc.code
                    retencionLocal.porcentajeRet  = retIvaDesc.percentage
                    retenido += parseFloat(row.valorRet)
                }else{
                    const retIvaDesc = _.find(this.retention_types_iva, {'code': row.codRetencion})
                    retencionLocal.tipo = 'IVA'
                    retencionLocal.desciption  = retIvaDesc.description
                    retencionLocal.code  = retIvaDesc.code
                    retencionLocal.porcentajeRet  = retIvaDesc.percentage
                    retenido += parseFloat(row.valorRet)
                }

                retencionLocal.base  = row.unit_value
                this.form.ret.push(retencionLocal)
            })

            this.form.total_ret = retenido
            //console.log('total valor con retencion',total - retenido)
            this.form.total = _.round(total - retenido, 2)
        },
        calculateTotal() {
            let total_discount = 0
            let total_charge = 0
            let total_exportation = 0
            let total_taxed = 0
            let total_exonerated = 0
            let total_unaffected = 0
            let total_free = 0
            let total_igv = 0
            let total_value = 0
            let total = 0
            let total_base_isc = 0
            let total_isc = 0
            let retention_iva = 0
            let retention_renta = 0
            let toal_retenido = 0

            this.form.ret = []

            this.form.items.forEach((row) => {

                //console.log('Rows: ',row)
                if(row.iva_retention > 0 || row.income_retention > 0){

                    retention_iva = parseFloat(row.iva_retention)
                    retention_renta = parseFloat(row.income_retention)

                    toal_retenido += (retention_iva + retention_renta)

                    if(this.form.ret.length > 0){
                        let nuevaRet = true

                        this.form.ret.forEach((data) => {
                            if(row.iva_retention > 0 ){
                                const retIvaDesc = _.find(this.retention_types_iva, {'id': row.retention_type_id_iva})
                                if(data.tipo == 'IVA' && data.desciption == retIvaDesc.description){
                                    data.valor += parseFloat(row.iva_retention)
                                    dato.base += row.total_taxes
                                    nuevaRet = false
                                }
                            }
                            if(row.income_retention > 0 ){
                                const retIncomeDesc = _.find(this.retention_types_income, {'id': row.retention_type_id_income})
                                if(data.tipo == 'RENTA' && data.desciption == retIncomeDesc.description){
                                    data.valor += parseFloat(row.income_retention)
                                    dato.base += row.unit_value * row.quantity
                                    nuevaRet = false
                                }
                            }


                        });

                        if(nuevaRet){
                            //console.log('Nueva Retencion')

                            if(row.iva_retention > 0 ){
                                let retencionLocal = {}
                                retencionLocal.tipo = 'IVA'
                                retencionLocal.valor  = parseFloat(row.iva_retention)
                                const retIvaDesc = _.find(this.retention_types_iva, {'id': row.retention_type_id_iva})
                                //console.log('Tipo retencion IVA: '+retIvaDesc.description)
                                retencionLocal.desciption  = retIvaDesc.description
                                retencionLocal.code  = retIvaDesc.code
                                retencionLocal.porcentajeRet  = retIvaDesc.percentage
                                retencionLocal.base  = row.total_taxes
                                this.form.ret.push(retencionLocal)
                            }
                            if(row.income_retention > 0 ){
                                let retencionLocal = {}
                                retencionLocal.tipo = 'RENTA'
                                retencionLocal.valor  = parseFloat(row.income_retention)
                                const retIvaDesc = _.find(this.retention_types_income, {'id': row.retention_type_id_income})
                                //console.log('Tipo retencion RENTA: '+retIvaDesc.description)
                                retencionLocal.desciption  = retIvaDesc.description
                                retencionLocal.code  = retIvaDesc.code
                                retencionLocal.porcentajeRet  = retIvaDesc.percentage
                                retencionLocal.base  = row.unit_value * row.quantity
                                this.form.ret.push(retencionLocal)
                            }
                        }

                    }else{
                        //console.log('Retencion Inicial')
                        if(row.iva_retention > 0 ){
                            let retencionLocal = {}
                            retencionLocal.tipo = 'IVA'
                            retencionLocal.valor  = parseFloat(row.iva_retention)
                            const retIvaDesc = _.find(this.retention_types_iva, {'id': row.retention_type_id_iva})
                            //console.log('Tipo retencion : '+retIvaDesc.description)
                            retencionLocal.desciption  = retIvaDesc.description
                            retencionLocal.code  = retIvaDesc.code
                            retencionLocal.porcentajeRet  = retIvaDesc.percentage
                            retencionLocal.base  = row.total_taxes
                            this.form.ret.push(retencionLocal)
                        }
                        if(row.income_retention > 0 ){
                            let retencionLocal = {}
                            retencionLocal.tipo = 'RENTA'
                            retencionLocal.valor  = parseFloat(row.income_retention)
                            const retIvaDesc = _.find(this.retention_types_income, {'id': row.retention_type_id_income})
                            //console.log('Tipo retencion : '+retIvaDesc.description)
                            retencionLocal.desciption  = retIvaDesc.description
                            retencionLocal.code  = retIvaDesc.code
                            retencionLocal.porcentajeRet  = retIvaDesc.percentage
                            retencionLocal.base  = row.unit_value * row.quantity
                            this.form.ret.push(retencionLocal)
                        }

                    }
                }


                total_discount += parseFloat(row.total_discount)
                total_charge += parseFloat(row.total_charge)

                if (row.affectation_igv_type_id === '10') {
                    total_taxed += parseFloat(row.total_value)
                }
                if (row.affectation_igv_type_id === '11') {
                    total_taxed += parseFloat(row.total_value)
                }
                if (row.affectation_igv_type_id === '12') {
                    total_taxed += parseFloat(row.total_value)
                }
                if (row.affectation_igv_type_id === '11') {
                    total_taxed += parseFloat(row.total_value)
                }
                if (row.affectation_igv_type_id === '12') {
                    total_taxed += parseFloat(row.total_value)
                }
                if (row.affectation_igv_type_id === '20') {
                    total_exonerated += parseFloat(row.total_value)
                }
                if (row.affectation_igv_type_id === '30') {
                    total_unaffected += parseFloat(row.total_value)
                }
                if (row.affectation_igv_type_id === '40') {
                    total_exportation += parseFloat(row.total_value)
                }

                if (['10','11','12', '20', '30', '40'].indexOf(row.affectation_igv_type_id) < 0) {
                    total_free += parseFloat(row.total_value)
                }

                total_value += parseFloat(row.total_value)
                total_igv += parseFloat(row.total_igv)


                total += parseFloat(row.total)

                // isc
                total_isc += parseFloat(row.total_isc)
                total_base_isc += parseFloat(row.total_base_isc)

                //console.log('total: '+ total)
                //console.log('retenido : '+ toal_retenido)
                //console.log('total: '+ total)
                //console.log('retenido : '+ toal_retenido)
            });

            // isc

            this.form.total_base_isc = _.round(total_base_isc, 2)
            this.form.total_isc = _.round(total_isc, 2)

            this.form.total_exportation = _.round(total_exportation, 2)
            this.form.total_taxed = _.round(total_taxed, 2)
            this.form.total_exonerated = _.round(total_exonerated, 2)
            this.form.total_unaffected = _.round(total_unaffected, 2)
            this.form.total_free = _.round(total_free, 2)
            this.form.total_igv = _.round(total_igv, 2)
            this.form.total_value = _.round(total_value, 2)
            // this.form.total_taxes = _.round(total_igv, 2)
            //impuestos (isc + igv)
            this.form.total_taxes = _.round(total_igv + total_isc, 2)
            this.form.total_ret =  _.round(toal_retenido, 2)

            total = total - toal_retenido

            this.form.total =  _.round(total, 2)

            this.calculatePerception()
            this.calculatePayments()
            this.calculateFee()
            this.recordItem = null

        },
        calculatePerception() {

            let supplier = _.find(this.all_suppliers, {'id': this.form.supplier_id})

            if (supplier) {

                if (supplier.perception_agent) {

                    let total_perception = 0
                    let quantity_item_perception = 0
                    let total_amount = 0
                    this.form.total_perception = 0

                    this.form.perception_date = moment().format('YYYY-MM-DD')

                    this.form.items.forEach((row) => {
                        quantity_item_perception += (row.item.has_perception) ? 1 : 0
                        total_perception += (row.item.has_perception) ? (parseFloat(row.unit_price) * parseFloat(row.quantity) * (parseFloat(row.item.percentage_perception) / 100)) : 0
                    });

                    this.is_perception_agent = (quantity_item_perception > 0) ? true : false
                    this.form.total_perception = _.round(total_perception, 2)
                    total_amount = this.form.total + parseFloat(this.form.total_perception)
                    this.total_amount = _.round(total_amount, 2)

                } else {

                    this.is_perception_agent = false
                    this.form.perception_date = null
                    this.form.perception_number = null
                    this.form.total_perception = null

                }

            }


        },
        validatePaymentDestination() {

            let error_by_item = 0

            this.form.payments.forEach((item) => {
                if (item.payment_destination_id == null) error_by_item++;
            })

            return {
                error_by_item: error_by_item,
            }

        },
        async ediItem(row, index) {
            row.indexi = index
            this.recordItem = row
            this.showDialogAddItem = true
        },
        async submit() {


            let validate = await this.validate_payments()
            if (!validate.success) {
                return this.$message.error(validate.message);
            }

            let validate_payment_destination = await this.validatePaymentDestination()

            if (validate_payment_destination.error_by_item > 0) {
                return this.$message.error('El destino del pago es obligatorio');
            }

            this.loading_submit = true
            // await this.changePaymentMethodType(false)
            await this.$http.post(`/${this.resource}/update`, this.form)
                .then(response => {

                    if (response.data.success) {
                        this.resetForm()
                        this.purchaseNewId = response.data.data.id
                        this.showDialogOptions = true
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        this.$message.error(error.response.data.message)
                    }
                })
                .then(() => {
                    this.loading_submit = false
                })
        },
        close() {
            location.href = '/purchases'
        },
        reloadDataSuppliers(supplier_id) {

            this.$http.get(`/${this.resource}/table/suppliers`).then((response) => {

                this.aux_supplier_id = supplier_id
                this.all_suppliers = response.data
                this.filterSuppliers()

            })
        },
    }
}
</script>
