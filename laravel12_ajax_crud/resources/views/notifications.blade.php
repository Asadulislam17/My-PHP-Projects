<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script type="text/javascript">
    
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "4000",
        "preventDuplicates": true
    };

    
    @session("success")
        toastr.success("{{ $value }}", "Success");
    @endsession
  
    @session("info")
        toastr.info("{{ $value }}", "Info");
    @endsession
  
    @session("warning")
        toastr.warning("{{ $value }}", "Warning");
    @endsession

    @session("error")
        toastr.error("{{ $value }}", "Error");
    @endsession
</script>
