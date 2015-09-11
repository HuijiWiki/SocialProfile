jQuery( document ).ready( function() {

    // Morris.Area({
    //     element: 'morris-area-chart',
    //     data: [{
    //         period: '2010 Q1',
    //         iphone: 2666,
    //         ipad: null,
    //         itouch: 2647
    //     }, {
    //         period: '2010 Q2',
    //         iphone: 2778,
    //         ipad: 2294,
    //         itouch: 2441
    //     }, {
    //         period: '2010 Q3',
    //         iphone: 4912,
    //         ipad: 1969,
    //         itouch: 2501
    //     }, {
    //         period: '2010 Q4',
    //         iphone: 3767,
    //         ipad: 3597,
    //         itouch: 5689
    //     }, {
    //         period: '2011 Q1',
    //         iphone: 6810,
    //         ipad: 1914,
    //         itouch: 2293
    //     }, {
    //         period: '2011 Q2',
    //         iphone: 5670,
    //         ipad: 4293,
    //         itouch: 1881
    //     }, {
    //         period: '2011 Q3',
    //         iphone: 4820,
    //         ipad: 3795,
    //         itouch: 1588
    //     }, {
    //         period: '2011 Q4',
    //         iphone: 15073,
    //         ipad: 5967,
    //         itouch: 5175
    //     }, {
    //         period: '2012 Q1',
    //         iphone: 10687,
    //         ipad: 4460,
    //         itouch: 2028
    //     }, {
    //         period: '2012 Q2',
    //         iphone: 8432,
    //         ipad: 5713,
    //         itouch: 1791
    //     }],
    //     xkey: 'period',
    //     ykeys: ['iphone', 'ipad', 'itouch'],
    //     labels: ['iPhone', 'iPad', 'iPod Touch'],
    //     pointSize: 2,
    //     hideHover: 'auto',
    //     resize: true
    // });

    // Morris.Donut({
    //     element: 'morris-donut-chart',
    //     data: [{
    //         label: "Download Sales",
    //         value: 12
    //     }, {
    //         label: "In-Store Sales",
    //         value: 30
    //     }, {
    //         label: "Mail-Order Sales",
    //         value: 20
    //     }],
    //     resize: true
    // });

    // Morris.Bar({
    //     element: 'morris-bar-chart',
    //     data: [{
    //         y: '2006',
    //         a: 100,
    //         b: 90
    //     }, {
    //         y: '2007',
    //         a: 75,
    //         b: 65
    //     }, {
    //         y: '2008',
    //         a: 50,
    //         b: 40
    //     }, {
    //         y: '2009',
    //         a: 75,
    //         b: 65
    //     }, {
    //         y: '2010',
    //         a: 50,
    //         b: 40
    //     }, {
    //         y: '2011',
    //         a: 75,
    //         b: 65
    //     }, {
    //         y: '2012',
    //         a: 100,
    //         b: 90
    //     }],
    //     xkey: 'y',
    //     ykeys: ['a', 'b'],
    //     labels: ['Series A', 'Series B'],
    //     hideHover: 'auto',
    //     resize: true
    // });
 // 路径配置
        require.config({
            paths: {
                echarts: 'http://echarts.baidu.com/build/dist'
            }
        });
        
        // use
        require(
            [
                'echarts',
                'echarts/chart/line', // 使用柱状图就加载bar模块，按需加载
                'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts图表
                var  myChart = ec.init(document.getElementById('morris-area-echart')); 
                // console.log(getViewRecordsFromUserIdGroupByWikiSite(-1,'',''));
                var  option = {
                        tooltip : {
                            trigger: 'axis'
                        },
                        legend: {
                            data:['网站排名','关注人数','浏览次数','编辑次数']
                        },
                        toolbox: {
                            show : true,
                            feature : {
                                mark : {show: false},
                                dataView : {show: true, readOnly: true},
                                magicType : {show: true, type: ['line', 'bar', 'tiled']},
                                // magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                                restore : {show: true},
                                saveAsImage : {show: true}
                            }
                        },
                        calculable : true,
                        xAxis : [
                            {
                                type : 'category',
                                boundaryGap : false,
                                data : ['周一','周二','周三','周四','周五','周六','周日','a','b','c','d']
                            }
                        ],
                        yAxis : [
                            {
                                type : 'value',
                                // min: 0,
                                // max: 1000,
                                // splitNumber: 500
                                // scale : true,
                                // splitNumber : 0,100
                                // show : false
                            }
                        ],
                        series : [
                            {
                                name:'网站排名',
                                type:'line',
                                // stack: '总量',
                                // color:'red',
                                itemStyle:{
                                    normal:{
                                        lineStyle:{
                                            color:'black',
                                            width:3
                                        }
                                    }
                                },
                                data:[100, 13, 10, 13, 90, 30, 21,12,22,33,44],
                                // data: getViewRecordsFromUserIdGroupByWikiSite(-1,'',''),

                                // 系列中的数据标注内容 series.markPoint  
                                markPoint:{  
                                    data:[  
                                        {type:'max',name:'最大值'},  
                                        {type:'min',name:'最小值'}  
                                    ]  
                                },  
                                //系列中的数据标线内容 series.markLine  
                                markLine:{  
                                    data:[  
                                        {type:'average',name:'平均值'}  
                                    ]  
                                }  
                            },
                            {
                                name:'关注人数',
                                type:'line',
                                // stack: '总量',
                                data:[200, 182, 191, 234, 290, 330, 310,222,111,333,223],
                                // 系列中的数据标注内容 series.markPoint  
                                markPoint:{  
                                    data:[  
                                        {type:'max',name:'最大值'},  
                                        {type:'min',name:'最小值'}  
                                    ]  
                                },  
                                //系列中的数据标线内容 series.markLine  
                                markLine:{  
                                    data:[  
                                        {type:'average',name:'平均值'}  
                                    ]  
                                }
                            },
                            {
                                name:'浏览次数',
                                type:'line',
                                // stack: '总量',
                                data:[300, 232, 201, 154, 190, 330, 410,234,555,433,233],
                                //系列中的数据标注内容 series.markPoint  
                                markPoint:{  
                                    data:[  
                                        {type:'max',name:'最大值'},  
                                        {type:'min',name:'最小值'}  
                                    ]  
                                },  
                                //系列中的数据标线内容 series.markLine  
                                markLine:{  
                                    data:[  
                                        {type:'average',name:'平均值'}  
                                    ]  
                                }
                            },
                            {
                                name:'编辑次数',
                                type:'line',
                                // stack: '总量',
                                data:[500, 332, 301, 334, 390, 330, 320,123,321,432,433],
                                //系列中的数据标注内容 series.markPoint  
                                markPoint:{  
                                    data:[  
                                        {type:'max',name:'最大值'},  
                                        {type:'min',name:'最小值'}  
                                    ]  
                                },  
                                //系列中的数据标线内容 series.markLine  
                                markLine:{  
                                    data:[  
                                        {type:'average',name:'平均值'}  
                                    ]  
                                }
                            }
                        ]
                    };
                // };
                // 为echarts对象加载数据 
                myChart.setOption(option); 
            }
        );
});
