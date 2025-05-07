<div class="modal-body">
    <div class="product-card">
        <div class="row align-items-center">

            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{__('Property')}} :</b>
                     {{!empty($maintenanceRequest->properties)?$maintenanceRequest->properties->name:'-'}}
                </p>
            </div>
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{__('Unit')}} :</b>
                    {{!empty($maintenanceRequest->units)?$maintenanceRequest->units->name:'-'}}
                </p>
            </div>
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{__('Issue')}} :</b>
                    {{!empty($maintenanceRequest->types)?$maintenanceRequest->types->title:'-'}}
                </p>
            </div>
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{__('Maintainer')}} :</b>
                     {{!empty($maintenanceRequest->maintainers)?$maintenanceRequest->maintainers->name:'-'}}
                </p>
            </div>

            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{__('Request Date')}} :</b>
                    {{dateFormat($maintenanceRequest->request_date)}}
                </p>
            </div>
            @if(!empty($maintenanceRequest->fixed_date))
                <div class="col-6">
                    <p class="mb-1 mt-2">
                        <b>{{__('Fixed Date')}} :</b>
                         {{dateFormat($maintenanceRequest->fixed_date)}}
                    </p>
                </div>
            @endif
            @if($maintenanceRequest->amount!=0)
                <div class="col-6">
                    <p class="mb-1 mt-2">
                        <b>{{__('Amount')}} :</b>
                         {{priceFormat($maintenanceRequest->amount)}}
                    </p>
                </div>
            @endif
            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{__('Status')}} :</b>

                        @if($maintenanceRequest->status=='pending')
                            <span
                                class="badge bg-light-warning"> {{\App\Models\MaintenanceRequest::$status[$maintenanceRequest->status]}}</span>
                        @elseif($maintenanceRequest->status=='in_progress')
                            <span
                                class="badge bg-light-info"> {{\App\Models\MaintenanceRequest::$status[$maintenanceRequest->status]}}</span>
                        @else
                            <span
                                class="badge bg-light-primary"> {{\App\Models\MaintenanceRequest::$status[$maintenanceRequest->status]}}</span>
                        @endif

                </p>
            </div>

            @if(!empty($maintenanceRequest->invoice))
                <div class="col-6">
                    <p class="mb-1 mt-2">
                        <b>{{__('Invoice')}} :</b>

                            <a href="{{asset(Storage::url('upload/invoice')).'/'.$maintenanceRequest->invoice}}"
                               download="download"><i class="fa fa-download"></i></a>

                    </p>
                </div>
            @endif

            <div class="col-6">
                <p class="mb-1 mt-2">
                    <b>{{__('Attachment')}} :</b>

                        @if(!empty($maintenanceRequest->issue_attachment))
                            <a href="{{asset(Storage::url('upload/issue_attachment')).'/'.$maintenanceRequest->issue_attachment}} "
                               download="download"><i class="fa fa-download"></i></a>
                        @else
                            -
                        @endif

                </p>
            </div>
            <div class="col-12">
                <p class="mb-1 mt-2">
                    <b>{{__('Notes')}} :</b>
                    {{$maintenanceRequest->notes}}
                </p>
            </div>
        </div>
    </div>
</div>
