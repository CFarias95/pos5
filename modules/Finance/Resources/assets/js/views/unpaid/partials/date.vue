<template>
    <el-dialog :title="titleDialog" :visible="showDialog" append-to-body width="30%" @open="create"
        @close="closeSplit">
        <form autocomplete="off">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-2" hidden>
                        <div class="form-group" :class="{ 'has-danger': errors.amount }">
                            <label class="control-label">
                                ID
                            </label>
                            <el-input-number v-model="form.fee_id" :value="recordId"></el-input-number>
                            <small class="form-control-feedback" v-if="errors.amount"
                                v-text="errors.description[0]"></small>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group" :class="{ 'has-danger': errors.date_of_due }">
                            <label class="control-label">
                                Fecha vencimiento
                            </label>
                            <el-date-picker v-model="form.date_of_due" type="datetime" value-format="yyyy-MM-dd"
                                format="dd/MM/yyyy" :clearable="true"></el-date-picker>

                            <small class="form-control-feedback" v-if="errors.date_of_due" v-text="errors.total[0]"></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button @click.prevent="clickCloseSplit()">Cerrar</el-button>
                <el-button @click.prevent="clicksaveSplit()" type="primary" native-type="submit">Generar</el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>
import { mapState, mapActions } from "vuex/dist/vuex.mjs";

export default {
    props: ['showDialog', 'recordId', 'amountFee'],
    components: {
    },
    data() {
        return {
            titleDialog: 'Dividir la cuota actual',
            loading: false,
            resource: 'unpaid',
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
                amount: 0,
                fee_id: this.recordId
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
            //this.form.fee_id = this.recordId
            await this.$http.post(`/${this.resource}/split`, this.form)
            .then(response => {
                if (response.data.success) {
                    this.$message.success(response.data.message);
                    this.close()
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
