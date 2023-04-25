<template>
  <div>
    <div class="card-body">
      <div class="col-md-12 col-lg-12 col-xl-12 ">
        <div class="row">
          <div class="col">
            <button type="button" data-placement="start" title="Generar PDF" class="btn btn-custom btn-sm  mt-2 mr-2"
              @click.prevent="clickDownload('pdf')"><i class="fa fa-file-pdf"></i> PDF</button>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="col-md-12">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <slot name="heading"></slot>
            </thead>
            <tbody>
              <slot v-for="(row, index) in datos" :index="customIndex(index)" :row="row">
                <tr :key="index">
                  <td>{{ row[0].codigo }}</td>
                  <td>{{ row[0].nombre }}</td>
                  <td>{{ row[0].saldo }}</td>
                </tr>
              </slot>
            </tbody>
          </table>
          <div>            
            <el-pagination :current-page.sync="pagination.current_page" :page-size="pagination.per_page"
              :total="pagination.total" layout="total, prev, pager, next" @current-change="getDatos">
            </el-pagination>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
  
  
<script>
import queryString from "query-string";

export default {
  props: {
    resource: String,
  },
  data() {
    return {
      showButtons: false,
      datos: [],
      form: {},
      pagination: {},
    };
  },
  computed: {},
  async created() {
    this.initForm();
    this.$eventHub.$on("reloadData", () => {
      this.getDatos();
    });
  },
  async mounted() {
    await this.getDatos();
  },
  methods: {

    getDatos() {
      this.showButtons = true;
      return this.$http.get(`/${this.resource}/datosSP?${this.getQueryParameters()}`)
        .then((response) => {
          this.datos = response.data.data;
          this.pagination = response.data.meta;
          this.pagination.per_page = parseInt(response.data.meta.per_page);
          this.showButtons = false;
        })
    },

    initForm() {
      this.form = {

      };
    },
    customIndex(index) {
      return (
        this.pagination.per_page * (this.pagination.current_page - 1) +
        index +
        1
      );
    },

    getQueryParameters() {
      return queryString.stringify({
        page: this.pagination.current_page,
        limit: this.limit,
        ...this.form
      })
    },

    clickDownload(type) {
      window.open(`/${this.resource}/${type}`, '_blank');
    },
  },
};
</script>