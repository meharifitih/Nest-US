@extends('layouts.app')

@section('content')
<h1>Pending Payments</h1>
<table>
    <thead>
        <tr>
            <th>User</th>
            <th>Amount</th>
            <th>Payment Type</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pendingPayments as $payment)
        <tr>
            <td>{{ $payment->user->name }}</td>
            <td>{{ $payment->amount }}</td>
            <td>{{ $payment->payment_type }}</td>
            <td>
                <form action="{{ route('admin.payments.approve', $payment->id) }}" method="POST">
                    @csrf
                    <button type="submit">Approve</button>
                </form>
                <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST">
                    @csrf
                    <button type="submit">Reject</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection 