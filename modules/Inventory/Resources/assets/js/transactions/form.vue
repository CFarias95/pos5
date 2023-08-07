<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create">
        <form autocomplete="off" @submit.prevent="submit" v-loading="loading">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group" :class="{'has-danger': errors.name}">
                            <label class="control-label">Descripcion</label>
                            <el-input v-model="form.name" required ></el-input>
                            <small class="form-control-feedback" v-if="errors.name"
                                   v-text="errors.name[0]"></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" :class="{'has-danger': errors.type}">
                            <label class="control-label">Tipo</label>
                            <el-select v-model="form.type" filterable required >
                                <el-option value="input"
                                           label="input"></el-option>
                                <el-option value="output"
                                           label="output"></el-option>
                            </el-select>
                            <small class="form-control-feedback" v-if="errors.type"
                                   v-text="errors.type[0]"></small>

                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group" :class="{'has-danger': errors.cta_account}">
                            <label class="control-label">Cuenta contable</label>
                            <el-select v-model="form.cta_account" filterable >
                                <el-option v-for="option in accounts" :key="option.id" :value="option.id"
                                           :label="option.code + ' - ' + option.description"></el-option>
                            </el-select>
                            <small class="form-control-feedback" v-if="errors.cta_account"
                                   v-text="errors.cta_account[0]"></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button type="primary" native-type="submit" :loading="loading_submit" >{{ buttonTitle }}</el-button>
            </div>
        </form>

    </el-dialog>

</template>

<script>
export default {
    props: ['showDialog', 'recordId'],
    data() {
        return {
            loading: false,
            loading_search: false,
            loading_submit: false,
            titleDialog: null,
            resource: 'inventory/transactions',
            errors: {},
            form: {},
            items: [],
            accounts: [],
            precision:2,
            buttonTitle : 'Guardar',
        }
    },
    // created() {
    //     this.initForm()
    // },
    methods: {
        initForm() {
            this.errors = {}
            this.form = {
                id: null,
                type:null,
                name:null,
                cta_account:null,
            }
        },
        ChangePrecision(){
            if (this.form.series_enabled) {
                /* Para series, debe ser entero*/
                this.precision = 0;
            }else{
                this.precision = 2;
            }
        },
        async initTables() {
            await this.$http.get(`/${this.resource}/tables/`)
                .then(response => {
                    // this.items = response.data.items
                    this.accounts = response.data.accounts
                })
        },
        async create() {

            this.loading = true;
            this.titleDialog = (this.recordId) ?'Editar motivo de ajuste':'Crear un nuevo motivo de ajuste';
            await this.initTables();
            this.initForm();
            this.loading = false;
            if(this.recordId){
                this.loadRecord();
            }
        },
        async loadRecord(){
            await this.$http.get(`/${this.resource}/record/${this.recordId}`)
                .then(response => {
                    // this.items = response.data.items
                    this.form = response.data
                })
        },
        async submit() {

            this.loading_submit = true
            //console.log(this.form)
            await this.$http.post(`/inventory/transactions/create`, this.form)
                .then(response => {
                    console.log("data response",response)
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                        this.close()
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    console.log(error)
                })
                .then(() => {
                    this.loading_submit = false
                })

        },
        close() {
            this.$emit('update:showDialog', false)
            this.initForm()
        },
    }
}
</script>
