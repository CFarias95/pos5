<template>
  <div>
    <div class="page-header pr-0">
      <h2>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
      </h2>
      <ol class="breadcrumbs">
        <li class="active"><span>Lista de Transacciones de Inventario</span></li>
      </ol>
    </div>
    <div class="card-header bg-info">
      <h3 class="my-0">Lista de Transacciones de Inventario</h3>
    </div>
    <div class="card mb-0">
      <div class="card-body">
        <div class="form-body">
          <table class="table" style="width: 100%">
            <thead>
              <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Visible?</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in records" :key="index">
                <td>{{ customIndex(index) }}</td>
                <td>{{ row.name }}</td>
                <td>
                  <el-switch v-model="row.visible"></el-switch>
                </td>
              </tr>
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
</template>

<script>
import queryString from "query-string";

export default {
  data() {
    return {
      resource: 'inventory/transactions/list',
      loading_submit: false,
      records: [],
      pagination: {}
    };
  },
  mounted() {
    this.getRecords();
  },
  methods: {
    customIndex(index) {
      return this.pagination.per_page * (this.pagination.current_page - 1) + index + 1;
    },
    getRecords() {
      let queryParams = queryString.stringify({
        page: this.pagination.current_page
      });

      this.$http.get(`/${this.resource}/records?${queryParams}`)
      .then((response) => {
        console.log('response', response)
        this.records = response.data.records;
        this.pagination = response.data.pagination;
      })
      .catch((error) => {
        console.error("There was an error fetching the records:", error);
      });
    }
  }
};
</script>
