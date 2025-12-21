$(document).ready(function() {
    // Only initialize if elements exist
    
    
        // ====================================================================
        // Super Admin dashboard charts
        // ====================================================================
        
        // Enrollment Trend Chart (Line Chart) - DYNAMIC DATA
        if (document.getElementById('enrollmentTrendChart') && typeof enrollmentChartData !== 'undefined') {
            var ctx = document.getElementById('enrollmentTrendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: enrollmentChartData.labels,
                    datasets: [{
                        label: 'Students Enrolled',
                        data: enrollmentChartData.students,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Teachers Enrolled',
                        data: enrollmentChartData.teachers,
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
        
        // User Distribution Chart (Doughnut Chart) - DYNAMIC DATA
        if (document.getElementById('userDistributionChart') && typeof userDistributionData !== 'undefined') {
            var ctx2 = document.getElementById('userDistributionChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: userDistributionData.labels,
                    datasets: [{
                        data: userDistributionData.data,
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
        

        // Chartist Bar Chart for Attendance - DYNAMIC DATA
        if (document.querySelector('.ct-bar-chart') && typeof weeklyAttendanceData !== 'undefined') {
            new Chartist.Bar('.ct-bar-chart', {
                labels: weeklyAttendanceData.labels,
                series: [weeklyAttendanceData.data]
            }, {
                distributeSeries: true,
                axisY: {
                    labelInterpolationFnc: function(value) {
                        return value + '%';
                    }
                }
            });
        }
        
        // Chartist Pie Chart for Performance - STATIC (can be made dynamic later)
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


        // Class Performance Comparison Chart - STATIC (can be made dynamic by querying class averages)
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
        // Super Admin dashboard charts Ends


            // ====================================================================
            // ADMIN DASHBOARD CHARTS - DYNAMIC DATA
            // ====================================================================
            
            // Weekly Attendance Trend Chart - DYNAMIC
            if (document.getElementById('weeklyAttendanceChart') && typeof weeklyAttendanceChartData !== 'undefined') {
                var ctx = document.getElementById('weeklyAttendanceChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: weeklyAttendanceChartData.labels,  // From controller
                        datasets: [{
                            label: 'Attendance %',
                            data: weeklyAttendanceChartData.data,   // From controller
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
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
            
            // Class-wise Attendance Chart (Bar) - DYNAMIC
            if (document.getElementById('classwiseAttendanceChart') && typeof classwiseAttendanceData !== 'undefined') {
                var ctx2 = document.getElementById('classwiseAttendanceChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: classwiseAttendanceData.labels,  // From controller
                        datasets: [{
                            label: 'Attendance %',
                            data: classwiseAttendanceData.data,   // From controller
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.6)',
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(255, 206, 86, 0.6)',
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(153, 102, 255, 0.6)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(153, 102, 255, 1)'
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
            // Admin dashboard charts Ends
        

            // ====================================================================
            // TEACHER DASHBOARD CHARTS - DYNAMIC DATA
            // ====================================================================
            
            // Class Performance Chart - DYNAMIC
            if (document.getElementById('classPerformanceChart') && typeof classPerformanceData !== 'undefined') {
                var ctx = document.getElementById('classPerformanceChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: classPerformanceData.labels,  // From controller
                        datasets: [{
                            label: 'Average Score %',
                            data: classPerformanceData.data,   // From controller
                            backgroundColor: [
                                'rgba(255, 159, 64, 0.6)',
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(255, 206, 86, 0.6)',
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(153, 102, 255, 0.6)'
                            ],
                            borderColor: [
                                'rgba(255, 159, 64, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(153, 102, 255, 1)'
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
        
            // Attendance Trend Chart - DYNAMIC
            if (document.getElementById('attendanceTrendChart') && typeof attendanceTrendData !== 'undefined') {
                var ctx2 = document.getElementById('attendanceTrendChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'line',
                    data: {
                        labels: attendanceTrendData.labels,  // From controller
                        datasets: [{
                            label: 'Overall Attendance %',
                            data: attendanceTrendData.data,   // From controller
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
            // Teacher dashboard charts Ends




        // Parent dashboard charts
        // Progress Trend Chart
        if (document.getElementById('progressTrendChart')) {
            var ctx = document.getElementById('progressTrendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['September', 'October', 'November', 'December'],
                    datasets: [{
                        label: 'Overall Average %',
                        data: [82, 85, 87, 88],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
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

        // Subject Comparison Chart
        if (document.getElementById('subjectComparisonChart')) {
            var ctx2 = document.getElementById('subjectComparisonChart').getContext('2d');
            new Chart(ctx2, {
                type: 'radar',
                data: {
                    labels: ['Mathematics', 'Physics', 'English', 'Chemistry', 'History'],
                    datasets: [{
                        label: 'Sarah\'s Scores',
                        data: [92, 88, 78, 90, 85],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(75, 192, 192, 1)'
                    }, {
                        label: 'Class Average',
                        data: [85, 82, 80, 84, 83],
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(255, 206, 86, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(255, 206, 86, 1)'
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
                        r: {
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
        // Parent dashboard charts Ends
        

});