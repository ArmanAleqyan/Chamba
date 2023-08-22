@extends('admin.layouts.default')
@section('title')
    Публикацие
@endsection

@section('content')
    <div class="content-wrapper" bis_skin_checked="1">
        <br><br>
        <br><br>
        <div class="col-12 grid-margin stretch-card" bis_skin_checked="1">
            <div class="card" bis_skin_checked="1">
                <div class="card-body" bis_skin_checked="1">
                    <div style="display: flex; justify-content: space-between;">
                        <h4 class="card-title">Публикация</h4>

                    </div>

                    <form class="forms-sample" method="post" action="">
                        @csrf
                        @if(isset($get->photo))
                        <div style="width: 400px;" id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner">
                                @foreach ($get->photo as $index => $post)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img class="d-block w-100" src="{{asset('uploads/'.$post->photo) }}" >
                                    </div>
                                @endforeach
                            </div>
                            <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                        @endif
                        <br>
                        <br>

                        @if(isset($get->description))
                            <div class="form-group" bis_skin_checked="1">
                            <label for="exampleInputPassword4">Описания</label>
                            <textarea   class="form-control" id="exampleInputPassword4" style="color: white; height: 500px;" name="description" placeholder="Описания">{{$get->description}}</textarea>
                        </div>
                        @endif



                        <div style="display: flex; justify-content: space-between">
{{--                            <button type="submit" class="btn btn-outline-success btn-fw">Сохранить</button>--}}
                            <a href="{{route('delete_post', $get->id)}}" class="btn btn-outline-danger btn-fw">Удалить</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection