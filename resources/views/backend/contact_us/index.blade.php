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
                                {{-- <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add User</a> --}}
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
                                                {{-- <th>Action</th> --}}
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
                                                        <span class="badge badge-{{ $data->status !== 'pending' ? 'success' : 'danger' }}">{{ $data->status !== 'pending' ? 'Solved' : 'Pending' }}</span>
                                                        {{-- <div class="pretty p-switch pr-2">
                                                            <input type="checkbox" name="status" class="language-status-toggle" data-language-id="{{ $data->id }}"
                                                                {{ $data->status ? 'checked' : '' }}>
                                                            <div class="state p-primary">
                                                                <label></label>
                                                            </div>
                                                        </div> --}}
                                                    </td>
                                                    {{-- <td> --}}
                                                        {{-- <div class="pretty p-switch pr-2">
                                                            <input type="checkbox" name="status" class="language-status-toggle" data-language-id="{{ $language->id }}"
                                                                {{ $language->status ? 'checked' : '' }}>
                                                            <div class="state p-primary">
                                                                <label></label>
                                                            </div>
                                                        </div> --}}
                                                        {{-- <a href="{{ route('admin.language.update', $language->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a> --}}
                                                    {{-- </td> --}}
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
    <script>
        document.querySelectorAll('.language-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const languageId = this.getAttribute('data-language-id');
                const status = this.checked ? 1 : 0;
                let url = "{{ route('admin.language.update', ':id') }}".replace(':id', languageId);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            status: status
                        })
                    }).then(res => {
                        console.log(data.message);
                    })
                    .then(data => {
                        console.log(data.message);
                    });
            });
        });
    </script>
@endsection
