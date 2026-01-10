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
        


        if (document.getElementById('studentsByHoursChart') && typeof studentsByHoursData !== 'undefined' && typeof studentsByHoursValues !== 'undefined') {
            var studentsByHoursCtx = document.getElementById('studentsByHoursChart').getContext('2d');
            new Chart(studentsByHoursCtx, {
                type: 'bar',
                data: {
                    labels: studentsByHoursData,
                    datasets: [{
                        label: 'Number of Students',
                        data: studentsByHoursValues,
                        backgroundColor: ['#3386f7', '#00bcd4', '#4caf50', '#ff9800'],
                        borderColor: ['#3386f7', '#00bcd4', '#4caf50', '#ff9800'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }


    // ========================================
    // INCOME BY HOUR RANGE CHART
    // ========================================
    if (document.getElementById('incomeByHourRangeChart') && typeof incomeByHourRangeLabels !== 'undefined' && typeof incomeByHourRangeValues !== 'undefined') {
        var incomeByHourRangeCtx = document.getElementById('incomeByHourRangeChart').getContext('2d');
        new Chart(incomeByHourRangeCtx, {
            type: 'doughnut',
            data: {
                labels: incomeByHourRangeLabels,
                datasets: [{
                    label: 'Monthly Income (£)',
                    data: incomeByHourRangeValues,
                    backgroundColor: ['#3386f7', '#00bcd4', '#4caf50', '#ff9800'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': £' + context.parsed.toFixed(2);
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




       // ====================================================================
        // PARENT DASHBOARD CHARTS - DYNAMIC DATA
        // ====================================================================
        
        // Progress Trend Chart - DYNAMIC
        if (document.getElementById('progressTrendChart') && typeof progressTrendData !== 'undefined') {
            var ctx = document.getElementById('progressTrendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: progressTrendData.labels,  // ← FROM CONTROLLER
                    datasets: [{
                        label: 'Overall Average %',
                        data: progressTrendData.data,   // ← FROM CONTROLLER
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

        // Subject Comparison Chart - DYNAMIC
        if (document.getElementById('subjectComparisonChart') && typeof subjectComparisonData !== 'undefined') {
            var ctx2 = document.getElementById('subjectComparisonChart').getContext('2d');
            new Chart(ctx2, {
                type: 'radar',
                data: {
                    labels: subjectComparisonData.labels,  // ← FROM CONTROLLER
                    datasets: [{
                        label: (typeof childName !== 'undefined' ? childName : 'Student') + '\'s Scores',  // ← DYNAMIC NAME
                        data: subjectComparisonData.studentData,  // ← FROM CONTROLLER
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(75, 192, 192, 1)'
                    }, {
                        label: 'Class Average',
                        data: subjectComparisonData.classAverageData,  // ← FROM CONTROLLER
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