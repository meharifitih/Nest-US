@extends('layouts.app')
@section('page-title')
    {{ __('Payment Accounts') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Payment Accounts') }}</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                        {{ __('Add Payment Account') }}
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Account Type') }}</th>
                                    <th>{{ __('Account Number') }}</th>
                                    <th>{{ __('Account Name') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts as $account)
                                    <tr>
                                        <td>{{ ucfirst($account->account_type) }}</td>
                                        <td>{{ $account->account_number }}</td>
                                        <td>{{ $account->account_name }}</td>
                                        <td>
                                            <form action="{{ route('payment.accounts.destroy', $account->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">{{ __('Delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add Payment Account') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('payment.accounts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('Account Type') }}</label>
                            <select name="account_type" class="form-control" required>
                                <option value="cbe">{{ __('CBE') }}</option>
                                <option value="telebirr">{{ __('TeleBirr') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Account Number') }}</label>
                            <input type="text" name="account_number" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Account Name') }}</label>
                            <input type="text" name="account_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection 