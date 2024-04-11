<template>
  <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create">
    <form autocomplete="off" @submit.prevent="submit">
      <div class="form-body">
        <div class="row">
          <div class="col-md-8">
            <div class="form-group" :class="{ 'has-danger': errors.item_id }">
              <label class="control-label">Producto</label>
              <el-select
                v-model="form.item_id"
                filterable
                remote
                :remote-method="searchRemoteItems"
                :loading="loading_search"
                @change="changeItem"
              >
                <el-option
                  v-for="(option, index) in items"
                  :key="index"
                  :value="option.id"
                  :label="option.description"
                ></el-option>
              </el-select>
              <small
                class="form-control-feedback"
                v-if="errors.item_id"
                v-text="errors.item_id[0]"
              ></small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group" :class="{ 'has-danger': errors.quantity }">
              <label class="control-label">Cantidad</label>
              <el-input type ="number" v-model="form.quantity" :step="0.001"></el-input>
              <small
                class="form-control-feedback"
                v-if="errors.quantity"
                v-text="errors.quantity[0]"
              ></small>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-group" :class="{ 'has-danger': errors.warehouse_id }">
              <label class="control-label">Almacén</label>
              <el-select v-model="form.warehouse_id" filterable @change="changeItem">
                <el-option
                  v-for="(option, index) in warehouses_filter"
                  :key="index"
                  :value="option.id"
                  :label="option.description"
                ></el-option>
              </el-select>
              <small
                class="form-control-feedback"
                v-if="errors.warehouse_id"
                v-text="errors.warehouse_id[0]"
              ></small>
            </div>
          </div>
          <div
            style="padding-top: 3%"
            class="col-md-2 col-sm-2"
            v-if="form.warehouse_id !== null && form.lots_enabled"
          >
            <a
              href="#"
              class="text-center font-weight-bold text-info"
              @click.prevent="clickLotGroup"
              >[&#10004; Seleccionar lote]</a
            >
          </div>
          <div
            style="padding-top: 3%"
            class="col-md-3 col-sm-3"
            v-if="form.item_id && form.series_enabled"
          >
            <!-- <el-button type="primary" native-type="submit" icon="el-icon-check">Elegir serie</el-button> -->
            <a
              href="#"
              class="text-center font-weight-bold text-info"
              @click.prevent="clickSelectLots"
              >[&#10004; Seleccionar series]</a
            >
          </div>

          <!--<div class="col-md-3" v-show="form.lots_enabled">
                        <div class="form-group" :class="{'has-danger': errors.date_of_due}">
                            <label class="control-label">Fec. Vencimiento</label>
                            <el-date-picker v-model="form.date_of_due" type="date" value-format="yyyy-MM-dd" :clearable="true"></el-date-picker>
                            <small class="form-control-feedback" v-if="errors.date_of_due" v-text="errors.date_of_due[0]"></small>
                        </div>
                    </div> -->

          <!--<div style="padding-top: 3%" class="col-md-4" v-if="form.warehouse_id && form.series_enabled">

                        <a href="#"  class="text-center font-weight-bold text-info" @click.prevent="clickLotcode">[&#10004; Ingresar series]</a>
                    </div>  -->
          <div class="col-md-8">
            <div
              class="form-group"
              :class="{
                'has-danger': errors.inventory_transaction_id,
              }"
            >
              <label class="control-label">Motivo traslado</label>
              <el-select v-model="form.inventory_transaction_id" filterable>
                <el-option
                  v-for="(option, index) in inventory_transactions"
                  :key="index"
                  :value="option.id"
                  :label="option.name"
                ></el-option>
              </el-select>
              <small
                class="form-control-feedback"
                v-if="errors.inventory_transaction_id"
                v-text="errors.inventory_transaction_id[0]"
              ></small>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-5">
            <label class="control-label">Fecha Produccion Finalizada</label>
            <el-date-picker
              v-model="form.filter_date"
              type="date"
              value-format="yyyy-MM-dd"
              format="dd/MM/yyyy"
              :clearable="true"
              @change="filterProductionDate"
            ></el-date-picker>
          </div>
          <div class="col-md-7">
            <label class="control-label">Produccion Finalizada</label>
            <el-select v-model="form.production_id" filterable clearable>
              <el-option
                v-for="(option, index) in production"
                :key="index"
                :value="option.id"
                :label="option.name"
              >
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="row">
          <div class="col-md-3">
            <label class="control-label">Fecha registro</label>
            <el-date-picker
              v-model="form.created_at"
              type="datetime"
              value-format="yyyy-MM-dd HH:mm:ss"
              format="dd/MM/yyyy HH:mm:ss"
              :clearable="true"
            ></el-date-picker>
          </div>
          <div class="col-md-8">
            <div class="form-group" :class="{ 'has-danger': errors.comments }">
              <label class="control-label">Comentarios </label>
              <el-input
                type="textarea"
                :rows="3"
                :maxlength="250"
                v-model="form.comments"
              ></el-input>
              <small
                class="form-control-feedback"
                v-if="errors.comments"
                v-text="errors.comments[0]"
              ></small>
            </div>
          </div>
        </div>
      </div>
      <div class="form-actions text-right mt-4">
        <el-button @click.prevent="close()">Cancelar</el-button>
        <el-button type="primary" native-type="submit" :loading="loading_submit"
          >Aceptar</el-button
        >
      </div>
    </form>

    <lots-group
      :quantity="form.quantity"
      :showDialog.sync="showDialogLots"
      :lots_group="form.lots_group"
      @addRowLotGroup="addRowLotGroup"
    >
    </lots-group>

    <select-lots-form
      :showDialog.sync="showDialogSelectLots"
      :lots="form.lots"
      @addRowSelectLot="addRowSelectLot"
    >
    </select-lots-form>
    <options
      :showDialog.sync="showDialogOptions"
      :recordId="this.inventory_id"
      :showClose="this.showClose"
      :type="this.type"
    >
    </options>
  </el-dialog>
</template>

<script>
import LotsGroup from "./lots_group.vue";
import SelectLotsForm from "./lots.vue";
import Options from "./partials/options.vue";
import { filterWords } from "../../../../../../resources/js/helpers/functions";

export default {
  components: { LotsGroup, SelectLotsForm, Options },
  props: ["showDialog", "recordId", "itemId", "warehouseId", "prod_order", "index"],
  data() {
    return {
      type: "output",
      loading: false,
      loading_search: false,
      loading_submit: false,
      showDialogLots: false,
      showDialogSelectLots: false,
      showDialogOptions: false,
      titleDialog: null,
      resource: "inventory",
      errors: {},
      form: {},
      items: [],
      warehouses: [],
      warehouses_filter: [],
      inventory_transactions: [],
      inventory_id: null,
      email: null,
      showClose: false,
      production: [],
    };
  },
  methods: {
    async changeItem() {
      this.form.lots = [];
      let item = _.find(this.items, { id: this.form.item_id });
      let idlots = item.lots_group
        .filter((obj) => obj.warehouse_id !== undefined && obj.warehouse_id !== null)
        .map((obj) => obj.warehouse_id)
        .sort();
      //this.warehouses_filter = this.warehouses.filter((obj) => idlots.includes(obj.id));
      if (idlots.length === 0) {
          this.warehouses_filter = this.warehouses;
      } else {
          this.warehouses_filter = this.warehouses.filter((obj) => idlots.includes(obj.id));
      }
      this.form.lots_enabled = item.lots_enabled;
      let lots = _.filter(item.lots, { warehouse_id: this.form.warehouse_id });
      this.form.lots = lots;
      this.form.series_enabled = item.series_enabled;
      this.form.lots_group_original = item.lots_group;
      this.form.purchase_mean_price = item.purchase_mean_price;
    },
    addRowOutputLot(lots) {
      this.form.lots = lots;
    },
    addRowLot(lots) {
      this.form.lots = lots;
    },
    clickLotcode() {
      this.showDialogLots = true;
    },
    clickLotcodeOutput() {
      this.showDialogLotsOutput = true;
    },
    filterProductionDate() {
      this.$http
        .get(`/${this.resource}/filterProduction/${this.form.filter_date}`)
        .then((response) => {
          this.production = response.data;
          console.log('response', response.data);
        });
    },
    initForm() {
      this.errors = {};
      this.form = {
        id: null,
        item_id: null,
        warehouse_id: null,
        inventory_transaction_id: null,
        quantity: 0,
        type: this.type,
        lot_code: null,
        lots_enabled: false,
        series_enabled: false,
        lots: [],
        date_of_due: null,
        IdLoteSelected: null,
        lots_group: [],
        lots_group_original: [],
        created_at: null,
        comments: null,
        purchase_mean_price: null,
      };
    },
    async initTables() {
      await this.$http
        .get(`/${this.resource}/tables/transaction/${this.type}`)
        .then((response) => {
          // this.items = response.data.items
          this.warehouses = response.data.warehouses;
          console.log("warehouses", this.warehouses);
          this.inventory_transactions = response.data.inventory_transactions;
        });
      await this.searchRemoteItems("");
    },
    async create() {
      this.loading = true;
      this.titleDialog = "Salida de producto del almacén";
      await this.initTables();
      this.initForm();
      this.loading = false;
      if (this.itemId != null && this.warehouseId != null) {

        //console.log("warehouses", this.warehouses);
        this.form.warehouse_id = this.warehouseId;
        this.form.item_id = this.itemId;
        this.form.production_id = this.prod_order;
        await this.changeItem();
      } else {
        console.log("No trae data");
      }
      this.filterProductionDate();
    },
    // async create() {
    //     this.titleDialog = 'Salida de producto del almacén'
    //     await this.$http.get(`/${this.resource}/tables/transaction/output`)
    //         .then(response => {
    //             // this.items = response.data.items
    //             this.warehouses = response.data.warehouses
    //             this.inventory_transactions = response.data.inventory_transactions
    //         })
    //
    // },
    async searchRemoteItems(search) {
      this.loading_search = true;
      this.items = [];
      await this.$http
        .post(`/${this.resource}/search_items`, { search: search })
        .then((response) => {
          let items = response.data.items;
          if (items.length > 0) {
            this.items = items; //filterWords(search, items);
          }
        });
      this.loading_search = false;
    },
    async submit() {
      if (this.form.lots.length > 0 && this.form.series_enabled) {
        console.log('lotes SEries: ',this.form.lots)
        let select_lots = await _.filter(this.form.lots, { has_sale: true });
        if (select_lots.length != this.form.quantity) {
          return this.$message.error(
            "La cantidad ingresada "+this.form.quantity+" es diferente a las series seleccionadas "+ select_lots.length
          );
        }
      }
      if (this.form.lots_enabled) {
        if (!this.form.lot_code) return this.$message.error("Debe seleccionar un lote.");
      }
      this.loading_submit = true;
      this.form.type = this.type;

      await this.$http
        .post(`/${this.resource}/transaction`, this.form)
        .then((response) => {
          //console.log('response ', response)
          if (response.data.success) {
            //console.log('entro al if success')
            this.$message.success(response.data.message);
            this.$eventHub.$emit("reloadData");
            //this.$emit('update:showDialog', false)

            this.showClose = false;
            this.inventory_id = response.data.id;
            if (this.itemId != null && this.warehouseId != null) {
              this.showDialogOptions = false;
              this.$emit("reloadStock", this.index, this.itemId, this.warehouseId );
              this.close();
            } else {
              this.showDialogOptions = true;
            }

            this.initForm();
          } else {
            this.$message.error(response.data.message);
          }
        })
        .catch((error) => {
          if (error.response.status === 422) {
            this.errors = error.response.data;
            // console.log(error.response.data)
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
    clickLotGroup() {
      this.form.lots_group = [];
      this.form.lots_group = this.form.lots_group_original.filter(
        (obj) => obj.warehouse_id == this.form.warehouse_id
      );
      this.showDialogLots = true;
    },
    addRowLotGroup(id) {
      this.form.lot_code = id;
      this.form.IdLoteSelected = true;
    },
    async clickSelectLots() {
      this.showDialogSelectLots = true;
    },
    addRowSelectLot(lots) {
      this.form.lots = lots;
    },
  },
};
</script>
