<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">
                {{ title }}
            </h3>
        </div>
        <div class="tab-content">
            <form autocomplete="off" @submit.prevent="submit">
                <div class="form-body">
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{'has-danger': errors.item_id}"
                                        class="form-group">
                                        <label class="control-label">Producto </label>
                                        <el-select
                                            v-model="form.item_id"
                                            :loading="loading_search"
                                            :remote-method="searchRemoteItems"
                                            filterable
                                            remote
                                            @change="changeItem"
                                        >
                                            <el-option
                                                v-for="option in items"
                                                :key="option.id"
                                                :label="option.description"
                                                :value="option.id"
                                            ></el-option>
                                        </el-select>
                                        <small v-if="errors.item_id" class="form-control-feedback"
                                            v-text="errors.item_id[0]"></small>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.warehouse_id }" class="form-group">
                                        <label class="control-label">Almacén</label>
                                        <el-select :disabled="!isCreating" v-model="form.warehouse_id" filterable>
                                            <el-option v-for="option in warehouses" :key="option.id"
                                                :label="option.description" :value="option.id"></el-option>
                                        </el-select>
                                        <small v-if="errors.warehouse_id" class="form-control-feedback"
                                            v-text="errors.warehouse_id[0]"></small>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.quantity }" class="form-group">
                                        <label class="control-label">Cantidad</label>
                                        <el-input-number
                                            v-model="form.quantity"
                                            :controls="false"
                                            :min="this.min_force"
                                            :max="this.max_force"
                                            :precision="precision"
                                            @change="handleChange($event)"
                                        ></el-input-number>
                                        <small
                                            v-if="errors.quantity"
                                            class="form-control-feedback"
                                            v-text="errors.quantity[0]"
                                        ></small>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.inventory_transaction_id }" class="form-group">
                                        <label class="control-label">Motivo traslado</label>
                                        <input class="form-control" readonly type="text" value="Ingreso de producción" />
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.name }" class="form-group">
                                        <label class="control-label">Número de Ficha</label>
                                        <el-input v-model="form.name"></el-input>
                                        <small v-if="errors.name" class="form-control-feedback"
                                            v-text="errors.name[0]"></small>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.comment }" class="form-group">
                                        <label class="control-label">Comentario</label>
                                        <el-input v-model="form.comment"></el-input>
                                        <small v-if="errors.comment" class="form-control-feedback"
                                            v-text="errors.comment[0]"></small>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.production_order }" class="form-group">
                                        <label class="control-label">Orden de producción</label>
                                        <input v-model="form.production_order" class="form-control"
                                            placeholder="Orden de producción" type="text" />

                                        <small v-if="errors.production_order" class="form-control-feedback"
                                            v-text="errors.production_order[0]"></small>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.machine_id }" class="form-group">
                                        <label class="control-label">
                                            Maquina
                                        </label>
                                        <el-select :disabled="!isCreating" v-model="form.machine_id"
                                            @change="fetchMachineInfo()">
                                            <el-option v-for="option in machines" :key="option.id" :label="option.name"
                                                :value="option.id"></el-option>
                                        </el-select>

                                        <small v-if="errors.machine_id" class="form-control-feedback"
                                            v-text="errors.machine_id[0]"></small>

                                    </div>

                                    <div class="form-group" v-if="form.machine_id">
                                        <el-tag type="danger" effect="dark">
                                            Min: {{ min_force }}
                                        </el-tag>

                                        <el-tag type="success" effect="dark">
                                            Max: {{ max_force }}
                                        </el-tag>
                                    </div>
                                </div>



                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.lot_code }" class="form-group">
                                        <label class="control-label">
                                            Lote
                                        </label>
                                        <input
                                            v-model="form.lot_code"
                                            class="form-control"
                                            placeholder="Lote"
                                            type="text"
                                        />

                                        <small v-if="errors.lot_code" class="form-control-feedback"
                                            v-text="errors.lot_code[0]"></small>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.agreed }" class="form-group">
                                        <label class="control-label">
                                            Conformes
                                        </label>
                                        <el-input-number v-model="form.agreed" :controls="false" :min="0"
                                            :precision="precision"></el-input-number>

                                        <small v-if="errors.agreed" class="form-control-feedback"
                                            v-text="errors.agreed[0]"></small>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.imperfect }" class="form-group">
                                        <label class="control-label">
                                            Defectuosos
                                        </label>


                                        <el-input-number v-model="form.imperfect" :controls="false" :min="0"
                                            :precision="precision"></el-input-number>


                                        <small v-if="errors.imperfect" class="form-control-feedback"
                                            v-text="errors.imperfect[0]"></small>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <div :class="{ 'has-danger': errors.item_extra_data }" class="form-group">
                                        <label class="control-label">
                                            Color
                                        </label>
                                        <el-select v-model="form.item_extra_data.color"
                                            :disable="item === undefined || item.colors === undefined || item.colors.length < 1"
                                            filterable>
                                            <el-option v-for="option in item.colors" :key="option.id"
                                                :label="option.color_name" :value="option.id"></el-option>
                                        </el-select>
                                        <small v-if="errors.item_extra_data" class="form-control-feedback"
                                            v-text="errors.item_extra_data[0]"></small>
                                    </div>
                                </div>

                                <hr>
                                <div class="col-12 mt-3">
                                    <div class="form-group">
                                        <label class="control-label">
                                            Producción
                                        </label>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4 ">
                                    <div class="row">


                                        <div class="col-6">
                                            <div :class="{ 'has-danger': errors.date_start }" class="form-group">
                                                <label class="control-label">
                                                    Fecha de inicio
                                                </label>
                                                <el-date-picker v-model="form.date_start" :clearable="false"
                                                    format="dd/MM/yyyy" type="date"
                                                    value-format="yyyy-MM-dd"></el-date-picker>
                                                <small v-if="errors.date_start" class="form-control-feedback"
                                                    v-text="errors.date_start[0]"></small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div :class="{ 'has-danger': errors.time_start }" class="form-group">
                                                <label class="control-label">Hora de Inicio</label>
                                                <el-time-picker v-model="form.time_start" dusk="time_start"
                                                    placeholder="Seleccionar" value-format="HH:mm:ss"></el-time-picker>
                                                <small v-if="errors.time_start" class="form-control-feedback"
                                                    v-text="errors.time_start[0]"></small>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <div class="col-sm-12 col-md-4 ">
                                    <div class="row">
                                        <div class="col-6">
                                            <div :class="{ 'has-danger': errors.date_end }" class="form-group">
                                                <label class="control-label">
                                                    Fecha de Finalización
                                                </label>
                                                <el-date-picker v-model="form.date_end" :clearable="false"
                                                    format="dd/MM/yyyy" type="date"
                                                    value-format="yyyy-MM-dd"></el-date-picker>
                                                <small v-if="errors.date_end" class="form-control-feedback"
                                                    v-text="errors.date_end[0]"></small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div :class="{ 'has-danger': errors.time_end }" class="form-group">
                                                <label class="control-label">Hora de finalización</label>
                                                <el-time-picker v-model="form.time_end" dusk="time_end"
                                                    placeholder="Seleccionar" value-format="HH:mm:ss"></el-time-picker>
                                                <small v-if="errors.time_end" class="form-control-feedback"
                                                    v-text="errors.time_end[0]"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div :class="{ 'has-danger': errors.production_collaborator }" class="form-group">
                                        <label class="control-label">Colaborador de producción</label>
                                        <input class="form-control" v-model="form.production_collaborator" type="text"
                                            value="Colaborador de produccion" />
                                    </div>
                                </div>

                                <hr>
                                <div class="col-12 mt-3">
                                    <div class="form-group">
                                        <label class="control-label">
                                            Mezcla
                                        </label>
                                    </div>
                                </div>


                                <div class="col-sm-12 col-md-4 ">
                                    <div class="row">
                                        <div class="col-6">
                                            <div :class="{ 'has-danger': errors.mix_date_start }" class="form-group">
                                                <label class="control-label">
                                                    Fecha de inicio
                                                </label>
                                                <el-date-picker v-model="form.mix_date_start" :clearable="false"
                                                    format="dd/MM/yyyy" type="date"
                                                    value-format="yyyy-MM-dd"></el-date-picker>
                                                <small v-if="errors.mix_date_start" class="form-control-feedback"
                                                    v-text="errors.mix_date_start[0]"></small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div :class="{ 'has-danger': errors.mix_time_start }" class="form-group">
                                                <label class="control-label">Hora de Inicio</label>
                                                <el-time-picker v-model="form.mix_time_start" dusk="time_start"
                                                    placeholder="Seleccionar" value-format="HH:mm:ss"></el-time-picker>
                                                <small v-if="errors.mix_time_start" class="form-control-feedback"
                                                    v-text="errors.mix_time_start[0]"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4 ">
                                    <div class="row">
                                        <div class="col-6">
                                            <div :class="{ 'has-danger': errors.mix_date_end }" class="form-group">
                                                <label class="control-label">
                                                    Fecha de Finalización
                                                </label>
                                                <el-date-picker v-model="form.mix_date_end" :clearable="false"
                                                    format="dd/MM/yyyy" type="date"
                                                    value-format="yyyy-MM-dd"></el-date-picker>
                                                <small v-if="errors.mix_date_end" class="form-control-feedback"
                                                    v-text="errors.mix_date_end[0]"></small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div :class="{ 'has-danger': errors.mix_time_end }" class="form-group">
                                                <label class="control-label">Hora de finalización</label>
                                                <el-time-picker v-model="form.mix_time_end" dusk="time_end"
                                                    placeholder="Seleccionar" value-format="HH:mm:ss"></el-time-picker>
                                                <small v-if="errors.mix_time_end" class="form-control-feedback"
                                                    v-text="errors.mix_time_end[0]"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div :class="{ 'has-danger': errors.mix_collaborator }" class="form-group">
                                        <label class="control-label">Colaborador de Mezcla</label>
                                        <input class="form-control" v-model="form.mix_collaborator" type="text"
                                            value="Colaborador de Mezcla" />
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3">
                                    <label class="control-label">
                                        Ficha Informativa
                                        <el-tooltip class="item" content="No se contabilizará el stock" effect="dark"
                                            placement="top-start">
                                            <i class="fa fa-info-circle"></i>
                                        </el-tooltip>
                                    </label>
                                    <div class="form-group" :class="{ 'has-danger': errors.informative }">
                                        <el-switch v-model="form.informative" active-text="Si"
                                            inactive-text="No"></el-switch>
                                        <small class="form-control-feedback" v-if="errors.informative"
                                            v-text="errors.informative[0]">

                                        </small>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-9" v-if="form.informative">
                                    <div :class="{ 'has-danger': errors.proccess_type }" class="form-group">
                                        <label class="control-label">
                                            Tipo de proceso
                                        </label>
                                        <el-input v-model="form.proccess_type"></el-input>
                                        <small v-if="errors.proccess_type" class="form-control-feedback"
                                            v-text="errors.proccess_type[0]"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div :class="{ 'has-danger': errors.records_id }" class="form-group">
                                <label class="control-label">Estado</label>
                                <el-select v-model="form.records_id"
                                           filterable>
                                    <el-option
                                        v-for="option in records"
                                        :key="option.id"
                                        :label="option.description"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small v-if="errors.records_id" class="form-control-feedback"
                                    v-text="errors.records_id[0]"></small>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="form-actions text-right mt-4" v-if="false">
                    <el-button
                        :loading="loading_submit"
                        native-type="submit"
                        type="primary"
                    >Guardar
                    </el-button>
                </div>

                <div class="form-actions text-right mt-4">
                    <el-button @click.prevent="onClose()">
                        Cancelar
                    </el-button>
                    <el-button :loading="loading_submit" native-type="(id) ? submit() : update()" type="primary">
                        {{ (id) ? 'Actualizar' : 'Guardar' }}
                    </el-button>
                </div>

                <div v-if="supplies && supplies.length > 0" class="col-12 col-md-12 mt-3">
                    <h3 class="my-0">Lista de materiales</h3>

                    <div class="col-md-12 mt-3 table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Cantidad a descargar</th>
                                <th>Cantidad base</th>
                                <th>Unidad de medida</th>
                                <th class="text-center">Almacen</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="row in this.supplies" :key="row.id">
                                <th> {{ row.individual_item ? row.individual_item.name +' / ' + row.individual_item.description  : row.description }}</th>
                                <th>
                                    <!-- {{ row.quantity }} -->
                                    <el-input-number v-model="row.quantityD" :disabled="row.modificable == 0 "></el-input-number>

                                    <div v-if="row.lots_enabled && isCreating" style="padding-top: 1%;">
                                        <a class="text-center font-weight-bold text-info" href="#"
                                            @click.prevent="clickLotGroup(row)">[&#10004;
                                            Seleccionar
                                            lote]</a>
                                    </div>
                                    <!-- JOINSOFTWARE
                                    <el-input-number v-model="quantityD" :step="1"></el-input-number>
                                    -->
                                </th>
                                <th>
                                    <el-input-number :value="row.quantity" :controls="false"
                                        disabled></el-input-number>
                                </th>
                                <th>{{ row.unit_type }}</th>
                                <th>

                                    <el-select v-model="row.warehouse_id"
                                               filterable>
                                        <el-option
                                            v-for="option in warehouses"
                                            :key="option.id"
                                            :label="option.description"
                                            :value="option.id"
                                        ></el-option>
                                    </el-select>
                                </th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


            </form>
        </div>


        <lots-group :lots_group="selectSupply.lots_group" :quantity="selectSupply.quantity"
            :showDialog.sync="showDialogLots" @addRowLotGroup="addRowLotGroup">
        </lots-group>
    </div>
