<style>
    *{
        background-image:url('https://media.istockphoto.com/id/1266723001/es/foto/incre%C3%ADble-polaris-en-el-cielo-nocturno-estrellado-profundo-espacio-con-estrellas.jpg?s=612x612&w=0&k=20&c=c74h0Xdjf7jE8TuYel4hbc76GfCw21_hLc-EHmQx-zU=') ;
    }
</style>
<div class="container">
<h5 class="card-title" style="color: blue ;">Nombre: {{$perfil->nombre}}</h5>
 <h5 class="card-title" style="color: blue ;">Cedula: {{$perfil->cedula}}</h5>
    

</div>
<form action="{{route('destroy',$perfil)}}" method="POST" style="display: inline;" onsubmit="return confirmation()">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger btn-sm" >Eliminar</button>
        <script type="text/javascript">
            function confirmation()
            {
                if(confirm("¿Estás seguro de eliminar el registro?"))
                {
                    return true;
                }
            else
                {
                    return false;
                }
            }
        </script>
    </form>
    <hr>

 
