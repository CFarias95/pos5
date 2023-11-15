<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group" :class="{'has-danger': errors.name}">
                            <label class="control-label">Nombre</label>
                            <el-input v-model="form.name"></el-input>
                            <small class="form-control-feedback" v-if="errors.name" v-text="errors.name[0]"></small>
                        </div>
                        <div class="form-group" :class="{'has-danger': errors.level_1}">
                            <label class="control-label">Nivel 1</label>
                            <el-select v-model="form.level_1"
                                        @change="changeParentCategorie()"
                                        filterable
                                        clearable >
                                <el-option
                                    v-for="option in level_1"
                                    :key="option.id"
                                    :label="option.name"
                                    :value="option.id" v-if="option.id != form.id"></el-option>
                            </el-select>
                        </div>
                        <div v-if="form.level_1" class="form-group" :class="{'has-danger': errors.level_2}">
                            <label class="control-label">Nivel 2</label>
                            <el-select v-model="form.level_2"
                                        filterable
                                        clearable
                                        @change="changeParentCategorie2()">
                                <el-option
                                    v-for="option in level_2"
                                    :key="option.id"
                                    :label="option.name"
                                    :value="option.id" v-if="option.id != form.id" ></el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right pt-2">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button type="primary" native-type="submit" :loading="loading_submit">Guardar</el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>

    export default {
        props: ['showDialog', 'recordId'],
        data() {
            return {
                loading_submit: false,
                titleDialog: null,
                resource: 'cost_centers',
                errors: {},
                form: {},
                level_1:[],
                level_2:[],
            }
        },
        created() {
            this.initForm()
        },
        methods: {
            initForm() {
                this.errors = {}
                this.form = {
                    id: null,
                    name: null,
                    level_1: null,
                    level_2: null,
                }
            },
            create() {

                this.titleDialog = (this.recordId)? 'Editar centro de costo':'Nuevo centro de costo'
                this.changeParentCategorie()

                if (this.recordId) {
                    this.$http.get(`/${this.resource}/record/${this.recordId}`).then(response => {
                            this.form = response.data
                            this.changeParentCategorie()
                            this.changeParentCategorie2()

                        })
                }
            },
            changeParentCategorie(){

                this.$http.get(`/${this.resource}/level/1/1`).then(response => {
                    this.level_1 = response.data.levels
                })
            },
            changeParentCategorie2(){
                if(this.form.level_1){

                    this.$http.get(`/${this.resource}/level/2/${this.form.level_1}`).then(response => {
                        this.level_2 = response.data.levels
                    })
                }
            },
            submit() {

                this.loading_submit = true
                this.$http.post(`${this.resource}`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                            this.$eventHub.$emit('reloadData')
                            this.close()
                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data
                        } else {
                            console.log(error.response)
                        }
                    })
                    .then(() => {
                        this.loading_submit = false
                    })

            },
            close() {
                this.$emit('update:showDialog', false)
                this.initForm()
            }
        }
    }
</script>
