@extends('layouts.app')

@push('title')Refund Policy | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col">
                <h1 class="text-center">Refund Policy</h1>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col">
                <h4>Premium Membership</h4>
                <p>
                    Each payment gets you 1 month of Premium Membership. When you cancel your membership, you will retain
                    your Premium benefits until the end of that billing period and you won't automatically renew after that.
                </p>
                <p>
                    If you have a valid reason for wanting to end your Premium benefits immediately and receive a refund,
                    email <a href="mailto:support@parkourhub.com" class="btn-link">support@parkourhub.com</a> and explain
                    your reason and provide your user ID. If your cancellation is within 2 days of your last payment, you
                    will receive a full refund. After that, you will receive a proportionate refund based on the time since
                    your previous payment.
                </p>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
