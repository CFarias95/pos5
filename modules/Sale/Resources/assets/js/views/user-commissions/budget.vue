<template>
    <el-dialog :title="title" :visible="showDialog" @close="close" @open="create" width="80%">
        <div class="form-body">
            <div class="row">
                <div class="col-md-12" v-if="records.length > 0">
                    <div class="table-responsive">
                        <table class="table">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>valor</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, index) in records" :key="index">
                                            <template v-if="row.id">
                                                <td>B-{{ row.id }}</td>
                                                <td>{{ row.date_from }}</td>
                                                <td>{{ row.date_until }}</td>
                                                <td>{{ row.amount }}</td>
                                                <td class="series-table-actions text-right">
                                                    <button type="button"
                                                        class="btn waves-effect waves-light btn-xs btn-danger"
                                                        @click.prevent="clickDelete(row.id)">Eliminar</button>
                                                </td>
                                            </template>
                                            <template v-else>
                                                <td></td>
                                                <td>
                                                    <div class="form-group mb-0"
                                                        :class="{ 'has-danger': row.errors.date_from }">
                                                        <el-date-picker v-model="row.date_from" type="date"
                                                            :clearable="false" format="dd/MM/yyyy"
                                                            value-format="yyyy-MM-dd"></el-date-picker>
                                                        <small class="form-control-feedback" v-if="row.errors.date_from"
                                                            v-text="row.errors.date_from[0]"></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group mb-0"
                                                        :class="{ 'has-danger': row.errors.date_until }">
                                                        <el-date-picker v-model="row.date_until" type="date"
                                                            :clearable="false" format="dd/MM/yyyy"
                                                            value-format="yyyy-MM-dd"></el-date-picker>
                                                        <small class="form-control-feedback" v-if="row.errors.date_until"
                                                            v-text="row.errors.date_until[0]"></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group mb-0"
                                                        :class="{ 'has-danger': row.errors.amount }">
                                                        <el-input v-model="row.amount" type="number"></el-input>
                                                        <small class="form-control-feedback" v-if="row.errors.amount"
                                                            v-text="row.errors.amount[0]"></small>
                                                    </div>
                                                </td>
                                                <td class="series-table-actions text-right px-0">
                                                    <button type="button"
                                                        class="btn waves-effect waves-light btn-sm btn-info"
                                                        @click.prevent="clickSubmit(index)">
                                                        <i class="fa fa-check d-block"></i>
                                                    </button>

                                                    <button type="button"
                                                        class="btn waves-effect waves-light btn-sm btn-danger"
                                                        @click.prevent="clickCancel(index)">
                                                        <i class="fa fa-trash d-block"></i>
                                                    </button>
                                                </td>
                                            </template>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </table>
                    </div>
                </div>
                <div class="col-md-12 text-center pt-2">
                    <template>
                        <el-button type="primary" icon="el-icon-plus" @click="clickAddRow">Nuevo</el-button>
                    </template>
                </div>
            </div>
        </div>
    </el-dialog>
</template>

<script>

export default {
    props: ['showDialog', 'recordId'],
    data() {
        return {
            loading_submit: false,
            titleDialog: null,
            resource: 'budget',
            form: {},
            text_type: null,
            types: [],
            users: [],
            records: [],
            title: 'Presupuestos asignados',
        }
    },
    created() {

    },
    methods: {
        initForm() {
            this.loading_submit = false,
                this.errors = {}
            this.records = []

        },
        resetForm() {
            this.initForm()
        },
        create() {
            this.titleDialog = 'Lista de presupuestos asignados'
            if (this.recordId) {
                this.$http.get(`/${this.resource}/records/${this.recordId}`)
                    .then(response => {
                        this.records = response.data.data
                    })
            }
        },
        getData() {
            this.$http.get(`/${this.resource}/records/${this.recordId}`)
                .then(response => {
                    this.records = response.data.data
                })
            this.$eventHub.$emit('reloadDataBudget')
        },
        clickAddRow() {

            this.records.push({
                id: null,
                date_from: moment().format('YYYY-MM-DD'),
                date_until: moment().format('YYYY-MM-DD'),
                user_id: this.recordId,
                amount: 0,
                errors: {},
                loading: false,
            });
            console.log('data', this.records)

            this.showAddButton = false;
        },
        clickCancel(index) {
            this.records.splice(index, 1);
            this.showAddButton = true;
            this.getData()
        },
        clickDelete(id) {
            this.$http.delete(`/${this.resource}/${id}`).then((response) => {

                if (response.data.success) {
                    this.$message.success(response.data.message);
                    this.getData()
                } else {
                    this.$message.error(response.data.message);
                }
            })
        },
        clickSubmit(index) {

            if (this.records[index].amount <= 0) {
                this.$message.error('Ingrese un valor válido');
                return;
            }

            let form = {
                id: this.records[index].id,
                user_id: this.recordId,
                date_from: this.records[index].date_from,
                date_until: this.records[index].date_until,
                amount: this.records[index].amount,
            };

            this.$http.post(`/${this.resource}`, form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.getData()
                        this.$eventHub.$emit('reloadData')
                    } else {
                        this.$message.error(response.data.message);
                        this.getData()
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.records[index].errors = error.response.data;
                    } else {

                        this.$message.error(error.response.data.message)
                    }
                })
        },
        close() {
            this.$emit('update:showDialog', false)
        },
    }
}
</script>
