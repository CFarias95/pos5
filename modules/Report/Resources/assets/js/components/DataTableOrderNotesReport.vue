<template>
    <div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 ">
                <div class="row mt-2">
                    <template>
                        <div class="col-lg-3 col-md-3">
                            <div class="form-group">
                                <label class="control-label">Pedido de venta número:
                                </label>
                                <el-select v-model="form.document_id" clearable filterable>
                                    <el-option v-for="option in documents" :key="option.id"
                                               :label="option.name"
                                               :value="option.id">
                                    </el-option>
                                </el-select>
                            </div>
                        </div>
                    </template>

                    <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top:29px">
                        <el-button :loading="loading_submit" class="submit" icon="el-icon-search"
                                   type="primary" @click.prevent="getRecordsByFilter">Buscar
                        </el-button>
                        <template v-if="resource == 'reports/order-notes-general'">
                        <el-button class="submit" type="success" @click.prevent="clickDownload('excel')">
                        <i class="fa fa-file-excel" >
                        </i>  Exportal Excel</el-button>
                        </template>
                    </div>
                </div>
                <div class="row mt-1 mb-4">
                </div>
            </div>
            <div v-if="records.length>0" class="col-md-12">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <slot name="heading">
                        </slot>
                        </thead>
                        <tbody>
                        <slot v-for="(row, index) in records" :index="customIndex(index)" :row="row">
                        </slot>
                        </tbody>
                        <tfoot
                            v-if="resource == 'reports/order-notes-consolidated' || resource == 'reports/sales-consolidated'">
                        <tr>
                            <td :colspan="resource == 'reports/order-notes-consolidated' ? 3:0">
                            </td>
                            <td>
                                <strong>Total</strong>
                            </td>
                            <td class="text-center">{{ totals }}</td>
                            <td class="text-center">S/ {{ totals_amount }}</td>
                        </tr>
                        </tfoot>
                    </table>
                    <div>
                        <el-pagination
                            :current-page.sync="pagination.current_page"
                            :page-size="pagination.per_page"
                            :total="pagination.total"
                            layout="total, prev, pager, next"
                            @current-change="getRecords">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </div>
        <totals-by-item-form
            :parameters="getQueryParameters()"
            :resource="'reports/order-notes-consolidated'"
            :showDialog.sync="showDialog"
        ></totals-by-item-form>
    </div>
</template>
<style>
.font-custom {
    font-size: 15px !important
}
</style>
<script>

import moment from 'moment'
import queryString from 'query-string'
import TotalsByItemForm from './partials/totals_by_item.vue'

export default {
    components: {
        TotalsByItemForm
    },
    props: {
        resource: String,
    },
    data() {
        return {
            showDialog: false,
            loading_submit: false,
            loading_search: false,
            records: [],
            documents: [],

        }
    },
    computed: {},
    created() {
        this.initForm()
        this.$eventHub.$on('reloadData', () => {
            this.getRecords()
        })
    },
    async mounted() {

        await this.$http.get(`/${this.resource}/filter`)
            .then(response => {

                this.documents = response.data.documents
            });

        this.form.type_person = 'customers'

    },
    methods: {
        clickTotalByItem() {
            this.showDialog = true
        },
        changeDisabledDates() {
            if (this.form.date_end < this.form.date_start) {
                this.form.date_end = this.form.date_start
            }
            // this.loadAll();
        },
        changePersons() {
            this.form.seller_id = null
            this.$eventHub.$emit('changeFilterColumn', 'person')
            // this.records = []
        },
        changeSellers() {
            this.form.person_id = null
            this.$eventHub.$emit('changeFilterColumn', 'seller')
            // this.records = []
        },
        searchRemotePersons(input) {

            if (input.length > 0) {

                this.loading_search = true
                let parameters = `input=${input}`

                this.form.type_person = 'customers'

                this.$http.get(`/reports/data-table/persons/${this.form.type_person}?${parameters}`)
                    .then(response => {
                        this.persons = response.data.persons
                        this.loading_search = false

                        if (this.persons.length == 0) {
                            this.filterPersons()
                        }
                    })
            } else {
                this.filterPersons()
            }

        },
        filterPersons() {
            this.persons = this.all_persons
        },
        clickDownload(type) {
            let query = queryString.stringify({
                ...this.form
            });
            window.open(`/${this.resource}/${type}/?${query}`, '_blank');
        },
        initForm() {

            this.form = {
                person_id: null,
                document_type_id: null,
                date_range_type_id: 'date_of_issue',
                order_state_type_id: 'pending',
                type_person: null,
                seller_id: null,
                date_start: moment().startOf('month').format('YYYY-MM-DD'),
                date_end: moment().endOf('month').format('YYYY-MM-DD'),
            }

        },
        customIndex(index) {
            return (this.pagination.per_page * (this.pagination.current_page - 1)) + index + 1
        },
        async getRecordsByFilter() {

            if (!this.form.person_id && !this.form.seller_id) {
                return this.$message.error('Debe seleccionar un cliente o vendedor')
            }

            this.loading_submit = await true
            await this.getRecords()
            // await this.getTotals()
            this.loading_submit = await false

        },
        getTotals() {
            this.totals = _.sumBy(this.records, (it) => parseFloat(it.item_quantity));
            this.totals_amount = _.sumBy(this.records, (it) => parseFloat(it.total));
        },
        getRecords() {
            return this.$http.get(`/${this.resource}/records?${this.getQueryParameters()}`).then((response) => {

                if (this.resource == 'reports/order-notes-consolidated') {
                    this.records = _.orderBy(response.data.data, ['user'], ['asc'])
                } else {
                    this.records = response.data.data
                }

                this.pagination = response.data.meta
                this.pagination.per_page = parseInt(response.data.meta.per_page)
                this.getTotals()
                this.loading_submit = false
            });


        },
        getQueryParameters() {
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form
            })
        },
    }
}
</script>
