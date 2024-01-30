<style>
/* Agrega esto en tu archivo CSS */
.el-loading-mask {
    background-color: rgba(255, 255, 255, 0.9);
}

.el-loading-spinner {
    font-size: 20px;
    color: #409eff;
    text-align: center;
}

@keyframes pulse {
    0% {
        transform: scale(0.95);
        opacity: 1;
    }

    70% {
        transform: scale(1);
        opacity: 0.7;
    }

    100% {
        transform: scale(0.95);
        opacity: 1;
    }
}

.el-loading-spinner i {
    animation: pulse 1.5s infinite ease-in-out;
}
</style>
<template>
    <el-dialog :title="titleDialog" :visible="showDialog" class="dialog-import" @close="close" @open="create"
        v-loading="loading">
        <div v-v-loading="loadingForm" class="form-container" v-loading.lock="loadingForm">
            <form autocomplete="off" @submit.prevent="submit">
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-12 mt-4">
                            <div :class="{ 'has-danger': errors.file }" class="form-group text-center">
                                <el-upload ref="upload" :auto-upload="false" :limit="1" :multiple="false" accept=".xml"
                                    :on-change="handleChange" :show-file-list="true" action="''">
                                    <el-button slot="trigger" type="primary">Seleccione un archivo (xml)</el-button>
                                </el-upload>
                                <small v-if="errors.file" class="form-control-feedback" v-text="errors.file[0]"></small>
                            </div>
                        </div>

                        <div class="col-md-12 mt-12" v-if="has_file && form.items && form.items.length > 0"
                            style="align-content: center">
                            <div style="text-align: center">
                                <h3>Lista de productos</h3>
                            </div>
                            <div>
                                <table bordered style="align-content: center; text-align: center">
                                    <tr slot="heading">
                                        <th>Original</th>
                                        <th>Cantidad</th>
                                        <th>Interno</th>
                                    </tr>
                                    <tr v-for="(item, index) in form.items" style="text-align: center">
                                        <td style="text-align: left">
                                            {{ item.desciption ? item.desciption : item.item.name }}
                                        </td>
                                        <td>{{ item.quantity }}</td>
                                        <td style="align-content: center">
                                            <el-select :disabled="item.item_id != null" v-model="item.item_id"
                                                @change="changeItem(item.item_id, index)" filterable required="true">
                                                <!-- :remote-method="searchRemoteItems" remote> -->
                                                <el-option v-for="(prod, index2) in items_all" :key="index2"
                                                    :value="prod.id" :label="prod.full_description"></el-option>
                                            </el-select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions text-right mt-4">
                    <el-button @click.prevent="close()">Cancelar</el-button>
                    <el-button v-if="has_file" :loading="loading_submit" native-type="submit" type="primary">Procesar
                    </el-button>
                </div>
            </form>
        </div>
    </el-dialog>
</template>

<script>
import { event } from "jquery";
import { calculateRowItem } from "../../../helpers/functions";
import { mapActions, mapState } from "vuex";

