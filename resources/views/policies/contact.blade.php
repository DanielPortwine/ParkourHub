@extends('layouts.app')

@push('title')Contact | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col">
                <h1 class="text-center">Contact</h1>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col">
                <h4>Support</h4>
                <p>
                    If you experience any issues or find any bugs please send as much detail as you can to
                    <a href="mailto:support@parkourhub.com" class="btn-link">support@parkourhub.com</a>.
                </p>
                <h4>Feedback & Suggestions</h4>
                <p>
                    If you have any general feedback or suggestions for improvements or new features, please get in touch
                    on <a href="" class="btn-link">Instagram</a> or email me at
                    <a href="mailto:dan@parkourhub.com" class="btn-link">dan@parkourhub.com</a>.
                </p>
                <h4>Newsletter</h4>
                <p>
                    If you have any questions, feedback or suggestions relating to the Pakour Hub Newsletter please contact
                    <a href="mailto:newsletter@parkourhub.com" class="btn-link">newsletter@parkourhub.com</a>.
                </p>
                <h4>Business & Partnership</h4>
                <p>
                    If you have any questions or enquiries relating to business or partnership please contact
                    <a href="mailto:business@parkourhub.com" class="btn-link">business@parkourhub.com</a>.
                </p>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
