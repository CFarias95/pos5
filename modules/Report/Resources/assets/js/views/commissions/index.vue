<template>
  <div class="card mb-0 pt-2 pt-md-0">
    <div class="card-header bg-info">
      <h3 class="my-0">Comisiones vendedores - ventas</h3>
    </div>
    <div class="card mb-0">
      <div class="card-body">
        <div>
          <data-table :applyCustomer="true" :resource="resource">
            <tr slot="heading">
              <th>#</th>
              <!-- <th class="text-center">Código vendedor</th> -->
              <th>Vendedor</th>
              <th class="text-center">Cantidad transacciones</th>
              <th class="text-center">Ventas acumuladas</th>
              <th class="text-center">Total comisiones</th>
            </tr>
            <tr slot-scope="{ index, row }">
              <td>{{ index }}</td>
              <td>{{ row.user_name }}</td>
              <td class="text-center">{{ row.total_transactions }}</td>
              <td class="text-center">{{ row.acum_sales }}</td>
              <td class="text-center">{{ row.total_commision }}</td>
            </tr>

          </data-table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import dxChart from 'devextreme/viz/chart';
import DataTable from '../../components/DataTableCommissions.vue'

export default {
  components: {
    DataTable,
    dxChart
  },
  data() {
    return {
      resource: 'reports/commissions',
    };
  },
  mounted() {
  },
};
</script>
<style>
#pivotgrid {
  margin-top: 20px;
}

.currency {
  text-align: center;
}
</style>

