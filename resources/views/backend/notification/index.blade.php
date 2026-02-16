@extends('backend.layouts.app')
@section('title', __('Notification List'))
@section('style')
    <style>
        div.dataTables_wrapper div.dataTables_length select {
            width: 80px;
            display: inline-block;
        }
    </style>
@endsection
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Notification</h4>
                                <div>
                                    <a href="{{ route('admin.notification.create') }}" class="btn btn-primary">Send Custom Notification</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th class="text-center col-1">#</th>
                                                <th>Subject</th>
                                                <th>Message</th>
                                                {{-- <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($notifications as $notification)
                                            @php
                                                $data = json_decode($notification->data)

                                            @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $notification->subject }}</td>
                                                    <td>{{ $notification->message }}</td>
                                                    {{-- <td>
                                                        <a href="{{ route('admin.quiz.question.edit', ['quiz' => $quiz_id, 'question' => $question->id]) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.quiz.question.destroy', ['quiz' => $quiz_id, 'question' => $question->id]) }}" method="POST" class="d-inline delete-form">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td> --}}
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center" colspan="10"> No data found</td>
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
