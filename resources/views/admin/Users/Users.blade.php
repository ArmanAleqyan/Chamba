@extends('admin.layouts.default')
@section('title')
    Пользователи
@endsection
@section('content')
    <div class="content-wrapper" bis_skin_checked="1">
        <br><br>
        <br><br>
        <div class="row " bis_skin_checked="1">
            <div class="col-12 grid-margin" bis_skin_checked="1">
                <div class="card" bis_skin_checked="1">
                    <div class="card-body" bis_skin_checked="1">
                        <div style="display: flex; justify-content: space-between; align-items: center">
                        <h4 class="card-title">Пользователи</h4>
                        <div class="form-group" bis_skin_checked="1">
                            <form action="{{ Route::currentRouteName()}}" method="get">
                            <div class="input-group" bis_skin_checked="1">
                                    <input name="search" style="color: white; width: 440px" type="text" class="form-control" value="@if(isset($_GET['search'])){{$_GET['search']}}@endif" placeholder="@if(isset($_GET['search'])){{$_GET['search']}}@else Введите данные для поиска @endif" aria-label="Recipient's username" aria-describedby="basic-addon2">
                                    <div class="input-group-append" bis_skin_checked="1">
                                        <button class="btn btn-sm btn-primary" type="submit">Поиск</button>
                                    </div>

                            </div>
                            </form>
                        </div>
                        </div>
                        @if(isset($_GET['search']))
                            По вашему запросу <span style="color: #2f567a;"><?php echo  $_GET['search']  ?> </span>   найдено <span style="color: #2f567a;">{{$count}}</span>
                        @endif

                        <div class="table-responsive" bis_skin_checked="1">
                            <table class="table">
                                <thead>
                                <tr>

                                    <th> Nickname </th>
                                    <th> Имя  </th>
                                    <th> Эл.почта </th>

                                </tr>
                                </thead>
                                @foreach($get as $user)
                                <tbody>
                                <tr>
                                    <td>
                                        <img src="{{asset('uploads/'.$user->avatar) }}" alt="image">
                                        <span  @if($user->black_list_status == 1) style="color: red" @endif class="ps-2">{{$user->nickname}}</span>
                                    </td>
                                    <td @if($user->black_list_status == 1) style="color: red" @endif> {{$user->name}} </td>
                                    <td @if($user->black_list_status == 1) style="color: red" @endif> {{$user->email}} </td>
                                    <td>
                                        <a href="{{route('single_page_user', $user->id)}}" type="button" class="btn btn-outline-warning btn-fw">Просмотреть</a>
                                    </td>
                                </tr>
                                </tbody>
                                    @endforeach
                            </table>
                        </div>
                        <br><br>
                        @if($get->isEmpty())
                            <h1 style="display: flex; justify-content: center">Ничего не найдено</h1>
                        @endif
                    </div>
                  <div style="display: flex; justify-content: center">{{$get->appends(['search' => request()->search, 'per_page' => request()->per_page])}}</div>
                </div>
            </div>
        </div>
    </div>
    @endsection
