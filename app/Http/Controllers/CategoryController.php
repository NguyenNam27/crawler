<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class CategoryController extends Controller
{
    public function listCategory()
    {
        $category = DB::table('categories')->paginate(10000);
        return view('category.list',[
            'category'=>$category
        ]);
    }
    public function addCategory(){
        return view('category.create');
    }
    public function saveCategory(Request $request)
    {
        $data = array();

        $data['name'] = $request->name;
        $data['url'] = $request->url;
        $data['status'] = $request->status;

        DB::table('categories')->insert($data);
        Session::put('message','Thêm danh mục sản phẩm thành công');
        return Redirect::to('list-category');
    }
    public function edit_category($id){
        $edit_category = DB::table('categories')->where('id',$id)->first();
        return view('category.edit',[
            'edit_category'=>$edit_category
        ]);
    }
    public function update_category(Request $request,$id){
        $data = array();

        $data['name'] = $request->name;
        $data['url'] = $request->url;
        $data['status'] = $request->status;

        DB::table('categories')->where('id',$id)->update($data);
        Session::put('message','Sửa danh mục sản phẩm thành công');
        return Redirect::to('list-category');
    }
    public function delete_category($id){
        DB::table('categories')->where('id',$id)->delete();
        Session::put('message','Xóa danh mục sản phẩm thành công');
        return Redirect::to('list-category');
    }
}
