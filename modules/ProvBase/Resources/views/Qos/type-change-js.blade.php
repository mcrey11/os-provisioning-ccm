
{{-- Reload when type field changes --}}
{{-- Cannot be done by hide/show because the custom fields may change --}}
<script language="javascript">

$('#type').change(function() {
    let url = new URL(location)
    let urlParams = new URLSearchParams(location.search)
    let selectfield = document.getElementById("type")

    urlParams.set('type', selectfield.options[selectfield.selectedIndex].value)
    url.search = urlParams.toString()

    location.href = url
});

</script>
