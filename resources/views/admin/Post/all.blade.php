@extends('admin.layouts.default')
@section('title')
    Публикацие
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
                            <h4 class="card-title">Публикацие</h4>
                            Кол-во публикаций за сутки {{$new_count}}
                        </div>
                        @if(isset($_GET['search']))
                            По вашему запросу <span style="color: #2f567a;"><?php echo  $_GET['search']  ?> </span>   найдено <span style="color: #2f567a;">{{$count}}</span>
                        @endif

                        <div class="table-responsive" bis_skin_checked="1">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Описания</th>
                                    <th> Имя  </th>
                                    <th> Эл.почта </th>
                                </tr>
                                </thead>
                                @foreach($get as $user)
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
                    </div>
                    <div style="display: flex; justify-content: center">{{$get->appends(['search' => request()->search, 'per_page' => request()->per_page])}}</div>
                </div>
            </div>
        </div>
    </div>

@endsection