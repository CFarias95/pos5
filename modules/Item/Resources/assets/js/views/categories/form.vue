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
                        <div class="form-group" :class="{'has-danger': errors.parent_id}">
                            <label class="control-label">Categoria Padre</label>
                            <el-select v-model="form.parent_id"
                                        @change="changeParentCategorie()"
                                        filterable>
                                <el-option
                                    v-for="option in categories"
                                    :key="option.id"
                                    :label="option.name"
                                    :value="option.id"></el-option>
                            </el-select>
                        </div>
                        <div v-if="form.parent_id" class="form-group" :class="{'has-danger': errors.parent_2_id}">
                            <label class="control-label">Subcategoria Padre</label>
                            <el-select v-model="form.parent_2_id"
                                        filterable
                                        @change="changeParentCategorie2()">
                                <el-option
                                    v-for="option in categories_1"
                                    :key="option.id"
                                    :label="option.name"
                                    :value="option.id"></el-option>
                            </el-select>
                        </div>
                        <div v-if="form.parent_2_id" class="form-group" :class="{'has-danger': errors.parent_3_id}">
                            <label class="control-label">Sub-subcategoria Padre</label>
                            <el-select v-model="form.parent_3_id"
                                       filterable>
                                <el-option
                                    v-for="option in categories_2"
                                    :key="option.id"
                                    :label="option.name"
                                    :value="option.id"></el-option>
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
                resource: 'categories',
                errors: {},
                form: {},
                categories:[],
                categories_1:[],
                categories_2:[],
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
                    parent_id: null,
                    parent_2_id: null,
                    parent_3_id: null,
                }
            },
            create() {

                this.titleDialog = (this.recordId)? 'Editar categoría':'Nueva categoría'
                this.$http.get(`/${this.resource}/tables`).then(response => {
                            this.categories = response.data.categories
                        })
                if (this.recordId) {
                    this.$http.get(`/${this.resource}/record/${this.recordId}`).then(response => {
                            this.form = response.data
                            this.changeParentCategorie()
                            this.changeParentCategorie2()

                        })
                }
            },
            changeParentCategorie(){

                if(this.form.parent_id){

                    this.$http.get(`/${this.resource}/subcategorie/${this.form.parent_id}`).then(response => {
                        this.categories_1 = response.data.categories
                    })
                }
            },
            changeParentCategorie2(){
                if(this.form.parent_2_id){

                    this.$http.get(`/${this.resource}/subcategorie/2/${this.form.parent_2_id}`).then(response => {
                        this.categories_2 = response.data.categories
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
