@extends('layouts.admin')

@section('title', 'Free Servers')

@section('content-header')
    <h1>Free Servers<small>Manage temporary free servers</small></h1>
@endsection

@section('content')
    <div class="box box-primary">
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Server ID</th>
                        <th>Expires At</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($freeServers as $free)
                        <tr>
                            <td>{{ $free->user->email }}</td>
                            <td>{{ $free->server_id ?? 'pending' }}</td>
                            <td>{{ $free->expires_at }}</td>
                            <td>
                                <form action="{{ route('admin.free-servers.delete', $free->id) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No free servers.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
