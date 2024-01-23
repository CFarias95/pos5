<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">Cuentas por cobrar</h3>
            <div class="data-table-visible-columns" style="top: 10px">
                <el-dropdown :hide-on-click="false">
                    <el-button type="primary">
                        Mostrar/Ocultar columnas<i class="el-icon-arrow-down el-icon--right"></i>
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item v-for="(column, index) in columns" :key="index">
                            <el-checkbox v-model="column.visible">{{ column.title }}</el-checkbox>
                        </el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-12">
                        <section>
                            <div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label">Establecimiento</label>
                                            <el-select v-model="form.establishment_id" @change="loadUnpaid">
                                                <el-option v-for="option in establishments" :key="option.id"
                                                    :value="option.id" :label="option.name"></el-option>
                                            </el-select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
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
                                            <el-date-picker v-model="form.month_end" type="month" @change="loadUnpaid"
                                                :picker-options="pickerOptionsMonths" value-format="yyyy-MM"
                                                format="MM/yyyy" :clearable="false"></el-date-picker>
                                        </div>
                                    </template>
                                    <template v-if="
                      form.period === 'date' ||
                      form.period === 'between_dates' ||
                      form.period == 'expired' ||
                      form.period == 'posdated'
                    ">
                                        <div class="col-md-3">
                                            <label class="control-label">Fecha del</label>
                                            <el-date-picker v-model="form.date_start" type="date"
                                                @change="changeDisabledDates" value-format="yyyy/MM/dd" format="dd/MM/yyyy"
                                                :clearable="false"></el-date-picker>
                                        </div>
                                    </template>
                                    <template v-if="
                      form.period === 'between_dates' ||
                      form.period == 'expired' ||
                      form.period == 'posdated'
                    ">
                                        <div class="col-md-3">
                                            <label class="control-label">Fecha al</label>
                                            <el-date-picker v-model="form.date_end" type="date"
                                                :picker-options="pickerOptionsDates" @change="loadUnpaid"
                                                value-format="yyyy/MM/dd" format="dd/MM/yyyy"
                                                :clearable="false"></el-date-picker>
                                        </div>
                                    </template>

                                    <div class="col-md-6">
                                        <label class="control-label">Cliente</label>
                                        <el-select @change="changeCustomerUnpaid" filterable clearable
                                            v-model="form.customer_id" placeholder="Seleccionar cliente">
                                            <el-option v-for="item in customers" :key="item.id" :label="item.name"
                                                :value="item.id"></el-option>
                                        </el-select>
                                    </div>

                                    <div v-if="typeUser == 'admin'" class="col-md-4">
                                        <label class="control-label">Vendedor</label>
                                        <el-select @change="changeUser" filterable clearable v-model="form.user_id"
                                            placeholder="Seleccionar vendedor">
                                            <el-option v-for="item in users" :key="item.id" :label="item.name"
                                                :value="item.id"></el-option>
                                        </el-select>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="form-group">
                                            <label class="control-label">Plataforma</label>
                                            <el-select v-model="form.web_platform_id" clearable filterable
                                                @change="changeWebPlatform">
                                                <el-option v-for="option in web_platforms" :key="option.id"
                                                    :value="option.id" :label="option.name"></el-option>
                                            </el-select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="form-group">
                                            <label>Orden de compra</label>
                                            <el-input @change="changePurchaseOrder" v-model="form.purchase_order"
                                                clearable></el-input>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="form-group">
                                            <label>Total documento
                                                <el-tooltip class="item" effect="dark"
                                                    content="Por defecto la condicion sera >=(mayor igual), si se desea otra condición especificar condicion,valor ejemplo =,100"
                                                    placement="top-start">
                                                    <i class="fa fa-info-circle"></i>
                                                </el-tooltip>
                                            </label>
                                            <el-input @change="changeImporte" v-model="form.importe" clearable></el-input>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="form-group">
                                            <label>Incluir Liquidadas</label>
                                            <el-switch v-model="form.include_liquidated" class="ml-2"
                                                @change="changeLiquidated" style="
                          --el-switch-on-color: #13ce66;
                          --el-switch-off-color: #ff4949;
                        " />
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="control-label">Métodos de cobro
                                            <el-tooltip class="item" effect="dark" content="Aplica a CPE"
                                                placement="top-start">
                                                <i class="fa fa-info-circle"></i>
                                            </el-tooltip>
                                        </label>
                                        <el-select @change="changePaymentMethodType" filterable clearable
                                            v-model="form.payment_method_type_id" placeholder="Seleccionar">
                                            <el-option v-for="item in payment_method_types" :key="item.id"
                                                :label="item.description" :value="item.id"></el-option>
                                        </el-select>
                                    </div>
                                </div>

                                <div class="row" v-loading="loading">
                                    <div class="col-md-12" style="margin-top: 29px">
                                        <el-button class="submit" type="success" @click.prevent="clickOpen()">
                                            <i class="fa fa-file-excel"></i>
                                            Exportar Todo
                                        </el-button>

                                        <el-button v-if="records.length > 0" class="submit" type="success"
                                            @click.prevent="clickDownload('excel')">
                                            <i class="fa fa-file-excel"></i>
                                            Exportar Excel
                                        </el-button>

                                        <el-tooltip class="item" effect="dark" content="Reporte por formas de pago (Días)"
                                            placement="top-start">
                                            <el-button v-if="records.length > 0" class="submit" type="primary"
                                                @click.prevent="clickDownloadPaymentMethod()">
                                                <i class="fa fa-file-excel"></i>
                                                Formas de pago (Días)
                                            </el-button>
                                        </el-tooltip>

                                        <el-button v-if="records.length > 0" class="submit" type="danger"
                                            @click.prevent="clickDownload('pdf')">
                                            <i class="fa fa-file-pdf"></i>
                                            Exportar PDF
                                        </el-button>

                                        <el-button v-if="records.length > 0" class="submit" type="warning"
                                            @click.prevent="clickMultiPay()">
                                            <i class="fa fa-check-square-o"></i>
                                            Generar Pago Multiple
                                        </el-button>

                                    </div>
                                    <div class="col-md-1 mt-5 text-right"></div>

                                    <div class="col-md-2 mt-5 text-right">
                                        <el-badge :value="getTotalRowsUnpaid" class="item">
                                            <span size="small">Total Vencimientos</span>
                                        </el-badge>
                                    </div>
                                    <!--
                                    <div class="col-md-2 mt-5 text-right">
                                        <el-badge :value="getTotalAmountUnpaid" class="item">
                                            <span size="small">Monto general (PEN)</span>
                                        </el-badge>
                                    </div>
                                    <div class="col-md-2 mt-5 text-right">
                                        <el-badge :value="getCurrentBalance" class="item">
                                            <span size="small">Saldo corriente (PEN)</span>
                                        </el-badge>
                                    </div>
                                    -->
                                    <div class="col-md-2 mt-5 text-right">
                                        <el-badge :value="getTotalAmountUnpaidUsd" class="item">
                                            <span size="small">Monto general (USD)</span>
                                        </el-badge>
                                    </div>
                                    <div class="col-md-2 mt-5 text-right">
                                        <el-badge :value="getCurrentBalanceUsd" class="item">
                                            <span size="small">Saldo pendiente</span>
                                        </el-badge>
                                    </div>

                                    <div class="col-md-12 table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>P. Multi</th>
                                                    <th>#</th>
                                                    <th>F.Emisión</th>
                                                    <th>F.Vencimiento</th>
                                                    <th>Fecha Posfechado</th>
                                                    <th>Ref. Posfechado</th>
                                                    <th>Número</th>
                                                    <th>Cliente</th>
                                                    <th>Usuario</th>
                                                    <th>Días de retraso</th>
                                                    <th>Penalidad</th>
                                                    <!--<th>Guías</th>-->
                                                    <th class="text-center" v-if="columns.web_platforms.visible">
                                                        Plataforma
                                                    </th>
                                                    <th v-if="columns.purchase_order.visible">Orden de compra</th>
                                                    <th>Ver Cartera</th>
                                                    <th>Moneda</th>
                                                    <th>Tiene Pago multiple</th>
                                                    <th class="text-right">Por cobrar</th>
                                                    <th class="text-right">T. Nota Crédito</th>
                                                    <th class="text-right">Total documento</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template v-for="(row, index) in records">
                                                    <tr v-if="row.total_to_pay > 0" :key="index">
                                                        <td>
                                                            <el-switch v-model="row.selected"></el-switch>
                                                        </td>
                                                        <td>
                                                            {{ customIndex(index) }}
                                                        </td>
                                                        <td>
                                                            {{ row.date_of_issue }}
                                                        </td>
                                                        <td>
                                                            {{
                                                            row.date_of_due
                                                            ? row.date_of_due
                                                            : "No tiene fecha de vencimiento."
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{ row.f_posdated ? row.f_posdated : "" }}
                                                        </td>
                                                        <td>
                                                            {{ row.posdated }}
                                                        </td>
                                                        <td>
                                                            {{ row.number_full }}
                                                        </td>
                                                        <td>
                                                            {{ row.customer_name }}
                                                        </td>
                                                        <td>
                                                            {{ row.username }}
                                                        </td>
                                                        <td>
                                                            {{
                                                            row.delay_payment < 0 ? row.delay_payment * -1
                                                                : "No tiene días atrasados." }} </td>
                                                        <td>
                                                            <el-popover placement="right" width="200" trigger="click">
                                                                <strong>Penalidad: {{ row.arrears }}</strong>
                                                                <el-button slot="reference">
                                                                    <i class="fa fa-eye"></i>
                                                                </el-button>
                                                            </el-popover>
                                                        </td>
                                                        <!--<td>
                                                            <template>
                                                                <el-popover placement="right" width="400" trigger="click">
                                                                    <el-table :data="row.guides">
                                                                        <el-table-column width="120"
                                                                            property="date_of_issue"
                                                                            label="Fecha Emisión"></el-table-column>
                                                                        <el-table-column width="100" property="number"
                                                                            label="Número"></el-table-column>
                                                                        <el-table-column width="100"
                                                                            property="date_of_shipping"
                                                                            label="Fecha Envío"></el-table-column>
                                                                        <el-table-column fixed="right" label="Descargas"
                                                                            width="120">
                                                                            <template slot-scope="scope">
                                                                                <button type="button"
                                                                                    class="btn waves-effect waves-light btn-xs btn-info"
                                                                                    @click.prevent="clickDownloadDispatch(scope.row.download_external_xml)">XML</button>
                                                                                <button type="button"
                                                                                    class="btn waves-effect waves-light btn-xs btn-info"
                                                                                    @click.prevent="clickDownloadDispatch(scope.row.download_external_pdf)">PDF</button>
                                                                                <button type="button"
                                                                                    class="btn waves-effect waves-light btn-xs btn-info"
                                                                                    @click.prevent="clickDownloadDispatch(scope.row.download_external_cdr)">CDR</button>
                                                                            </template>
                                                                        </el-table-column>
                                                                    </el-table>
                                                                    <el-button slot="reference"
                                                                        icon="el-icon-view"></el-button>
                                                                </el-popover>
                                                            </template>
                                                        </td>-->

                                                        <td v-if="columns.web_platforms.visible">
                                                            <template v-for="(platform, i) in row.web_platforms"
                                                                v-if="row.web_platforms !== undefined">
                                                                <label class="d-block">{{ platform.name }}</label>
                                                            </template>
                                                        </td>
                                                        <td v-if="columns.purchase_order.visible">
                                                            {{ row.purchase_order }}
                                                        </td>
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

                                                                <el-button icon="el-icon-view" slot="reference"></el-button>
                                                            </el-popover>
                                                        </td>
                                                        <td>
                                                            {{ row.currency_type_id }}
                                                        </td>
                                                        <td>
                                                            {{ row.multipay }}
                                                        </td>

                                                        <td class="text-right text-danger">
                                                            {{ row.total_to_pay }}
                                                        </td>
                                                        <td class="text-center">
                                                            <template v-if="row.type == 'document'">
                                                                {{ row.total_credit_notes }}
                                                            </template>
                                                            <template v-else> - </template>
                                                        </td>

                                                        <td class="text-right">
                                                            {{ row.total }}
                                                        </td>
                                                        <td class="text-right">
                                                            <template v-if="row.type === 'document'">
                                                                <button type="button" style="min-width: 41px"
                                                                    class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                                                    @click.prevent="
                                    clickDocumentPayment(
                                      row.fee_id,
                                      row.id,
                                      row.customer_id
                                    )
                                  ">
                                                                    Pagos
                                                                </button>
                                                            </template>
                                                            <template v-else>
                                                                <button type="button" style="min-width: 41px"
                                                                    class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                                                    @click.prevent="clickSaleNotePayment(row.id)">
                                                                    Pagos
                                                                </button>
                                                            </template>

                                                            <template>
                                                                <button type="button" style="min-width: 41px"
                                                                    class="btn waves-effect waves-light btn-xs btn-primary m-1__2"
                                                                    @click.prevent="
                                    clickPosFechado(row.fee_id, row.id, row.customer_id)
                                  ">
                                                                    POSfechar
                                                                </button>
                                                            </template>
                                                            <template v-if="row.total_to_pay > 0 && row.fee_id">
                                                                <button type="button" style="min-width: 41px"
                                                                    class="btn waves-effect waves-light btn-xs btn-danger m-1__2"
                                                                    @click.prevent="
                                    clickSplit(row.fee_id, row.total_to_pay)
                                  ">
                                                                    Dividir cuota
                                                                </button>
                                                            </template>
                                                            <template v-if="row.total_to_pay > 0 && row.fee_id">
                                                                <button type="button" style="min-width: 41px"
                                                                    class="btn waves-effect waves-light btn-xs btn-warning m-1__2"
                                                                    @click.prevent="clickDate(row.fee_id)">
                                                                    Cambiar F. Vencimiento
                                                                </button>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="row.total_to_pay <= 0" :class="{
                              'bg-success text-white': row.total_to_pay <= 0,
                            }" :key="index">
                                                        <td>
                                                            <el-switch v-model="row.selected"></el-switch>
                                                        </td>
                                                        <td>
                                                            {{ customIndex(index) }}
                                                        </td>
                                                        <td>
                                                            {{ row.date_of_issue }}
                                                        </td>
                                                        <td>
                                                            {{
                                                            row.date_of_due
                                                            ? row.date_of_due
                                                            : "No tiene fecha de vencimiento."
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{ row.f_posdated ? row.f_posdated : "" }}
                                                        </td>
                                                        <td>
                                                            {{ row.posdated }}
                                                        </td>
                                                        <td>
                                                            {{ row.number_full }}
                                                        </td>
                                                        <td>
                                                            {{ row.customer_name }}
                                                        </td>
                                                        <td>
                                                            {{ row.username }}
                                                        </td>

                                                        <td>
                                                            {{ "Liquidado" }}
                                                        </td>
                                                        <td>
                                                            <el-popover placement="right" width="200" trigger="click">
                                                                <strong>Penalidad: {{ row.arrears }}</strong>
                                                                <el-button slot="reference">
                                                                    <i class="fa fa-eye"></i>
                                                                </el-button>
                                                            </el-popover>
                                                        </td>
                                                        <td>
                                                            <template>
                                                                <el-popover placement="right" width="400" trigger="click">
                                                                    <el-table :data="row.guides">
                                                                        <el-table-column width="120"
                                                                            property="date_of_issue"
                                                                            label="Fecha Emisión"></el-table-column>
                                                                        <el-table-column width="100" property="number"
                                                                            label="Número"></el-table-column>
                                                                        <el-table-column width="100"
                                                                            property="date_of_shipping"
                                                                            label="Fecha Envío"></el-table-column>
                                                                        <el-table-column fixed="right" label="Descargas"
                                                                            width="120">
                                                                            <template slot-scope="scope">
                                                                                <button type="button"
                                                                                    class="btn waves-effect waves-light btn-xs btn-info"
                                                                                    @click.prevent="
                                            clickDownloadDispatch(
                                              scope.row.download_external_xml
                                            )
                                          ">
                                                                                    XML
                                                                                </button>
                                                                                <button type="button"
                                                                                    class="btn waves-effect waves-light btn-xs btn-info"
                                                                                    @click.prevent="
                                            clickDownloadDispatch(
                                              scope.row.download_external_pdf
                                            )
                                          ">
                                                                                    PDF
                                                                                </button>
                                                                                <button type="button"
                                                                                    class="btn waves-effect waves-light btn-xs btn-info"
                                                                                    @click.prevent="
                                            clickDownloadDispatch(
                                              scope.row.download_external_cdr
                                            )
                                          ">
                                                                                    CDR
                                                                                </button>
                                                                            </template>
                                                                        </el-table-column>
                                                                    </el-table>
                                                                    <el-button slot="reference"
                                                                        icon="el-icon-view"></el-button>
                                                                </el-popover>
                                                            </template>
                                                        </td>

                                                        <td v-if="columns.web_platforms.visible">
                                                            <template v-for="(platform, i) in row.web_platforms"
                                                                v-if="row.web_platforms !== undefined">
                                                                <label class="d-block">{{ platform.name }}</label>
                                                            </template>
                                                        </td>
                                                        <td v-if="columns.purchase_order.visible">
                                                            {{ row.purchase_order }}
                                                        </td>
                                                        <td>
                                                            {{ row.currency_type_id }}
                                                        </td>
                                                        <td>
                                                            {{ row.multipay }}
                                                        </td>
                                                        <td class="text-right text-danger">
                                                            {{ (row.total_to_pay <= 0)? 0 : row.total_to_pay }}
                                                        </td>
                                                        <td class="text-center">
                                                            <template v-if="row.type == 'document'">
                                                                {{ row.total_credit_notes }}
                                                            </template>
                                                            <template v-else> - </template>
                                                        </td>
                                                        <td class="text-right">
                                                            {{ row.total }}
                                                        </td>
                                                        <td class="text-right">
                                                            <template v-if="row.type === 'document'">
                                                                <button type="button" style="min-width: 41px"
                                                                    class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                                                    @click.prevent="
                                    clickDocumentPayment(
                                      row.fee_id,
                                      row.id,
                                      row.customer_id
                                    )
                                  ">
                                                                    Pagos
                                                                </button>
                                                            </template>
                                                            <template v-else>
                                                                <button type="button" style="min-width: 41px"
                                                                    class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                                                    @click.prevent="clickSaleNotePayment(row.id)">
                                                                    Pagos
                                                                </button>
                                                            </template>
                                                            <template>
                                                                <button type="button" style="min-width: 41px"
                                                                    v-if="row.total_to_pay > 0"
                                                                    class="btn waves-effect waves-light btn-xs btn-primary m-1__2"
                                                                    @click.prevent="
                                    clickPosFechado(row.fee_id, row.id, row.customer_id)
                                  ">
                                                                    POSfechar
                                                                </button>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                        <div>
                                            <el-pagination @current-change="loadUnpaid()" layout="total, prev, pager, next"
                                                :total="pagination.total" :current-page.sync="pagination.current_page"
                                                :page-size="pagination.per_page">
                                            </el-pagination>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <document-payments :showDialog.sync="showDialogDocumentPayments" :documentId="recordId" :customerId="customerId"
            :external="true" :configuration="this.configuration" :documentFeeId="feeID"></document-payments>
        <pos-fechado :showDialog.sync="showDialogPosFechado" :documentId="recordId" :documentFeeId="feeID"></pos-fechado>
        <split-form :showDialog.sync="showDialogSplit" :documentId="recordId" :amountFee="amountFeeRow"></split-form>
        <date-form :showDialog.sync="showDialogDate" :documentId="recordId"></date-form>
        <sale-note-payments :showDialog.sync="showDialogSaleNotePayments" :documentId="recordId" :external="true"
            :configuration="this.configuration"></sale-note-payments>
        <template #default>
            <el-dialog style="background-color: rgb(14 14 14 / 64%);" :show-close="false" :visible="this.showMultiPay"
                title="Generar multipago" append-to-body align-center>
                <el-form>
                    <el-form-item label="Fecha de pago">
                        <el-date-picker v-model="formMultiPay.date_of_payment" type="date" :clearable="false"
                            format="dd/MM/yyyy" value-format="yyyy-MM-dd"></el-date-picker>
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
                        <el-button v-if="formMultiPay.payment > 0" type="primary" @click="generateMultiPay()">
                            Generar
                        </el-button>
                    </span>
                </template>
            </el-dialog>
        </template>
    </div>
</template>

<script>
import DocumentPayments from "@views/documents/partials/payments.vue";
import SaleNotePayments from "@views/sale_notes/partials/payments.vue";
import PosFechado from "@views/documents/partials/posFechado.vue";
import SplitForm from "./partials/split.vue";
import DateForm from "./partials/date.vue";
// import DataTable from '../../components/DataTableWithoutPaging.vue'
import queryString from "query-string";

export default {
    props: ["typeUser", "configuration"],
    components: {
        DocumentPayments,
        SaleNotePayments,
        PosFechado,
        SplitForm,
        DateForm,
    },
    data() {
        return {
            resource: "finances/unpaid",
            form: {},
            customers: [],
            recordId: null,
            amountFeeRow: 0,
            customerId: null,
            feeID: null,
            records: [],
            establishments: [],
            web_platforms: [],
            showMultiPay: false,
            multiPayArray: [],
            pickerOptionsDates: {
                disabledDate: (time) => {
                    time = moment(time).format("YYYY/MM/DD");
                    return this.form.date_start > time;
                },
            },
            pickerOptionsMonths: {
                disabledDate: (time) => {
                    time = moment(time).format("YYYY-MM");
                    return this.form.month_start > time;
                },
            },

            showDialogDocumentPayments: false,
            showDialogPosFechado: false,
            showDialogSplit: false,
            showDialogDate: false,
            showDialogSaleNotePayments: false,
            users: [],
            payment_method_types: [],
            payment_destinations: [],
            pagination: {},
            loading: false,
            columns: {
                purchase_order: {
                    title: "Orden de compra",
                    visible: false,
                },
                web_platforms: {
                    title: "Plataformas web",
                    visible: false,
                },
            },
            formMultiPay: {
                unpaid: [],
                date_of_payment: moment().format('YYYY-MM-DD'),
                payment : 0,
                payment_method_type_id : '01',
                payment_destination_id : 'cash',
                reference : 'N/A',
            },
        };
    },
    async created() {
        this.$eventHub.$on("reloadDataUnpaid", () => {
            this.loadUnpaid();
        });
        this.$eventHub.$on("reloadData", () => {
            this.loadUnpaid();
        });

        await this.initForm();
        await this.filter();
        await this.changePeriod();
    },
    computed: {
        getCurrentBalance() {
            const self = this;
            let source = [];
            if (self.form.customer_id) {
                source = _.filter(self.records, function (item) {
                    return (
                        item.total_to_pay > 0 &&
                        item.customer_id == self.form.customer_id &&
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
            if (self.form.customer_id) {
                source = _.filter(self.records, function (item) {
                    return (
                        item.total_to_pay > 0 &&
                        item.customer_id == self.form.customer_id &&
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

            if (self.form.customer_id) {
                return _.filter(self.records, function (item) {
                    return item.total_to_pay > 0 && item.customer_id == self.form.customer_id;
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
            if (self.form.customer_id) {
                source = _.filter(self.records, function (item) {
                    return (
                        item.total_to_pay > 0 &&
                        item.customer_id == self.form.customer_id &&
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
            if (self.form.customer_id) {
                source = _.filter(self.records, function (item) {
                    return (
                        item.total_to_pay > 0 &&
                        item.customer_id == self.form.customer_id &&
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
        changePaymentMethodType() {
            this.loadUnpaid();
        },
        initForm() {
            this.form = {
                establishment_id: null,
                period: "between_dates",
                date_start: moment().format("YYYY/MM/DD"),
                date_end: moment().format("YYYY/MM/DD"),
                month_start: moment().format("YYYY-MM"),
                month_end: moment().format("YYYY-MM"),
                customer_id: null,
                user_id: null,
                payment_method_type_id: null,
                include_liquidated: false,
            };
        },
        async filter() {
            await this.$http.get(`/${this.resource}/filter`, this.form).then((response) => {
                this.establishments = response.data.establishments;
                this.customers = response.data.customers;
                this.form.establishment_id =
                    this.establishments.length > 0 ? this.establishments[0].id : null;
                this.users = response.data.users;
                this.payment_method_types = response.data.payment_method_types;
                this.payment_destinations = response.data.payment_destinations;

                this.web_platforms = response.data.web_platforms;
            });
        },
        customIndex(index) {
            return this.pagination.per_page * (this.pagination.current_page - 1) + index + 1;
        },
        async loadUnpaid() {
            this.loading = true;

            await this.$http
                .get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then((response) => {
                    this.records = response.data.data;
                    this.pagination = response.data;
                    this.pagination.per_page = parseInt(response.data.per_page);
                    const setting = response.data.configuration;
                    this.records.sort(function (a, b) {
                        return parseFloat(a.delay_payment) - parseFloat(b.delay_payment);
                    });
                    //console.log('registros', this.records);
                    this.records = this.records.map((r) => {
                        if (setting.apply_arrears) {
                            r.arrears = parseFloat(r.delay_payment * setting.arrears_amount).toFixed(2);
                        } else {
                            r.arrears = 0;
                        }
                        return r;
                    });
                })
                .catch((error) => { })
                .then(() => {
                    this.loading = false;
                });
        },
        getQueryParameters() {
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form,
            });
        },
        clickDocumentPayment(feeID, recordId, customer) {
            this.recordId = recordId;
            this.customerId = customer;
            this.feeID = feeID;
            this.showDialogDocumentPayments = true;
        },
        clickPosFechado(feeID, recordId, customer) {
            this.recordId = recordId;
            this.customerId = customer;
            this.feeID = feeID;
            this.showDialogPosFechado = true;
        },
        clickSplit(recordId, amount) {
            console.log(recordId, amount);
            this.recordId = recordId;
            this.amountFeeRow = parseFloat(amount);
            this.showDialogSplit = true;
        },
        clickDate(recordId) {
            this.recordId = recordId;
            this.showDialogDate = true;
        },
        clickSaleNotePayment(recordId) {
            this.recordId = recordId;
            this.showDialogSaleNotePayments = true;
        },
        clickDownloadDispatch(download) {
            window.open(download, "_blank");
        },
        clickDownload(type) {
            let query = queryString.stringify({
                ...this.form,
            });

            if (type == "pdf") {
                return window.open(`/${this.resource}/${type}/?${query}`, "_blank");
            }

            window.open(`/reports/no_paid/${type}/?${query}`, "_blank");
        },
        clickDownloadPaymentMethod() {
            let query = queryString.stringify({
                ...this.form,
            });
            window.open(`/${this.resource}/report-payment-method-days?${query}`, "_blank");
        },
        clickOpen() {
            window.open(`/${this.resource}/unpaidall`, "_blank");
        },
        changeCustomerUnpaid() {
            // if (this.form.customer_id) {

            this.loadUnpaid();
            /*this.records = _.filter(this.records_base, {
                  customer_id: this.selected_customer
                  });*/
            // } else {
            //     this.records = []
            // }
        },
        changeUser() {
            this.loadUnpaid();
        },
        changePurchaseOrder() {
            if (this.form.purchase_order !== undefined && this.form.purchase_order.length > 3) {
                this.loadUnpaid();
            }
        },
        changeWebPlatform() {
            this.loadUnpaid();
        },
        changeImporte() {
            this.loadUnpaid();
        },
        changeDisabledDates() {
            if (this.form.date_end < this.form.date_start) {
                this.form.date_end = this.form.date_start;
            }
            this.loadUnpaid();
        },
        changeDisabledMonths() {
            if (this.form.month_end < this.form.month_start) {
                this.form.month_end = this.form.month_start;
            }
            this.loadUnpaid();
        },
        changeLiquidated() {
            this.loadUnpaid();
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
                this.form.date_start = moment().format("YYYY/MM/DD");
                this.form.date_end = moment().format("YYYY/MM/DD");
            }
            if (this.form.period === "between_dates") {
                this.form.date_start = moment().startOf("month").format("YYYY/MM/DD");
                this.form.date_end = moment().endOf("month").format("YYYY/MM/DD");
            }
            this.loadUnpaid();
        },
        initFormMultiPay() {

            this.formMultiPay= {
                unpaid: [],
                date_of_payment: moment().format('YYYY-MM-DD'),
                payment : 0,
                payment_method_type_id : '01',
                payment_destination_id : 'cash',
                reference : 'N/A',
            }
        },
        clickMultiPay() {
            console.log('clickMultiPay show dialog')

            this.records.forEach(element => {
                if (element.selected) {
                    this.formMultiPay.unpaid.push({ document: element.number_full, document_id: element.id, fee_id: element.fee_id, maxamount: parseFloat(element.total_to_pay), amount: parseFloat(element.total_to_pay), customer_id: element.customer_id, customer: element.customer_name })
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
            this.loadUnpaid();
            this.initFormMultiPay()
        },
        async generateMultiPay() {
            this.loading = true;

            await this.$http.post(`/${this.resource}/multipay`, this.formMultiPay)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.formMultiPay = [];
                    } else {
                        this.$message.error(response.data.message);
                    }
                });

            this.loadUnpaid();
            this.loading = false;
            this.showMultiPay = false;
            this.initFormMultiPay()

        }
    },
};
</script>
