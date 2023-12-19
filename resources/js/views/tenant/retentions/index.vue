<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Retenciones recibidas</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <!-- <a :href="`/${resource}/create`" class="btn btn-custom btn-sm  mt-2 mr-2" ><i class="fa fa-plus-circle"></i> Nuevo</a> -->
                <button @click.prevent="clickImport()" type="button" class="btn btn-custom btn-sm  mt-2 mr-2"><i
                        class="fa fa-upload"></i> Importar TXT</button>
                <button @click.prevent="clickDownload('excel')" type="button" class="btn btn-success btn-sm  mt-2 mr-2"><i
                        class="fa fa-download"></i>Exportar Excel</button>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <th class="text-center">Fecha Emisión</th>
                        <th class="text-center">Fecha Real</th>
                        <th>Cliente</th>
                        <th>Número</th>
                        <th>Secuencial</th>
                        <th>Clave Acceso</th>
                        <th>Doc Sustento</th>
                        <th>Estado</th>
                        <th class="text-right">T.Retención</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">En uso</th>
                        <th class="text-center">Total usado</th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <td class="text-center">{{ row.date_of_issue }}</td>
                        <td class="text-center">{{ row.date_real }}</td>
                        <td>{{ row.supplier_name }}<br /><small v-text="row.supplier_number"></small></td>
                        <td>{{ row.number }}</td>
                        <td>{{ row.secuencial }}</td>
                        <td>{{ row.clave_acceso }}</td>
                        <td>{{ row.doc_sustento }}</td>
                        <td>
                            <span class="badge bg-secondary text-white"
                                :class="{ 'bg-secondary': (row.state_type_id === '01'), 'bg-info': (row.state_type_id === '03'), 'bg-success': (row.state_type_id === '05'), 'bg-secondary': (row.state_type_id === '07'), 'bg-dark': (row.state_type_id === '09') }">{{
                                    row.state_type_description }}</span>
                        </td>
                        <td class="text-right">{{ row.total_retention }}</td>
                        <td class="text-right">{{ row.total }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary text-white"
                                :class="{ 'bg-info': (row.in_use === false), 'bg-success': (row.in_use === true) }">{{
                                    (row.in_use) ? 'SI' : 'NO' }}</span>
                        </td>
                        <td class="text-center">{{ row.total_used }}</td>
                    </tr>
                </data-table>
            </div>
            <retention-import :showDialog.sync="showImportDialog"></retention-import>
            <retention-options :showDialog.sync="showDialogOptions" :recordId="recordId"
                :showClose="true"></retention-options>
        </div>
    </div>
</template>

<script>

import DataTable from '../../../components/DataTable.vue';
import RetentionOptions from './partials/options.vue';
import RetentionImport from './import.vue';
import queryString from "query-string";

export default {
    components: { DataTable, RetentionOptions, RetentionImport },
    data() {
        return {
            resource: 'retentions',
            showDialogOptions: false,
            showImportDialog: false,
            recordId: null,
        }
    },
    created() {
    },
    methods: {
        clickOptions(recordId) {
            this.recordId = recordId
            this.showDialogOptions = true
        },
        clickDownload(type) {

            let query = queryString.stringify({
                ...this.form
            });

            window.open(`/reports/retention/${type}/?${query}`, "_blank");

        },
        clickImport() {
            this.showImportDialog = true
        },
    }
}
</script>
