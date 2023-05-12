
<style>
    *{
        background-image:url('https://media.istockphoto.com/id/1266723001/es/foto/incre%C3%ADble-polaris-en-el-cielo-nocturno-estrellado-profundo-espacio-con-estrellas.jpg?s=612x612&w=0&k=20&c=c74h0Xdjf7jE8TuYel4hbc76GfCw21_hLc-EHmQx-zU=') ;
    }
</style>
<div class="result" style="background-color: blue;">
<a  class="btn btn-primary btn-sm" href="{{route('create')}}">Crear</a>

@forelse ($perfil as $portafolio)
                <div class="card" style="width: 18rem;">
                    <div class="card-body">
                        <p class="card-title font-weight-bold" style="color: yellow">{{ $portafolio->nombre }}</p>
                        <h5 class="card-title text-truncate" style="color:yellow">{{ $portafolio->cedula }}</h5>
                        <br>
                        <br>
                        <a href="{{ route('show', $portafolio) }}" class="btn btn-secondary w-100" style="color:red">Más
                            información</a>
                            <a href="{{route('edit',$portafolio)}}" class="btn btn-success btn-sm" >Actualizar</a>
                    </div>
                    
                </div>
                <hr>
            @empty
                <p>No Existen Registros</p>
            @endforelse
</div>
