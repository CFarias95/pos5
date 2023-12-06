<template>
    <el-dialog :title="titleDialog" width="50%"  :visible="showDialog"  @open="create"  :close-on-click-modal="false" :close-on-press-escape="false" append-to-body :show-close="false">

        <div class="form-body">
            <div>
                <el-alert :title="title" type="info" effect="dark" :closable="false"/>
            </div>
            <div class="row" >
                <div class="col-lg-12 col-md-12 table-responsive">
                    <table width="100%" class="table">
                        <thead>
                            <tr width="100%">
                                <th class="text-center">#</th>
                                <th >Lote</th>
                                <th>Serie</th>
                                <th>Fecha</th>
                                <th>Cant. Actual</th>
                                <th>Cant. Mover</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in lots" :key="index" width="100%">
                                <!-- <td>{{index}}</td> -->
                                <td class="text-center">
                                    <el-checkbox v-model="row.checked"></el-checkbox>
                                </td>
                                <td>
                                    {{row.code}}
                                </td>
                                <td>
                                    {{(row.series)?row.series:'N/A'}}
                                </td>
                                <td>
                                    {{row.date_of_due}}
                                </td>
                                <td>
                                    {{row.quantity}}
                                </td>
                                <td>
                                    <el-input-number v-model="row.compromise_quantity" :max="row.quantity" :min="0"></el-input-number>
                                </td>
                                <br>
                            </tr>
                        </tbody>
                    </table>


                </div>

            </div>
        </div>

        <div class="form-actions text-right pt-2">
            <el-button @click.prevent="close()">Cerrar</el-button>
            <el-button type="primary" @click="submit" >Guardar</el-button>
        </div>

    </el-dialog>
</template>

<script>
    export default {
        props: ['showDialog', 'lots', 'total'],
        data() {
            return {
                titleDialog: 'Series/Lotes',
                title: 'Cantidad a mover: '+this.total,
                loading: false,
                errors: {},
                form: {},
            }
        },
        async created() {

        },
        methods: {
            create(){

            },
            async submit(){

                let totalLotes = 0;

                this.lots.forEach(element => {
                    totalLotes += element.compromise_quantity
                });

                if( totalLotes > total){

                    this.$message({ message: `La cantidad no puede superar las ${this.total} unidades`, type: "error"});
                    return;
                }

                let val_lots = await this.validateLots()
                if(!val_lots.success)
                     return this.$message.error(val_lots.message);

                await this.$emit('addRowLot', this.lots);
                await this.$emit('update:showDialogLotsOutput', false)

            },
            close() {
                this.$emit('update:showDialog', false)
            },
            async clickCancelSubmit() {

                this.$emit('addRowLot', []);
                await this.$emit('update:showDialogLotsOutput', false)

            },
        }
    }
</script>
