@extends('layouts.master')

@section('content')
<div class="container">
    <x-tables.card title="Subscribed Trainers for {{ $trainee->name }}">
        <x-tables.table :headers="['Trainer', 'Email', 'Phone', 'Status', 'Subscribed At']" :striped="true">
            @foreach ($subscriptions as $sub)
                <tr>
                    <td>{{ optional($sub->trainer)->name }}</td>
                    <td>{{ optional($sub->trainer)->email }}</td>
                    <td>{{ optional($sub->trainer)->phone }}</td>
                    <td>{{ $sub->status }}</td>
                    <td>{{ optional($sub->subscribed_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </x-tables.table>
        <div class="mt-3">
            {{ $subscriptions->links() }}
        </div>
    </x-tables.card>
</div>
@endsection