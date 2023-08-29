<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Documentos Avanzados</span></li>
                <li><span class="text-muted">Pedidos Internos</span>
                </li>
            </ol>
            <div class="right-wrapper pull-right">

                <button class="btn btn-custom btn-sm  mt-2 mr-2" type="button" @click.prevent="clickCreate()"><i
                    class="fa fa-plus-circle"></i> Nuevo
                </button>
            </div>
        </div>
    </div>
</template>
<script>
    import DataTable from '../../../components/DataTableImports.vue'
    export default{
        methods:{
            clickCreate(){
                console.log("click en boton crear")
            },
        }

    }
</script>
