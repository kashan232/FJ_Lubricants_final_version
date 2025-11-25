<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ env('APP_URL') }}public/assets/js/jquery-3.6.0.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/feather.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/jquery.slimscroll.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/jquery.dataTables.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/bootstrap.bundle.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/plugins/apexchart/apexcharts.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/plugins/apexchart/chart-data.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

     $(document).ready(function() {
        $('.search').select2({
            placeholder: "Searh Or Select",
            allowClear: true,
            width: '100%'  // Bootstrap ke sath fit karne ke liye
        });
    });
    
    document.addEventListener("DOMContentLoaded", function() {
        let globalLoader = document.getElementById("global-loader");
        if (globalLoader) {
            globalLoader.style.display = "none";
        }
    });
</script>
</body>
</html>
