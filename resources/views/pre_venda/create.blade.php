@extends('front_box.default', ['title' => 'PRÉ VENDA'])
@section('content')

{!! Form::open()
->post()
->route('pre-venda.store') !!}
<div class="">
    @include('pre_venda._forms')
</div>
{!! Form::close() !!}

@endsection
