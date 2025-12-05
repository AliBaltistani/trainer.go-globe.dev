@extends('layouts.master')

@section('content')
  
<div class="row">
                        <div class="col-xl-12">
                            <x-tables.card title="Goals list">
    <x-slot:tools>
        <a href="{{ route('goals.create')}}" class="btn btn-sm btn-primary-light">Add New</a>
    </x-slot:tools>

    @if (session('success') || session('error'))
        <div class="card-body border-bottom">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i class="bi bi-x"></i></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i class="bi bi-x"></i></button>
                </div>
            @endif
        </div>
    @endif

    <x-tables.table 
        :headers="['Sr.#', 'name', 'status', 'Created At', 'Updated At', 'Action']"
        :striped="true"
    >
        @foreach ( $goals  as  $goal )
        <tr>
            <th scope="row">{{ $loop->iteration }}</th>
            <td>{{ $goal->name }}</td>
            <td>{!! $goal->status ? '<span class="badge bg-success-transparent">Active</span>' : '<span class="badge bg-light text-dark">Inactive</span>' !!}</td>
            <td>{{ $goal->created_at->format('d-m-Y') }}</td>
            <td>{{ $goal->updated_at->format('d-m-Y') }}</td>
            <td>
                <x-tables.actions edit="{{ route('goals.edit', $goal->id) }}">
                    <form action="{{ route('goals.destroy', $goal->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill" title="Delete">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </form>
                </x-tables.actions>
            </td>
        </tr>
        @endforeach
    </x-tables.table>
</x-tables.card>
                        </div>
                    </div>
@endsection