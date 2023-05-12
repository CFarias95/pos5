<style>
    *{
        background-image:url('https://media.istockphoto.com/id/1266723001/es/foto/incre%C3%ADble-polaris-en-el-cielo-nocturno-estrellado-profundo-espacio-con-estrellas.jpg?s=612x612&w=0&k=20&c=c74h0Xdjf7jE8TuYel4hbc76GfCw21_hLc-EHmQx-zU=') ;
    }
</style>
<form action="{{ route('store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group row mb-3">
                            <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Nombre</label>
                            <div class="col-sm-10">

                                <input type="text" name="nombre"
                                    class="form-control form-control-sm @error('nombre')is-invalid @enderror"
                                    placeholder="Ingrese información" value="{{ old('nombre') }}">

                                @error('nombre')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="colFormLabelSm"
                                class="col-sm-2 col-form-label col-form-label-sm ">Cedula</label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('cedula')is-invalid @enderror"
                                    name="cedula" rows="3">{{ old('cedula') }}</textarea>
                                @error('cedula')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrar</button>
                    </form>











                    