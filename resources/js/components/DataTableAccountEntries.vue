<template>
  <div>
    <div class="card-body">

      <div class="col-md-12 col-lg-12 col-xl-12 ">

        <div class="row">
          <div class="col-md-7 col-lg-7 col-xl-7">
            <div class="form-group row">
              <label class="col-sm-4 col-form-label font-weight-bold">
                Cuenta:
              </label>
              <div class="col-lg-8 col-md-8 col-sm-8 pb-2">
                <el-select v-model="form.account_movement_id" filterable>
                  <el-option v-for="option in accounts" :key="option.id" :value="option.id"
                    :label="option.description"></el-option>
                </el-select>
              </div>
            </div>
          </div>
          <div class="col-md-5 col-lg-5 col-xl-5">
            <div class="form-group row">
              <label class="col-sm-4 col-form-label font-weight-bold">
                Desde:
              </label>
              <div class="col-lg-8 col-md-8 col-sm-8 pb-2">
                <el-date-picker v-model="form.date_start" type="date" @change="changeDisabledDates"
                  value-format="yyyy-MM-dd" format="dd/MM/yyyy" :clearable="false"></el-date-picker>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-7 col-lg-7 col-xl-7">
            <div class="form-group row">
              <label class="col-sm-4 col-form-label font-weight-bold">
                Centro de costo:
              </label>
              <div class="col-lg-8 col-md-8 col-sm-8 pb-2">
                <el-input placeholder="Buscar" v-model="form.cost" style="width: 100%;">
                </el-input>
              </div>
            </div>
          </div>
          <div class="col-md-5 col-lg-5 col-xl-5">
            <div class="form-group row">
              <label class="col-sm-4 col-form-label font-weight-bold">
                Hasta:
              </label>
              <div class="col-lg-8 col-md-8 col-sm-8 pb-2">
                <el-date-picker v-model="form.date_end" type="date" value-format="yyyy-MM-dd"
                  :picker-options="pickerOptionsDates" format="dd/MM/yyyy" :clearable="false">
                </el-date-picker>
              </div>
            </div>
          </div>
        </div>


        <div class="row">
          <div class="col-md-7 col-lg-7 col-xl-7">
            <div class="form-group row">
              <label class="col-sm-4 col-form-label font-weight-bold">
                Buscar:
              </label>
              <div class="col-lg-8 col-md-8 col-sm-8 pb-2">
                <el-input placeholder="Buscar" v-model="form.search" style="width: 100%;">
                </el-input>
              </div>
            </div>
          </div>
          <div class="col-md-5 col-lg-5 col-xl-5">
            <div class="form-group row">
              <label class="col-sm-4 col-form-label font-weight-bold">
                Tipo:
              </label>
              <div class="col-lg-8 col-md-8 col-sm-8 pb-2">
                <el-select v-model="form.types_accounting_entrie_id" filterable>
                  <el-option v-for="option in types_seat" :key="option.id" :value="option.id"
                    :label="option.name"></el-option>
                </el-select>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="searhForm()"><i
                class="fa fa-search"></i> Consultar</button>
          </div>
        </div>
      </div>
    </div>


    <div class="col-md-12 px-0 pt-3">
      <div class="row flex justify-content-center" v-if="showButtons">
        <span class="loader-custom"></span>
      </div>
      <div class="table-responsive" v-if="!showButtons">

        <div>
          <slot name="heading"></slot>
        </div>
        <div>
          <slot v-for="(row, index) in records" :row="row" :index="customIndex(index)"></slot>
        </div>

        <div>
          <el-pagination @current-change="getRecords" layout="total, prev, pager, next" :total="pagination.total"
            :current-page.sync="pagination.current_page" :page-size="pagination.per_page">
          </el-pagination>
        </div>
      </div>
    </div>
  </div>

</template>


<script>
import moment from "moment";
import queryString from "query-string";

export default {
  props: {
    resource: String,
    applyFilter: {
      type: Boolean,
      default: true,
      required: false,
    },
  },
  data() {
    return {
      types_accounting_entrie_id: null,
      account_movement_id: null,
      showButtons: false,
      types_seat: [],
      accounts: [],
      search: {
        column: null,
        form: null,
        value: null,
      },
      columns: [],
      records: [],
      form: {},
      pagination: {},
      pickerOptionsDates: {
        disabledDate: (time) => {
          time = moment(time).format("YYYY-MM-DD");
          return this.form.date_start > time;
        },
      },
      pickerOptionsMonths: {
        disabledDate: (time) => {
          time = moment(time).format("YYYY-MM");
          return this.form.month_start > time;
        },
      },
    };
  },
  computed: {},
  async created() {
    this.initForm();
    this.$eventHub.$on("reloadData", () => {
      this.getRecords();
    });
    this.$http
      .get(`/${this.resource}/item/tables`)
      .then((response) => {
        let data = response.data;
        this.accounts = data.account_movement2;
        this.types_seat = data.types_seat;
        this.types_seat.unshift({ id: '0', name: 'Todos' })
        this.accounts.unshift({ id: '0', description: 'Todos' })
      });
  },
  async mounted() {
    let column_resource = _.split(this.resource, "/");
    await this.$http
      .get(`/${_.head(column_resource)}/columns`)
      .then((response) => {
        this.columns = response.data;
        this.search.column = _.head(Object.keys(this.columns));
      });
    await this.getRecords();
  },
  methods: {
    changeDisabledDates() {
      if (this.form.date_end < this.form.date_start) {
        this.form.date_end = this.form.date_start;
      }

      this.selectDate();
    },


    selectDate() {
      // this.form.date_start = null;
      // this.form.date_end = null;
      // this.form.date_start = moment().startOf('month').format('YYYY-MM-DD');
      //this.form.date_end = moment().endOf('month').format('YYYY-MM-DD');

      //   this.getRecords();
    },
    //changeDisabledMonths() {},
    /*changePeriod() {
      this.form.date_start = null;
      this.form.date_end = null;
      this.form.week = null;
      this.form.month = null;
      this.form.d_start = null;
      this.form.d_end = null;

      this.getRecords();
    },*/
    initForm() {
      this.form = {
        period: 'between_dates',
        date_start: moment().startOf('month').format('YYYY-MM-DD'),
        date_end: moment().endOf('month').format('YYYY-MM-DD'),
        account_movement_id: null,
        types_accounting_entrie_id: null,
        search: null,
        cost: null
      };
    },
    customIndex(index) {
      return (
        this.pagination.per_page * (this.pagination.current_page - 1) +
        index +
        1
      );
    },
    getRecords() {
      this.search.form = JSON.stringify(this.form);
      this.showButtons = true;
      return this.$http
        .get(`/${this.resource}/records?${this.getQueryParameters()}`)
        .then((response) => {
          this.records = response.data.data;
          this.pagination = response.data.meta;
          this.pagination.per_page = parseInt(response.data.meta.per_page);
          this.showButtons = false;
        });
    },
    getQueryParameters() {
      return queryString.stringify({
        page: this.pagination.current_page,
        limit: this.limit,
        ...this.form
      })
    },
    changeClearInput() {
      this.search.value = "";
      this.getRecords();
    },

    searhForm() {
      this.showButtons = true;
      return this.$http
        .get(`/${this.resource}/records?${this.getQueryParameters()}`)
        .then((response) => {
          this.records = response.data.data;
          this.pagination = response.data.meta;
          this.pagination.per_page = parseInt(response.data.meta.per_page);
          this.showButtons = false;
        });
    }
  },
};
</script>
