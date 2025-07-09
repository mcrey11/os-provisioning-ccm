
{{-- Reload when device field changes --}}
{{-- Cannot be done by hide/show because the custom fields may change --}}
<script language="javascript">

$('#device').change(function() {
    let url = new URL(location)
    let urlParams = new URLSearchParams(location.search)
    let selectfield = document.getElementById("device")

    urlParams.set('device', selectfield.options[selectfield.selectedIndex].value)
    url.search = urlParams.toString()

    location.href = url
});

</script>
