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
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($contactUs as $data)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $data->name }}</td>
                                                    <td>{{ $data->email }}</td>
                                                    <td>{{ $data->subject ?? 'N/A' }}</td>
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
            document.querySelectorAll('.status-toggle').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    console.log("Clicked badge with ID:", this.dataset.id); // ✅ test log
                    const contactId = this.dataset.id;
                    const currentStatus = this.dataset.status;
                    const newStatus = currentStatus === 'pending' ? 'resolved' : 'pending';
                    let url = "{{ route('admin.contact-us.update', ':id') }}".replace(':id', contactId);

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
                                this.dataset.status = newStatus;
                                this.textContent = newStatus === 'pending' ? 'Pending' : 'Solved';
                                this.classList.toggle('badge-success', newStatus !== 'pending');
                                this.classList.toggle('badge-danger', newStatus === 'pending');
                            }
                        })
                        .catch(err => console.error(err));
                });
            });
        });
    </script>
@endpush
