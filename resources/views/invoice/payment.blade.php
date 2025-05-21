@if(auth()->user()->type == 'tenant')
    <div class="card mb-4">
        <div class="card-header">
            <h5>Owner Bank Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Bank Name:</strong> {{ $settings['company_bank_name'] ?? 'N/A' }}</p>
            <p><strong>Account Name:</strong> {{ $settings['company_bank_account_name'] ?? 'N/A' }}</p>
            <p><strong>Account Number:</strong> {{ $settings['company_bank_account_number'] ?? 'N/A' }}</p>
            <p class="text-muted">Please upload your payment screenshot after transferring to the owner's bank account.</p>
        </div>
    </div>
    {{Form::open(array('route'=>array('invoice.payment.store',$invoice_id),'method'=>'post','enctype' => "multipart/form-data"))}}
    <div class="modal-body">
        <div class="row">
            <div class="form-group  col-md-12">
                {{Form::label('payment_date',__('Payment Date'),array('class'=>'form-label'))}}
                {{Form::date('payment_date',date('Y-m-d'),array('class'=>'form-control'))}}
            </div>
            <div class="form-group  col-md-12">
                {{Form::label('amount',__('Amount'),array('class'=>'form-label'))}}
                {{Form::number('amount',$invoice->getInvoiceDueAmount(),array('class'=>'form-control'))}}
            </div>
            <div class="form-group  col-md-12">
                {{Form::label('receipt',__('Receipt'),array('class'=>'form-label'))}}
                {{Form::file('receipt',array('class'=>'form-control'))}}
            </div>
            <div class="form-group ">
                {{Form::label('notes',__('Notes'),array('class'=>'form-label'))}}
                {{Form::textarea('notes',null,array('class'=>'form-control','rows'=>3,'placeholder'=>__('Enter Payment Notes')))}}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        {{Form::submit(__('Add'),array('class'=>'btn btn-secondary btn-rounded'))}}
    </div>
    {{ Form::close() }}
@endif


