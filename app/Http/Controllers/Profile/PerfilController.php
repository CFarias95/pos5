<?php
namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;
use App\Models\Perfiles;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function index(){
        $perfil=Perfiles::all();
        return view('listaperfi',compact('perfil'));
    }
    public function store(Request $request){

        $perfil=$request->validate([
            'nombre'=>'required|max:50',
            'cedula'=>'required|max:50'
        ]);
        Perfiles::create($perfil);
        return redirect()->route('portafolio');

    }
    public function show($id){
        $perfil=Perfiles::find($id);
        return view('verperfil',compact('perfil'));
    }
    public function edit($id){
    
        $perfil=Perfiles::find($id);
        return view ('editarperfi',compact('perfil'));

    }
    public function create(){
        return view('crearperfil');
        return redirect()->route('portafolio');

    }

    public function update(Perfiles $id){
        
        $id->update([
           'nombre'=>request('nombre'),
            'cedula'=>request('cedula')
        ]);
        return redirect()->route('portafolio');

    }

    public function destroy(Perfiles $id){
        $id->delete();
    }
}