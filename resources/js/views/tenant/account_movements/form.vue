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
          <div class="col-md-5">
            <div
              :class="{ 'has-danger': errors.account_group_id }"
              class="form-group"
            >
              <label class="control-label"> Grupo </label>
              <el-select
                v-model="form.account_group_id"
                clearable
                filterable
                dusk="type"
                @change="setPrefix"
              >
                <el-option
                  v-for="option in account_groups"
                  :key="option.id"
                  :label="option.description"
                  :value="option.id"
                ></el-option>
              </el-select>
              <small
                v-if="errors.account_group_id"
                class="form-control-feedback"
                v-text="errors.account_group_id[0]"
              ></small>
            </div>
          </div>

          <div class="col-md-3">
            <div
              :class="{ 'has-danger': errors.cost_center }"
              class="form-group"
            >
              <label class="control-label"> Centro Costo </label>
              <el-select
                v-model="form.cost_center"
                clearable
                filterable
                dusk="cost_center"
              >
                <el-option
                  v-for="option in costc"
                  :key="option.id"
                  :label="option.value"
                  :value="option.id"
                ></el-option>
              </el-select>
              <small
                v-if="errors.cost_center"
                class="form-control-feedback"
                v-text="errors.cost_center[0]"
              ></small>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group" :class="{ 'has-danger': errors.code }">
              <label class="control-label"
                >Código Cuenta <span class="text-danger">*</span></label
              >
              <div class="input-group mb-2 mr-sm-2">
                <div class="input-group-prepend">
                  <div
                    class="input-group-text"
                    style="font-size: 0.85rem; letter-spacing: 1px"
                  >
                    {{ form.prefix }}
                  </div>
                </div>
                <input
                  type="text"
                  v-model="form.inputcode"
                  dusk="code"
                  class="form-control el-input__inner"
                  style="height: 35px; line-height: 32px"
                />
              </div>
              <small
                class="form-control-feedback"
                v-if="errors.code"
                v-text="errors.code[0]"
              ></small>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div
              class="form-group"
              :class="{ 'has-danger': errors.description }"
            >
              <label class="control-label"
                >Descripción <span class="text-danger">*</span></label
              >
              <el-input
                v-model="form.description"
                dusk="description"
              ></el-input>
              <small
                class="form-control-feedback"
                v-if="errors.description"
                v-text="errors.description[0]"
              ></small>
            </div>
          </div>
        </div>
      </div>
      <div class="form-actions text-right mt-4">
        <el-button @click.prevent="close()">Cancelar</el-button>
        <el-button type="primary" native-type="submit" :loading="loading_submit"
          >Guardar</el-button
        >
      </div>
    </form>
  </el-dialog>
</template>

<script>
export default {
  props: ["showDialog", "recordId"],
  data() {
    return {
      titleDialog: null,
      loading_submit: false,
      resource: "accounts-movements",
      errors: {},
      tipos: [{ value: "BALANCE GENERAL" }, { value: "RESULTADOS" }],
      costc: [
        { id: 1, value: "SI" },
        { id: 0, value: "NO" },
      ],
      form: {},
      account_groups: [],
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
        prefix: 0,
        inputcode: null,
        code:null,
        description: null,
        cost_center: 0,
        account_group_id: null,
      };
    },
    async create() {
      this.titleDialog = this.recordId
        ? "Editar cuenta movimiento"
        : "Nueva cuenta movimiento";
      await this.$http.get(`/${this.resource}/tables`).then((response) => {
        const data = response.data;
        this.account_groups = data.account_groups;
      });
      if (this.recordId) {
        this.initForm()
        this.$http
          .get(`/${this.resource}/record/${this.recordId}`)
          .then((response) => {
            let data = response.data;
            //console.log('dart', data);
            let valor = this.account_groups.find((item) => {
              if (item.id === data.account_group_id) {
                return item.code;
              }
            });

            this.form = {
              id: data.id,
              prefix: valor.code,
              inputcode: data.code.toString().slice(valor.code.toString().length),
              description: data.description,
              cost_center: data.cost_center,
              account_group_id: data.account_group_id,
            };
            //console.log('tis form', this.form);
            //this.form = response.data;
          });
      }
    },
    setPrefix() {
      let valor = this.account_groups.find((item) => {
        if (item.id === this.form.account_group_id) {
          return item.code;
        }
      });
      this.form.prefix = valor.code;
    },
    submit() {
      //console.log('form',this.form);
      this.loading_submit = true;
      if( this.form.inputcode!=null && this.form.inputcode!="" && this.form.prefix!=0){
        this.form.code = this.form.prefix + this.form.inputcode;
      }


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
