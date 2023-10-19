<template>
    <el-dialog :title="title" :visible="showDialog" @close="close" @open="create" width="25%">
        <form autocomplete="on" @submit.prevent="submit" class="pt-1">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">Fecha PosFechado</label>
                            <div class="form-group mb-0" :class="{ 'has-danger': errors.f_posdated }">
                                <el-date-picker v-model="form.f_posdated" type="date" :clearable="false" format="dd/MM/yyyy"
                                    value-format="yyyy-MM-dd"></el-date-picker>
                                <small class="form-control-feedback" v-if="errors.f_posdated"
                                    v-text="errors.f_posdated[0]"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">Ref. Posfechado</label>
                            <div class="form-group mb-0" :class="{ 'has-danger': errors.posdated }">
                                <el-input v-model="form.posdated"></el-input>
                                <small class="form-control-feedback" v-if="errors.posdated"
                                    v-text="errors.posdated[0]"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions text-right mt-4">
                    <el-button @click.prevent="close()">Cancelar</el-button>
                    <el-button type="primary" native-type="submit" :loading="loading_submit">Guardar</el-button>
                </div>

            </div>
        </form>
    </el-dialog>
</template>

<script>

export default {
    props: ['showDialog', 'documentId', 'documentFeeId'],
    components: {

    },
    data() {
        return {
            title: 'POSFechado',
            loading_submit: false,
            resource: 'finances/unpaid',
            errors: {},
            form: {}
        }
    },
    async created() {
        this.initForm();
    },
    methods: {
        initForm() {
            this.errors = {};
            this.form = {
                id: null,
                f_posdated: null,
                document_id: null,
                posdated: null,
            };
        },
        create() {
            if (this.documentFeeId) {
                this.$http
                    .get(`/${this.resource}/posdated/${this.documentId}/${this.documentFeeId}`)
                    .then((response) => {
                        var datos = response.data;
                        if (datos.f_posdated != null) {
                            this.form.id = datos.id
                            this.form.document_id = datos.document_id
                            this.form.f_posdated = datos.f_posdated
                            this.form.posdated = datos.posdated
                        } else {
                            //this.form.f_posdated = moment().format('YYYY-MM-DD')
                            this.form.f_posdated = null
                            this.form.posdated = null
                            this.form.id = datos.id
                            this.form.document_id = datos.document_id
                        }
                    });
            }
        },

        submit() {
            this.loading_submit = true;
            this.$http
                .post(`/${this.resource}/posdated`, this.form)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadDataUnpaid");
                        this.close();
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        console.log(error);
                    }
                })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        close() {
            this.$emit("update:showDialog", false);
            this.initForm();
        },

    }
}
</script>
