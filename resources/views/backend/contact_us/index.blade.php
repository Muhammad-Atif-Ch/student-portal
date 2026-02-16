@extends('backend.layouts.app')
@section('title', __('Contact Us List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Contact Us List</h4>
                                {{-- <a href="{{ route('admin.contact-us.create') }}" class="btn btn-primary">Add User</a> --}}
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                {{-- <th>Device Id</th>   --}}
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Subject</th>
                                                <th>Platform</th>
                                                <th>Created At</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($contactUs as $data)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    {{-- <td>{{ $data->user->device_id ?? 'N/A' }}</td> --}}
                                                    <td>{{ $data->name }}</td>
                                                    <td>{{ $data->email }}</td>
                                                    <td>{{ $data->subject ?? 'N/A' }}</td>
                                                    <td>{{ !empty($data->platform) ? $data->platform : 'N/A' }}</td>
                                                    <td>{{ $data->created_at ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge status-toggle badge-{{ $data->status !== 'pending' ? 'success' : 'danger' }}" data-id="{{ $data->id }}"
                                                            data-status="{{ $data->status }}" style="cursor:pointer; user-select:none;">
                                                            {{ $data->status !== 'pending' ? 'Solved' : 'Pending' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.contact-us.show', $data->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>
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
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', async function(e) {
                const toggle = e.target.closest('.status-toggle');
                if (!toggle) return;

                const contactId = toggle.dataset.id;
                const currentStatus = toggle.dataset.status;

                if (!contactId) {
                    console.error("No data-id found on element", toggle);
                    return;
                }

                const newStatus = currentStatus === 'pending' ? 'resolved' : 'pending';

                console.log("Clicked badge ID:", contactId);

                const url = "{{ route('admin.contact-us.update', ':id') }}".replace(':id', contactId);

                try {
                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        }).then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // update badge instantly
                                console.log("newStatus:", newStatus);
                                toggle.dataset.status = newStatus;

                                toggle.textContent = newStatus === 'pending' ? 'Pending' : 'Solved';

                                toggle.classList.toggle('badge-success', newStatus !== 'pending');
                                toggle.classList.toggle('badge-danger', newStatus === 'pending');
                            }
                        })
                        .catch(err => console.error(err));
                } catch (error) {
                    console.error("Update failed:", error);
                    alert(error.message);
                }
            });
        });
    </script>
@endpush