</template>

<script>

import LotsGroup from './lots_group.vue'

export default {
    components: {
        LotsGroup
    },
    props: {
        id: {
            type: Number,
            required: false,
        },
    },
    computed: {
        suppliesCalc() {
            return 0
        }
    },
    data() {
        return {
            resource: 'production',
            loading_submit: false,
            showDialogLots: false,
            errors: {},
            records: {},
            isCreating: false,
            title: "Nuevo producto fabricado",
            item: {
            },
            supplies: {},
            form: {
                items: [],
                informative: false,
                item_extra_data: {
                    color: null
                },

            },
            selectSupply: {
                supply_id: null,
                lots_group: [],
                quantity: 0,
            },
            loading_search: false,
            warehouses: [],
            precision: 4,
            items: [],
            machines: [],
            // JOINSOFTWARE
            quantityD: 0,
            max_force: 1000,
            min_force: 0,
            canEdit: true,
        }
    },
    async created() {
        await this.getTable();
        this.initForm()
    },
    methods: {
        addRowLotGroup(id) {
            let IdLoteSelected = id;
            const index = this.supplies.findIndex(item => item.id === this.selectSupply.supply_id);

            if (index !== -1) {
                this.supplies[index].IdLoteSelected = IdLoteSelected
            }
        },
        clickLotGroup(row) {
            //console.log("row", row)
            let donwloadQuantity = row.quantityD;
            this.selectSupply.supply_id = row.individual_item_id;
            this.selectSupply.lots_group = row.lots_group;
            this.selectSupply.quantity = _.round(donwloadQuantity,4);
            this.showDialogLots = true;
        },
        deleteStatus(id) {
            const index = this.records.findIndex((estado) => estado.id === id);
            if (index !== -1) {
                this.records.splice(index, 1);
            }
        },
        fetchMachineInfo() {
            if (this.form.machine_id) {
                const machine = this.machines.find(m => m.id === this.form.machine_id);
                this.min_force = parseFloat(machine.minimum_force);
                this.max_force = parseFloat(machine.maximum_force);
            } else {
                this.min_force = null;
                this.max_force = null;
            }

            if(this.form.quantity > this.max_force ||  this.form.quantity < this.min_force){
                this.$message.error('Verifica la cantidad a producir en base a la maquina seleccionada');
                this.form.quantity = 0;
            }
        },
        onClose() {
            window.location.href = '/production'
        },
        async isUpdate() {
            this.title = "Nuevo producto fabricado";
            if (this.id) {
                this.isCreating = false;
                await this.$http.get(`/${this.resource}/record/${this.id}`)
                    .then(response => {
                        this.title = "Editar producto fabricado";
                        this.form = response.data
                        //console.log("DATA: ",response.data)

                        let currentStatus = this.form.records_id;
                        switch (currentStatus) {
                            case '01':
                                this.isCreating = true;
                                this.deleteStatus("03")
                                this.deleteStatus('04')
                                this.changeItem()
                                break;
                            case '02':
                                this.deleteStatus("01")
                                this.deleteStatus("04")
                                this.supplies = this.form.supplies
                                break;
                            case '03':
                                this.deleteStatus("01")
                                this.deleteStatus("02")
                                this.supplies = this.form.supplies
                                break;
                            case '04':
                                this.records = []
                                break
                            default:
                                break;
                        }

                        this.fetchMachineInfo();

                    })
            } else {
                this.isCreating = true;
                this.deleteStatus('04')
                this.deleteStatus("03")
                this.deleteStatus("02")
            }

        },
        async initForm() {
            this.form = {
                id: this.id,
                item_id: null,
                warehouse_id: null,
                quantity: 0,
                informative:false,
                records_id: null,
                agreed: 0,
                imperfect: 0,
                lot_code: null,
                item_extra_data: {
                    color: null
                },

            }
            this.supplies = {};
            await this.isUpdate();
        },
        async getTable() {
            await this.$http.get(`/${this.resource}/tables`)
                .then(response => {

                    let data = response.data
                    this.warehouses = data.warehouses
                    this.items = data.items
                    //console.log('itemsss', this.items)
                    this.machines = data.machines
                    this.records = response.data.state_types_prod;
                })

        },
        //JOINSOFTWARE
        handleChange(value) {

            if (value > 0) {
                this.quantityD = value
                this.supplies.forEach(row => {
                    if(value > 0 ){
                        row.quantityD = _.round(value * row.quantity,4);
                    }else{
                        row.quantityD = _.round(row.quantity,4);
                    }
                });
            } else {
                return this.$message.error('La cantidad debe ser mayor a 0');
            }
        },
        async searchRemoteItems(search) {
            this.loading_search = true;
            this.items = [];
            await this.$http.post(`/${this.resource}/search_items`, { 'search': search })
                .then(response => {
                    this.items = response.data.items
                })
            this.loading_search = false;
        },

        async submit() {
            if (this.form.quantity > 0 ) {

            }else{
                return this.$message.error('La cantidad debe ser mayor a 0');
            }


            this.loading_submit = true;
            this.form.supplies = this.supplies;
            // Si no existe un ID, estás creando un nuevo registro
            console.log("submit production",this.form)

            if (!this.form.id) {

                await this.$http.post(`/${this.resource}/create`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message);
                            this.initForm();
                            window.location.href = '/production';

                        } else {
                            this.$message.error(response.data.message);
                        }
                    })
                    .catch(error => {
                        this.errors = error.response.data;
                    })
                    .finally(() => {
                        this.loading_submit = false;
                    });
            } else {
                // Si existe un ID, estás actualizando un registro existente
                //VALIDAMOS SI YA SE LE ASIGNO UN LOTE Y SI VA A ESTADO FINALIZADO
                if(this.form.records_id == '03' && this.form.lot_code == null){

                    let loteSugerido = this.form.item_id + this.form.date_end + this.form.production_order
                    alert("Se asignar de forma automática el lote : "+loteSugerido);
                    this.form.lot_code = loteSugerido

                }
                await this.$http.put(`/${this.resource}/update/${this.form.id}`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message);
                            window.location.href = '/production';
                        } else {
                            this.$message.error(response.data.message);

                        }
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data;
                        } else {
                            console.log(error);
                        }
                    })
                    .finally(() => {
                        this.loading_submit = false;
                    });
            }
        },

        changeItem() {

            let item = _.find(this.items, { 'id': this.form.item_id })
            this.form.item_extra_data = {}
            this.form.item_extra_data.color = null
            this.item = item
            //console.log("changeIte: ",item )
            this.form.warehouse_id = (item.lugar_produccion)?item.lugar_produccion:item.warehouse_id
            item.supplies.forEach(row => {

                if(this.form.quantity > 0 ){

                    row.quantityD = _.round(this.form.quantity * row.quantity,4);
                }else{

                    row.quantityD = _.round(row.quantity,4);
                }
            });

            this.supplies = item.supplies
            console.log('itemssupplui', this.supplies)
        },

        changeQuantityForm(){


        }

    }
}
</script>
