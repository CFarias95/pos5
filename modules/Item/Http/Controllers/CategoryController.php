<?php

namespace Modules\Item\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Item\Models\Category;
use Modules\Item\Http\Resources\CategoryCollection;
use Modules\Item\Http\Resources\CategoryResource;
use Modules\Item\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{

    public function index()
    {
        return view('item::categories.index');
    }


    public function columns()
    {
        $columns = [
            'name' => 'Nombre',
            'parent_id' => 'Departamento',
        ];

        $categories = Category::where('parent_id',null)->get();

        return compact('columns','categories');
    }

    public function records(Request $request)
    {
        if($request->column == 'parent_id'){
            if($request->value){
                $records = Category::where($request->column, $request->value)->orwhere('id',$request->value);
            }else{
                $records = Category::query();
            }

        }else{
            $records = Category::where($request->column, 'like', "%{$request->value}%");
        }
        return new CategoryCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function record($id)
    {
        $record = Category::findOrFail($id);
        return $record;
    }

    /**
     * Crea o edita una nueva categoría.
     * El nombre de categoría debe ser único, por lo tanto se valida cuando el nombre existe.
     *
     * @param CategoryRequest $request
     *
     * @return array
     */
    public function store(CategoryRequest $request)
    {
        $id = (int)$request->input('id');
        $name = $request->input('name');
        $error = null;
        $category = null;
        if(!empty($name)){
            $category = Category::where('name', $name);
            if(empty($id)) {
                $category= $category->first();
                if (!empty($category)) {
                    $error = 'El nombre de categoría ya existe';
                }
            }else{
                $category = $category->where('id','!=',$id)->first();
                if (!empty($category)) {
                    $error = 'El nombre de categoría ya existe para otro registro';
                }
            }
        }
        $data = [
            'success' => false,
            'message' => $error,
            'data' => $category
        ];
        if(empty($error)){
            $category = Category::firstOrNew(['id' => $id]);
            $category->fill($request->all());
            $category->save();
            $data = [
                'success' => true,
                'message' => ($id)?'Categoría editada con éxito':'Categoría registrada con éxito',
                'data' => $category
            ];
        }
        return $data;
    }

    public function destroy($id)
    {
        try {

            $category = Category::findOrFail($id);
            $category->delete();

            return [
                'success' => true,
                'message' => 'Categoría eliminada con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => "La categoría esta siendo usada por otros registros, no puede eliminar"] : ['success' => false,'message' => "Error inesperado, no se pudo eliminar la categoría"];

        }

    }

    public function tables(){
        $categories = Category::where('parent_id',null)->get();
        return compact('categories');
    }

    public function subcategories($id){
        $categories = Category::where('parent_id',$id)->where('parent_2_id',null)->get();
        return compact('categories');
    }

    public function subcategories2($id){
        $categories = Category::where('parent_2_id',$id)->where('parent_3_id',null)->get();
        return compact('categories');
    }


}