export default {
    props: ["showDialog"],
    data() {
        return {
            has_file: false,
            loading_submit: false,
            headers: headers_token,
            titleDialog: null,
            resource: "purchases",
            errors: {},
            form: {},
            formXmlJson: {},
            items_all: [],
            affectation_igv_types: [],
            system_isc_types: [],
            discount_types: [],
            charge_types: [],
            attribute_types: [],
            purchaseItems: [],
            loading_search: false,
            loading: true,
            loadingForm: false,
        };
    },
    created() {
        this.loading == true;

        this.loadWarehouses(this.$store);
        this.loadAllItems(this.$store);

        this.$http.get(`/${this.resource}/item/tables`).then((response) => {
            console.log("ITEMS IMPORT: ", response.data.items_import);
            this.items_all = response.data.items_import;

            this.affectation_igv_types = response.data.affectation_igv_types;
            this.system_isc_types = response.data.system_isc_types;
            this.discount_types = response.data.discount_types;
            this.charge_types = response.data.charge_types;
            this.attribute_types = response.data.attribute_types;
            this.$store.commit("setWarehouses", response.data.warehouses);
        });

        this.initForm();
    },
    mounted() {
        this.$http.post(`get-items`).then((response) => {
            this.$store.commit("setAllItems", response.data.data);
        });
    },
    methods: {
        ...mapActions(["loadWarehouses", "loadAllItems"]),
        handleChange(file) {
            this.loadingForm = true;

            setTimeout(() => {
                const self = this;
                const reader = new FileReader();
                reader.onload = (e) => {
                    self.parseXml(e.target.result);
                    self.loadingForm = false;
                };
                reader.readAsText(file.raw);
            }, 5000);
            //console.log("File: ",file)
        },
        MensajeError(campo) {
            this.$message.error(
                `${campo} No se ha encontrado en el xml, no se puede continuar`
            );
            console.error(`${campo} no se encuentra en el xml`);
        },
        RetornoIndexIndefinido(array, index) {
            if (!(array[index] !== undefined)) {
                this.MensajeError(index);
                return false;
            }
            return true;
        },
        async parseXml(source) {
            this.loading_submit = true;
            let convert = require("xml-js");
            this.formXmlJson = convert.xml2js(source, { compact: true, spaces: 4 });
            this.has_file = false;
            await this.setdataForm();
            this.loading_submit = false;
        },
        async setdataForm() {
            let convert = require("xml-js");

            let Invoice = convert.xml2js(this.formXmlJson.autorizacion.comprobante["_cdata"], {
                compact: true,
                spaces: 4,
            });
            console.log("setdataForm", Invoice);

            let evalu = "";
            let ID = [];

            if (Invoice === undefined) {
                this.$message.error("No se encuentra datos de XML");
                console.error("No se encuentra datos de XML");
                return false;
            }

            evalu = "factura";
            if (!this.RetornoIndexIndefinido(Invoice, evalu)) return false;

            console.log(Invoice.factura.infoFactura.fechaEmision["_text"]);
            let date = new Date(
                parseInt(Invoice.factura.infoFactura.fechaEmision["_text"].substr(6, 4)),
                parseInt(Invoice.factura.infoFactura.fechaEmision["_text"].substr(3, 2)) - 1,
                parseInt(Invoice.factura.infoFactura.fechaEmision["_text"].substr(0, 2))
            );

            this.form.date_of_due = date.toLocaleDateString("en-CA");
            //evalu = 'cbc:IssueDate';
            if (!this.RetornoIndexIndefinido(Invoice, evalu)) return false;
            this.form.date_of_issue = date.toLocaleDateString("en-CA");
            this.form.time_of_issue = "00:00:00";

            evalu = "[factura][infoTributaria][ruc]";
            if (Invoice.factura.infoTributaria.ruc) {
                this.form.supplier_ruc = Invoice.factura.infoTributaria.ruc["_text"];
            } else {
                this.MensajeError(evalu);
                return false;
            }

            evalu = "[factura][infoTributaria][claveAcceso]";
            if (Invoice.factura.infoTributaria.claveAcceso) {
                this.form.auth_number = Invoice.factura.infoTributaria.claveAcceso["_text"];
            } else {
                this.MensajeError(evalu);
                return false;
            }

            evalu = "[factura][infoTributaria][secuencial]";
            if (Invoice.factura.infoTributaria.secuencial) {
                this.form.sequential_number =
                    Invoice.factura.infoTributaria.estab["_text"] +
                    Invoice.factura.infoTributaria.ptoEmi["_text"] +
                    Invoice.factura.infoTributaria.secuencial["_text"];
            } else {
                this.MensajeError(evalu);
                return false;
            }

            evalu = "[factura][detalles][detalle]";
            if (Invoice.factura.detalles.detalle !== undefined) {
                console.log('item to load', Invoice.factura.detalles.detalle)
                await this.setFormItems(Invoice.factura.detalles.detalle);
            } else {
                this.MensajeError(evalu);
                return false;
            }

            evalu = "[factura][infoFactura][totalDescuento]";
            if (Invoice.factura.infoFactura.totalDescuento !== undefined) {
                this.form.total_discount = parseFloat(Invoice.factura.infoFactura.totalDescuento);
            } else {
                this.MensajeError(evalu);
                return false;
            }

            evalu = "[factura][infoFactura][importeTotal]";
            if (Invoice.factura.infoFactura.importeTotal !== undefined) {
                evalu = "[factura][infoFactura][pagos]";
                if (Invoice.factura.infoFactura.pagos) {
                    this.form.total = parseFloat(Invoice.factura.infoFactura.importeTotal["_text"]);
                    this.form.total_discount = parseFloat(
                        Invoice.factura.infoFactura.totalDescuento["_text"]
                    );
                    //PAYMENTS
                    this.form.payments = [
                        {
                            id: null,
                            purchase_id: null,
                            date_of_payment: new Date(
                                Invoice.factura.infoFactura.fechaEmision["_text"]
                            ).toLocaleDateString("en-ca"),
                            payment_method_type_id: "01",
                            reference: null,
                            payment_destination_id: "cash",
                            payment: parseFloat(Invoice.factura.infoFactura.importeTotal["_text"]),
                        },
                    ];
                } else {
                    this.MensajeError(evalu);
                    return false;
                }

                evalu = "[factura][infoFactura][totalSinImpuestos]";
                if (
                    Invoice.factura.infoFactura.totalSinImpuestos !== undefined &&
                    Invoice.factura.infoFactura.importeTotal !== undefined
                ) {
                    let impuestos =
                        parseFloat(Invoice.factura.infoFactura.importeTotal["_text"]) -
                        parseFloat(Invoice.factura.infoFactura.totalSinImpuestos["_text"]);
                    //this.form.total_taxes = impuestos
                } else {
                    this.MensajeError(evalu);
                    return false;
                }
            } else {
                this.MensajeError(evalu);
                return false;
            }

            evalu = "[factura][infoFactura][totalSinImpuestos]";
            if (Invoice.factura.infoFactura.totalSinImpuestos !== undefined) {
                //this.form.total_taxed = Invoice.factura.infoFactura.totalSinImpuestos["_text"];
            } else {
                this.MensajeError(evalu);
                return false;
            }

            if (Invoice.factura.infoFactura.totalConImpuestos.totalImpuesto.length > 1) {
                Invoice.factura.infoFactura.totalConImpuestos.totalImpuesto.forEach((element) => {
                    console.log("codigoPorcentaje" + element["codigoPorcentaje"]["_text"]);

                    if (parseFloat(element["codigoPorcentaje"]["_text"]) > 0) {
                        this.form.total_taxed += parseFloat(element["baseImponible"]["_text"]);
                    } else {
                        this.form.total_unaffected += parseFloat(element["baseImponible"]["_text"]);
                    }
                });
            } else {
                console.log(
                    Invoice.factura.infoFactura.totalConImpuestos.totalImpuesto.codigoPorcentaje[
                    "_text"
                    ]
                );
                if (
                    parseFloat(
                        Invoice.factura.infoFactura.totalConImpuestos.totalImpuesto.codigoPorcentaje[
                        "_text"
                        ]
                    ) > 0
                ) {
                    this.form.total_taxed = parseFloat(
                        Invoice.factura.infoFactura.totalConImpuestos.totalImpuesto.baseImponible[
                        "_text"
                        ]
                    );
                } else {
                    this.form.total_unaffected = parseFloat(
                        Invoice.factura.infoFactura.totalConImpuestos.totalImpuesto.baseImponible[
                        "_text"
                        ]
                    );
                }
            }

            this.form.total_value = this.form.total_taxed + this.form.total_unaffected;
            this.has_file = true;
        },
        findItem(search) {
            //if (search === '') return undefined;
            let item = this.all_items.find(
                (obj) =>
                    obj.id == search ||
                    obj.item_code == search ||
                    obj.model == search ||
                    obj.internal_id == search
            );

            return item;
        },
        setFormItems(items) {
            console.log('setFormItems', items);
            const self = this;
            self.form.items = [];

            if (items.length > 1) {
                console.log('items mas de uno')

                items.forEach((element) => {
                    console.log('item a agregar', element);
                    let formItem = self.initFormItem();
                    formItem.item_id = null;
                    formItem.desciption = element["descripcion"]["_text"];
                    formItem.unit_value = parseFloat(element["precioUnitario"]["_text"]);
                    formItem.quantity = parseFloat(element["cantidad"]["_text"]);
                    formItem.iva = parseInt(element["impuestos"]["impuesto"]["tarifa"]["_text"]);

                    //console.log('elemento convertido a float: ', parseFloat(element["descuento"]["_text"]));

                    if (parseFloat(element["descuento"]["_text"]) > 0) {
                        formItem.discounts = [
                            {
                                amount: parseFloat(element["descuento"]["_text"]),
                                base: parseFloat(element["precioUnitario"]["_text"]),
                                discount_type_id: "00",
                                discount_type: {
                                    active: 1,
                                    base: 1,
                                    descripcion: "Descuentos que afectan la base imponible",
                                    id: "00",
                                    level: "item",
                                    type: "discount",
                                },
                                description: "Descuento",
                                factor: _.round(
                                    (parseFloat(element["descuento"]["_text"]) * 100) /
                                    parseFloat(element["precioUnitario"]["_text"]) /
                                    100,
                                    2
                                ),
                                is_amount: true,
                                percentage: _.round(
                                    (parseFloat(element["descuento"]["_text"]) * 100) /
                                    parseFloat(element["precioUnitario"]["_text"]),
                                    2
                                ),
                                use_input_amount: true,
                            },
                        ];
                    }

                    console.log('item a agregar base: ',formItem)
                    self.form.items.push(formItem);
                });
            } else {
                console.log('elementos a agregar: ', items)
                let element = items;
                let formItem = self.initFormItem();
                formItem.item_id = null;
                formItem.desciption = element["descripcion"]["_text"];
                formItem.unit_value = parseFloat(element["precioUnitario"]["_text"]);
                formItem.quantity = parseFloat(element["cantidad"]["_text"]);
                formItem.iva = parseInt(element["impuestos"]["impuesto"]["tarifa"]["_text"]);
                //formItem.total_discount = parseFloat(element['descuento']['_text']);
                if (parseFloat(element["descuento"]["_text"]) > 0) {
                    formItem.discounts = [
                        {
                            amount: parseFloat(element["descuento"]["_text"]),
                            base: parseFloat(element["precioUnitario"]["_text"]),
                            discount_type_id: "00",
                            discount_type: {
                                active: 1,
                                base: 1,
                                descripcion: "Descuentos que afectan la base imponible",
                                id: "00",
                                level: "item",
                                type: "discount",
                            },
                            description: "Descuento",
                            factor: _.round(
                                (parseFloat(element["descuento"]["_text"]) * 100) /
                                parseFloat(element["precioUnitario"]["_text"]) /
                                100,
                                2
                            ),
                            is_amount: true,
                            percentage: _.round(
                                (parseFloat(element["descuento"]["_text"]) * 100) /
                                parseFloat(element["precioUnitario"]["_text"]),
                                2
                            ),
                            use_input_amount: true,
                        },
                    ];
                }

                self.form.items.push(formItem);
            }
            //console.info(self.form.items)
        },
        async changeItem(id, index) {
            let formItem = this.findItem(id);
            let itemActual = this.form.items[index];

            console.log("changeItem", itemActual);
            console.log("formItem", formItem);

            if (formItem !== undefined) {
                this.form.items[index].item_id = id;

                itemActual.item = formItem;
                itemActual.unit_price = itemActual.unit_value;
                itemActual.item_unit_types = formItem.item_unit_types;
                itemActual.quantity = itemActual.quantity;
                itemActual.has_igv = false;
                itemActual.item.presentation = {};

                let row = calculateRowItem(
                    itemActual,
                    this.config.currency_type_id,
                    1,
                    itemActual.iva,
                    null
                );

                row.warehouse_id = 1;
                row.warehouse_description = "Almacén Oficina Principal";
                row.iva = itemActual.iva;
                row.desciption = itemActual.desciption;
                this.form.items[index] = row;
            } else {
                this.$message.error("No se encontro el item en la lista disponible");
                this.form.items[index].item_id = null;
            }
        },
        initFormItem() {
            return {
                item_id: null,
                warehouse_id: 1,
                warehouse_description: null,
                item: {},
                affectation_igv_type_id: null,
                affectation_igv_type: {},
                has_isc: false,
                system_isc_type_id: null,
                percentage_isc: 0,
                suggested_price: 0,
                quantity: 1,
                unit_price: 0,
                charges: [],
                discounts: [],
                attributes: [],
                item_unit_types: [],
            };
        },

        initForm() {
            this.errors = {};
            this.form = {
                establishment_id: 1,
                document_type_id: "01",
                document_type_intern: "01",
                series: "CC",
                number: 0,
                date_of_issue: null,
                time_of_issue: null,
                supplier_id: null,
                payment_method_type_id: "01",
                currency_type_id: this.config.currency_type_id,
                purchase_order: null,
                exchange_rate_sale: 1,
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
                date_of_due: null,
                items: [],
                charges: [],
                discounts: [],
                attributes: [],
                guides: [],
                is_aproved: false,
                codSustento: "01",
                customer_id: null,
                has_client: false,
                has_payment: true,
                payment_condition_id: "01",
            };
            this.loading == false;
        },
        create() {
            this.titleDialog = "Importar Factura Compra";
        },
        async searchRemoteItems(input) {
            if (input.length > 2) {
                this.loading_search = true;
                let parameters = `input=${input}`;

                await this.$http
                    .get(`/${this.resource}/search-items/?${parameters}`)
                    .then((response) => {
                        this.items_all = response.data.items;
                        this.loading_search = false;

                        if (this.items_all.length == 0) {
                            this.initFilterItems();
                        }
                    });
            }
        },
        initFilterItems() {
            this.activeName = "first";
        },
        async submit() {
            this.loading_submit = true;
            await this.$http
                .post(`/${this.resource}/import`, this.form)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadData");
                        this.$refs.upload.clearFiles();
                        this.close();
                    } else {
                        this.$message({ message: response.data.message, type: "error" });
                    }
                })
                .catch((error) => {
                    this.$message.error(error.response.message);
                })
                .then(() => {
                    this.loading_submit = false;
                });
            //console.log('XML',this.form)
        },
        close() {
            this.$emit("update:showDialog", false);
            this.$refs.upload.clearFiles();
            this.initForm();
        },
        successUpload(response, file, fileList) {
            if (response.success) {
                //this.$message.success(response.message)
                //this.$eventHub.$emit('reloadData')
                //this.$refs.upload.clearFiles()
                //this.close()
            } else {
                this.$message({ message: response.message, type: "error" });
            }
        },
        errorUpload(response) {
            console.log(response);
        },
        xmlToJson(xml) {
            // Create the return object
            var obj = {};

            if (xml.nodeType == 1) {
                // element
                // do attributes
                if (xml.attributes.length > 0) {
                    obj["@attributes"] = {};
                    for (var j = 0; j < xml.attributes.length; j++) {
                        var attribute = xml.attributes.item(j);
                        obj["@attributes"][attribute.nodeName] = attribute.nodeValue;
                    }
                    //console.log('obj', obj)
                }
            } else if (xml.nodeType == 3) {
                // text
                obj = xml.nodeValue;
                //console.log('obj', obj)
            }

            // do children
            // If all text nodes inside, get concatenated text from them.
            var textNodes = [].slice.call(xml.childNodes).filter(function (node) {
                return node.nodeType === 3;
            });

            if (xml.hasChildNodes() && xml.childNodes.length === textNodes.length) {
                obj = [].slice.call(xml.childNodes).reduce(function (text, node) {
                    return text + node.nodeValue;
                }, "");
            } else if (xml.hasChildNodes()) {
                for (var i = 0; i < xml.childNodes.length; i++) {
                    var item = xml.childNodes.item(i);
                    var nodeName = item.nodeName;
                    if (typeof obj[nodeName] == "undefined") {
                        obj[nodeName] = this.xmlToJson(item);
                    } else {
                        if (typeof obj[nodeName].push == "undefined") {
                            var old = obj[nodeName];
                            obj[nodeName] = [];
                            obj[nodeName].push(old);
                        }
                        obj[nodeName].push(this.xmlToJson(item));
                    }
                }
            }
            return obj;
        },
        demo() {
            parseXMLToJSON();
            return false;
        },
    },
    computed: {
        ...mapState(["config", "userType", "all_items"]),
    },
};
</script>
