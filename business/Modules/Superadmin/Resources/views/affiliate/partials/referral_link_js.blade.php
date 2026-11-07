{{--
    Copy-to-clipboard behaviour for the referral link field.
    Include from a page's @section('javascript'), after jQuery is available.
--}}
<script type="text/javascript">
    $(document).ready(function() {
        $('#copy_referral_link').click(function() {
            var input = document.getElementById('referral_link');

            if (!input) {
                return;
            }

            //Works on readonly inputs and is what the fallback below copies from.
            input.select();
            input.setSelectionRange(0, 99999); //Needed on mobile safari.

            var done = function() {
                document.getSelection().removeAllRanges();
                input.blur();
                toastr.success("{{ __('superadmin::lang.referral_link_copied') }}");
            };

            /*
             * navigator.clipboard only exists in a secure context, so an app
             * served over plain http on a LAN falls back to execCommand.
             */
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value).then(done, function() {
                    document.execCommand('copy');
                    done();
                });
            } else {
                document.execCommand('copy');
                done();
            }
        });
    });
</script>
