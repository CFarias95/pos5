<template>
    <el-dialog :title="titleDialog" :visible="showDialog" append-to-body width="30%" @open="create" @close="closeSplit">
        <form autocomplete="off">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group" :class="{ 'has-danger': errors.date_of_due }">
                            <label class="control-label">
                                Fecha vencimiento
                            </label>
                            <el-date-picker v-model="form.date_of_due" type="date" value-format="yyyy-MM-dd"
                                format="dd/MM/yyyy" :clearable="true"></el-date-picker>

                            <small class="form-control-feedback" v-if="errors.date_of_due" v-text="errors.total[0]"></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button @click.prevent="clickCloseSplit()">Cerrar</el-button>
                <el-button @click.prevent="clicksaveSplit()" type="primary" native-type="submit">Actualizar</el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>
import { mapState, mapActions } from "vuex/dist/vuex.mjs";

export default {
    props: ['showDialog', 'documentId'],
    components: {
    },
    data() {
        return {
            titleDialog: 'Actualizar fecha de Vencimiento',
            loading: false,
            resource: 'finances/unpaid',
            errors: {},
            form: {},
            company: {},
        }
    },
    created() {
    },
    mounted() {
        this.initForm()
    },
    computed: {
        ...mapState([
            'config',
        ]),
    },
    methods: {
        ...mapActions(['loadConfiguration']),
        initForm() {
            this.errors = {};
            this.form = {
                date_of_due: moment().format('YYYY-MM-DD'),
                fee_id: this.documentId
            };
        },
        async create() {

        },
        clickCloseSplit() {
            this.$emit('update:showDialog', false)
            this.initForm()
        },
        closeSplit() {
            this.$emit('update:showDialog', false);
        },
        async clicksaveSplit() {
            this.form.fee_id = this.documentId
            await this.$http.post(`/${this.resource}/update-date`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.initForm()
                        this.$emit('update:showDialog', false)
                        this.$eventHub.$emit('reloadDataUnpaid')
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.data.message)
                })
        }
    }
}
</script>
