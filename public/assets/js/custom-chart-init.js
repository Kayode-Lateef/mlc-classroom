$(document).ready(function() {
    // Only initialize if elements exist
    

                // Enrollment Trend Chart (Line Chart)
            if (document.getElementById('enrollmentTrendChart')) {
                var ctx = document.getElementById('enrollmentTrendChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['July', 'August', 'September', 'October', 'November', 'December'],
                        datasets: [{
                            label: 'Students Enrolled',
                            data: [980, 1045, 1098, 1150, 1198, 1245],
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }, {
                            label: 'Teachers Enrolled',
                            data: [65, 70, 73, 78, 82, 85],
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // User Distribution Chart (Pie Chart)
            if (document.getElementById('userDistributionChart')) {
                var ctx2 = document.getElementById('userDistributionChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['Students', 'Teachers', 'Admins', 'Staff'],
                        datasets: [{
                            data: [1245, 85, 12, 28],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(153, 102, 255, 0.8)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
            

            // Chartist Bar Chart for Attendance
            if (document.querySelector('.ct-bar-chart')) {
                new Chartist.Bar('.ct-bar-chart', {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    series: [[95, 92, 98, 94, 96, 88]]
                }, {
                    distributeSeries: true,
                    axisY: {
                        labelInterpolationFnc: function(value) {
                            return value + '%';
                        }
                    }
                });
            }
            
            // Chartist Pie Chart for Performance
            if (document.querySelector('.ct-pie-chart')) {
                new Chartist.Pie('.ct-pie-chart', {
                    series: [45, 30, 15, 10],
                    labels: ['Excellent', 'Good', 'Average', 'Below Average']
                }, {
                    donut: true,
                    donutWidth: 60,
                    startAngle: 270,
                    showLabel: true
                });
            }


                        // Class Performance Comparison Chart
            if (document.getElementById('classPerformanceChart')) {
                var ctx = document.getElementById('classPerformanceChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Grade 9A', 'Grade 9B', 'Grade 9C', 'Grade 10A', 'Grade 10B', 'Grade 11A', 'Grade 11B', 'Grade 12A'],
                        datasets: [{
                            label: 'Average Score (%)',
                            data: [85, 78, 82, 88, 75, 90, 86, 92],
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(255, 206, 86, 0.6)',
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(153, 102, 255, 0.6)',
                                'rgba(255, 159, 64, 0.6)',
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(255, 99, 132, 0.6)',
                                'rgba(54, 162, 235, 0.6)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            console.log('Super Admin Dashboard loaded successfully');
});