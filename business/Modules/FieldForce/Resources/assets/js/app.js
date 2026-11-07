if (response.success == true) {
    $('#visitForm').trigger('reset');
    $('#create_visit_modal').modal('hide');
    $('#calendar').fullCalendar('refetchEvents');
    toastr.success(response.msg);
} else {
    toastr.error(response.msg);
}
console.log(response);