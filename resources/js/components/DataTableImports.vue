<template>
  <div>
    <div class="row">
      <div class="col-md-12 col-lg-12 col-xl-12">
        <div class="row col-12">
          <div class="col-lg-9 col-md-8 col-sm-12 mb-2">
            <div class="form-group">
              <label class="control-label font-custom"
                ><strong>Filtros de busqueda</strong></label
              >
              <template v-if="!see_more">
                <a
                  class="control-label font-weight-bold text-info font-custom"
                  href="#"
                  @click="clickSeeMore"
                  ><strong> [+ Ver más]</strong></a
                >
              </template>
              <template v-else>
                <a
                  class="control-label font-weight-bold text-info font-custom"
                  href="#"
                  @click="clickSeeMore"
                  ><strong> [- Ver menos]</strong></a
                >
              </template>
            </div>
          </div>
          <div class="col-lg-2 col-md-3 col-sm-12 text-right">
            <slot name="showhide"></slot>
          </div>
        </div>
        <div class="row mt-2" v-if="see_more">
          <div class="col-lg-2 col-md-2">
            <div class="form-group">
              <label class="control-label">Estado</label>
              <el-select
                v-model="search.estado"
                popper-class="el-select-document_type"
                filterable
                clearable
              >
                <el-option
                  v-for="option in estados"
                  :key="option.id"
                  :value="option.description"
                  :label="option.description"
                ></el-option>
              </el-select>
            </div>
          </div>
          <div class="col-lg-2 col-md-2">
            <div class="form-group">
              <label class="control-label">Transporte</label>
              <el-select
                v-model="search.tipoTransporte"
                popper-class="el-select-document_type"
                filterable
                clearable
              >
                <el-option
                  v-for="option in tipoTransportes"
                  :key="option.id"
                  :value="option.description"
                  :label="option.description"
                ></el-option>
              </el-select>
            </div>
          </div>
          <div class="col-lg-2 col-md-2">
            <div class="form-group">
              <label class="control-label">Importacion</label>
              <el-input placeholder="Ingresar" v-model="search.numeroImportacion">
              </el-input>
            </div>
          </div>
          <div class="col-lg-2 col-md-2 pb-2">
            <div class="form-group">
              <label class="control-label">Fecha Embarque </label>
              <el-date-picker
                v-model="search.fechaEmbarque"
                type="date"
                style="width: 100%"
                placeholder="Buscar"
                value-format="yyyy-MM-dd"
                @change="changeDisabledDates"
              >
              </el-date-picker>
            </div>
          </div>
          <div class="col-lg-2 col-md-2 pb-2">
            <div class="form-group">
              <label class="control-label">Fecha Llegada</label>
              <el-date-picker
                v-model="search.fechaLlegada"
                type="date"
                style="width: 100%"
                placeholder="Buscar"
                value-format="yyyy-MM-dd"
                :picker-options="pickerOptionsDates"
                @change="changeEndDate"
              >
              </el-date-picker>
            </div>
          </div>
          <div class="col-lg-2 col-md-2 col-sm-12 pb-2">
            <label class="control-label">Fecha emisión</label>
            <el-date-picker
              v-model="search.date_of_issue"
              type="date"
              style="width: 100%"
              placeholder="Buscar"
              value-format="yyyy-MM-dd"
              @change="changeDateOfIssue"
            >
            </el-date-picker>
          </div>
          <div class="col-lg-4 col-md-4 col-md-4 col-sm-12" style="margin-top: 29px">
            <el-button
              class="submit"
              type="primary"
              @click.prevent="getRecordsByFilter"
              :loading="loading_submit"
              icon="el-icon-search"
              >Buscar</el-button
            >
            <el-button
              class="submit"
              type="info"
              @click.prevent="cleanInputs"
              icon="el-icon-delete"
              >Limpiar
            </el-button>
          </div>
        </div>
        <div class="row mt-1 mb-3"></div>

        <!-- Totales -->
        <div
          class="row col-md-12 mt-1 mb-3"
          v-if="totals !== undefined && totals !== null && totals.length > 0"
        >
          <div
            class="col-md-6 col-sm-12 row"
            v-for="(row, index) in totals"
            :row="row"
            :key="index"
          >
            <div class="col-md-6">
              {{ row.name }}
            </div>
            <div class="col-md-6 text-right">
              {{ row.total }}
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <div id="scroll1" style="overflow-x: auto">
          <div style="height: 20px"></div>
        </div>
        <div class="table-responsive" id="scroll2" style="overflow-x: auto">
          <table class="table">
            <thead>
              <slot name="heading"></slot>
            </thead>
            <tbody>
              <slot
                v-for="(row, index) in records"
                :row="row"
                :index="customIndex(index)"
              ></slot>
            </tbody>
          </table>
          <div>
            <el-pagination
              @current-change="getRecords"
              layout="total, prev, pager, next"
              :total="pagination.total"
              :current-page.sync="pagination.current_page"
              :page-size="pagination.per_page"
            >
            </el-pagination>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
