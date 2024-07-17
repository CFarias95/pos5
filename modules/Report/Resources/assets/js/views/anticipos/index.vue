<template>
  <div>
    <div class="page-header pr-0">
      <h2>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
      </h2>
      <ol class="breadcrumbs">
        <li class="active"><span>Reporte Anticipos Clientes-Proveedor</span></li>
      </ol>
    </div>
    <div class="card-header bg-info">
      <h3 class="my-0">Reporte Anticipos Clientes-Proveedor</h3>
    </div>
    <div class="card mb-0">
      <div class="card-body">
        <div class="col-md-12 col-lg-12 col-xl-12">
          <div class="row mt-2">
            <div class="col-md-3">
              <label class="control-label">Desde</label>
              <el-date-picker
                v-model="form.date_start"
                :clearable="false"
                format="dd/MM/yyyy"
                type="date"
                value-format="yyyy-MM-dd"
              ></el-date-picker>
            </div>
            <div class="col-md-3">
              <label class="control-label">Hasta</label>
              <el-date-picker
                v-model="form.date_end"
                :clearable="false"
                format="dd/MM/yyyy"
                type="date"
                value-format="yyyy-MM-dd"
              ></el-date-picker>
            </div>
            <div class="col-md-3">
              <label class="control-label">Estado</label>
              <el-select v-model="form.state_type">
                <el-option :key="0" :value="2" label="Todos"></el-option>
                <el-option :key="1" :value="1" label="Usados"></el-option>
                <el-option :key="2" :value="0" label="No usados"></el-option>
              </el-select>
            </div>
            <div class="col-md-3">
              <label class="control-label">Tipo</label>
              <el-select v-model="form.type_id" @change = "changeType">
                <el-option :key="0" value="todos" label="Todos"></el-option>
                <el-option :key="1" value="suppliers" label="Proveedores"></el-option>
                <el-option :key="2" value="customers" label="Clientes"></el-option>
              </el-select>
            </div>
            <div class="col-md-3">
              <label class="control-label">Cliente/Proveedor</label>
              <el-select v-model="form.person_id" filterable>
                <el-option :key="0" :value="0" label="Todos"></el-option>
                <el-option v-for="option in persons" :key="option.id" :value="option.id" :label="option.name"></el-option>
              </el-select>
            </div>
            <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top: 29px">
              <el-button
                class="submit"
                icon="el-icon-search"
                type="primary"
                @click.prevent="getRecordsByFilter"
                >Buscar
              </el-button>

              <el-button class="submit" type="success" @click.prevent="clickDownloadExcel"
                ><i class="fa fa-file-excel"></i>
                Exportar Excel
              </el-button>

              <el-button class="submit" type="success" @click.prevent="clickDownloadPDF"
                ><i class="fa fa-file-pdf"></i>
                Exportar PDF
              </el-button>
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <slot
                  v-for="(key, value) in anticipos_header"
                  :index="customIndex(value)"
                  :row="key"
                >
                  <tr slot="heading" :key="value">
                    <th
                      v-for="(value1, name) in key"
                      :index="customIndex(name)"
                      :row="value1"
                      class=""
                      slot="heading"
                      :key="name"
                    >
                      <strong>{{ value1 }}</strong>
                    </th>
                  </tr>
                </slot>
              </thead>
              <tbody>
                <slot
                  v-for="(row, index) in records"
                  :index="customIndex(index)"
                  :row="row"
                >
                  <tr v-for="valor in row" :row="valor" class="" slot="heading">
                    <td
                      v-for="(obj, nombre) in valor"
                      :index="customIndex(nombre)"
                      :row="obj"
                      :key="nombre"
                    >
                      {{ obj }}
                    </td>
                  </tr>
                </slot>
              </tbody>
            </table>
            <el-pagination
              :current-page.sync="pagination.current_page"
              :page-size="pagination.per_page"
              :total="pagination.total"
              layout="total, prev, pager, next"
              @current-change="getRecords"
            >
            </el-pagination>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";
import queryString from "query-string";

export default {
  data() {
    return {
      resource: "reports/anticipos",
      form: {
        date_start: null,
        date_end: null,
      },
      loading_submit: false,
      anticipos_header: [],
      records: [],
      pagination: {},
      search: {},
      persons_all: [],
      persons: [],
      pickerOptionsDates: {
        disabledDate: (time) => {
          time = moment(time).format("YYYY-MM-DD");
          return this.form.date_start > time;
        },
      },
    };
  },
  created() {
    this.initForm();
    this.$http.get(`/${this.resource}/tables`).then((response) => {
            if(response.data){
                this.persons_all = response.data.persons
                this.persons = response.data.persons
            }
        });
  },

  async mounted() {
    this.getRecords();
  },
  methods: {
    clickDownloadPDF() {
      let query = queryString.stringify({
        ...this.form,
      });

      window.open(`/${this.resource}/pdf/?${query}`, "_blank");
    },

    clickDownloadExcel() {
      let query = queryString.stringify({
        ...this.form,
      });

      window.open(`/${this.resource}/excel/?${query}`, "_blank");
    },
    initForm() {
      this.form = {
        date_start: moment().format("YYYY-MM-DD"),
        date_end: moment().format("YYYY-MM-DD"),
        state_type : 2,
        type_id : 'todos',
        person_id : 0,
      };
    },
    customIndex(index) {
      return this.pagination.per_page * (this.pagination.current_page - 1) + index + 1;
    },
    async getRecordsByFilter() {
      this.loading_submit = await true;
      await this.getRecords();
      this.loading_submit = await false;
    },
    getRecords() {
      return this.$http
        .get(`/${this.resource}/datosSP?${this.getQueryParameters()}`)
        .then((response) => {
          this.records = response.data.data;
          this.anticipos_header = this.records[this.records.length - 1];
          //console.log('data', this.records);
          let len = this.records.length;
          this.records.splice(len - 1, 1);
          this.pagination = response.data.meta;
          this.pagination.per_page = parseInt(response.data.meta.per_page);
          this.loading_submit = false;
        });
    },
    getQueryParameters() {
      return queryString.stringify({
        page: this.pagination.current_page,
        limit: this.limit,
        ...this.form,
      });
    },
    changeType(){
        if(this.form.type_id != 'todos'){
            this.persons = this.persons_all.filter((i) => i.type == this.form.type_id)
        }
    }
  },
};
</script>
