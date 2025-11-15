@extends('layouts.app')

@section('content')
<h2>New Record</h2>
<form action="{{ route('records.store') }}" method="POST">
	@csrf
	@include('records.form')
	<button type="submit">Save</button>
</form>
@endsection


