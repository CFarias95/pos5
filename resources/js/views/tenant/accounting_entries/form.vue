<template>
  <div class="card mb-0 pt-2 pt-md-0">
    <div class="page-header pr-0">
      <h2>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
      </h2>
      <ol class="breadcrumbs">
        <li class="active"><span>Crear Asiento Contable</span></li>
      </ol>
    </div>
    <div class="tab-content" v-if="loading_form">
      <div class="invoice">
        <div class="form-body">
          <div class="row mt-1">
            <div class="col-lg-6">
              <div
                class="form-group row"
                :class="{ 'has-danger': errors.seat_date }"
              >
                <label
                  class="col-sm-4 col-form-label font-weight-bold text-info"
                  >Fecha:</label
                >
                <div class="col-sm-6">
                  <el-date-picker
                    v-model="form.seat_date"
                    type="date"
                    value-format="yyyy-MM-dd"
                    :clearable="false"
                  ></el-date-picker>
                </div>
                <small
                  class="form-control-feedback"
                  v-if="errors.seat_date"
                  v-text="errors.seat_date[0]"
                ></small>
              </div>
            </div>
          </div>

          <div class="row mt-1">
            <div class="col-lg-6">
              <div class="form-group row">
                <label
                  class="col-sm-4 col-form-label font-weight-bold text-info"
                  >Comentario:</label
                >
                <div class="col-sm-8">
                  <el-input v-model="form.comment"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.comment"
                    v-text="errors.comment[0]"
                  ></small>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-1">
            <div class="col-lg-6">
              <div class="form-group row">
                <label
                  class="col-sm-4 col-form-label font-weight-bold text-info"
                  >Tipo Asiento:</label
                >
                <div class="col-sm-8">
                  <el-select
                    v-model="form.types_accounting_entrie_id"
                    filterable
                    :class="{
                      'h-danger': errors['types_accounting_entrie_id'],
                    }"
                  >
                    <el-option
                      v-for="option in types_seat"
                      :key="option.id"
                      :value="option.id"
                      :label="option.name"
                    ></el-option>
                  </el-select>
                  <small
                    class="text-danger"
                    v-if="errors['types_accounting_entrie_id']"
                    v-text="errors['types_accounting_entrie_id'][0]"
                  ></small>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-1">
            <div class="col-lg-8">
              <div class="form-group row">
                <div class="col-sm-3 font-weight-bold text-info">
                  <el-select v-model="form.is_client" readonly="true">
                    <el-option
                      v-for="option in tipo_cliente"
                      :key="option.id"
                      :value="option.id"
                      :label="option.name"
                    ></el-option>
                  </el-select>
                </div>

                <div class="col-sm-8 flex d-flex" v-if="form.is_client == 0">
                  <el-select
                    :class="{ 'h-danger': errors['person_id'] }"
                    v-model="customer_id"
                    filterable
                    remote
                    placeholder="Escriba el nombre o número de documento del cliente"
                    :remote-method="searchRemoteCustomers"
                    @focus="focus_on_client = true"
                    @blur="focus_on_client = false"
                    :loading="loading_search"
                  >
                    <el-option
                      v-for="option in customers"
                      :key="option.id"
                      :value="option.id"
                      :label="option.description"
                    ></el-option>
                  </el-select>
                  <button v-if="customer_id!==null" type="button" @click="clearCustomer" class="btn waves-effect waves-light btn-xs btn-danger mx-2 m-0">
                    <i class="fas fa-fw fa-times" ></i>
                     &nbsp; </button>
                  <a
                    class="col-md-3 d-flex align-items-center"
                    href="#"
                    @click.prevent="showDialogNewPerson = true"
                    >[+ Nuevo]</a
                  >
                </div>

                <div class="col-sm-8 flex d-flex" v-if="form.is_client == 1">
                  <el-select
                    :class="{ 'h-danger': errors['person_id'] }"
                    v-model="supplier_id"
                    filterable
                    remote
                    class="border-left rounded-left border-info"
                    popper-class="el-select-customers"
                    placeholder="Escriba el nombre o número de documento del proveedor"
                    :remote-method="searchRemoteSuppliers"
                    @focus="focus_on_client = true"
                    @blur="focus_on_client = false"
                    :loading="loading_search"
                  >
                    <el-option
                      v-for="option in suppliers"
                      :key="option.id"
                      :value="option.id"
                      :label="option.description"
                    ></el-option>
                  </el-select>
                  <button v-if="supplier_id!==null" type="button" @click="clearSupplier" class="btn waves-effect waves-light btn-xs btn-danger mx-2 m-0">
                    <i class="fas fa-fw fa-times" ></i>
                     &nbsp; </button>
                  <a
                    class="col-md-3 d-flex align-items-center"
                    href="#"
                    @click.prevent="showDialogNewSupplier = true"
                    >[+ Nuevo]</a
                  >
                </div>
                <div class="offset-md-3 col-md-8">
                  <small
                    class="text-danger"
                    v-if="errors['person_id']"
                    v-text="errors['person_id'][0]"
                  ></small>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-12">
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th width="5%">#</th>
                      <th class="font-weight-bold" width="30%">Cuenta</th>
                      <th width="15%" class="text-center font-weight-bold">
                        Debe
                      </th>
                      <th width="15%" class="text-center font-weight-bold">
                        Haber
                      </th>
                      <th class="text-center font-weight-bold">Centro costo</th>
                      <th class="text-center font-weight-bold">Comentario</th>
                      <th class="text-center font-weight-bold" width="4%"></th>
                    </tr>
                  </thead>
                  <tbody v-if="form.items.length > 0">
                    <tr v-for="(row, index) in form.items" :key="index">
                      <td>{{ index + 1 }}</td>
                      <td>
                        <div
                          class="input-group"
                          :class="{
                            'h-danger':
                              errors['items.' + index + '.account_movement_id'],
                          }"
                        >
                          <el-input
                            v-model="row.cuenta"
                            :readonly="true"
                            class="cursor-pointer form-control borde-0 p-0"
                            type="text"
                          ></el-input>
                          <div class="input-group-append">
                            <button
                              class="btn btn-outline-secondary"
                              style="border-color: #ced4da"
                              type="button"
                              @click="clickAddItem(index)"
                            >
                              <i class="fa fa-search"></i>
                            </button>
                          </div>
                        </div>
                        <small
                          class="text-danger"
                          v-if="
                            errors['items.' + index + '.account_movement_id']
                          "
                          v-text="
                            errors['items.' + index + '.account_movement_id'][0]
                          "
                        ></small>
                      </td>
                      <td class="text-center">
                        <el-input-number
                          v-model="row.debe"
                          :controls="false"
                          :min="0.0"
                          :precision="precision"
                          @input="calculateTotal()"
                          prefix-icon="feather feather-dollar-sign"
                        ></el-input-number>
                      </td>
                      <td class="text-center">
                        <el-input-number
                          v-model="row.haber"
                          :controls="false"
                          :min="0.0"
                          :precision="precision"
                          @input="calculateTotal()"
                          prefix-icon="feather feather-dollar-sign"
                        >
                        </el-input-number>
                      </td>
                      <td class="text-center">
                        <el-input
                          :class="{
                            'h-danger': errors['items.' + index + '.seat_cost'],
                          }"
                          v-model="row.seat_cost"
                        ></el-input>
                        <small
                          class="text-danger"
                          v-if="errors['items.' + index + '.seat_cost']"
                          v-text="errors['items.' + index + '.seat_cost'][0]"
                        ></small>
                      </td>
                      <td class="text-center">
                        <el-input
                          :class="{
                            'h-danger': errors['items.' + index + '.comment'],
                          }"
                          v-model="row.comment"
                        ></el-input>
                        <small
                          class="text-danger"
                          v-if="errors['items.' + index + '.comment']"
                          v-text="errors['items.' + index + '.comment'][0]"
                        ></small>
                      </td>
                      <td class="series-table-actions text-center">
                        <div v-if="index > 1">
                          <button
                            type="button"
                            class="btn waves-effect waves-light btn-xs btn-danger"
                            @click.prevent="clickRemoveItem(index)"
                          >
                            <i class="fa fa-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td class="border-top border-dark">
                        <el-input-number
                          v-model="form.total_debe"
                          :controls="false"
                          :min="0.0"
                          :disabled="true"
                          :precision="precision"
                          prefix-icon="\E749"
                        ></el-input-number>
                      </td>
                      <td class="border-top border-dark">
                        <el-input-number
                          v-model="form.total_haber"
                          :controls="false"
                          :min="0.0"
                          disabled
                          :precision="precision"
                          prefix-icon="feather feather-dollar-sign"
                        ></el-input-number>
                      </td>
                      <td></td>
                      <td></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p class="text-center text-success" v-if="habilitado === true">
                ASIENTO CORRECTO SE PUEDE GUARDAR
              </p>
              <p class="text-center text-danger" v-if="habilitado === false">
                ASIENTO DESCUADRADO NO SE GUARDA
              </p>
            </div>
            <div class="col-lg-12 col-md-6 d-flex align-items-end">
              <div class="form-group">
                <button
                  type="button"
                  class="btn waves-effect waves-light btn-primary"
                  @click="addNewRow"
                >
                  + Agregar Detalle
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="form-actions text-right mt-4">
          <el-button @click.prevent="close()">Cancelar</el-button>
          <el-button
            class="submit"
            type="primary"
            native-type="submit"
            :loading="loading_submit"
            v-if="habilitado === true"
            @click.prevent="submit"
            >Generar</el-button
          >
        </div>
      </div>
    </div>

    <account-form
      :showDialog.sync="showDialogAddItem"
      :typeUser="typeUser"
      :configuration="config"
      :row-id="row_id"
      @add="addRow"
    ></account-form>
    <person-form
      :showDialog.sync="showDialogNewPerson"
      type="customers"
      :external="true"
      :input_person="input_person"
      :document_type_id="form.document_type_id"
    ></person-form>

        <account-options :showDialog.sync="showDialogOptions"
                          :recordId="quotationNewId"
                          :typeUser="typeUser"
                          :showGenerate="false"
                          :showClose="false"></account-options>
    <supplier-form
      :external="true"
      :input_person="input_person"
      :showDialog.sync="showDialogNewSupplier"
      type="suppliers"
    ></supplier-form>
  </div>
