<template>
    <el-dialog
        :title="titleDialog"
        :visible="showDialog"
        class="dialog-import"
        @close="close"
        @open="create"
    >
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12 mt-4">
                        <div :class="{'has-danger': errors.file}" class="form-group text-center">
                            <el-upload
                                ref="upload"
                                :auto-upload="false"
                                :limit="1"
                                :multiple="false"
                                :on-change="handleChange"
                                :show-file-list="true"
                                accept=".txt"
                                action="''"
                            >
                                <el-button slot="trigger" type="primary">Seleccione un archivo (TXT)</el-button>
                            </el-upload>
                            <small v-if="errors.file" class="form-control-feedback" v-text="errors.file[0]"></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button v-if='has_file' :loading="loading_submit" native-type="submit" type="primary">Procesar
                </el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>
import { event } from "jquery";
import {calculateRowItem} from "../../../helpers/functions";
import {mapActions, mapState} from "vuex";

export default {
    props: ["showDialog"],
    data() {
        return {
            has_file: false,
            loading_submit: false,
            headers: headers_token,
            titleDialog: null,
            resource: "retentions",
            errors: {},
            form: {},
            formXmlJson: {},
            items: [],
            affectation_igv_types: [],
            system_isc_types: [],
            discount_types: [],
            charge_types: [],
            attribute_types: [],
            purchaseItems:[],
        };
    },
    created() {

    },
    mounted(){
    },
    methods: {
        ...mapActions([
            'loadWarehouses',
            'loadAllItems'
        ]),
        handleChange(file) {

            const self = this;
            const reader = new FileReader();
            reader.onload = e => self.parseTxt(e.target.result);
            reader.readAsText(file.raw);
            console.log(reader)
        },
        MensajeError(campo) {
            this.$message.error(`${campo} No se ha encontrado en el xml, no se puede continuar`);
            console.error(`${campo} no se encuentra en el xml`)
        },
        RetornoIndexIndefinido(array, index) {
            if (!(array[index] !== undefined)) {
                this.MensajeError(index)
                return false;
            }
            return true;
        },
        async parseTxt(source) {
            this.loading_submit = true;
            console.log(source)
            this.form.data = source;
            this.has_file = true;
            this.loading_submit = false;
        },
        initForm() {
            this.errors = {};
            this.form = {
                data: null,
            };

        },
        create() {
            this.titleDialog = "Importar Retenciones Recibidas";
        },
        async submit() {
            this.loading_submit = true;
            await this.$http
                .post(`/${this.resource}/import`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$message.success("Se procesaron de forma exitosa: "+response.data.procesed);
                        this.$message.error("No se procesaron: "+response.data.fail + "/");
                        this.$eventHub.$emit("reloadData");
                        this.$refs.upload.clearFiles();
                        this.close();
                    } else {
                        this.$message({message: response.data.message, type: "error"});
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.message);
                })
                .then(() => {
                    this.loading_submit = false;
                });
                //console.log('XML',this.form)
        },
        close() {
            this.$emit("update:showDialog", false);
            this.initForm();
        },
        successUpload(response, file, fileList) {
            if (response.success) {
                //this.$message.success(response.message)
                //this.$eventHub.$emit('reloadData')
                //this.$refs.upload.clearFiles()
                //this.close()
            } else {
                this.$message({message: response.message, type: "error"});
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
                    console.log('obj', obj)
                }
            } else if (xml.nodeType == 3) {
                // text
                obj = xml.nodeValue;
                console.log('obj', obj)
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
        }
    },
    computed: {
        ...mapState([
            'config',
            'userType',
            'all_items',
        ]),
    },
};
</script>
