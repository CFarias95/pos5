<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Notas de crédito</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <!-- <a :href="`/${resource}/create`" class="btn btn-custom btn-sm  mt-2 mr-2" ><i class="fa fa-plus-circle"></i> Nuevo</a> -->
                <!-- <button @click.prevent="clickImport()" type="button" class="btn btn-custom btn-sm  mt-2 mr-2"><i
                        class="fa fa-upload"></i> Importar TXT</button>
                <button @click.prevent="clickDownload('excel')" type="button" class="btn btn-success btn-sm  mt-2 mr-2"><i
                        class="fa fa-download"></i>Exportar Excel</button> -->
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <th class="text-center">Fecha Emisión</th>
                        <th>Tipo</th>
                        <th>Id</th>
                        <th>Persona</th>
                        <th>Documento</th>
                        <th>Total</th>
                        <th>Es uso</th>
                        <th>Usado</th>
                    </tr>
                    <tr slot-scope="{ index, row }"
                                :class="{'bg-success': (row.used >= row.total) }">
                        <td class="text-center">{{ row.date_of_issue }}</td>
                        <td>{{ row.type }}</td>
                        <td>{{ row.id }}</td>
                        <td>{{ row.person }}<br /><small v-text="row.person_number"></small></td>
                        <td>{{ row.document }}</td>
                        <td>{{ row.total }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary text-white"
                                :class="{ 'bg-info': (row.in_use === false), 'bg-success': (row.in_use === true) }">{{
                                    (row.in_use) ? 'SI' : 'NO' }}</span>
                        </td>
                        <td class="text-center">{{ row.used }}</td>
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
            resource: 'cnp',
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
