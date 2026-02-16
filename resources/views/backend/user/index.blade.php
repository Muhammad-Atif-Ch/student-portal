@extends('backend.layouts.app')
@section('title', __('Users'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Users List</h4>
                                {{-- <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add User</a> --}}
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Name</th>
                                                <th>Device Id</th>
                                                <th>Roles</th>
                                                <th>Platform</th>
                                                <th>Membership</th>
                                                <th>Price</th>
                                                <th>Currency</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($users as $user)
                                                @php
                                                    $activeMembership = $user->iosMembership !== null ? $user->iosMembership : ($user->membership !== null ? $user->membership : null);
                                                    // dd($activeMembership, $user, $user->iosMembership !== null);
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $user->id }}</td>
                                                    <td>{{ $user->name ?? 'N/A' }}</td>
                                                    <td>{{ $user->device_id ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge badge-primary">{{ ucfirst($user->roles->map(fn($role) => $role->name)->join(', ')) }}</span>
                                                    </td>
                                                    <td><span class="badge {{ $user->platform ? 'badge-primary' : 'badge-danger' }}">{{ ucfirst($user->platform) }}</span></td>
                                                    <td>
                                                        <span class="badge {{ $activeMembership && $activeMembership->membership_type !== null ? 'badge-primary' : 'badge-danger' }}">
                                                            {{ ucfirst(string: $activeMembership->membership_type ?? 'N/A') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $activeMembership && $activeMembership->price ? 'badge-primary' : 'badge-danger' }}">
                                                            {{ ucfirst(string: $activeMembership->price ?? 'N/A') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $activeMembership && $activeMembership->currency ? 'badge-primary' : 'badge-danger' }}">
                                                            {{ ucfirst(string: $activeMembership->currency ?? 'N/A') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{-- <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a> --}}
                                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center" colspan="4"> No data found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('backend.layouts.partials.setting_sidebar')
    </div>
@endsection
@section('scripts')
@endsection
