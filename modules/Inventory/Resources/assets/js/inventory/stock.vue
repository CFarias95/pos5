<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="control-label">Producto</label>
                            <el-input v-model="form.item_description" :readonly="true"></el-input>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Stock en el sistema</label>
                            <el-input v-model="form.quantity" :readonly="true"></el-input>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="control-label">Almacén</label>
                            <el-input v-model="form.warehouse_description" :readonly="true"></el-input>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Stock real</label>
                            <el-input v-model="form.quantity_real"></el-input>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group" :class="{'has-danger': errors.inventory_transaction_id}">
                            <label class="control-label">Motivo ajuste</label>
                            <el-select v-model="form.inventory_transaction_id" filterable>
                                <el-option v-for="option in inventory_transactions" :key="option.id" :value="option.id"
                                           :label="option.name"></el-option>
                            </el-select>
                            <small class="form-control-feedback" v-if="errors.inventory_transaction_id"
                                   v-text="errors.inventory_transaction_id[0]"></small>
                        </div>
                    </div>

                    <div class="col-md-4 mt-4" v-if="form.item_id && form.warehouse_id && form.series_enabled">
                        <!-- <el-button type="primary" native-type="submit" icon="el-icon-check">Elegir serie</el-button> -->
                        <a href="#"  class="text-center font-weight-bold text-info" @click.prevent="clickLotcodeOutput">[&#10004; Seleccionar series]</a>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button type="primary" native-type="submit" :loading="loading_submit">Aceptar</el-button>
            </div>
        </form>
        <output-lots-form
            :showDialog.sync="showDialogLotsOutput"
            :lots="form.lots"
            @addRowOutputLot="addRowOutputLot">
        </output-lots-form>
        <lots-group
            :quantity="this.quantity"
            :showDialog.sync="showDialogLots"
            :lots_group="form.lots_group"
            @addRowLotGroup="addRowLotGroup">
        </lots-group>

        <options
            :showDialog.sync="showDialogOptions"
            :recordId="this.inventoryId"
            :showClose="this.showClose"
            :type="this.type">
        </options>

    </el-dialog>

</template>

<script>
    import OutputLotsForm from './partials/lots.vue'
    import LotsGroup from './lots_group.vue'
    import Options from './partials/options.vue'

    export default {
        components: {OutputLotsForm, Options, LotsGroup},
        props: ['showDialog', 'recordId'],
        data() {
            return {
                loading_submit: false,
                titleDialog: null,
                showDialogLotsOutput:false,
                showDialogLots:false,
                resource: 'inventory',
                errors: {},
                form: {},
                warehouses: [],
                inventory_transactions:[],
                showDialogOptions:false,
                type:'fix',
                inventoryId:null,
                quantity: 1,
            }
        },
        created() {
            this.initForm()
            this.$http.get(`/${this.resource}/tables`)
                .then(response => {
                    this.warehouses = response.data.warehouses
                    this.inventory_transactions = response.data.inventory_transactions
                })
        },
        methods: {
            addRowOutputLot(lots){
                this.form.lots = lots
            },
            clickLotcodeOutput(){
                this.showDialogLotsOutput = true
            },
            initForm() {
                this.errors = {}
                this.form = {
                    id: null,
                    item_id: null,
                    lot_code: null,
                    item_description: null,
                    warehouse_id: null,
                    warehouse_description: null,
                    quantity: null,
                    quantity_real: null,
                    lots_enabled:false,
                    series_enabled:false,
                    inventory_transaction_id:null,
                    lots:[],
                    lots_group:[],
                    purchase_mean_price :null,
                }
            },
            create() {
                this.titleDialog = 'Ajuste de stock'
                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data.data
                        this.form.lots = Object.values(response.data.data.lots)
                    })
            },
            async submit() {

                if(this.form.series_enabled){
                    let select_lots = await _.filter(this.form.lots, {'has_sale':true})
                    if(select_lots.length != this.form.quantity_move){
                        return this.$message.error('La cantidad ingresada es diferente a las series seleccionadas');
                    }
                }

                this.loading_submit = true
                await this.$http.post(`/${this.resource}/stock`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                            this.$eventHub.$emit('reloadData')
                            this.inventoryId = response.data.id
                            this.type = 'fix'
                            this.showClose = false
                            this.showDialogOptions = true

                            //this.close()
                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data.errors
                        } else {
                            console.log(error)
                        }
                    })
                    .then(() => {
                        this.loading_submit = false
                    })
            },
            clickLotGroup() {
                this.quantity = 1
                this.showDialogLots = true
            },
            close() {
                this.$emit('update:showDialog', false)
                this.initForm()
            },
            addRowSelectLot(lots) {
                this.form.lots = lots
            },
        }
    }
</script>
