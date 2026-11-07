<table class="table table-bordered table-striped">
    <thead>
        <tr class="bg-light-green">
            <th rowspan="2">@lang('hms::lang.rooms')</th>
            <th colspan="4">@lang('hms::lang.booking_received')</th>
            <th colspan="4">@lang('hms::lang.booking_confirmed')</th>
            <th colspan="4">@lang('hms::lang.booking_cancelled')</th>
            <th colspan="4">@lang('hms::lang.booking_pending')</th>
        </tr>
        <tr>
            <th class="bg-info">@lang('hms::lang.booked')</th>
            <th class="bg-info">@lang('hms::lang.guests')</th>
            <th class="bg-info">@lang('hms::lang.nights')</th>
            <th class="bg-info">@lang('hms::lang.amount')</th>
            <th class="bg-success">@lang('hms::lang.booked')</th>
            <th class="bg-success">@lang('hms::lang.guests')</th>
            <th class="bg-success">@lang('hms::lang.nights')</th>
            <th class="bg-success">@lang('hms::lang.amount')</th>
            <th class="bg-danger">@lang('hms::lang.booked')</th>
            <th class="bg-danger">@lang('hms::lang.guests')</th>
            <th class="bg-danger">@lang('hms::lang.nights')</th>
            <th class="bg-danger">@lang('hms::lang.amount')</th>
            <th class="bg-yellow">@lang('hms::lang.booked')</th>
            <th class="bg-yellow">@lang('hms::lang.guests')</th>
            <th class="bg-yellow">@lang('hms::lang.nights')</th>
            <th class="bg-yellow">@lang('hms::lang.amount')</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($all_room_types as $index => $all_room_type)
            <tr>
                <td>{{ $all_room_type->type }}</td>
                <td>{{ $all_room_type->transactions_count }}</td>
                <td>{{ $all_room_type->no_of_guest ?? 0 }}</td>

                @if ($all_room_type->transactions_count != 0)
                    <td>
                        {{ $all_room_type->total_days == 0 ? 1 : $all_room_type->total_days }}
                    </td>
                @else
                    <td>0</td>
                @endif

                <td class="display_currency" data-currency_symbol="true">{{ $all_room_type->total_price }}</td>

                @if (isset($confirmed_room_types[$index]))
                    <td>{{ $confirmed_room_types[$index]->transactions_count }}</td>
                @endif
                @if (isset($confirmed_room_types[$index]))
                    <td>{{ $confirmed_room_types[$index]->no_of_guest ?? 0 }}</td>
                @endif
                @if (isset($confirmed_room_types[$index]))
                    @if ($confirmed_room_types[$index]->transactions_count != 0)
                        <td>
                            {{ $confirmed_room_types[$index]->total_days == 0 ? 1 : $confirmed_room_types[$index]->total_days }}
                        </td>
                    @else
                        <td>0</td>
                    @endif
                @endif
                @if (isset($confirmed_room_types[$index]))
                    <td class="display_currency" data-currency_symbol="true">
                        {{ $confirmed_room_types[$index]->total_price }}
                    </td>
                @endif

                @if (isset($cancelled_room_types[$index]))
                    <td>{{ $cancelled_room_types[$index]->transactions_count ?? 0 }}</td>
                @endif
                @if (isset($cancelled_room_types[$index]))
                    <td>{{ $cancelled_room_types[$index]->no_of_guest ?? 0 }}</td>
                @endif
                @if (isset($cancelled_room_types[$index]))
                    @if ($cancelled_room_types[$index]->transactions_count != 0)
                        <td>
                            {{ $cancelled_room_types[$index]->total_days == 0 ? 1 : $cancelled_room_types[$index]->total_days }}
                        </td>
                    @else
                        <td>0</td>
                    @endif
                @endif
                @if (isset($cancelled_room_types[$index]))
                    <td class="display_currency" data-currency_symbol="true">
                        {{ $cancelled_room_types[$index]->total_price }}
                    </td>
                @endif

                @if (isset($pending_room_types[$index]))
                    <td>{{ $pending_room_types[$index]->transactions_count ?? 0 }}</td>
                @endif
                @if (isset($pending_room_types[$index]))
                    <td>{{ $pending_room_types[$index]->no_of_guest ?? 0 }}</td>
                @endif
                @if (isset($pending_room_types[$index]))
                    @if ($pending_room_types[$index]->transactions_count != 0)
                        <td>
                            {{ $pending_room_types[$index]->total_days == 0 ? 1 : $pending_room_types[$index]->total_days }}
                        </td>
                    @else
                        <td>0</td>
                    @endif
                @endif
                @if (isset($pending_room_types[$index]))
                    <td class="display_currency" data-currency_symbol="true">
                        {{ $pending_room_types[$index]->total_price }}
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
