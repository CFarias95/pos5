<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">
                {{title}}
            </h3>
        </div>
        <div class="tab-content">
            <form autocomplete="off"
                  @submit.prevent="submit">
                <div class="form-body">
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div :class="{'has-danger': errors.machine_type_id}"
                                 class="form-group">
                                <label class="control-label">
                                    Tipo de maquina
                                </label>
                                <el-select v-model="form.machine_type_id"  >
                                    <el-option
                                        v-for="option in machine_types"
                                        :key="option.id"
                                        :value="option.id"
                                        :label="option.name"></el-option>
                                </el-select>

                                <small v-if="errors.machine_type_id"
                                       class="form-control-feedback"
                                       v-text="errors.machine_type_id[0]"></small>
                            </div>
                        </div>


                        <div class="col-sm-6 col-md-3">
                            <div :class="{'has-danger': errors.name}"
                                 class="form-group">
                                <label class="control-label">
                                    Nombre
                                </label>
                                <el-input v-model="form.name"></el-input>
                                <small v-if="errors.name"
                                       class="form-control-feedback"
                                       v-text="errors.name[0]"></small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div :class="{'has-danger': errors.brand}"
                                 class="form-group">
                                <label class="control-label">
                                    Marca
                                </label>
                                <el-input v-model="form.brand"></el-input>
                                <small v-if="errors.brand"
                                       class="form-control-feedback"
                                       v-text="errors.brand[0]"></small>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div :class="{'has-danger': errors.model}"
                                 class="form-group">
                                <label class="control-label">
                                    Modelo
                                </label>
                                <el-input v-model="form.model"></el-input>
                                <small v-if="errors.model"
                                       class="form-control-feedback"
                                       v-text="errors.model[0]"></small>
                            </div>
                        </div> 
                        
                        <div class="col-sm-6 col-md-3">
                            <div :class="{'has-danger': errors.minimum_force}"
                                 class="form-group">
                                <label class="control-label">
                                    capacidad minima
                                </label>
                                <el-input v-model="form.minimum_force"></el-input>
                                <small v-if="errors.minimum_force"
                                       class="form-control-feedback"
                                       v-text="errors.minimum_force[0]"></small>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div :class="{'has-danger': errors.maximum_force}"
                                 class="form-group">
                                <label class="control-label">
                                    capacidad maxima
                                </label>
                                <el-input v-model="form.maximum_force"></el-input>
                                <small v-if="errors.maximum_force"
                                       class="form-control-feedback"
                                       v-text="errors.maximum_force[0]"></small>
                            </div>
                        </div>


                        <div class="col-sm-6 col-md-3">
                            <div class="form-group" :class="{'has-danger': errors.unit_type_id}">
                                <label class="control-label">Unidad</label>
                                <el-select v-model="form.unit_type_id" dusk="unit_type_id">
                                    <el-option v-for="option in unit_types" :key="option.id" :value="option.id" :label="option.description"></el-option>
                                </el-select>
                                <small class="form-control-feedback" v-if="errors.unit_type_id" v-text="errors.unit_type_id[0]"></small>
                            </div>
                        </div>
                       
                    </div>
                </div>
                <div class="form-actions text-right mt-4">
                    <el-button
                        @click.prevent="onClose()">
                        Cancelar
                    </el-button>
                    <el-button
                        :loading="loading_submit"
                        native-type="submit"
                        type="primary">
                        {{ (id) ? 'Actualizar' : 'Guardar' }}
                    </el-button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>


export default {
    props: {
        id: {
            type: Number,
            required: false,
        },
    },
    components: {},
    data() {
        return {
            resource: 'machine-production',
            title: 'Nueva maquina',

            showDialog: false,
            showDialogNewPerson: false,
            showDialogOptions: false,
            loading_submit: false,
            errors: {},
            form: {
                id:null,
                machine_type_id:null,
                unit_type_id: null,
                brand:'',
                model:'',
                maximum_force:'',
                minimum_force: '',
            },
            aux_supplier_id: null,
            machine_types: [],
            unit_types: [],
            currency_types: [],
            suppliers: [],
            establishment: {},
            currency_type: {},
            mill_method_types: [],
            payment_destinations: [],
            mill_reasons: [],
            millNewId: null
        }
    },
    mounted() {
        this.getTable();
        this.initForm()
    },
    methods: {
        showAddItemModal() {
            this.showDialog = true;
        },
        isUpdate() {
            this.title= 'Nueva maquina';
            if (this.id) {
                this.$http.get(`/${this.resource}/record/${this.id}`)
                    .then(response => {
                        this.title= 'Editar maquina';
                        this.form = response.data
                    })
            }

        },
        getTable(){
            this.$http.get(`/${this.resource}/tables`)
                .then(response => {
                    this.machine_types = response.data.machine_types
                    this.unit_types = response.data.unit_types
                })

        },
        initForm() {
            this.errors = {}
            this.form = {
                id: this.id,
                machine_type_id:null,
                brand:'',
                model:'',
                maximum_force:'',
                minimum_force: '',
                unit_type_id: null,

            }
            this.isUpdate()

        },
        submit() {
            this.loading_submit = true
            this.$http.post(`/${this.resource}`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.initForm()
                        this.$message.success(response.data.message)
                        this.onClose()
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        this.$message.error(error.response.data.message)
                    }
                })
                .finally(() => {
                    this.loading_submit = false
                })
        },
        onClose() {
            window.location.href = '/machine-production'
            this.$emit("update:visible", false);
        },
    }
}
</script>
