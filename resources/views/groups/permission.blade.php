@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>
                PHÂN QUYỀN NHÓM {{$permission->name}} <a href="{{route('add-module')}}" class="btn bg-purple btn-flat"><i class="fa fa-plus"></i> Thêm Module</a>
                <a href="{{route('add-permission')}}" class="btn bg-purple btn-flat"><i class="fa fa-plus"></i> Thêm Quyền</a>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-user"></i> QUẢN LÝ NHÓM {{$permission->name}} </a></li>
            </ol>
        </section>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    <div class="box">

                        <div class="box-header with-border">

                            <h3 class="box-title">Phân Quyền Nhóm {{$permission->name}} </h3><br/>

                        </div>
                        <?php
                        $message = Session::get('message');
                        if ($message) {
                            echo '<h3 class="text-alert" style="color: red">' . $message . '</h3>';
                            Session::put('message', null);
                        }
                        ?>
                        <?php
                        $message = Session::get('msg');
                        if ($message) {
                            echo '<h3 class="text-alert" style="color: red">' . $message . '</h3>';
                            Session::put('msg', null);
                        }
                        ?>


                        <form action="" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="box-body">
                                <table class="table table-border">
                                    <tbody>
                                    <tr>
                                        <th style="width: 10px">STT</th>
                                        <th>Module</th>
                                        <th>Quyền</th>

                                    </tr>
                                    @if($module->count()>0)
                                        @foreach($module as $key=> $module)
                                            <tr>
                                                <th>{{$key+1}}</th>
                                                <th>{{$module->title}}</th>
                                                @if(!empty($roleListArr))
                                                    @foreach($roleListArr as $roleName=>$roleLabel)
                                                        <td>
                                                            <div class="col-3">
                                                                <label for="role_{{$module->name}}_{{$roleName}}">
                                                                    <input type="checkbox"
                                                                           name="role[{{$module->name}}][]"
                                                                           id="role_{{$module->name}}_{{$roleName}}"
                                                                           value="{{$roleName}}" {{isRole($roleArr, $module->name, $roleName) ? 'checked':false}}>
                                                                    {{$roleLabel}}
                                                                </label>
                                                            </div>
                                                        </td>
                                                    @endforeach
                                                @endif

                                                <td>
                                                    @if($module->name == 'groups')
                                                        <div class="col-3">
                                                            <label for="role_{{$module->name}}_permission">
                                                                <input type="checkbox" name="role[{{$module->name}}][]"
                                                                       id="role_{{$module->name}}_permission"
                                                                       value="permission" {{isRole($roleArr, $module->name, 'permission') ? 'checked':false}}>
                                                                Phân quyền
                                                            </label>
                                                        </div>
                                                    @endif
                                                </td>
{{--                                                <td style="display: flex">--}}
{{--                                                    <a href="" class="btn btn-warning btn-edit"><i class="fa fa-pencil"></i></a>--}}
{{--                                                    <a onclick="return confirm('Bạn có chắc là muốn xóa đối tác này ko?')" class="btn btn-danger btn-delete" href=""><i class="fa fa-trash"></i></a>--}}
{{--                                                </td>--}}
                                            </tr>
                                        @endforeach
                                    @endif

                                    </tbody>
                                </table>
                                <button type="submit" class="btn btn-primary">Phân quyền</button>
                            </div>
                        </form>

                        <div class="box-footer clearfix">
                            {{--                            {{ $groupList->links()}}                --}}
                        </div>
                    </div>
                </div>
            </div>
        </section>
@endsection

