
<style>
    *{
        background-image:url('https://media.istockphoto.com/id/1266723001/es/foto/incre%C3%ADble-polaris-en-el-cielo-nocturno-estrellado-profundo-espacio-con-estrellas.jpg?s=612x612&w=0&k=20&c=c74h0Xdjf7jE8TuYel4hbc76GfCw21_hLc-EHmQx-zU=') ;
    }
</style>
<a href="{{route('portafolio')}}" style="background: green;">Regresar</a>
<form action="{{route('update',$perfil)}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group row mb-3">
                            <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Nombre</label>
                            <div class="col-sm-10">
                                <input type="text" name="nombre" class="form-control form-control-sm" id="colFormLabelSm" value="{{$perfil->nombre}}">
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Descripción</label>
                            <div class="col-sm-10">
                            <textarea class="form-control" name="cedula" id="exampleFormControlTextarea1" rows="3" >{{$perfil->cedula}}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Actualizar</button>
                    </form>















                   