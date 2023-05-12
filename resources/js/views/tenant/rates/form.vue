<template>
    <el-dialog
      :title="titleDialog"
      :visible="showDialog"
      @close="close"
      @open="create"
    >
      <form autocomplete="off" @submit.prevent="submit">
        <div class="form-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group" :class="{ 'has-danger': errors.rate_name }">
                <label class="control-label"
                  >Nombre <span class="text-danger">*</span></label
                >
                <el-input v-model="form.rate_name" dusk="rate_name"></el-input>
                <small
                  class="form-control-feedback"
                  v-if="errors.rate_name"
                  v-text="errors.rate_name[0]"
                ></small>
              </div>
            </div>

            <div class="col-md-4 center-el-checkbox mt-3" v-if="recordId==null">
              <div class="form-group">
                <el-checkbox v-model="form.rate_offer">Oferta</el-checkbox>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label"
                  >Fecha Inicio <span class="text-danger">*</span></label
                >
                <div class="pb-2">
                  <el-date-picker
                    v-model="form.rate_start"
                    type="date"
                    @change="changeDisabledDates"
                    value-format="yyyy-MM-dd"
                    format="dd/MM/yyyy"
                    :clearable="false"
                  ></el-date-picker>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label"
                  >Fecha Fin <span class="text-danger">*</span></label
                >
                <div class="pb-2">
                  <el-date-picker
                    v-model="form.rate_end"
                    type="date"
                    value-format="yyyy-MM-dd"
                    format="dd/MM/yyyy"
                    :clearable="false"
                  ></el-date-picker>
                </div>
              </div>
            </div>
          </div>
          <div class="form-actions text-right mt-4">
            <el-button @click.prevent="close()">Cancelar</el-button>
            <el-button
              type="primary"
              native-type="submit"
              :loading="loading_submit"
              >Guardar</el-button
            >
          </div>
        </div>
      </form>
    </el-dialog>
  </template>

  <script>
  import moment from "moment";
  export default {
    props: ["showDialog", "recordId"],
    data() {
      return {
        titleDialog: null,
        loading_submit: false,
        resource: "rates-lists",
        errors: {},
        form: {},
      };
    },
    created() {
      this.initForm();
    },
    methods: {
      initForm() {
        this.errors = {};
        this.form = {
          id: null,
          rate_name: null,
          rate_start : moment().format('YYYY-MM-DD'),
          rate_end : moment().endOf('month').format('YYYY-MM-DD'),
          rate_offer: false,
        };
      },
      async create() {
        this.titleDialog = this.recordId ? "Editar Tarifa" : "Nueva Tarifa";
        if (this.recordId) {
          this.initForm();
          this.$http
            .get(`/${this.resource}/record/${this.recordId}`)
            .then((response) => {
              let data = response.data;
              this.form = {
                id: data.id,
                rate_name: data.rate_name,
                rate_start: data.rate_start,
                rate_end: data.rate_end,
                rate_offer: data.rate_offer ? true : false,
              };
              console.log("this form", this.form);
            });
        }
      },
      changeDisabledDates() {
        if (this.form.rate_end < this.form.rate_start) {
          this.form.rate_end = this.form.rate_start;
        }
      },
      submit() {
        console.log("form", this.form);
        this.loading_submit = true;
        this.$http
          .post(`/${this.resource}`, this.form)
          .then((response) => {
            if (response.data.success) {
              this.$message.success(response.data.message);
              this.$eventHub.$emit("reloadData");
              this.close();
            } else {
              this.$message.error(response.data.message);
            }
          })
          .catch((error) => {
            if (error.response.status === 422) {
              this.errors = error.response.data;
            } else {
              console.log(error);
            }
          })
          .then(() => {
            this.loading_submit = false;
          });
      },
      close() {
        this.$emit("update:showDialog", false);
        this.initForm();
      },
    },
  };
  </script>
