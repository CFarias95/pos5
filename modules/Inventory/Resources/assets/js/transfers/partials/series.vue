<template>
    <el-dialog :title="titleDialog" width="50%"  :visible="showDialog"  @open="create"  :close-on-click-modal="false" :close-on-press-escape="false" append-to-body :show-close="false">

        <div class="form-body">
            <div>
                <el-alert :title="'Cantidad a mover '+quantity" type="info" effect="dark" :closable="false"/>
            </div>
            <div class="row" >
                <div class="col-lg-12 col-md-12 table-responsive">
                    <table width="100%" class="table">
                        <thead>
                            <tr width="100%">
                                <th class="text-center">Seleccionar</th>
                                <th>Lote</th>
                                <th>Serie</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in lots" :key="index" width="100%">
                                <!-- <td>{{index}}</td> -->
                                <td class="text-center">
                                    <el-checkbox v-model="row.checked"></el-checkbox>
                                </td>
                                <td>
                                    {{(row.lot_code)?row.lot_code:'N/A'}}
                                </td>
                                <td>
                                    {{(row.series)?row.series:'N/A'}}
                                </td>
                                <td>
                                    {{row.date}}
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
        props: ['showDialog', 'lots', 'quantity'],
        data() {
            return {

                titleDialog: 'Mover Series',
                loading: false,
                errors: {},
                form: {},
                quantityNow: 0,
            }
        },
        async created() {

        },
        methods: {
            create(){

            },
            async submit(){

                let totalSelecionadas = 0;
                this.lots.forEach(element => {
                    if(element.checked == true){
                        totalSelecionadas += 1;
                    }
                });

                if( totalSelecionadas > this.quantity){
                    this.$message({ message: `La cantidad no puede superar ${this.quantity}`, type: "error"});
                    return;
                }
                if( totalSelecionadas < this.quantity){
                    this.$message({ message: `La cantidad no puede ser menor  ${this.quantity}`, type: "error"});
                    return;
                }
                console.log('Series a enviar: ',this.lots)
                await this.$emit('addRowLot', this.lots);
                await this.$emit('update:showDialog', false)

            },
            changeQuantity(index){

                let totalLotes = 0;

                this.lots.forEach(element => {
                    totalLotes += element.compromise_quantity
                });

                this.quantityNow = totalLotes;
                this.lots[index].checked = true;
                if(this.quantityNow > this.quantity){

                    this.lots[index].compromise_quantity = 0;
                    this.lots[index].checked = false;
                    return this.$message.error('La cantidad a mover supera la cantidad solicitada');

                }
            },
            close() {
                this.$emit('update:showDialog', false)
            },
            async clickCancelSubmit() {

                this.$emit('addRowLot', []);
                await this.$emit('update:showDialog', false)

            },
        }
    }
</script>
