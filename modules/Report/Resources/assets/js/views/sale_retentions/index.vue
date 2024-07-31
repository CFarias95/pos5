<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Ventas</span></li>
            </ol>
        </div>
        <div class="card-header bg-info">
            <h3 class="my-0">Extracto retenciones Ventas</h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <div class="col-md-12 col-lg-12 col-xl-12 ">
                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label class="control-label">Mes</label>
                            <el-date-picker v-model="form.month" :clearable="true" format="MMyyyy" type="month"
                                value-format="MMyyyy"></el-date-picker>
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Cliente</label>
                            <el-select v-model="form.customer_id" filterable>
                                <el-option :key="0" :value="0" label="Todos"></el-option>
                                <el-option v-for="customer in customers" :key="customer.id" :label="customer.name"
                                    :value="customer.id">
                                </el-option>
                            </el-select>
                        </div>
                        <div class="col-md-3">
                            <label class="control-label">Tipo</label>
                            <el-select v-model="form.type" filterable>
                                <el-option :key="0" :value="'TODOS'" label="TODOS"></el-option>
                                <el-option :key="1" :value="'IVA'" label="IVA"></el-option>
                                <el-option :key="2" :value="'RENTA'" label="RENTA"></el-option>
                            </el-select>
                        </div>

                        <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top:29px">
                            <el-button class="submit" icon="el-icon-search" type="primary"
                                @click.prevent="getRecordsByFilter">Buscar
                            </el-button>

                            <el-button class="submit" type="success" @click.prevent="clickDownloadExcel"><i
                                    class="fa fa-file-excel"></i>
                                Exportar Excel
                            </el-button>
                        </div>
                        <div class="col-md-12 mt-4">
                            <div v-if="records && records.length > 0" class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th v-for="(value, key) in records[0]" :key="key">{{ key }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, index) in records" :key="index">
                                            <td v-for="(value, key) in row" :key="key">{{ value }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center mt-4">
                                <p>No se encontraron datos para mostrar.</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</template>
<script>
import queryString from 'query-string'
export default {
    data() {
        return {
            form: {},
            customers: [],
            loading_submit: false,
            resource: 'reports/sale/retentions',
            records : [],

        }
    },
    methods: {
        clickDownloadExcel() {

            let query = queryString.stringify({
                ...this.form
            });

            window.open(`/${this.resource}/excel/?${query}`, '_blank');
        },

        getRecordsByFilter() {
            this.loading_submit = true
            this.$http.post(`/${this.resource}/records`, this.form).then((response) => {
                console.log('dta', response)
                if (response.data.success == true) {
                    this.records = response.data.data
                } else {
                    this.$message.error(response.data.message);
                }

                this.loading_submit = false
            });
        }
    },
    created() {
        // Lifecycle hook for when the component is created

        this.$http.get(`/${this.resource}/tables`).then((response) => {
            this.customers = response.data.customers
        });

        this.form = {
            type: 'TODOS',
            customer_id: 0,
            month: null
        }
    },
    mounted() {
    }
}
</script>
