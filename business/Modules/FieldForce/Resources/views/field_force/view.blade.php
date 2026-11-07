<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'fieldforce::lang.visit_details' ) (#{{$visit->visit_id}})</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>@lang('report.contact'):</strong>
                    {{$visit->contact->full_name_with_business ?? $visit->visit_to}}
                </div>
                <div class="col-md-6 mb-5">
                    <strong>@lang('lang_v1.assigned_to'): </strong><br>
                    {!! $visit->user->user_full_name !!}
                </div>
                <div class="col-md-6">
                    @php
                    $address_string = !empty($visit->contact_id) ? urlencode(implode(', ', $visit->contact->contact_address_array)) : urlencode($visit->visit_address);
                    @endphp
                    <strong>@lang('business.address'): </strong> <a target="_blank" class="btn btn-primary btn-xs" href="https://maps.google.com/?q={{$address_string}}"><i class="fas fa-map-marker-alt"></i> @lang('fieldforce::lang.view_on_map')</a><br>
                    {!! $visit->contact->contact_address ?? $visit->visit_address !!}
                </div>
                <div class="col-md-6">
                    <strong>@lang('fieldforce::lang.visit_on'): </strong><br>
                    {{ @format_datetime($visit->visit_on) }}
                </div>
                <div class="col-md-6 mb-5">
                    <strong>@lang('fieldforce::lang.visit_for'): </strong><br>
                    {{ $visit->visit_for }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <strong>@lang('fieldforce::lang.visited_on'): </strong><br>
                    @if(!empty($visit->visited_on))
                    {{ @format_datetime($visit->visited_on) }}
                    @endif
                </div>
                <div class="col-md-6">
                    <strong>@lang('sale.status'): </strong>
                    <span class="label {{$visit_statuses[$visit->status]['class']}}">{{$visit_statuses[$visit->status]['label']}}</span>

                    @if($visit->status == 'did_not_meet_contact')
                    <p>
                        @if(!empty($visit->reason_to_not_meet_contact))
                        ({{$visit->reason_to_not_meet_contact}})
                        @endif
                    </p>
                    @endif
                </div>
                <div class="clearfix"></div>
                <div class="col-md-6">
                    <strong>@lang('fieldforce::lang.visited_address'): </strong>
                    @if(!empty($visit->visited_address_latitude) && !empty($visit->visited_address_longitude))
                    <a target="_blank" class="btn btn-primary btn-xs" href="https://maps.google.com/?q=loc:{{$visit->visited_address_latitude}}+{{$visit->visited_address_longitude}} "><i class="fas fa-map-marker-alt"></i> @lang('fieldforce::lang.view_on_map')</a>
                    @endif<br>
                    {{ $visit->visited_address }}
                </div>
                <div class="col-md-6">
                    <strong>@lang('fieldforce::lang.discussions_with_contact'): </strong><br>
                    {{ $visit->comments }}
                </div>
                <br><br>
                <div class="col-md-12">
                    <div class="form-group">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('fieldforce::lang.meet_with')</th>
                                    <th>@lang('fieldforce::lang.meet_with_mobileno')</th>
                                    <th>@lang('fieldforce::lang.designation')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($visit->meet_with) && !empty($visit->meet_with_mobileno) && !empty($visit->meet_with_designation))
                                <tr>
                                    <td>1</td>
                                    <td class="col-md-4" style="word-break: break-word;">{{ $visit->meet_with }}</td>
                                    <td class="col-md-4" style="word-break: break-word;">{{ $visit->meet_with_mobileno }}</td>
                                    <td class="col-md-3" style="word-break: break-word;">{{ $visit->meet_with_designation }}</td>
                                </tr>
                                @endif
                                @if(!empty($visit->meet_with2) && !empty($visit->meet_with_mobileno2) && !empty($visit->meet_with_designation2))
                                <tr>
                                    <td>2</td>
                                    <td class="col-md-4" style="word-break: break-word;">{{ $visit->meet_with2 }}</td>
                                    <td class="col-md-4" style="word-break: break-word;">{{ $visit->meet_with_mobileno2 }}</td>
                                    <td class="col-md-3" style="word-break: break-word;">{{ $visit->meet_with_designation2 }}</td>
                                </tr>
                                @endif
                                @if(!empty($visit->meet_with3) && !empty($visit->meet_with_mobileno3) && !empty($visit->meet_with_designation3))
                                <tr>
                                    <td>3</td>
                                    <td class="col-md-4" style="word-break: break-word;">{{ $visit->meet_with3 }}</td>
                                    <td class="col-md-4" style="word-break: break-word;">{{ $visit->meet_with_mobileno3 }}</td>
                                    <td class="col-md-3" style="word-break: break-word;">{{ $visit->meet_with_designation3 }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-6">
                    <strong>@lang('fieldforce::lang.photo'): </strong><br>
                    @if(!empty($visit->media->first()))
                    <img src="{{$visit->media->first()->display_url}}" alt="visit image" width="200px">
                    @endif
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
    </div>
</div>