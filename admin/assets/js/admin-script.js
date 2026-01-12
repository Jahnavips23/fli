/**
 * Flione IT - Admin Panel JavaScript
 * Version: 1.0
 */

(function($) {
    'use strict';
    
    // Toggle sidebar on mobile
    $('#sidebarCollapse').on('click', function() {
        $('.admin-wrapper').toggleClass('sidebar-active');
    });
    
    // Toggle sidebar on desktop
    $('#sidebarCollapseDesktop').on('click', function() {
        $('.admin-wrapper').toggleClass('sidebar-collapsed');
    });
    
    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if ($(window).width() < 992) {
            if (!$(e.target).closest('#sidebar, #sidebarCollapse').length) {
                $('.admin-wrapper').removeClass('sidebar-active');
            }
        }
    });
    
    // Prevent dropdown menus from closing when clicking inside
    $('.dropdown-menu').on('click', function(e) {
        if ($(this).hasClass('dropdown-menu-form')) {
            e.stopPropagation();
        }
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // File upload preview
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
        
        // Image preview
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Confirm delete
    $('.confirm-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').alert('close');
    }, 5000);
    
    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        var input = $($(this).attr('toggle'));
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).html('<i class="fas fa-eye-slash"></i>');
        } else {
            input.attr('type', 'password');
            $(this).html('<i class="fas fa-eye"></i>');
        }
    });
    
    // Slug generator
    $('#title').on('keyup', function() {
        var title = $(this).val();
        var slug = title.toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
        $('#slug').val(slug);
    });
    
    // Initialize DataTables
    if ($.fn.DataTable) {
        // Only initialize tables that haven't been initialized yet
        $('.datatable:not(.dt-initialized)').each(function() {
            // Destroy existing DataTable instance if it exists
            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().destroy();
            }
            
            // Initialize DataTable
            $(this).addClass('dt-initialized').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "<i class='fas fa-chevron-right'></i>",
                        previous: "<i class='fas fa-chevron-left'></i>"
                    }
                }
            });
        });
    }
    
    // Initialize Summernote
    if ($.fn.summernote) {
        // Only initialize textareas that haven't been initialized yet
        $('.summernote:not(.note-initialized)').each(function() {
            // Destroy existing Summernote instance if it exists
            if ($(this).hasClass('note-editor') || $(this).hasClass('note-frame') || $(this).next().hasClass('note-editor')) {
                $(this).summernote('destroy');
            }
            
            // Initialize Summernote
            $(this).addClass('note-initialized').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        for (var i = 0; i < files.length; i++) {
                            uploadSummernoteImage(files[i], this);
                        }
                    }
                }
            });
        });
    }
    
    // Upload image for Summernote
    function uploadSummernoteImage(file, editor) {
        var formData = new FormData();
        formData.append('file', file);
        
        $.ajax({
            url: 'upload-image.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                $(editor).summernote('insertImage', data.url);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error(textStatus + ': ' + errorThrown);
            }
        });
    }
    
    // Chart.js instance variable
    var visitorChart = null;
    
    // Function to load visitor data via AJAX
    function loadVisitorData(period) {
        // Show loading indicator
        $('#chartLoading').removeClass('d-none');
        
        $.ajax({
            url: ADMIN_URL + '/ajax/get-visitor-stats.php',
            method: 'GET',
            data: { period: period },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log('Loaded visitor data for period:', response.period);
                    console.log('Labels:', response.labels);
                    console.log('Data:', response.data);
                    
                    updateVisitorChart(response.labels, response.data);
                    
                    // Update dropdown button text
                    var periodText = 'This Year';
                    if (period === 'week') periodText = 'This Week';
                    if (period === 'month') periodText = 'This Month';
                    $('#chartDropdown').text(periodText);
                    
                    // Update active class in dropdown
                    $('.chart-period').removeClass('active');
                    $('.chart-period[data-period="' + period + '"]').addClass('active');
                } else {
                    console.error('Error loading visitor data:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load visitor data:', error);
            },
            complete: function() {
                // Hide loading indicator
                $('#chartLoading').addClass('d-none');
            }
        });
    }
    
    // Function to update the visitor chart with new data
    function updateVisitorChart(labels, data) {
        if (visitorChart) {
            visitorChart.data.labels = labels;
            visitorChart.data.datasets[0].data = data;
            visitorChart.update();
        }
    }
    
    // Handle chart period change
    $('.chart-period').on('click', function(e) {
        e.preventDefault();
        var period = $(this).data('period');
        loadVisitorData(period);
    });
    
    // Initialize Chart.js
    if (typeof Chart !== 'undefined' && document.getElementById('visitorChart')) {
        var chartElement = document.getElementById('visitorChart');
        var ctx = chartElement.getContext('2d');
        
        // Get actual visitor data from global variables set in the page
        var monthLabels = (typeof visitorChartLabels !== 'undefined') ? visitorChartLabels : [];
        var visitorData = (typeof visitorChartData !== 'undefined') ? visitorChartData : [];
        
        // Log the data to console for debugging
        console.log('Chart Labels:', monthLabels);
        console.log('Chart Data:', visitorData);
        
        // If no data is available, use default values
        if (!monthLabels.length || !visitorData.length) {
            console.log('Using default chart data');
            monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            visitorData = [45, 52, 58, 63, 68, 75, 82, 90, 97, 105, 112, 120];
        }
        
        visitorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Visitors',
                    backgroundColor: 'rgba(0, 86, 179, 0.1)',
                    borderColor: 'rgba(0, 86, 179, 1)',
                    data: visitorData,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                animation: {
                    duration: 1000, // Smoother animation
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toLocaleString() + ' visitors';
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 14
                        }
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: Math.max.apply(null, visitorData) * 1.1, // Dynamic max based on data
                        grid: {
                            drawBorder: false
                        },
                        ticks: {
                            // Format y-axis labels with commas for thousands
                            callback: function(value) {
                                return value.toLocaleString();
                            },
                            maxTicksLimit: 6 // Limit the number of ticks on y-axis
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
    
})(jQuery);