</template>

<script>
import AccountForm from "./partials/item.vue";
import PersonForm from "../persons/form.vue";
import SupplierForm from "../persons/form.vue";
import AccountOptions from '../accounting_entries/partials/options.vue'
import { functions, exchangeRate } from "../../../mixins/functions";
import { mapActions, mapState } from "vuex/dist/vuex.mjs";

export default {
  props: ["typeUser", "configuration"],
  components: { AccountForm, PersonForm, SupplierForm, AccountOptions},
  mixins: [functions, exchangeRate],
  data() {
    return {
      resource: "accounting-entries",
      showDialogAddItem: false,
      loading_submit: false,
      loading_form: false,
      currency_types: [],
      precision: 2,
      showDialogOptions: false,
      userid: null,
      habilitado: true,
      showDialogNewPerson: false,
      showDialogNewSupplier: false,
      errors: {},
      customers: [],
      all_customers: [],
      all_suppliers: [],
      supplier_id: null,
      customer_id: null,
      suppliers: [],
      form: {},
      focus_on_client: false,
      loading_search: false,
      tipo_cliente: [
        { id: 0, name: "Cliente" },
        { id: 1, name: "Proveedor" },
      ],
      types_seat: [],
      quotationNewId: null,
      row_id: null,
      input_person: {},
    };
  },
  async created() {
    this.loadConfiguration();
    this.$store.commit("setConfiguration", this.configuration);
    await this.initForm();
    await this.$http.get(`/${this.resource}/tables`).then((response) => {
      const data = response.data;
      this.userid = data.user.id;
      this.currency_types = data.currency_types;
      this.establishments = data.establishments;
      this.all_customers = data.customers;
      this.all_suppliers = data.suppliers;
      this.types_seat = data.types_seat;
      this.company = data.company;
      //this.form.currency_type_id =this.currency_types.length > 0 ? this.currency_types[0].id : null;
      this.form.establishment_id =
        this.establishments.length > 0 ? this.establishments[0].id : null;
      this.allCustomers();
      this.allSuppliers();
      this.changeEstablishment();
    });
    this.loading_form = true;
    this.$eventHub.$on("reloadDataPersons", (customer_id) => {
      this.reloadDataCustomers(customer_id);
    });
  },
  computed: {
    ...mapState(["config"]),
  },
  methods: {
    ...mapActions(["loadConfiguration"]),

    clickAddItem(index) {
      this.row_id = index;
      this.showDialogAddItem = true;
    },

    initForm() {
      this.errors = {};
      this.customer_id=null,
      this.supplier_id=null,
      this.form = {
        user_id: null,
        currency_type_id: null,
        seat: "",
        seat_general: "",
        seat_date: moment().format("YYYY-MM-DD"),
        types_accounting_entrie_id: null,
        comment: null,
        total_debe: 0.0,
        total_haber: 0.0,
        is_client: 0,
        external_id: null,
        establishment_id: null,
        establishment: null,
        prefix: "ASC",
        filename: null,
        person_id: null,
        person: [],
        actions: {
          format_pdf: "a4",
        },
        items: [
          {
            account_movement_id: null,
            seat_line: null,
            debe: 0.0,
            haber: 0.0,
            seat_cost: null,
          },
          {
            account_movement_id: null,
            seat_line: null,
            debe: 0.0,
            haber: 0.0,
            seat_cost: null,
          },
        ],
      };
    },

    resetForm() {
      this.initForm();
        this.form.establishment_id = (this.establishments.length > 0)?this.establishments[0].id:null
        this.changeEstablishment()
    },
    addRow(row) {
      this.form.items = this.form.items.map((item, index) => {
        if (row.item_id == index) {
          item.cuenta = row.name;
          item.account_movement_id = row.id;
          item.typecost = row.cost;
        }
        return item;
      });
    },

    addNewRow() {
      this.form.items.push({
        account_movement_id: null,
        seat_line: null,
        debe: 0.0,
        haber: 0.0,
        seat_cost: null,
        comment: null,
      });
      this.calculateTotal();
    },
    reloadDataCustomers(customer_id) {
      this.$http
        .get(`/${this.resource}/search/customer/${customer_id}`)
        .then((response) => {
          this.customers = response.data.customers;
          this.form.customer_id = customer_id;
        });
    },
    changeEstablishment() {
      this.form.establishment = _.find(this.establishments, {
        id: this.form.establishment_id,
      });
    },
    searchRemoteCustomers(input) {
      if (input.length > 0) {
        this.loading_search = true;
        let parameters = `input=${input}`;
        this.$http
          .get(`/${this.resource}/search/customers?${parameters}`)
          .then((response) => {
            this.customers = response.data.customers;
            this.loading_search = false;
            this.input_person.number =
              this.customers.length == 0 ? input : null;
          });
      } else {
        this.allCustomers();
        this.input_person.number = null;
        this.customer_id=null;
      }
    },
    searchRemoteSuppliers(input) {
      if (input.length > 0) {
        this.loading_search = true;
        let parameters = `input=${input}`;

        this.$http
          .get(`/${this.resource}/search/suppliers?${parameters}`)
          .then((response) => {
            this.suppliers = response.data.suppliers;
            this.loading_search = false;
            this.input_person.number =
              this.suppliers.length == 0 ? input : null;
          });
      } else {
        this.allSuppliers();
        this.input_person.number = null;
        this.supplier_id=null;
      }
    },
    allCustomers() {
      this.customers = this.all_customers;
    },
    allSuppliers() {
      this.suppliers = this.all_suppliers;
    },

    clickRemoveItem(index) {
      this.form.items.splice(index, 1);
      this.calculateTotal();
    },

    calculateTotal() {
      let total_debe = 0.0;
      let total_haber = 0.0;

      this.form.items.forEach((row) => {
        total_debe += parseFloat(row.debe);
        total_haber += parseFloat(row.haber);
      });
      this.form.total_debe = _.round(total_debe, 2);
      this.form.total_haber = _.round(total_haber, 2);
      if (
        this.form.total_debe == this.form.total_haber &&
        this.form.total_debe > 0 &&
        this.form.total_haber > 0
      ) {
        this.habilitado = true;
      } else {
        this.habilitado = false;
      }
    },

    //guardar
    async submit() {
      this.errors = {};
      this.form.person_id=null;
      if (this.form.is_client == 0) {
        if(this.customer_id!==null){
          this.form.person_id = this.customer_id;
        }else{
            this.form.person_id=null
        }
      } else {
         if(this.supplier_id!==null){
          this.form.person_id = this.supplier_id;
        }else{
            this.form.person_id=null
        }
      }

      this.form.user_id = this.userid;
      this.form.items = this.form.items.map((item, index) => {
        item.seat_line = index + 1;
        return item;
      });

      this.loading_submit = true;

      await this.$http.post(`/${this.resource}`, this.form).then((response) => {
          if (response.data.success) {
            this.resetForm();
            this.quotationNewId = response.data.data.id;
            this.$message.success(`El Asiento ${response.data.data.number_full} fue generada`)

            this.showDialogOptions = true;
          } else {
            this.$message.error(response.data.message);
          }
        })
        .catch((error) => {
          if (error.response.status === 422) {
            this.errors = error.response.data;
          } else {
            this.$message.error(error.response.data.message);
          }
          this.loading_submit = false;
        })
        .then(() => {
          this.loading_submit = false;
        });
    },
    close() {
      location.href = "/accounting-entries";
    },
    clearCustomer(){
      this.customer_id=null;
      this.supplier_id=null;
      this.form.person_id=null;
    },
    clearSupplier(){
      this.supplier_id=null;
      this.customer_id=null;
      this.form.person_id=null;
    }
  },
};
</script>
