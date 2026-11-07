{{-- AFF-TEAM: everyone below this affiliate in the referral tree. Expects: $affiliate --}}
@php($aff_team = array_values(array_filter(app(\Modules\Superadmin\Utils\AffiliateUtil::class)->teamOf($affiliate), function ($t) { return $t['level'] == 1; }))) {{-- AFF-TEAM4: direct members only --}}
@php($aff_team_total = \Modules\Superadmin\Entities\AffiliateCommission::where('affiliate_user_id', $affiliate->user_id)->where('commission_type', 'team')->where('status', '!=', 'cancelled')->sum('commission_amount'))
@component('components.widget', ['title' => 'Team (referral tree)'])
    <p class="text-muted">
        Members: <b>{{ count($aff_team) }}</b> &nbsp;|&nbsp; Team commission earned:
        <span class="display_currency" data-currency_symbol="true" data-use_page_currency="true">{{ $aff_team_total }}</span>
    </p>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr><th>Member</th><th>Level</th><th>Joined under</th><th>Status</th>@if (!empty($team_admin))<th></th>@endif</tr> {{-- AFF-TEAM3 --}}
            </thead>
            <tbody>
                @forelse ($aff_team as $row)
                    <tr>
                        <td style="padding-left: {{ 8 + ($row['level'] - 1) * 20 }}px;">
                            {{ $row['level'] > 1 ? '└ ' : '' }}{{ optional($row['affiliate']->user)->user_full_name }}
                            <br><small class="text-muted">{{ optional($row['affiliate']->user)->email }}</small>
                        </td>
                        <td>{{ $row['level'] }}</td>
                        <td>{{ optional(\App\User::find($row['affiliate']->parent_affiliate_user_id))->user_full_name }}</td>
                        <td>{{ \Modules\Superadmin\Entities\Affiliate::statuses()[$row['affiliate']->status] ?? $row['affiliate']->status }}</td>
                        @if (!empty($team_admin))
                            <td>@if ($row['level'] == 1)
                                {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'removeTeamMember'], [$affiliate->id, $row['affiliate']->user_id]), 'method' => 'post', 'style' => 'display:inline', 'onsubmit' => "return confirm('Remove from this team?')"]) !!}
                                <button type="submit" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error">Remove</button>
                                {!! Form::close() !!}
                            @endif</td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ !empty($team_admin) ? 5 : 4 }}" class="text-center">No team members yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endcomponent
