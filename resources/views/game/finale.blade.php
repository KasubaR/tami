@extends('layouts.app')
@section('title', 'Happy Birthday!')

@section('content')
    <section class="flex flex-col items-center gap-5 text-center">
        <div class="animate-float text-8xl" aria-hidden="true">🥂</div>
        <h1 class="text-4xl font-bold text-champagne-600">Happy Birthday, Tamara!</h1>
        <div class="card w-full max-w-md whitespace-pre-line text-lg">{{ $message }}</div>
        <a href="{{ url('/play') }}" class="btn-secondary">Back to my gifts</a>
    </section>
@endsection
