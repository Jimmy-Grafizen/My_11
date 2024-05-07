'use strict';
$(document).ready(function() {


    function valincome(a, b, f) {
        if (f == null) {
            f = "rgba(0,0,0,0)";
        }
        return {
            labels: ["1", "2", "3", "4", "5"],
            datasets: [{
                label: "",
                borderColor: a,
                borderWidth: 0,
                hitRadius: 30,
                pointRadius: 0,
                pointHoverRadius: 4,
                pointBorderWidth: 2,
                pointHoverBorderWidth: 12,
                pointBackgroundColor: Chart.helpers.color("#000000").alpha(0).rgbString(),
                pointBorderColor: a,
                pointHoverBackgroundColor: a,
                pointHoverBorderColor: Chart.helpers.color("#000000").alpha(.1).rgbString(),
                fill: true,
                backgroundColor: Chart.helpers.color(f).alpha(1).rgbString(),
                data: b,
            }]
        };
    }

    function valincomebuildoption() {
        return {
            maintainAspectRatio: false,
            title: {
                display: false,
            },
            tooltips: {
                enabled: false,
            },
            legend: {
                display: false
            },
            hover: {
                mode: 'index'
            },
            scales: {
                xAxes: [{
                    display: false,
                    gridLines: false,
                    scaleLabel: {
                        display: true,
                        labelString: 'Month'
                    }
                }],
                yAxes: [{
                    display: false,
                    gridLines: false,
                    scaleLabel: {
                        display: true,
                        labelString: 'Value'
                    },
                    ticks: {
                        min: 1,
                    }
                }]
            },
            elements: {
                point: {
                    radius: 4,
                    borderWidth: 12
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 0,
                    top: 15,
                    bottom: 0
                }
            }
        };
    }
/*
    $(function() {
        var amchart = AmCharts.makeChart("sales-analytics", {
            "type": "serial",
            "theme": "light",
            "marginTop": 10,
            "marginRight": 0,
            "dataProvider": [{
                "day": "1",
                "value": -0.307
                
            }, {
                "day": "2",
                "value": 50.168
            }, {
                "day": "3",
                "value": -20.168
            }, {
                "day": "4",
                "value": -10.168
            }, {
                "day": "5",
                "value": 40.168
            },{
                "day": "6",
                "value": 0.47
            }],
            "valueAxes": [{
                "axisAlpha": 0,
                "gridAlpha": 0,
                "position": "left"
            }],
            "graphs": [{
                "id": "g1",
                "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                "bullet": "round",
                "bulletSize": 8,
                "lineColor": "#fe5d70",
                "lineThickness": 2,
                "negativeLineColor": "#fe9365",
                "type": "smoothedLine",
                "valueField": "value"
            }],
            "chartCursor": {
                "categoryBalloonDateFormat": "DD",
                "cursorAlpha": 0,
                "valueLineEnabled": true,
                "valueLineBalloonEnabled": true,
                "valueLineAlpha": 0.5,
                "fullWidth": true
            },
            "dataDateFormat": "DD",
            "categoryField": "day",
            "categoryAxis": {
                "minPeriod": "DD",
                "parseDates": true,
                "gridAlpha": 0,
                "minorGridAlpha": 0,
                "minorGridEnabled": true
            },
            "export": {
                "enabled": true
            }
        });
        amchart.addListener("rendered", zoomChart);
        if (amchart.zoomChart) {
            amchart.zoomChart();
        }

        function zoomChart() {
            amchart.zoomToIndexes(Math.round(amchart.dataProvider.length * 0.4), Math.round(amchart.dataProvider.length * 0.55));
        }
    });*/

    function amuntchart(a, b, f) {
        if (f == null) {
            f = "rgba(0,0,0,0)";
        }
        return {
            labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October"],
            datasets: [{
                label: "",
                borderColor: a,
                borderWidth: 2,
                hitRadius: 30,
                pointHoverRadius: 4,
                pointBorderWidth: 50,
                pointHoverBorderWidth: 12,
                pointBackgroundColor: Chart.helpers.color("#000000").alpha(0).rgbString(),
                pointBorderColor: Chart.helpers.color("#000000").alpha(0).rgbString(),
                pointHoverBackgroundColor: a,
                pointHoverBorderColor: Chart.helpers.color("#000000").alpha(.1).rgbString(),
                fill: true,
                backgroundColor: f,
                data: b,
            }]
        };
    }

    function buildchartoption() {
        return {
            maintainAspectRatio: false,
            title: {
                display: !1
            },
            tooltips: {
                enabled: false,
            },
            legend: {
                display: !1,
                labels: {
                    usePointStyle: !1
                }
            },
            responsive: !0,
            maintainAspectRatio: !0,
            hover: {
                mode: "index"
            },
            scales: {
                xAxes: [{
                    display: !1,
                    gridLines: !1,
                    scaleLabel: {
                        display: !0,
                        labelString: "Month"
                    }
                }],
                yAxes: [{
                    display: !1,
                    gridLines: !1,
                    scaleLabel: {
                        display: !0,
                        labelString: "Value"
                    },
                    ticks: {
                        beginAtZero: !0
                    }
                }]
            },
            elements: {
                point: {
                    radius: 4,
                    borderWidth: 12
                }
            },
            layout: {
                padding: {
                    left: 0,
                    right: 0,
                    top: 5,
                    bottom: 0
                }
            }
        };
    }
});
