    </div>
    <!-- /#content -->
</div>
<!-- /#wrapper -->

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
            if ($('#sidebar').css('margin-left') === '0px') {
               $('#sidebar').css('margin-left', '-250px');
            } else {
               $('#sidebar').css('margin-left', '0px');
            }
        });
    });
</script>

</body>
</html>
