@extends('layouts.master')

@section('content')
<div class="container">
    <x-tables.card title="Subscribers for {{ $trainer->name }}">
        <x-tables.table :headers="['Client', 'Email', 'Phone', 'Subscribed At']" :striped="true">
            @foreach ($subscriptions as $sub)
                <tr>
                    <td>{{ optional($sub->client)->name }}</td>
                    <td>{{ optional($sub->client)->email }}</td>
                    <td>{{ optional($sub->client)->phone }}</td>
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