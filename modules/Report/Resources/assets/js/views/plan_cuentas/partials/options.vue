<template>
    <div>
      <el-dialog
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        :show-close="false"
        :title="titleDialog"
        :visible="showDialog"
        width="26%"
        @open="create"
      >
        <div class="row flex justify-content-center" v-if="showButtons">
          <span class="loader-custom"></span>
          </div>
        <div class="row flex justify-content-between" v-if="!showButtons">
          <div class="col-lg-4 col-md-4 col-sm-4 text-center font-weight-bold">
            <p>Imprimir A4</p>
            <button
              type="button"
              class="btn btn-lg btn-info waves-effect waves-light"
              @click="clickToPrint('a4')"
            >
              <i class="fa fa-print"></i>
            </button>
          </div>
          <div class="col-lg-6 col-md-6 col-sm-6 text-center font-weight-bold">
            <p>Descargar A4</p>
            <button
              type="button"
              class="btn btn-lg btn-info waves-effect waves-light"
              @click="clickDownload('a4')"
            >
              <i class="fa fa-download"></i>
            </button>
          </div>
        </div>
        <br />
        <span slot="footer" class="dialog-footer">
          <el-button @click="clickFinalize">Ir al listado</el-button>
          <el-button type="primary" @click="clickNewQuotation"
            >Nuevo Asiento</el-button
          >
        </span>
      </el-dialog>
    </div>
  </template>
  
  <script>
  export default {
    props: [
      "showDialog",
      "recordId",
      "showClose",
      "showGenerate",
      "type",
      "typeUser",
    ],
    mixins: [],
    computed: {},
    data() {
      return {
        titleDialog: null,
        loading: false,
        resource: "accounting-entries",
        errors: {},
        showButtons:false,
        form: {},
      };
    },
    created() {
      this.initForm();
    },
    methods: {
      create() {
        this.showButtons=true;
        this.$http
          .get(`/${this.resource}/record/${this.recordId}`)
          .then((response) => {
            this.form = response.data.data;
            this.titleDialog = `Asiento contable N°: ${this.form.identifier}`;
            this.showButtons=false;
          });
      },
      initForm() {
        this.form = {
          id: null,
          external_id: null,
        };
      },
  
      clickFinalize() {
        location.href = `/${this.resource}`;
      },
      clickNewQuotation() {
        this.clickClose();
        this.clickFinalize();
      },
      clickClose() {
        this.$emit("update:showDialog", false);
        this.initForm();
      },
      clickToPrint(format) {
        // Si no hay external id, no hará nada.
        if (this.form.external_id == null) return null;
        window.open(
          `/${this.resource}/print/${this.form.external_id}/${format}`,
          "_blank"
        );
      },
      clickDownload(format) {
        window.open(
          `/${this.resource}/download/${this.form.external_id}/${format}`,
          "_blank"
        );
      },
    },
  };
  </script>