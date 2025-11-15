@extends('layouts.app')

@section('content')
<h2>Edit Record</h2>
<form action="{{ route('records.update', $record) }}" method="POST">
	@csrf
	@method('PUT')
	@include('records.form', ['record' => $record])
	<button type="submit">Update</button>
</form>
@endsection


