@extends('layouts.master')

@section('content')
<div class="container">
    <x-tables.card title="All Subscriptions">
        <x-tables.table 
            :headers="['Sr.#', 'Client', 'Trainer', 'Status', 'Subscribed At', 'Unsubscribed At', 'Actions']"
            :bordered="true"
        >
            <tbody>
                @foreach ($subscriptions as $sub)
                    <tr>
                        <td>{{ $sub->id }}</td>
                        <td>{{ optional($sub->client)->name }}</td>
                        <td>{{ optional($sub->trainer)->name }}</td>
                        <td>{{ $sub->status }}</td>
                        <td>{{ optional($sub->subscribed_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($sub->unsubscribed_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <x-tables.actions>
                                <form method="POST" action="{{ route('admin.subscriptions.toggle', $sub->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        {{ $sub->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </x-tables.actions>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-tables.table>
        <div class="mt-4 pb-3">
            {{ $subscriptions->links() }}
        </div>
    </x-tables.card>
</div>
@endsection