.font-custom {
  font-size: 15px !important;
}
</style>
<script>
import moment from "moment";
import queryString from "query-string";
import $ from "jquery";
import { mapActions, mapState } from "vuex/dist/vuex.mjs";

export default {
  props: {
    resource: String,
  },
  data() {
    return {
      loading_submit: false,
      columns: [],
      totals: [],
      records: [],
      customers: [],
      all_customers: [],
      items: [],
      all_items: [],
      loading_search: false,
      loading_search_item: false,
      estados: [
        { id: 1, description: "Registrada" },
        { id: 2, description: "Liberada" },
        { id: 3, description: "Liquidada" },
      ],
      tipoTransportes: [
        { id: 1, description: "Aereo" },
        { id: 2, description: "Maritimo" },
        { id: 3, description: "Terrestre" },
      ],
      categories: [],
      state_types: [],
      pagination: {},
      search: {},
      all_series: [],
      establishment: null,
      establishments: [],
      series: [],
      activePanel: 0,
      see_more: false,
      pickerOptionsDates: {
        disabledDate: (time) => {
          time = moment(time).format("YYYY-MM-DD");
          return this.search.fechaEmbarque > time;
        },
      },
    };
  },
  computed: {
    ...mapState(["config"]),
  },
  created() {
    this.loadConfiguration();
    this.initForm();
    this.$eventHub.$on("reloadData", () => {
      this.getRecords();
    });
  },
  async mounted() {
    await this.getRecords();
    //await this.filterCustomers()
    //await this.filterItems()
    await this.cargalo();
  },
  methods: {
    ...mapActions(["loadConfiguration"]),

    searchRemoteItems(input) {
      if (input.length > 0) {
        this.loading_search = true;
        let parameters = `input=${input}`;

        this.$http.get(`/documents/data-table/items?${parameters}`).then((response) => {
          // console.log(response.data)
          this.items = response.data;
          this.loading_search = false;

          if (this.items.length == 0) {
            this.filterItems();
          }
        });
      } else {
        this.filterItems();
      }
    },
    filterItems() {
      this.items = this.all_items;
    },
    searchRemoteCustomers(input) {
      if (input.length > 0) {
        this.loading_search = true;
        let parameters = `input=${input}`;

        this.$http
          .get(`/documents/data-table/customers?${parameters}`)
          .then((response) => {
            this.customers = response.data.customers;
            this.loading_search = false;

            if (this.customers.length == 0) {
              this.filterCustomers();
            }
          });
      } else {
        this.filterCustomers();
      }
    },
    filterCustomers() {
      this.customers = this.all_customers;
    },
    clickSeeMore() {
      this.see_more = this.see_more ? false : true;
    },
    initForm() {
      this.search = {
        date_of_issue: null,
        document_type_id: null,
        customer_id: null,
        item_id: null,
        category_id: null,
        state_type_id: null,
        series: null,
        number: null,
        d_start: null,
        d_end: null,
        pending_payment: false,
        numeroImportacion: null,
      };
    },
    filterSeries() {
      this.search.series = null;
      this.series = _.filter(this.all_series, {
        document_type_id: this.search.document_type_id,
      });
      this.search.series = this.series.length > 0 ? this.series[0].number : null;
    },
    customIndex(index) {
      return this.pagination.per_page * (this.pagination.current_page - 1) + index + 1;
    },
    async getRecordsByFilter() {
      this.loading_submit = await true;
      await this.getRecords();
      this.loading_submit = await false;
      this.getTotalRecords();
    },
    getRecords() {
      return this.$http
        .get(`/${this.resource}/records?${this.getQueryParameters()}`)
        .then((response) => {
          console.log(response.data);
          this.records = response.data.data;
          this.pagination = response.data.meta;
          this.pagination.per_page = parseInt(response.data.meta.per_page);
          this.loading_submit = false;
        });
    },
    getTotalRecords() {
      if (this.config.show_totals_on_cpe_list !== true) return null;
      if (this.config.typeUser !== "admin") return null;
      return this.$http
        .get(`/${this.resource}/recordsTotal?${this.getQueryParameters()}`)
        .then((response) => {
          this.totals = response.data;
        });
    },
    getQueryParameters() {
      return queryString.stringify({
        page: this.pagination.current_page,
        limit: this.limit,
        ...this.search,
      });
    },
    changeClearInput() {
      this.search.value = "";
      // this.getRecords()
    },
    changeDisabledDates() {},
    changeDateOfIssue() {
      this.search.fechaLlegada = null;
      this.search.fechaEmbarque = null;
    },
    changeEndDate() {
      this.search.date_of_issue = null;
      this.search.fechaEmbarque = null;
    },
    cleanInputs() {
      this.initForm();
    },
    cargalo() {
      $("#scroll1 div").width($(".table").width());
      $("#scroll1").on("scroll", function () {
        $("#scroll2").scrollLeft($(this).scrollLeft());
      });
      $("#scroll2").on("scroll", function () {
        $("#scroll1").scrollLeft($(this).scrollLeft());
      });
    },
  },
};
</script>
