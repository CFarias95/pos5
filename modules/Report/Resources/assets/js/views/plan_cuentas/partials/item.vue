<template>
    <el-dialog
      :close-on-click-modal="false"
      :title="titleDialog"
      :visible="showDialog"
      top="7vh"
      :append-to-body="true"
      @close="close"
      @open="create"
    >
      <div class="form-body">
        <div class="row">
          <div class="col-md-8 col-lg-8 col-xl-8 col-sm-8">
            <div
              id="custom-select"
              :class="{ 'has-danger': errors.item_id }"
              class="form-group row"
            >
              <label class="control-label col-sm-4"> Buscar Cuenta: </label>
              <el-input
                class="form-control borde-0 p-0 col-md-8"
                v-model="filter"
                type="text"
              ></el-input>
            </div>
          </div>
          <div class="col-md-4 my-auto">
            <a
              v-if="can_add_new_product"
              href="#"
              @click.prevent="showDialogNewItem = true"
            >
              [+ Nueva Cuenta]
            </a>
          </div>
        </div>
  
        <div class="col-md-12 table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th class="font-weight-bold" width="18%">Código</th>
                <th class="font-weight-bold">Cuenta</th>
                <th class="font-weight-bold" width="15%">Grupo</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in filteredRows" :key="`cuenta-${index}`">
                <td v-html="highlightMatches(row.code)"></td>
                <td>
                  <a
                    class="link-cuenta"
                    href="#"
                    @click.prevent="
                      clickAddCuenta(
                        row.id,
                        row.code,
                        row.description,
                        row.cost_center
                      )
                    "
                    v-html="
                      highlightMatches([...row.description].sort().join(', '))
                    "
                  >
                  </a>
                </td>
                <td>{{ row.account_group.description }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <!-- @todo: Mejorar evitando duplicar codigo -->
      <!-- Mostrar en cel -->
  
      <div class="row hidden-md-up form-actions text-center">
        <div class="col-12">&nbsp;</div>
        <div class="col-6">
          <el-button class="form-control" @click.prevent="close()"
            >Cerrar
          </el-button>
        </div>
        <div class="col-6">
          <el-button
            v-if="form.item_id"
            class="add form-control btn btn-primary"
            native-type="submit"
            type="primary"
          >
            {{ titleAction }}
          </el-button>
        </div>
      </div>
      <!-- @todo: Mejorar evitando duplicar codigo -->
      <!-- Mostrar en cel -->
      <!-- @todo: Mejorar evitando duplicar codigo -->
      <!-- Ocultar en cel -->
  
      <div class="form-actions text-right pt-2 hidden-sm-down">
        <el-button @click.prevent="close()">Cerrar</el-button>
        <el-button
          v-if="form.item_id"
          class="add"
          native-type="submit"
          type="primary"
        >
          {{ titleAction }}
        </el-button>
      </div>
  
      <groupmov-form
        :external="true"
        :showDialog.sync="showDialogNewItem"
      ></groupmov-form>
    </el-dialog>
  </template>
  <style>
  .el-select-dropdown {
    margin-right: 5% !important;
    max-width: 80% !important;
  }
  </style>
  
  <script>
  import groupmovForm from "../../account_movements/form.vue";
  
  import { mapActions, mapState } from "vuex/dist/vuex.mjs";
  
  export default {
    props: ["showDialog", "typeUser", "configuration", "rowId"],
    components: {
      groupmovForm,
    },
    data() {
      return {
        can_add_new_product: false,
        loading_search: false,
        titleAction: "",
        titleDialog: "Seleccionar Cuenta",
        resource: "accounting-entries",
        showDialogNewItem: false,
        errors: {},
        form: {},
        filter: "",
        accounts: [],
      };
    },
   async created() {
      this.loadConfiguration();
      this.$store.commit("setConfiguration", this.configuration);
     await this.initForm();
      this.$eventHub.$on("reloadData", () => {
        this.reloadTables();
      });
    },
    mounted() {
      this.getTables();
      this.canCreateProduct();
    },
    computed: {
      ...mapState(["config", "item_search_extra_parameters"]),
  
      filteredRows() {
        return this.accounts.filter((row) => {
          const code = row.code.toString().toLowerCase();
          const description = row.description.toLowerCase();
          const searchTerm = this.filter.toLowerCase();
  
          return code.includes(searchTerm) || description.includes(searchTerm);
        });
      },
    },
    methods: {
      ...mapActions(["loadConfiguration"]),
  
      //resaltar busqueda
      highlightMatches(text) {
        const matchExists = text
          .toLowerCase()
          .includes(this.filter.toLowerCase());
        if (!matchExists) return text;
  
        const re = new RegExp(this.filter, "ig");
        return text.replace(
          re,
          (matchedText) => `<strong>${matchedText}</strong>`
        );
      },
  
      getTables() {
        let params = {};
        if (this.item_search_extra_parameters !== undefined) {
          if (this.item_search_extra_parameters.only_service !== undefined) {
            params.only_service = 1;
          }
        }
        this.$http
          .get(`/${this.resource}/item/tables`, { params })
          .then((response) => {
            let data = response.data;
            this.accounts = data.account_movement;
          });
      },
      canCreateProduct() {
        if (this.typeUser === "admin") {
          this.can_add_new_product = true;
        } else if (this.typeUser === "seller") {
          if (
            this.config !== undefined &&
            this.config.seller_can_create_product !== undefined
          ) {
            this.can_add_new_product = this.config.seller_can_create_product;
          }
        }
        return this.can_add_new_product;
      },
  
      initForm() {
        this.filter = "";
        this.errors = {};
        this.form = {
          id: null,
          item_id: null,
          code: null,
          name: "",
          description: "",
        };
      },
      async reloadTables() {
        await this.$http
          .get(`/${this.resource}/item/tables`)
          .then((response) => {
            let data = response.data;
            this.accounts = data.account_movement;
          });
      },
      async create() {
        //this.titleDialog = (this.recordItem) ? ' Editar Producto o Servicio' : ' Agregar Producto o Servicio';
        this.titleDialog = " Seleccionar Cuenta";
        //this.titleAction = this.recordItem ? " Editar" : " Agregar";
      },
  
      close() {
        this.initForm();
        this.$emit("update:showDialog", false);
      },
      clickAddCuenta(id, code, description, cost) {
        this.form.id = id;
        this.form.item_id = this.rowId;
        this.form.code = code;
        this.form.cost = cost;
        this.form.description = description;
        this.form.name = code + " " + description;
        this.$emit("add", this.form);
        this.close();
      },
    },
  };
  </script>