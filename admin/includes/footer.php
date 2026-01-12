            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Summernote -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Admin URL for AJAX calls -->
    <script>
        var ADMIN_URL = '<?php echo ADMIN_URL; ?>';
    </script>
    
    <!-- Custom Admin JS -->
    <script src="<?php echo ADMIN_ASSETS; ?>/js/admin-script.js"></script>
    
    <script>
        // This script handles any initialization that might not have been handled by admin-script.js
        $(document).ready(function() {
            // Check if any DataTables need initialization
            if ($.fn.DataTable && $('.datatable:not(.dt-initialized)').length > 0) {
                console.log('Initializing remaining DataTables from footer.php');
                $('.datatable:not(.dt-initialized)').each(function() {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).addClass('dt-initialized').DataTable({
                            responsive: true,
                            order: [[0, 'desc']]
                        });
                    }
                });
            }
            
            // Check if any Summernote editors need initialization
            if ($.fn.summernote && $('.summernote:not(.note-initialized)').length > 0) {
                console.log('Initializing remaining Summernote editors from footer.php');
                $('.summernote:not(.note-initialized)').each(function() {
                    if (!$(this).next().hasClass('note-editor')) {
                        $(this).addClass('note-initialized').summernote({
                            height: 300,
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'underline', 'clear']],
                                ['color', ['color']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['table', ['table']],
                                ['insert', ['link', 'picture']],
                                ['view', ['fullscreen', 'codeview', 'help']]
                            ]
                        });
                    }
                });
            }
            
            // Sidebar toggle (if not already handled)
            if ($('#sidebarCollapse').length > 0 && (!$._data || !$._data($('#sidebarCollapse')[0], 'events') || !$._data($('#sidebarCollapse')[0], 'events').click)) {
                $('#sidebarCollapse, #sidebarCollapseDesktop').on('click', function() {
                    $('.admin-wrapper').toggleClass('sidebar-collapsed');
                });
            }
            
            // Confirm delete (if not already handled)
            if ($('.confirm-delete').length > 0 && (!$._data || !$._data($('.confirm-delete')[0], 'events') || !$._data($('.confirm-delete')[0], 'events').click)) {
                $('.confirm-delete').on('click', function(e) {
                    if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>
</html>