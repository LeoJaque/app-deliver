<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Category;
use File;

class CategoryController extends Controller
{
	public function index()
    {
    	$categories = Category::orderBy('name')->paginate(10);
    	return view('admin.categories.index')->with(compact('categories')); // listado
    }

    public function create()
    {
    	return view('admin.categories.create'); // formulario de registro
    }

    public function store(Request $request)
    {
        // validar
        $messages = [
            'name.required' => 'Es necesario ingresar un nombre para la categoria.',
            'name.min' => 'El nombre del producto debe tener al menos 3 caracteres.',
            'description.max' => 'La descripción corta solo admite hasta 250 caracteres.',
        ];
        $rules = [
            'name' => 'required|min:3',
            'description' => 'required|max:250',
        ];
        $this->validate($request, Category::$rules, Category::$messages);

        //registrar en la bd
        Category::create($request->all()); //

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = public_path() . '/images/categories';
            $fileName = uniqid() . '-' . $file->getClientOriginalName();
            $moved = $file->move($path, $fileName);
            
            // update category
            if ($moved) {
                $category->image = $fileName;
                $category->save(); // UPDATE
            }
        }

        return redirect('/admin/categories');
    }

    public function edit(Category $category) //Recibir id directamente
    {
        return view('admin.categories.edit')->with(compact('category')); // form de edición
    }

    public function update(Request $request, Category $category)
    {
        $this->validate($request, Category::$rules, Category::$messages);

        $category->update($request->only('name', 'description'));

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = public_path() . '/images/categories';
            $fileName = uniqid() . '-' . $file->getClientOriginalName();
            $moved = $file->move($path, $fileName);
            
            // update category
            if ($moved) {
                $previousPath = $path . '/' . $category->image;

                $category->image = $fileName;
                $saved = $category->save(); // UPDATE

                if ($saved)
                    File::delete($previousPath);
            }
        }        

        return redirect('/admin/categories');
    }

    public function destroy(Category $category)
    {
        $category->delete(); // DELETE
        return back();
    }
}
