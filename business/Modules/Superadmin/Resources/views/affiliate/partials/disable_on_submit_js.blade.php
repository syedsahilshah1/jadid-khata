{{--
    Disables the submit button of any form marked .disable-on-submit, so a
    double click cannot post it twice.

    Forms that run jQuery validation do this from their own submitHandler
    instead, because the button must only be disabled once the form is valid.
--}}
<script type="text/javascript">
    $(document).ready(function() {
        $('form.disable-on-submit').on('submit', function() {
            $(this).find('input[type="submit"], button[type="submit"]').prop('disabled', true);
        });
    });
</script>
