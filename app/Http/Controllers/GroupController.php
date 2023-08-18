<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Module;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class GroupController extends Controller
{
    public function listGroup(){
        $groupList = DB::table('groups')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('groups.list', [
            'groupList' => $groupList
        ]);
    }
    public function addGroup(){
        return view('groups.create');

    }
    public function saveGroup(Request $request){
        $data = [
            'name' => $request->name,
            'user_id' => $request->user_id,
        ];

        Group::create($data);
        Session::put('message', 'Thêm nhóm người dùng thành công');
        return Redirect::to('list-group');
    }
    public function editGroup($id)
    {
        $edit_group = DB::table('groups')->where('id', $id)->first();
        return view('groups.edit', [
            'edit_group' => $edit_group,
        ]);
    }
    public function updateGroup(Request $request,$id){
        $data = [
            'name' => $request->name,
            'user_id' => $request->user_id,
        ];
        DB::table('groups')->where('id', $id)->update($data);
        Session::put('message', 'Cập nhập nhóm người dùng thành công');
        return Redirect::to('list-group');
    }
    public function deleteGroup($id)
    {
        DB::table('groups')->where('id', $id)->delete();
        Session::put('message', 'Xóa nhóm thành công');
        return Redirect::to('list-group');
    }
    public function Permissions(Group $group,$id,Request $request){
        $permission = DB::table('groups')->where('id', $id)->first();
        $module = Module::all();

        $roleListArr = [
            'view'=>'Xem',
            'add'=>'Thêm',
            'edit'=>'Sửa',
            'delete'=>'Xóa',
            'import'=>'import',
            'export'=>'export',

        ];
        $roleJson = $permission->permissions;
        if (!empty($roleJson)){
            $roleArr = json_decode($roleJson,true);
        } else {
            $roleArr = [];
        }

        return view('groups.permission',[
            'permission'=>$permission,
            'module'=>$module,
            'roleListArr'=>$roleListArr,
            'roleArr'=>$roleArr
        ]);
    }
    public function addModule(){
        return view('modules.create');
    }
    public function addPermissions(){
        return view('permissions.create');
    }
    public function savePermissions(Request $request){
        $permission_key = $request->input('permission_key');
        $permission_value = $request->input('permission_value');
        $Arr = [
            $permission_key=>$permission_value
        ];
        Session::put('message', 'Thêm quyền thành công');

        return Redirect::to('list-group');
    }
    public function saveModule(Request $request){
        $data = [
            'name' => $request->name,
            'title' => $request->title,
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s')
        ];
        Module::create($data);
        Session::put('message', 'Thêm module thành công');
        return Redirect::to('list-group');
    }
    public function postPermissions(Group $group,Request $request){
        if (!empty($request->role)){
            $roleArr = $request->role;
        } else {
            $roleArr = [];
        }
        $roleJson = json_encode($roleArr);
        $group->permissions = $roleJson;
        $group->save();
        return back()->with('msg','Phân quyền thành công');
    }
}
