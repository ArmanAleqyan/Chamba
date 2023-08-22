@extends('admin.layouts.default')
@section('title')
    Пользователи
@endsection
@section('content')
    <div class="content-wrapper" bis_skin_checked="1">
        <br><br>
        <br><br>
        <div class="col-12 grid-margin stretch-card" bis_skin_checked="1">
            <div class="card" bis_skin_checked="1">
                <div class="card-body" bis_skin_checked="1">
                    <div style="display: flex; justify-content: space-between;">
                    <h4 class="card-title">Пользователь</h4>

                        <div style="display: grid; ">
                        <img src="{{asset('uploads/'.$get->avatar)}}" alt="sample" class="rounded mw-100">
                            <a  style="display: flex; justify-content: center; color: red" href="{{route('delete_user_photo', $get->id)}}">Удалить </a>
                        </div>
                    </div>
                    <form class="forms-sample" method="post" action="{{route('update_user_data')}}">
                        @csrf
                    <div class="form-group" bis_skin_checked="1">
                            <label for="exampleInputName1">Nickname</label>
                            <input type="text" class="form-control" id="exampleInputName1" placeholder="nickname" disabled style="color: black  " value="{{$get->nickname}}" name="nickname">
                        </div>
                        <input type="hidden" name="user_id" value="{{$get->id}}">
                        <div class="form-group" bis_skin_checked="1">
                            <label for="exampleInputEmail3">Эл.почта</label>
                            <input type="email" class="form-control" value="{{$get->email}}" disabled style="color: black" id="exampleInputEmail3" placeholder="Эл.почта">
                        </div>
                        <div class="form-group" bis_skin_checked="1">
                            <label for="exampleInputName1">Имя</label>
                            <input type="text" class="form-control" id="exampleInputName1" style="color: white" placeholder="Имя" value="{{$get->name}}" name="name">
                        </div>

                        <div class="form-group" bis_skin_checked="1">
                            <label for="exampleInputPassword4">Пароль</label>
                            <input type="password" class="form-control" id="exampleInputPassword4" style="color: white" name="password" placeholder="Пароль">
                        </div>
                            <div style="display: flex; justify-content: space-between">
                        <button type="submit" class="btn btn-outline-success btn-fw">Сохранить</button>
                        <a href="{{route('delete_user', $get->id)}}" class="btn btn-outline-danger btn-fw">Удалить Пользователя</a>
                        <a href="{{route('user_star', $get->id)}}" type="submit" class="btn btn-outline-warning btn-fw">@if($get->star == 0) Поставить @else Удалить @endif  звезду</a>
                        <a href="{{route('black_list', $get->id)}}" class="btn btn-outline-danger btn-fw">@if($get->black_list_status == null) Добавить в  чёрный список @else Удалить из чёрного списка  @endif </a>
                            </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="content-wrapper" bis_skin_checked="1">
            <br><br>
            <br><br>
            <div class="row " bis_skin_checked="1">
                <div class="col-12 grid-margin" bis_skin_checked="1">
                    <div class="card" bis_skin_checked="1">
                        <div class="card-body" bis_skin_checked="1">
                            <div style="display: flex; justify-content: space-between; align-items: center">
                                <h4 class="card-title">Публикации</h4>
                            </div>

                            <div class="table-responsive" bis_skin_checked="1">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>Описания</th>
                                        <th> Имя  </th>
                                        <th> Эл.почта </th>
                                    </tr>
                                    </thead>

                                    @foreach($post as $user)
                                        <tbody>
                                        <tr>
                                            <td>{{Str::limit($user->description, 20, '...')  }}</td>
                                            <td @if($user->black_list_status == 1) style="color: red" @endif> {{$user->user->name}} </td>
                                            <td @if($user->black_list_status == 1) style="color: red" @endif> {{$user->user->email}} </td>
                                            <td>
                                                <a href="{{route('single_page_post', $user->id)}}" type="button" class="btn btn-outline-warning btn-fw">Просмотреть</a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    @endforeach
                                </table>

                            </div>
                            <br><br>
                            @if($post->isEmpty())
                           <h1 style="display: flex; justify-content: center">Нет публикаций</h1>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
